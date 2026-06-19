import os
import asyncio
import json
import logging
import sys
import tempfile
import time
from pathlib import Path

from PIL import Image
from fastapi import FastAPI, File, HTTPException, UploadFile


app = FastAPI(title="Pembukuan OCR Service")
logger = logging.getLogger("ocr_service")
startup_started_at = time.time()
warmup_status = {
    "state": "disabled",
    "error": None,
    "started_at": None,
    "finished_at": None,
}


def env_int(name: str, default: int) -> int:
    try:
        return int(os.getenv(name, str(default)))
    except ValueError:
        return default


def capped_env_int(name: str, default: int, minimum: int, maximum: int) -> int:
    return max(minimum, min(maximum, env_int(name, default)))


def env_bool(name: str, default: bool = False) -> bool:
    value = os.getenv(name)

    if value is None:
        return default

    return value.strip().lower() in {"1", "true", "yes", "on"}


scan_semaphore = asyncio.Semaphore(max(1, env_int("OCR_MAX_CONCURRENT_SCANS", 1)))


def resize_for_ocr(source_path: str) -> str:
    max_dim = capped_env_int("OCR_MAX_IMAGE_DIM", 640, 320, 736)

    try:
        image = Image.open(source_path)
        image.load()
    except Exception as exc:
        raise ValueError("File gambar tidak bisa dibaca.") from exc

    image = image.convert("RGB")
    width, height = image.size
    longest = max(height, width)

    if longest <= max_dim:
        return source_path

    scale = max_dim / longest
    resized = image.resize((int(width * scale), int(height * scale)), Image.Resampling.LANCZOS)

    resized_path = str(Path(source_path).with_suffix(".resized.jpg"))
    resized.save(resized_path, format="JPEG", quality=85, optimize=True)

    return resized_path


def build_ocr_env() -> dict:
    child_env = os.environ.copy()
    child_env.update({
        "PYTHONUNBUFFERED": "1",
        "PYTHONIOENCODING": "utf-8",
        "OMP_NUM_THREADS": "1",
        "MKL_NUM_THREADS": "1",
        "OPENBLAS_NUM_THREADS": "1",
        "NUMEXPR_NUM_THREADS": "1",
        "MALLOC_ARENA_MAX": "2",
        "OMP_WAIT_POLICY": "PASSIVE",
        "OCR_YOLO_BACKEND": "onnxruntime",
        "OCR_LOW_MEMORY_MODE": "true",
        "OCR_RELEASE_OCR_AFTER_SCAN": "true",
        "OCR_MAX_IMAGE_DIM": str(capped_env_int("OCR_MAX_IMAGE_DIM", 640, 320, 736)),
        "OCR_YOLO_IMGSZ": str(capped_env_int("OCR_YOLO_IMGSZ", 384, 320, 416)),
        "OCR_YOLO_CONF": os.environ.get("OCR_YOLO_CONF", "0.35"),
        "OCR_YOLO_MAX_DET": str(capped_env_int("OCR_YOLO_MAX_DET", 8, 1, 10)),
        "OCR_CROP_MAX_DET": str(capped_env_int("OCR_CROP_MAX_DET", 5, 1, 6)),
        "OCR_MAX_CROP_PIXELS": str(capped_env_int("OCR_MAX_CROP_PIXELS", 90000, 20000, 120000)),
        "OCR_ORT_THREADS": "1",
        "OCR_CV2_THREADS": "1",
    })

    return child_env


def run_ocr_subprocess(image_path: str, timeout: int) -> dict:
    import subprocess

    try:
        process = subprocess.run(
            [sys.executable, "-m", "ocr.scan_nota", image_path],
            cwd=str(Path(__file__).resolve().parents[1]),
            env=build_ocr_env(),
            capture_output=True,
            text=True,
            timeout=timeout,
        )
    except subprocess.TimeoutExpired as exc:
        raise TimeoutError("OCR subprocess melewati batas waktu.") from exc

    if process.returncode != 0:
        logger.error("OCR subprocess failed", extra={
            "returncode": process.returncode,
            "stderr": process.stderr[-2000:],
        })

        if process.returncode in {-9, 137}:
            raise MemoryError("OCR subprocess kehabisan memori.")

        error_text = process.stderr.strip() or process.stdout.strip() or "OCR subprocess gagal."
        raise RuntimeError(error_text[-1500:])

    stdout_lines = [line.strip() for line in process.stdout.splitlines() if line.strip()]
    json_payload = stdout_lines[-1] if stdout_lines else ""

    try:
        result = json.loads(json_payload)
    except json.JSONDecodeError as exc:
        logger.error("OCR subprocess returned invalid JSON", extra={
            "stdout": process.stdout[-2000:],
            "stderr": process.stderr[-2000:],
        })
        raise RuntimeError("OCR subprocess memberi JSON tidak valid.") from exc

    if not isinstance(result, dict):
        raise RuntimeError("OCR subprocess memberi response JSON tidak valid.")

    return result


