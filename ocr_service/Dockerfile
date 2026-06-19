FROM python:3.12-slim

ENV PYTHONUNBUFFERED=1 \
    MALLOC_ARENA_MAX=2 \
    OMP_WAIT_POLICY=PASSIVE \
    YOLO_CONFIG_DIR=/tmp/ultralytics \
    OCR_YOLO_BACKEND=onnxruntime \
    OCR_LOW_MEMORY_MODE=true \
    OCR_RELEASE_OCR_AFTER_SCAN=true \
    OCR_WARMUP_ON_START=false \
    OCR_MAX_CONCURRENT_SCANS=1 \
    OCR_MAX_IMAGE_DIM=640 \
    OCR_YOLO_IMGSZ=384 \
    OCR_YOLO_CONF=0.25 \
    OCR_YOLO_MAX_DET=8 \
    OCR_CROP_MAX_DET=5 \
    OCR_CROP_PADDING=2 \
    OCR_MIN_CROP_AREA=80 \
    OCR_MAX_CROP_PIXELS=90000 \
    OCR_MAX_UPLOAD_MB=3 \
    OCR_SCAN_TIMEOUT=75 \
    OCR_BOX_IOU_THRESHOLD=0.35 \
    OCR_ITEM_CLASSES=banyak_barang_satuan,harga_satuan,harga_total_perbarang,nama_barang,total_value \
    OCR_ORT_THREADS=1 \
    OCR_CV2_THREADS=1

RUN apt-get update && apt-get install -y --no-install-recommends \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY ocr_service/requirements.txt /app/ocr_service/requirements.txt
RUN pip install --no-cache-dir -r /app/ocr_service/requirements.txt

RUN mkdir -p /app/ocr
COPY ocr/*.py /app/ocr/
COPY ocr/*.onnx /app/ocr/
COPY ocr_service /app/ocr_service

EXPOSE 8080

CMD ["sh", "-c", "uvicorn ocr_service.app:app --host 0.0.0.0 --port ${PORT:-8080} --workers 1 --timeout-keep-alive 10"]
