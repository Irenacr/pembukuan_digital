import os
import asyncio
import logging
import tempfile
import time
from pathlib import Path

import cv2
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


def env_bool(name: str, default: bool = False) -> bool:
    value = os.getenv(name)

    if value is None:
        return default

    return value.strip().lower() in {"1", "true", "yes", "on"}


scan_semaphore = asyncio.Semaphore(max(1, env_int("OCR_MAX_CONCURRENT_SCANS", 1)))


def resize_for_ocr(source_path: str) -> str:
    max_dim = max(320, env_int("OCR_MAX_IMAGE_DIM", 736))
    image = cv2.imread(source_path)

    if image is None:
        raise ValueError("File gambar tidak bisa dibaca.")

    height, width = image.shape[:2]
    longest = max(height, width)

    if longest <= max_dim:
        return source_path

    scale = max_dim / longest
    resized = cv2.resize(
        image,
        (int(width * scale), int(height * scale)),
        interpolation=cv2.INTER_AREA,
    )

    resized_path = str(Path(source_path).with_suffix(".resized.jpg"))
    cv2.imwrite(resized_path, resized, [int(cv2.IMWRITE_JPEG_QUALITY), 90])

    return resized_path


def warmup_models() -> None:
    warmup_status.update({
        "state": "loading",
        "error": None,
        "started_at": time.time(),
        "finished_at": None,
    })

    try:
        from ocr.scan_nota import ensure_models_loaded

        ensure_models_loaded()
        warmup_status.update({
            "state": "ready",
            "error": None,
            "finished_at": time.time(),
        })
    except Exception as exc:
        logger.exception("OCR model warmup failed")
        warmup_status.update({
            "state": "failed",
            "error": str(exc),
            "finished_at": time.time(),
        })


@app.on_event("startup")
async def startup() -> None:
    if env_bool("OCR_WARMUP_ON_START", False):
        asyncio.create_task(asyncio.to_thread(warmup_models))


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

    max_upload_bytes = max(1, env_int("OCR_MAX_UPLOAD_MB", 4)) * 1024 * 1024
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

        scan_timeout = max(10, env_int("OCR_SCAN_TIMEOUT", 90))

        from ocr.scan_nota import infer

        try:
            await asyncio.wait_for(scan_semaphore.acquire(), timeout=1)
        except asyncio.TimeoutError as exc:
            raise HTTPException(
                status_code=429,
                detail="OCR service sedang memproses nota lain. Coba ulangi beberapa detik lagi.",
            ) from exc

        try:
            return await asyncio.wait_for(
                asyncio.to_thread(infer, image_path),
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
    except MemoryError as exc:
        raise HTTPException(
            status_code=503,
            detail="OCR service kehabisan memori. Turunkan ukuran gambar OCR atau naikkan memory service di Railway.",
        ) from exc
    except RuntimeError as exc:
        if "sedang memproses nota lain" in str(exc):
            raise HTTPException(status_code=429, detail=str(exc)) from exc

        logger.exception("OCR scan runtime failed")
        raise HTTPException(status_code=500, detail="OCR service gagal memproses nota.") from exc
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
