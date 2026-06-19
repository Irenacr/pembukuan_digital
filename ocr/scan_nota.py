import json
import gc
import math
import os
import sys
import tempfile
import threading
import traceback
from pathlib import Path

os.environ.setdefault("OMP_NUM_THREADS", "1")
os.environ.setdefault("MKL_NUM_THREADS", "1")
os.environ.setdefault("OPENBLAS_NUM_THREADS", "1")
os.environ.setdefault("NUMEXPR_NUM_THREADS", "1")


def ensure_home_environment():
    print("STEP 1 - ENV READY", file=sys.stderr, flush=True)
    if os.name == 'nt':
        home = os.environ.get('HOME') or os.environ.get('USERPROFILE')
        if not home and os.environ.get('HOMEDRIVE') and os.environ.get('HOMEPATH'):
            home = os.environ.get('HOMEDRIVE') + os.environ.get('HOMEPATH')
        if not home:
            home = tempfile.gettempdir()
        os.environ['HOME'] = home
        os.environ['USERPROFILE'] = home
        os.environ.setdefault('APPDATA', os.path.join(home, 'AppData', 'Roaming'))
        os.environ.setdefault('LOCALAPPDATA', os.path.join(home, 'AppData', 'Local'))
    else:
        home = os.environ.get('HOME') or tempfile.gettempdir()
        os.environ['HOME'] = home

ensure_home_environment()

print("PYTHON_EXE=" + sys.executable, file=sys.stderr)
print("PYTHON_VERSION=" + sys.version, file=sys.stderr)
print("HOME=" + os.environ.get('HOME', ''), file=sys.stderr)
print("USERPROFILE=" + os.environ.get('USERPROFILE', ''), file=sys.stderr)
print("APPDATA=" + os.environ.get('APPDATA', ''), file=sys.stderr)
print("LOCALAPPDATA=" + os.environ.get('LOCALAPPDATA', ''), file=sys.stderr)

cv2 = None
ocr = None
model = None
runtime_ready = False
models_lock = threading.Lock()
scan_lock = threading.Lock()


def env_bool(name, default=False):
    value = os.environ.get(name)

    if value is None:
        return default

    return value.strip().lower() in {'1', 'true', 'yes', 'on'}


def env_int(name, default):
    try:
        return int(os.environ.get(name, str(default)))
    except ValueError:
        return default


def capped_env_int(name, default, minimum, maximum):
    return max(minimum, min(maximum, env_int(name, default)))


def debug_import_error(exc):
    print('=== OCR ENV DEBUG ===', file=sys.stderr, flush=True)
    print('sys.executable=' + sys.executable, file=sys.stderr, flush=True)
    print('sys.version=' + sys.version.replace('\n', ' '), file=sys.stderr, flush=True)
    print('HOME=' + os.environ.get('HOME', ''), file=sys.stderr, flush=True)
    print('USERPROFILE=' + os.environ.get('USERPROFILE', ''), file=sys.stderr, flush=True)
    print('APPDATA=' + os.environ.get('APPDATA', ''), file=sys.stderr, flush=True)
    print('LOCALAPPDATA=' + os.environ.get('LOCALAPPDATA', ''), file=sys.stderr, flush=True)
    print('PATH=' + os.environ.get('PATH', ''), file=sys.stderr, flush=True)
    print('SystemRoot=' + os.environ.get('SystemRoot', ''), file=sys.stderr, flush=True)
    print('WINDIR=' + os.environ.get('WINDIR', ''), file=sys.stderr, flush=True)
    print('PYTHONPATH=' + os.environ.get('PYTHONPATH', ''), file=sys.stderr, flush=True)
    print('PYTHONHOME=' + os.environ.get('PYTHONHOME', ''), file=sys.stderr, flush=True)
    try:
        print('Path.home=' + str(Path.home()), file=sys.stderr, flush=True)
    except Exception as home_exc:
        print('Path.home ERROR=' + str(home_exc), file=sys.stderr, flush=True)
    traceback.print_exc(file=sys.stderr)
    sys.stderr.flush()
    raise exc

