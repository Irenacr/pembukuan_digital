import os
import tempfile
from pathlib import Path

import cv2
from fastapi import FastAPI, File, HTTPException, UploadFile

from ocr.scan_nota import infer


app = FastAPI(title="Pembukuan OCR Service")


def resize_for_ocr(source_path: str) -> str:
    max_dim = int(os.getenv("OCR_MAX_IMAGE_DIM", "960"))
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

    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as temp_file:
            temp_file.write(await file.read())
            original_path = temp_file.name

        image_path = resize_for_ocr(original_path)
        return infer(image_path)
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc
    finally:
        for path in {locals().get("original_path"), locals().get("image_path")}:
            if path and os.path.exists(path):
                try:
                    os.remove(path)
                except OSError:
                    pass