@app.on_event("startup")
async def startup() -> None:
    if env_bool("OCR_WARMUP_ON_START", False) and not env_bool("OCR_LOW_MEMORY_MODE", True):
        warmup_status.update({
            "state": "disabled",
            "error": "Warmup dimatikan untuk mencegah OOM di Railway low-memory mode.",
            "started_at": None,
            "finished_at": None,
        })


@app.get("/health")
def health():
    return {"ok": True}


@app.get("/ready")
def ready():
    return {
        "ok": warmup_status["state"] in {"ready", "disabled"},
        "warmup": warmup_status,
        "uptime_seconds": round(time.time() - startup_started_at, 3),
    }


@app.post("/scan")
async def scan(file: UploadFile = File(...)):
    suffix = Path(file.filename or "nota.jpg").suffix.lower()
    if suffix not in {".jpg", ".jpeg", ".png"}:
        raise HTTPException(status_code=422, detail="Format file harus jpg, jpeg, atau png.")

    max_upload_bytes = capped_env_int("OCR_MAX_UPLOAD_MB", 3, 1, 4) * 1024 * 1024
    original_path = None
    image_path = None

    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as temp_file:
            uploaded_bytes = 0
            while True:
                chunk = await file.read(1024 * 1024)
                if not chunk:
                    break

                uploaded_bytes += len(chunk)
                if uploaded_bytes > max_upload_bytes:
                    raise HTTPException(
                        status_code=413,
                        detail=f"Ukuran file maksimal {max_upload_bytes // (1024 * 1024)} MB.",
                    )

                temp_file.write(chunk)

            original_path = temp_file.name

        image_path = resize_for_ocr(original_path)

        scan_timeout = capped_env_int("OCR_SCAN_TIMEOUT", 75, 20, 90)

        try:
            await asyncio.wait_for(scan_semaphore.acquire(), timeout=1)
        except asyncio.TimeoutError as exc:
            raise HTTPException(
                status_code=429,
                detail="OCR service sedang memproses nota lain. Coba ulangi beberapa detik lagi.",
            ) from exc

        try:
            return await asyncio.wait_for(
                asyncio.to_thread(run_ocr_subprocess, image_path, scan_timeout),
                timeout=scan_timeout,
            )
        finally:
            scan_semaphore.release()
    except HTTPException:
        raise
    except asyncio.TimeoutError as exc:
        raise HTTPException(
            status_code=504,
            detail="OCR service melewati batas waktu internal. Coba ulangi dengan foto lebih kecil/jelas.",
        ) from exc
    except TimeoutError as exc:
        raise HTTPException(
            status_code=504,
            detail="OCR service melewati batas waktu internal. Coba ulangi dengan foto lebih kecil/jelas.",
        ) from exc
    except MemoryError as exc:
        raise HTTPException(
            status_code=503,
            detail="OCR service kehabisan memori. Turunkan ukuran gambar OCR atau naikkan memory service di Railway.",
        ) from exc
    except RuntimeError as exc:
        if "sedang memproses nota lain" in str(exc):
            raise HTTPException(status_code=429, detail=str(exc)) from exc

        logger.exception("OCR scan runtime failed")
        detail = str(exc).strip() or "OCR service gagal memproses nota."
        raise HTTPException(status_code=500, detail=detail[-800:]) from exc
    except ValueError as exc:
        raise HTTPException(status_code=422, detail=str(exc)) from exc
    except Exception as exc:
        logger.exception("OCR scan failed")
        raise HTTPException(status_code=500, detail="OCR service gagal memproses nota.") from exc
    finally:
        await file.close()
        for path in {original_path, image_path}:
            if path and os.path.exists(path):
                try:
                    os.remove(path)
                except OSError:
                    pass