ONNX_MODEL_PATH = Path(__file__).resolve().parent / 'notaocr_yolov8_train2.onnx'
if not ONNX_MODEL_PATH.exists():
    onnx_files = sorted(Path(__file__).resolve().parent.glob('*.onnx'))
    ONNX_MODEL_PATH = onnx_files[0] if onnx_files else None

MODEL_PATH = Path(__file__).resolve().parent / 'best.pt'
if not MODEL_PATH.exists():
    pt_files = sorted(Path(__file__).resolve().parent.glob('*.pt'))
    MODEL_PATH = pt_files[0] if pt_files else None

if ONNX_MODEL_PATH is None and MODEL_PATH is None:
    raise FileNotFoundError(
        'Model YOLO not found in the ocr/ folder. Place a .onnx model or a .pt model there.'
    )

DEFAULT_CLASS_NAMES = [
    'banyak_barang_satuan',
    'harga_satuan',
    'harga_total_perbarang',
    'nama_barang',
    'total_value',
]


def ensure_runtime_modules():
    global cv2, runtime_ready

    if runtime_ready:
        return

    try:
        import cv2 as cv2_module
        cv2_module.setNumThreads(int(os.environ.get("OCR_CV2_THREADS", "1")))
        cv2 = cv2_module
        print("STEP 3 - CV2 OK", file=sys.stderr, flush=True)
    except Exception as exc:
        debug_import_error(exc)

    runtime_ready = True


def ensure_yolo_loaded():
    global model

    if model is not None:
        return

    with models_lock:
        ensure_runtime_modules()

        if model is not None:
            return

        if MODEL_PATH is None:
            raise RuntimeError('Model YOLO .pt tidak tersedia. Gunakan OCR_YOLO_BACKEND=onnxruntime.')

        try:
            from ultralytics import YOLO
            print("STEP 4 - YOLO IMPORT OK", file=sys.stderr, flush=True)
        except Exception as exc:
            debug_import_error(exc)

        print("STEP 9 - LOAD YOLO", file=sys.stderr, flush=True)
        model = YOLO(str(MODEL_PATH))
        print("STEP 10 - YOLO LOADED", file=sys.stderr, flush=True)


def sigmoid(value):
    return 1 / (1 + math.exp(-value))


def prepare_yolo_input(image, imgsz):
    height, width = image.shape[:2]
    scale = min(imgsz / width, imgsz / height)
    resized_width = int(round(width * scale))
    resized_height = int(round(height * scale))
    pad_x = (imgsz - resized_width) / 2
    pad_y = (imgsz - resized_height) / 2

    resized = cv2.resize(image, (resized_width, resized_height), interpolation=cv2.INTER_AREA)
    canvas = cv2.copyMakeBorder(
        resized,
        int(pad_y),
        imgsz - resized_height - int(pad_y),
        int(pad_x),
        imgsz - resized_width - int(pad_x),
        cv2.BORDER_CONSTANT,
        value=(114, 114, 114),
    )

    blob = canvas[:, :, ::-1].transpose(2, 0, 1)
    blob = blob.astype('float32') / 255.0
    blob = blob[None, :, :, :]

    return blob, scale, pad_x, pad_y


