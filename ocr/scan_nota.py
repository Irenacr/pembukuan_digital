import json
import os
import sys
import tempfile
import traceback
from pathlib import Path


def ensure_home_environment():
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

try:
    import torch
    print("TORCH OK", file=sys.stderr)
except Exception as e:
    print("TORCH ERROR = " + str(e), file=sys.stderr)
    raise


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

try:
    import cv2
except Exception as exc:
    debug_import_error(exc)

try:
    from ultralytics import YOLO
except Exception as exc:
    debug_import_error(exc)

try:
    from rapidocr_onnxruntime import RapidOCR
except Exception as exc:
    debug_import_error(exc)

MODEL_PATH = Path(__file__).resolve().parent / 'best.pt'
if not MODEL_PATH.exists():
    pt_files = sorted(Path(__file__).resolve().parent.glob('*.pt'))
    if pt_files:
        MODEL_PATH = pt_files[0]
    else:
        raise FileNotFoundError(
            'Model YOLO not found in the ocr/ folder. Place a .pt file such as best.pt or notaocr_yolov8_train2.pt there.'
        )

#print(f'Using YOLO model: {MODEL_PATH}', file=sys.stderr)
ocr = RapidOCR()
model = YOLO(str(MODEL_PATH))


def load_image(image_path):
    path = Path(image_path)
    if path.suffix.lower() == '.pdf':
        raise RuntimeError('PDF support requires pdf2image and poppler. Convert to image first.')

    image = cv2.imread(str(path))
    if image is None:
        raise RuntimeError(f'Unable to read image: {path}')
    return image


def ocr_image(image):

    with tempfile.TemporaryDirectory() as tmpdir:

        tmp_file = os.path.join(tmpdir, 'crop.jpg')

        cv2.imwrite(tmp_file, image)

        result, _ = ocr(tmp_file)

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

def infer(image_path):
    image = load_image(image_path)
    results = model(
    str(image_path),
    verbose=False
)

    output = {
        'raw_text': None,
        'ocr_text': None,
        'detections': [],
        'items': [],
    }

    result = results[0]

    # jika tidak ada objek terdeteksi
    if result.boxes is None or len(result.boxes) == 0:
        lines = ocr_image(image)

        text = '\n'.join([
            line['text']
            for line in lines
        ])

        output['raw_text'] = text
        output['ocr_text'] = text

        return output

    collected_text = []

    for box in result.boxes:

        x1, y1, x2, y2 = map(
            int,
            box.xyxy[0].tolist()
        )

        confidence = float(box.conf[0])

        class_id = int(box.cls[0])

        class_name = result.names[class_id]

        crop = image[y1:y2, x1:x2]

        if crop.size == 0:
            continue

        lines = ocr_image(crop)

        text = '\n'.join([
            line['text']
            for line in lines
        ])

        collected_text.append(text)

        output['detections'].append({
            'bbox': [x1, y1, x2, y2],
            'confidence': confidence,
            'class_name': class_name,
            'text': text,
            'ocr_lines': lines,
        })

    joined_text = '\n'.join([
        item['text']
        for item in output['detections']
        if item['text']
    ])

    if not joined_text:

        lines = ocr_image(image)

        joined_text = '\n'.join([
            line['text']
            for line in lines
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
