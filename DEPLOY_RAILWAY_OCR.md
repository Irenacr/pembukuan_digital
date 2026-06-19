# Deploy Railway: Laravel + OCR Service

Target production:

- Service 1: Laravel web app.
- Service 2: Python OCR API.

Dengan pola ini, Laravel tidak menjalankan `torch`, `ultralytics`, dan RapidOCR langsung dari request web.

## 1. Deploy OCR Service

1. Buat service baru di Railway dari repo yang sama.
2. Pilih Dockerfile:

   ```text
   ocr_service/Dockerfile
   ```

3. Set public networking agar service punya URL.
4. Tambahkan environment variable jika perlu:

   ```text
   OCR_MAX_IMAGE_DIM=960
   OCR_YOLO_IMGSZ=512
   OCR_YOLO_CONF=0.25
   OCR_YOLO_MAX_DET=16
   OCR_CROP_MAX_DET=12
   OCR_CROP_PADDING=2
   OCR_ITEM_CLASSES=banyak_barang_satuan,harga_satuan,harga_total_perbarang,nama_barang,total_value
   OCR_MIN_CROP_AREA=80
   OCR_MAX_CROP_PIXELS=250000
   OCR_MAX_UPLOAD_MB=6
   OCR_BOX_IOU_THRESHOLD=0.35
   OCR_YOLO_DEVICE=cpu
   OCR_TORCH_THREADS=1
   OCR_CV2_THREADS=1
   ```

5. Setelah deploy selesai, buka:

   ```text
   https://URL-OCR-SERVICE/health
   ```

   Response harus:

   ```json
   {"ok":true}
   ```

## 2. Deploy Laravel Web Service

1. Deploy service Laravel dari repo yang sama.
2. Pakai Dockerfile root:

   ```text
   Dockerfile
   ```

3. Tambahkan environment variable Laravel seperti biasa:

   ```text
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:...
   APP_URL=https://URL-LARAVEL.up.railway.app
   DB_CONNECTION=mysql
   DB_HOST=...
   DB_PORT=...
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD=...
   SESSION_DRIVER=database
   ```

4. Tambahkan URL OCR:

   ```text
   OCR_SERVICE_URL=https://URL-OCR-SERVICE.up.railway.app
   OCR_HTTP_TIMEOUT=120
   ```

## 3. Cara Kerja Scan Nota

1. User upload nota di Laravel.
2. Laravel menyimpan file sementara.
3. Laravel mengirim file ke `OCR_SERVICE_URL/scan`.
4. OCR service memproses gambar dan mengembalikan JSON.
5. Laravel menyimpan hasil scan ke session dan mengarahkan user ke form transaksi.

## 4. Catatan Penting

- Jangan kosongkan `OCR_SERVICE_URL` di production.
- Jangan set `APP_DEBUG=true` di production.
- Jika OCR masih lambat, naikkan resource OCR service, bukan Laravel service.
- Jika gambar nota dari HP terlalu besar, OCR service otomatis resize maksimal sesuai `OCR_MAX_IMAGE_DIM`.
- RapidOCR hanya memproses crop dari bounding box YOLO, bukan seluruh gambar nota.
- `/health` tidak memuat model OCR, jadi Railway healthcheck tetap ringan. Model YOLO/RapidOCR dimuat saat scan pertama.
- `OCR_HTTP_TIMEOUT` di Laravel harus lebih besar dari waktu scan normal, tetapi jangan terlalu tinggi agar user tidak menunggu tanpa kepastian.
- Jika Railway memberi error `signal 9`, naikkan memory OCR service terlebih dahulu. Laravel service tidak perlu dinaikkan untuk masalah ini.