def run_yolo_onnx(image):
    if ONNX_MODEL_PATH is None:
        raise RuntimeError('Model YOLO ONNX tidak ditemukan.')

    try:
        import numpy as np
        import onnxruntime as ort
    except Exception as exc:
        debug_import_error(exc)

    imgsz = capped_env_int("OCR_YOLO_IMGSZ", 384, 320, 416)
    conf_threshold = float(os.environ.get("OCR_YOLO_CONF", "0.35"))
    max_det = capped_env_int("OCR_YOLO_MAX_DET", 8, 1, 10)
    image_height, image_width = image.shape[:2]

    session_options = ort.SessionOptions()
    session_options.intra_op_num_threads = int(os.environ.get("OCR_ORT_THREADS", "1"))
    session_options.inter_op_num_threads = 1
    session_options.graph_optimization_level = ort.GraphOptimizationLevel.ORT_ENABLE_ALL

    session = ort.InferenceSession(
        str(ONNX_MODEL_PATH),
        sess_options=session_options,
        providers=['CPUExecutionProvider'],
    )

    blob, scale, pad_x, pad_y = prepare_yolo_input(image, imgsz)
    output = session.run(None, {session.get_inputs()[0].name: blob})[0]
    predictions = np.squeeze(output, axis=0).T

    detections = []

    for prediction in predictions:
        class_scores = prediction[4:]
        if class_scores.size == 0:
            continue

        class_id = int(np.argmax(class_scores))
        confidence = float(class_scores[class_id])

        if confidence > 1:
            confidence = sigmoid(confidence)

        if confidence < conf_threshold:
            continue

        cx, cy, width, height = [float(value) for value in prediction[:4]]
        x1 = (cx - width / 2 - pad_x) / scale
        y1 = (cy - height / 2 - pad_y) / scale
        x2 = (cx + width / 2 - pad_x) / scale
        y2 = (cy + height / 2 - pad_y) / scale

        x1 = max(0, min(int(round(x1)), image_width))
        y1 = max(0, min(int(round(y1)), image_height))
        x2 = max(0, min(int(round(x2)), image_width))
        y2 = max(0, min(int(round(y2)), image_height))

        if x2 <= x1 or y2 <= y1:
            continue

        class_name = DEFAULT_CLASS_NAMES[class_id] if class_id < len(DEFAULT_CLASS_NAMES) else str(class_id)

        detections.append({
            'bbox': [x1, y1, x2, y2],
            'confidence': confidence,
            'class_name': class_name,
        })

    detections = suppress_overlapping_boxes(
        detections,
        float(os.environ.get("OCR_BOX_IOU_THRESHOLD", "0.35")),
    )

    detections.sort(key=lambda item: item['confidence'], reverse=True)
    return detections[:max_det], {index: name for index, name in enumerate(DEFAULT_CLASS_NAMES)}


def ensure_ocr_loaded():
    global ocr

    if ocr is not None:
        return

    with models_lock:
        ensure_runtime_modules()

        if ocr is not None:
            return

        try:
            from rapidocr_onnxruntime import RapidOCR
            print("STEP 5 - RAPIDOCR IMPORT OK", file=sys.stderr, flush=True)
        except Exception as exc:
            debug_import_error(exc)

        print("STEP 7 - CREATE RAPIDOCR", file=sys.stderr, flush=True)
        ocr = RapidOCR()
        print("STEP 8 - RAPIDOCR CREATED", file=sys.stderr, flush=True)


def ensure_models_loaded():
    ensure_runtime_modules()

    if env_bool("OCR_LOW_MEMORY_MODE", True):
        print("STEP 6 - LOW MEMORY MODE, SKIP PERSISTENT MODEL WARMUP", file=sys.stderr, flush=True)
        return

    ensure_yolo_loaded()
    ensure_ocr_loaded()


def release_yolo_model():
    global model

    if model is not None:
        model = None
        gc.collect()


def release_ocr_engine():
    global ocr

    if ocr is not None:
        ocr = None
        gc.collect()


def load_image(image_path):
    ensure_runtime_modules()

    path = Path(image_path)
    if path.suffix.lower() == '.pdf':
        raise RuntimeError('PDF support requires pdf2image and poppler. Convert to image first.')

    image = cv2.imread(str(path))
    if image is None:
        raise RuntimeError(f'Unable to read image: {path}')
    return image


def ocr_crop_image(crop_image):
    ensure_ocr_loaded()

    with tempfile.TemporaryDirectory() as tmpdir:

        tmp_file = os.path.join(tmpdir, 'crop.jpg')

        cv2.imwrite(tmp_file, crop_image)

        try:
            result, _ = ocr(tmp_file)
        except Exception as exc:
            print(
                "RAPIDOCR CROP ERROR = " + str(exc),
                file=sys.stderr,
                flush=True,
            )
            return []

        lines = []

        if result:

            for item in result:

                text = item[1]

                score = float(item[2])

                lines.append({
                    'text': str(text),
                    'confidence': score
                })

        return lines


def box_iou(a, b):
    ax1, ay1, ax2, ay2 = a['bbox']
    bx1, by1, bx2, by2 = b['bbox']

    inter_x1 = max(ax1, bx1)
    inter_y1 = max(ay1, by1)
    inter_x2 = min(ax2, bx2)
    inter_y2 = min(ay2, by2)

    inter_w = max(0, inter_x2 - inter_x1)
    inter_h = max(0, inter_y2 - inter_y1)
    inter_area = inter_w * inter_h

    area_a = max(0, ax2 - ax1) * max(0, ay2 - ay1)
    area_b = max(0, bx2 - bx1) * max(0, by2 - by1)
    union = area_a + area_b - inter_area

    if union <= 0:
        return 0

    return inter_area / union


