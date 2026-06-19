FROM python:3.12-slim

ENV PYTHONUNBUFFERED=1 \
    OCR_MAX_IMAGE_DIM=960 \
    OCR_YOLO_IMGSZ=512 \
    OCR_YOLO_CONF=0.25 \
    OCR_YOLO_MAX_DET=24 \
    OCR_CROP_MAX_DET=20 \
    OCR_MIN_CROP_AREA=80 \
    OCR_YOLO_DEVICE=cpu \
    OCR_TORCH_THREADS=1 \
    OCR_CV2_THREADS=1

RUN apt-get update && apt-get install -y --no-install-recommends \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY ocr_service/requirements.txt /app/ocr_service/requirements.txt
RUN pip install --no-cache-dir -r /app/ocr_service/requirements.txt

COPY ocr /app/ocr
COPY ocr_service /app/ocr_service

EXPOSE 8080

CMD ["uvicorn", "ocr_service.app:app", "--host", "0.0.0.0", "--port", "8080"]
