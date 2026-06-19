import os
import logging
import tempfile
from pathlib import Path

import cv2
from fastapi import FastAPI, File, HTTPException, UploadFile


app = FastAPI(title="Pembukuan OCR Service")
logger = logging.getLogger("ocr_service")


def env_int(name: str, default: int) -> int:
    try:
        return int(os.getenv(name, str(default)))
    except ValueError:
        return default


def resize_for_ocr(source_path: str) -> str:
    max_dim = max(320, env_int("OCR_MAX_IMAGE_DIM", 960))
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


@app.get("/health")
def health():
    return {"ok": True}


@app.post("/scan")
async def scan(file: UploadFile = File(...)):
    suffix = Path(file.filename or "nota.jpg").suffix.lower()
    if suffix not in {".jpg", ".jpeg", ".png"}:
        raise HTTPException(status_code=422, detail="Format file harus jpg, jpeg, atau png.")

    max_upload_bytes = max(1, env_int("OCR_MAX_UPLOAD_MB", 6)) * 1024 * 1024
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

        from ocr.scan_nota import infer

        return infer(image_path)
    except HTTPException:
        raise
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