def suppress_overlapping_boxes(boxes, iou_threshold):
    kept = []

    for candidate in sorted(boxes, key=lambda item: item['confidence'], reverse=True):
        is_duplicate = False

        for existing in kept:
            if (
                candidate['class_name'] == existing['class_name']
                and box_iou(candidate, existing) > iou_threshold
            ):
                is_duplicate = True
                break

        if not is_duplicate:
            kept.append(candidate)

    return kept


def infer(image_path):
    if not scan_lock.acquire(blocking=False):
        raise RuntimeError('OCR service sedang memproses nota lain. Coba ulangi beberapa detik lagi.')

    try:
        return _infer_locked(image_path)
    finally:
        if env_bool("OCR_LOW_MEMORY_MODE", True):
            release_yolo_model()
            if env_bool("OCR_RELEASE_OCR_AFTER_SCAN", True):
                release_ocr_engine()
        scan_lock.release()


def _infer_locked(image_path):
    ensure_runtime_modules()
    low_memory_mode = env_bool("OCR_LOW_MEMORY_MODE", True)

    print("STEP 11 - ENTER INFER", file=sys.stderr, flush=True)

    image = load_image(image_path)

    print(
        f"STEP 12 - IMAGE SHAPE = {image.shape}",
        file=sys.stderr,
        flush=True
    )

    print("STEP 13 - BEFORE YOLO PREDICT", file=sys.stderr, flush=True)

    yolo_backend = os.environ.get("OCR_YOLO_BACKEND", "onnxruntime").strip().lower()

    if yolo_backend == "onnxruntime" and ONNX_MODEL_PATH is not None:
        candidate_boxes, result_names = run_yolo_onnx(image)
        results = None
        result = None
        yolo_model = None
    else:
        if low_memory_mode:
            if MODEL_PATH is None:
                raise RuntimeError('Model YOLO .pt tidak tersedia. Gunakan OCR_YOLO_BACKEND=onnxruntime.')
            from ultralytics import YOLO
            yolo_model = YOLO(str(MODEL_PATH))
        else:
            ensure_yolo_loaded()
            yolo_model = model

        results = yolo_model(
            str(image_path),
            verbose=False,
            imgsz=capped_env_int("OCR_YOLO_IMGSZ", 384, 320, 416),
            conf=float(os.environ.get("OCR_YOLO_CONF", "0.35")),
            max_det=capped_env_int("OCR_YOLO_MAX_DET", 8, 1, 10),
            device=os.environ.get("OCR_YOLO_DEVICE", "cpu"),
        )

        result = results[0]
        result_names = dict(result.names)

    print("STEP 14 - AFTER YOLO PREDICT", file=sys.stderr, flush=True)

    output = {
        'raw_text': None,
        'ocr_text': None,
        'ocr_scope': 'yolo_crops_only',
        'pipeline': ['image', 'yolo', 'crop_per_class', 'rapidocr', 'json'],
        'detections': [],
        'items': [],
    }

    # Jika tidak ada objek YOLO, jangan OCR seluruh nota.
    # Flow scan form hanya boleh memakai teks dari kotak YOLO.
    if yolo_backend == "onnxruntime" and ONNX_MODEL_PATH is not None:
        has_boxes = len(candidate_boxes) > 0
    else:
        has_boxes = result.boxes is not None and len(result.boxes) > 0

    if not has_boxes:
        output['raw_text'] = ''
        output['ocr_text'] = ''
        if low_memory_mode:
            if results is not None:
                del results, result, yolo_model
            gc.collect()
        return output

    default_ocr_item_classes = ",".join(result_names.values())
    ocr_item_classes = {
        class_name.strip()
        for class_name in os.environ.get(
            "OCR_ITEM_CLASSES",
            default_ocr_item_classes,
        ).split(",")
        if class_name.strip()
    }
    crop_max_det = capped_env_int("OCR_CROP_MAX_DET", 5, 1, 6)
    min_crop_area = max(1, env_int("OCR_MIN_CROP_AREA", 80))
    max_crop_pixels = max(min_crop_area, capped_env_int("OCR_MAX_CROP_PIXELS", 90000, 20000, 120000))
    box_iou_threshold = float(os.environ.get("OCR_BOX_IOU_THRESHOLD", "0.35"))
    crop_padding = max(0, int(os.environ.get("OCR_CROP_PADDING", "2")))
    if yolo_backend != "onnxruntime" or ONNX_MODEL_PATH is None:
        candidate_boxes = []
    else:
        candidate_boxes = [
            box for box in candidate_boxes
            if box['class_name'] in ocr_item_classes
        ]
    image_height, image_width = image.shape[:2]

    if yolo_backend == "onnxruntime" and ONNX_MODEL_PATH is not None:
        for detected in candidate_boxes:
            x1, y1, x2, y2 = detected['bbox']
            detected['bbox'] = [
                max(0, min(x1 - crop_padding, image_width)),
                max(0, min(y1 - crop_padding, image_height)),
                max(0, min(x2 + crop_padding, image_width)),
                max(0, min(y2 + crop_padding, image_height)),
            ]

    if yolo_backend != "onnxruntime" or ONNX_MODEL_PATH is None:
        for box in result.boxes:

            x1, y1, x2, y2 = map(
                int,
                box.xyxy[0].tolist()
            )

            x1 = max(0, min(x1 - crop_padding, image_width))
            y1 = max(0, min(y1 - crop_padding, image_height))
            x2 = max(0, min(x2 + crop_padding, image_width))
            y2 = max(0, min(y2 + crop_padding, image_height))

            if x2 <= x1 or y2 <= y1:
                continue

            confidence = float(box.conf[0])

            class_id = int(box.cls[0])

            class_name = result_names[class_id]

            if class_name not in ocr_item_classes:
                continue

            candidate_boxes.append({
                'bbox': [x1, y1, x2, y2],
                'confidence': confidence,
                'class_name': class_name,
            })

    candidate_boxes = suppress_overlapping_boxes(
        candidate_boxes,
        box_iou_threshold,
    )

    if low_memory_mode:
        if results is not None:
            del results, result, yolo_model
        release_yolo_model()
        gc.collect()

    candidate_boxes.sort(key=lambda item: (
        item['bbox'][1],
        item['bbox'][0],
    ))

    for detected in candidate_boxes[:crop_max_det]:

        x1, y1, x2, y2 = detected['bbox']
        confidence = detected['confidence']
        class_name = detected['class_name']

        crop = image[y1:y2, x1:x2]

        crop_area = crop.shape[0] * crop.shape[1] if crop.size else 0

        if crop_area < min_crop_area:
            continue

        if crop_area > max_crop_pixels:
            scale = (max_crop_pixels / crop_area) ** 0.5
            crop = cv2.resize(
                crop,
                (
                    max(1, int(crop.shape[1] * scale)),
                    max(1, int(crop.shape[0] * scale)),
                ),
                interpolation=cv2.INTER_AREA,
            )

        print(
            f"STEP 15 - OCR CLASS {class_name}",
            file=sys.stderr,
            flush=True
        )

        lines = ocr_crop_image(crop)

        text = '\n'.join([
            line['text']
            for line in lines
        ])

        output['detections'].append({
            'bbox': [x1, y1, x2, y2],
            'confidence': confidence,
            'class_name': class_name,
            'text': text,
            'ocr_lines': lines,
        })

    if env_bool("OCR_RELEASE_OCR_AFTER_SCAN", True):
        release_ocr_engine()

    joined_text = '\n'.join([
        item['text']
        for item in output['detections']
        if item['text']
    ])

    output['raw_text'] = joined_text
    output['ocr_text'] = joined_text
    output['items'] = output['detections']

    return output


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Missing image path'}, ensure_ascii=False))
        sys.exit(1)

    image_path = sys.argv[1]
    if not os.path.exists(image_path):
        print(json.dumps({'error': f'File not found: {image_path}'}, ensure_ascii=False))
        sys.exit(1)

    result = infer(image_path)
    print(json.dumps(result, ensure_ascii=False))
