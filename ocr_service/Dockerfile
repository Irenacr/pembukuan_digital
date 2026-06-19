FROM python:3.12-slim

ENV PYTHONUNBUFFERED=1 \
    OCR_MAX_IMAGE_DIM=960 \
    OCR_YOLO_IMGSZ=512 \
    OCR_YOLO_CONF=0.25 \
    OCR_YOLO_MAX_DET=16 \
    OCR_CROP_MAX_DET=12 \
    OCR_CROP_PADDING=2 \
    OCR_MIN_CROP_AREA=80 \
    OCR_MAX_CROP_PIXELS=250000 \
    OCR_MAX_UPLOAD_MB=6 \
    OCR_BOX_IOU_THRESHOLD=0.35 \
    OCR_ITEM_CLASSES=banyak_barang_satuan,harga_satuan,harga_total_perbarang,nama_barang,total_value \
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

CMD ["sh", "-c", "uvicorn ocr_service.app:app --host 0.0.0.0 --port ${PORT:-8080} --workers 1 --timeout-keep-alive 10"]
