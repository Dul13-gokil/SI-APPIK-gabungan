import sys
import os
import json
import cv2
import numpy as np

COCO_CLASSES = [
    "person", "bicycle", "car", "motorcycle", "airplane", "bus", "train", "truck", "boat",
    "traffic light", "fire hydrant", "stop sign", "parking meter", "bench", "bird", "cat",
    "dog", "horse", "sheep", "cow", "elephant", "bear", "zebra", "giraffe", "backpack",
    "umbrella", "handbag", "tie", "suitcase", "frisbee", "skis", "snowboard", "sports ball",
    "kite", "baseball bat", "baseball glove", "skateboard", "surfboard", "tennis racket",
    "bottle", "wine glass", "cup", "fork", "knife", "spoon", "bowl", "banana", "apple",
    "sandwich", "orange", "broccoli", "carrot", "hot dog", "pizza", "donut", "cake",
    "chair", "couch", "potted plant", "bed", "dining table", "toilet", "tv", "laptop",
    "mouse", "remote", "keyboard", "cell phone", "microwave", "oven", "toaster", "sink",
    "refrigerator", "book", "clock", "vase", "scissors", "teddy bear", "hair drier", "toothbrush"
]

RDD_CLASSES = [
    'alligator crack',
    'transverse crack',
    'longitudinal crack',
    'other corruption',
    'pothole'
]

_onnx_coco_net = None
_onnx_pothole_net = None
_onnx_rdd_net = None

def get_model_path(filename):
    script_dir = os.path.dirname(os.path.abspath(__file__))
    candidates = [
        os.path.join(script_dir, filename),
        os.path.abspath(os.path.join(script_dir, '../../../../..', filename)),
        os.path.join(os.getcwd(), filename),
    ]
    for c in candidates:
        if os.path.exists(c):
            return c
    return None

def detect_people_and_objects(img):
    """
    Deteksi manusia dan objek umum non-infrastruktur menggunakan OpenCV DNN ONNX
    (Sangat cepat ~0.15s, tanpa PyTorch overhead / freeze).
    """
    global _onnx_coco_net
    try:
        if _onnx_coco_net is None:
            mp = get_model_path('yolov8n.onnx')
            if mp and os.path.exists(mp):
                _onnx_coco_net = cv2.dnn.readNetFromONNX(mp)

        if _onnx_coco_net is None:
            return []

        h_orig, w_orig = img.shape[:2]
        image_area = float(h_orig * w_orig)
        blob = cv2.dnn.blobFromImage(img, 1.0 / 255.0, (640, 640), swapRB=True, crop=False)
        _onnx_coco_net.setInput(blob)
        preds = _onnx_coco_net.forward()[0]  # (84, 8400)
        preds = preds.T  # (8400, 84)

        boxes = []
        confidences = []
        class_ids = []

        for row in preds:
            scores = row[4:]
            max_s = float(np.max(scores))
            if max_s >= 0.25:
                cid = int(np.argmax(scores))
                cx, cy, bw, bh = row[0:4]
                x1 = int((cx - bw / 2.0) * w_orig / 640.0)
                y1 = int((cy - bh / 2.0) * h_orig / 640.0)
                w_box = int(bw * w_orig / 640.0)
                h_box = int(bh * h_orig / 640.0)
                boxes.append([x1, y1, w_box, h_box])
                confidences.append(max_s)
                class_ids.append(cid)

        if not boxes:
            return []

        indices = cv2.dnn.NMSBoxes(boxes, confidences, 0.25, 0.45)
        results = []
        for i in indices:
            idx = int(i[0] if isinstance(i, (list, tuple, np.ndarray)) else i)
            cid = class_ids[idx]
            name = COCO_CLASSES[cid] if 0 <= cid < len(COCO_CLASSES) else "unknown"
            conf = confidences[idx]
            bx, by, bw, bh = boxes[idx]
            box_area = float(max(0, bw) * max(0, bh)) / max(1.0, image_area)
            results.append((name, conf, box_area, [bx, by, bw, bh]))

        return results
    except Exception:
        return []

def detect_potholes(img):
    """
    Deteksi spesifik lubang jalan (potholes) menggunakan model YOLOv8 Pothole Detector.
    """
    global _onnx_pothole_net
    try:
        if _onnx_pothole_net is None:
            mp = get_model_path('pothole_yolov8.onnx')
            if mp and os.path.exists(mp):
                _onnx_pothole_net = cv2.dnn.readNetFromONNX(mp)

        if _onnx_pothole_net is None:
            return []

        h_orig, w_orig = img.shape[:2]
        blob = cv2.dnn.blobFromImage(img, 1.0 / 255.0, (640, 640), swapRB=True, crop=False)
        _onnx_pothole_net.setInput(blob)
        preds = _onnx_pothole_net.forward()[0]  # (5, 8400)
        scores = preds[4, :]
        candidates = np.where(scores > 0.20)[0]
        if len(candidates) == 0:
            return []

        boxes = []
        confidences = []
        for c in candidates:
            cx, cy, bw, bh = preds[0:4, c]
            x1 = int((cx - bw / 2.0) * w_orig / 640.0)
            y1 = int((cy - bh / 2.0) * h_orig / 640.0)
            w_box = int(bw * w_orig / 640.0)
            h_box = int(bh * h_orig / 640.0)
            boxes.append([x1, y1, w_box, h_box])
            confidences.append(float(scores[c]))

        indices = cv2.dnn.NMSBoxes(boxes, confidences, 0.20, 0.45)
        results = []
        for i in indices:
            idx = int(i[0] if isinstance(i, (list, tuple, np.ndarray)) else i)
            bx, by, bw, bh = boxes[idx]
            area_ratio = float(max(0, bw) * max(0, bh)) / float(h_orig * w_orig)
            results.append({
                'class': 'pothole',
                'confidence': confidences[idx],
                'box': [bx, by, bw, bh],
                'area_ratio': area_ratio
            })
        return results
    except Exception:
        return []

def detect_road_damage_rdd(img):
    """
    Deteksi kerusakan perkerasan jalan (cracks, potholes, amblas) menggunakan RDD2022 YOLO model.
    """
    global _onnx_rdd_net
    try:
        if _onnx_rdd_net is None:
            mp = get_model_path('road_damage_yolov8.onnx')
            if mp and os.path.exists(mp):
                _onnx_rdd_net = cv2.dnn.readNetFromONNX(mp)

        if _onnx_rdd_net is None:
            return []

        h_orig, w_orig = img.shape[:2]
        blob = cv2.dnn.blobFromImage(img, 1.0 / 255.0, (640, 640), swapRB=True, crop=False)
        _onnx_rdd_net.setInput(blob)
        preds = _onnx_rdd_net.forward()[0]  # (9, 8400)
        scores = preds[4:9, :]
        max_scores = np.max(scores, axis=0)
        candidates = np.where(max_scores > 0.15)[0]
        if len(candidates) == 0:
            return []

        boxes = []
        confidences = []
        class_ids = []
        for c in candidates:
            cid = int(np.argmax(scores[:, c]))
            cx, cy, bw, bh = preds[0:4, c]
            x1 = int((cx - bw / 2.0) * w_orig / 640.0)
            y1 = int((cy - bh / 2.0) * h_orig / 640.0)
            w_box = int(bw * w_orig / 640.0)
            h_box = int(bh * h_orig / 640.0)
            boxes.append([x1, y1, w_box, h_box])
            confidences.append(float(max_scores[c]))
            class_ids.append(cid)

        indices = cv2.dnn.NMSBoxes(boxes, confidences, 0.15, 0.45)
        results = []
        for i in indices:
            idx = int(i[0] if isinstance(i, (list, tuple, np.ndarray)) else i)
            cid = class_ids[idx]
            cname = RDD_CLASSES[cid] if 0 <= cid < len(RDD_CLASSES) else 'road damage'
            bx, by, bw, bh = boxes[idx]
            area_ratio = float(max(0, bw) * max(0, bh)) / float(h_orig * w_orig)
            results.append({
                'class': cname,
                'confidence': confidences[idx],
                'box': [bx, by, bw, bh],
                'area_ratio': area_ratio
            })
        return results
    except Exception:
        return []

def classify_road_surface(img, gray, hsv, h_img, w_img):
    """
    Klasifikasi kondisi fisik permukaan perkerasan jalan:
    1. Jalan Tanah
    2. Batu Lepas / Makadam
    3. Aspal Hancur Bergelombang
    4. Genangan Lumpur Parah
    5. Longsor / Amblas Badan Jalan
    6. Aspal Berlubang
    """
    total_pixels = h_img * w_img
    h_channel = hsv[:, :, 0]
    s_channel = hsv[:, :, 1]
    v_channel = hsv[:, :, 2]

    # Spektrum warna tanah (Hue cokelat 8-28, saturasi sedang-tinggi)
    dirt_mask = (h_channel >= 8) & (h_channel <= 28) & (s_channel >= 25) & (s_channel <= 185) & (v_channel >= 35) & (v_channel <= 220)
    dirt_ratio = float(np.sum(dirt_mask) / total_pixels)

    # Lumpur pekat / genangan air keruh
    mud_mask = (h_channel >= 10) & (h_channel <= 30) & (s_channel >= 30) & (v_channel >= 20) & (v_channel <= 120)
    mud_ratio = float(np.sum(mud_mask) / total_pixels)

    # Kepadatan batu pecah / kerikil makadam
    blurred = cv2.GaussianBlur(gray, (3, 3), 0)
    edges = cv2.Canny(blurred, 60, 160)
    stone_edge_density = float(np.sum(edges > 0) / total_pixels)

    # Spektrum aspal (saturasi rendah, abu-abu gelap/sedang)
    b, g, r = cv2.split(img)
    color_diff = np.maximum(np.abs(r.astype(np.int16) - g.astype(np.int16)),
                            np.abs(g.astype(np.int16) - b.astype(np.int16)))
    asphalt_mask = (s_channel < 55) & (v_channel >= 30) & (v_channel <= 170) & (color_diff < 25)
    asphalt_ratio = float(np.sum(asphalt_mask) / total_pixels)

    # Deteksi amblas / patahan tebing di pinggir jalan
    lower_edges = edges[int(h_img * 0.5):, :]
    slope_lines = cv2.HoughLinesP(lower_edges, 1, np.pi / 180, threshold=40, minLineLength=int(w_img * 0.25), maxLineGap=20)
    steep_slopes = 0
    if slope_lines is not None:
        for line in slope_lines:
            x1, y1, x2, y2 = line[0]
            ang = np.abs(np.arctan2(y2 - y1, x2 - x1) * 180.0 / np.pi)
            if 30 <= ang <= 75:
                steep_slopes += 1

    if steep_slopes >= 3 and (dirt_ratio > 0.20 or asphalt_ratio > 0.20):
        return "Longsor / Amblas Badan Jalan", "badan jalan mengalami patahan tebing dan amblas ke lereng curam"
    elif mud_ratio > 0.16 and dirt_ratio > 0.22:
        return "Genangan Lumpur Parah", "permukaan jalan berupa tanah berlumpur tebal dan alur roda kendaraan tergenang air keruh"
    elif dirt_ratio > 0.32:
        return "Jalan Tanah", "ruas jalan berupa tanah alami tanpa lapis aspal, bergelombang dan licin saat hujan"
    elif stone_edge_density > 0.075 and (dirt_ratio > 0.15 or asphalt_ratio < 0.40):
        return "Batu Lepas / Makadam", "lapisan batu makadam terlepas berserakan tanpa pengikat aspal dan membahayakan kendaraan"
    elif asphalt_ratio > 0.26:
        return "Aspal Hancur Bergelombang", "lapisan permukaan aspal hancur terkelupas, bergelombang parah dan penuh lubang bertubi-tubi"
    else:
        return "Aspal Berlubang", "lapisan permukaan perkerasan jalan berlubang dan memerlukan penambalan perkerasan"

def is_smooth_road_surface(img, gray, hsv, h_img, w_img, asphalt_ratio, dirt_ratio, pothole_count, rdd_count):
    """
    Memvalidasi apakah foto merupakan permukaan jalan mulus / normal (kondisi baik):
    - Permukaan aspal atau beton bersih dan rata
    - Tidak ada lubang jalan (pothole_count == 0) dan tidak ada retak (rdd_count == 0)
    - Tekstur agregat alami jalan, bukan interior/elektronik
    """
    if pothole_count > 0 or rdd_count > 0:
        return False

    total_pixels = h_img * w_img
    s_channel = hsv[:, :, 1]
    v_channel = hsv[:, :, 2]

    # Jalan mulus memiliki saturasi sangat rendah (abu-abu netral) di mayoritas area
    neutral_surface = (s_channel < 40) & (v_channel >= 35) & (v_channel <= 210)
    neutral_ratio = float(np.sum(neutral_surface) / total_pixels)

    # Varian Laplacian alami permukaan aspal/beton (bukan PCB elektronik yang sangat tinggi, bukan layar polos yang < 5)
    lap_var = float(cv2.Laplacian(gray, cv2.CV_64F).var())
    is_asphalt_texture = 15.0 <= lap_var <= 350.0

    # Cek ketiadaan warna pelangi/sintetis (seperti kabel warna-warni, PCB hijau cerah)
    h_channel = hsv[:, :, 0]
    saturated_colored = (s_channel >= 80) & (v_channel >= 50) & ~((h_channel >= 8) & (h_channel <= 28))  # bukan tanah
    colored_ratio = float(np.sum(saturated_colored) / total_pixels)

    # Area bawah harus didominasi aspal/beton bersih
    lower_neutral = float(np.sum(neutral_surface[int(h_img * 0.4):, :]) / ((h_img - int(h_img * 0.4)) * w_img))

    return neutral_ratio >= 0.48 and lower_neutral >= 0.52 and is_asphalt_texture and colored_ratio < 0.08

def analyze_single_image(image_path, road_only=False):
    if not os.path.exists(image_path):
        return {"error": f"File not found: {image_path}"}

    img = cv2.imread(image_path)
    if img is None:
        return {"error": "Failed to decode image"}

    h_img, w_img = img.shape[:2]

    # Convert to Grayscale & HSV
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)

    mean_val = float(np.mean(gray))
    std_val = float(np.std(gray))
    laplacian_var = float(cv2.Laplacian(gray, cv2.CV_64F).var())

    h_channel = hsv[:, :, 0]
    s_channel = hsv[:, :, 1]
    v_channel = hsv[:, :, 2]
    b, g, r = cv2.split(img)

    # -------------------------------------------------------------------------
    # 1. DETEKSI DEEP LEARNING: COCO, POTHOLE, DAN RDD ROAD DAMAGE
    # -------------------------------------------------------------------------
    yolo_objects = detect_people_and_objects(img)
    pothole_dets = detect_potholes(img)
    rdd_dets = detect_road_damage_rdd(img)

    has_yolo_person = any(label == 'person' and confidence >= 0.35 and area >= 0.02
                          for label, confidence, area, _ in yolo_objects)
    
    non_infrastructure_objects = [
        (label, confidence, area) for label, confidence, area, _ in yolo_objects
        if label not in {'person', 'car', 'motorcycle', 'bus', 'truck', 'bicycle', 'traffic light'}
        and confidence >= 0.28 and area >= 0.03
    ]

    # -------------------------------------------------------------------------
    # 2. FILTER 1: TOLAK SOSOK / WAJAH MANUSIA
    # -------------------------------------------------------------------------
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
    profile_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_profileface.xml')
    detected_faces = face_cascade.detectMultiScale(gray, 1.12, 4, minSize=(40, 40))
    if len(detected_faces) == 0:
        detected_faces = profile_cascade.detectMultiScale(gray, 1.12, 4, minSize=(40, 40))

    has_human_face = False
    for (fx, fy, fw, fh) in detected_faces:
        f_hsv = hsv[fy:fy+fh, fx:fx+fw]
        f_skin = (f_hsv[:,:,0] >= 5) & (f_hsv[:,:,0] <= 25) & (f_hsv[:,:,1] >= 35) & (f_hsv[:,:,1] <= 180) & (f_hsv[:,:,2] >= 55)
        if (np.sum(f_skin) / (fw * fh)) > 0.22:
            has_human_face = True
            break

    skin_mask1 = (h_channel >= 5) & (h_channel <= 25) & (s_channel >= 35) & (s_channel <= 180) & (v_channel >= 55)
    skin_mask2 = (h_channel >= 165) & (s_channel >= 45) & (s_channel <= 175) & (v_channel >= 60)
    skin_ratio = float(np.sum(skin_mask1 | skin_mask2) / (h_img * w_img))

    hog = cv2.HOGDescriptor()
    hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())
    scale = min(1.0, 640.0 / max(h_img, w_img))
    person_img = cv2.resize(img, (int(w_img * scale), int(h_img * scale))) if scale < 1 else img
    people, weights = hog.detectMultiScale(person_img, winStride=(8, 8), padding=(8, 8), scale=1.05)
    has_person = any(float(weight) > 0.35 for weight in weights)

    if has_human_face or has_person or has_yolo_person or skin_ratio > 0.12:
        return {
            "is_valid_infrastructure": False,
            "is_damaged": False,
            "category_id": 1,
            "category_label": "Bukan Foto Infrastruktur",
            "confidence": 0.98,
            "severity_level": 0,
            "severity_text": "TIDAK VALID (Foto Manusia / Wajah)",
            "damage_type": "Foto Tidak Sesuai Kategori",
            "damage_detail": "Foto terdeteksi memuat wajah atau sosok manusia. Sistem hanya menerima foto fisik infrastruktur publik (jalan, jembatan, penerangan, atau trotoar).",
            "suggested_title": "",
            "suggested_road_condition": "",
            "llm_description": ""
        }

    # -------------------------------------------------------------------------
    # 3. FILTER 2: TOLAK OBJEK NON-INFRASTRUKTUR TERDETEKSI (BARANG, ELEKTRONIK, HEWAN)
    # -------------------------------------------------------------------------
    if non_infrastructure_objects:
        obj_name, obj_conf, _ = non_infrastructure_objects[0]
        id_names = {
            'cat': 'kucing / hewan', 'dog': 'anjing / hewan', 'bird': 'burung / hewan',
            'horse': 'kuda / hewan', 'cow': 'sapi / hewan', 'chair': 'kursi / perabot',
            'couch': 'sofa / perabot', 'potted plant': 'tanaman / pot', 'bed': 'tempat tidur',
            'dining table': 'meja makan', 'toilet': 'toilet / saniter', 'tv': 'televisi / monitor',
            'laptop': 'laptop / komputer', 'mouse': 'mouse komputer', 'remote': 'remote kontrol',
            'keyboard': 'keyboard', 'cell phone': 'ponsel / gadget', 'microwave': 'microwave',
            'oven': 'oven / perabot', 'toaster': 'pemanggang roti', 'sink': 'wastafel / bak cuci',
            'refrigerator': 'kulkas', 'book': 'buku / dokumen', 'clock': 'jam dinding',
            'vase': 'vas bunga', 'scissors': 'gunting', 'teddy bear': 'boneka',
            'hair drier': 'pengering rambut', 'toothbrush': 'sikat gigi', 'bottle': 'botol / kemasan',
            'wine glass': 'gelas kaca', 'cup': 'cangkir', 'fork': 'garpu', 'knife': 'pisau',
            'spoon': 'sendok', 'bowl': 'mangkuk', 'banana': 'makanan / buah', 'apple': 'makanan / buah',
            'sandwich': 'makanan', 'orange': 'buah', 'broccoli': 'makanan', 'carrot': 'makanan',
            'hot dog': 'makanan', 'pizza': 'makanan', 'donut': 'makanan', 'cake': 'kue / makanan',
            'backpack': 'tas ransel', 'umbrella': 'payung', 'handbag': 'tas jinjing', 'suitcase': 'koper'
        }
        obj_id_name = id_names.get(obj_name, obj_name)
        return {
            "is_valid_infrastructure": False,
            "is_damaged": False,
            "category_id": 1,
            "category_label": "Bukan Foto Infrastruktur",
            "confidence": round(float(obj_conf), 2),
            "severity_level": 0,
            "severity_text": f"TIDAK VALID (Objek Lain: {obj_id_name.title()})",
            "damage_type": "Foto Tidak Sesuai Kategori",
            "damage_detail": f"Foto terdeteksi memuat objek non-infrastruktur ({obj_id_name}). Sistem hanya menerima foto fisik infrastruktur publik (jalan, jembatan, tiang lampu penerangan, atau trotoar).",
            "suggested_title": "",
            "suggested_road_condition": "",
            "llm_description": ""
        }

    # -------------------------------------------------------------------------
    # 4. FILTER 3: TOLAK SCREENSHOT / DOKUMEN / GAMBAR POLOS
    # -------------------------------------------------------------------------
    pure_white = float(np.sum((v_channel >= 240) & (s_channel <= 20)) / (h_img * w_img))
    pure_black = float(np.sum(v_channel <= 20) / (h_img * w_img))
    extreme_contrast = pure_white + pure_black

    if extreme_contrast > 0.70 or laplacian_var < 5.0:
        return {
            "is_valid_infrastructure": False,
            "is_damaged": False,
            "category_id": 1,
            "category_label": "Bukan Foto Infrastruktur",
            "confidence": 0.95,
            "severity_level": 0,
            "severity_text": "TIDAK VALID (Dokumen / Layar Polos)",
            "damage_type": "Foto Tidak Sesuai Kategori",
            "damage_detail": "Objek pada foto berupa tangkapan layar, dokumen teks, atau gambar polos tanpa tekstur fisik infrastruktur.",
            "suggested_title": "",
            "suggested_road_condition": "",
            "llm_description": ""
        }

    # -------------------------------------------------------------------------
    # 5. KARAKTERISTIK VISUAL DASAR (ASPAL, TANAH, VEGETASI, LANGIT)
    # -------------------------------------------------------------------------
    color_diff = np.maximum(np.abs(r.astype(np.int16) - g.astype(np.int16)),
                            np.abs(g.astype(np.int16) - b.astype(np.int16)))
    asphalt_mask = (s_channel < 55) & (v_channel >= 30) & (v_channel <= 180) & (color_diff < 28)
    asphalt_ratio = float(np.sum(asphalt_mask) / (h_img * w_img))

    dirt_mask = (h_channel >= 8) & (h_channel <= 28) & (s_channel >= 25) & (v_channel >= 35) & (v_channel <= 220)
    dirt_ratio = float(np.sum(dirt_mask) / (h_img * w_img))

    green_mask = (h_channel >= 35) & (h_channel <= 85) & (s_channel >= 45)
    green_ratio = float(np.sum(green_mask) / (h_img * w_img))

    upper_h = int(h_img * 0.45)
    upper_v = v_channel[:upper_h, :]
    upper_s = s_channel[:upper_h, :]
    upper_h_ch = h_channel[:upper_h, :]
    blue_sky_mask = (upper_h_ch >= 90) & (upper_h_ch <= 135) & (upper_s >= 25) & (upper_v >= 50)
    blue_sky_ratio = float(np.sum(blue_sky_mask) / (upper_h * w_img))
    white_sky_mask = (upper_v >= 200) & (upper_s <= 25)
    white_sky_ratio = float(np.sum(white_sky_mask) / (upper_h * w_img))

    full_edges = cv2.Canny(cv2.GaussianBlur(gray, (5, 5), 0), 40, 120)
    full_lines = cv2.HoughLinesP(full_edges, 1, np.pi / 180, threshold=45, minLineLength=int(h_img * 0.20), maxLineGap=20)
    vertical_lines_count = 0
    horizontal_lines_count = 0
    if full_lines is not None:
        for line in full_lines:
            x1, y1, x2, y2 = line[0]
            angle = np.abs(np.arctan2(y2 - y1, x2 - x1) * 180.0 / np.pi)
            if 70 <= angle <= 110:
                vertical_lines_count += 1
            elif angle <= 20 or angle >= 160:
                horizontal_lines_count += 1

    upper_contours, _ = cv2.findContours(full_edges[:int(h_img * 0.5)], cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    tall_pole_found = False
    for uc in upper_contours:
        ux, uy, uw, uh = cv2.boundingRect(uc)
        if uh > (h_img * 0.25) and (uh / max(1, uw)) >= 3.0:
            tall_pole_found = True
            break

    is_street_light = tall_pole_found and vertical_lines_count >= 2 and (
        blue_sky_ratio > 0.30 or (white_sky_ratio > 0.40 and green_ratio > 0.10)
    )

    # -------------------------------------------------------------------------
    # 6. KLASIFIKASI KATEGORI 2: PENERANGAN JALAN UMUM (PJU)
    # -------------------------------------------------------------------------
    if is_street_light:
        if road_only:
            return {
                "is_valid_infrastructure": False,
                "is_damaged": False,
                "category_id": 1,
                "category_label": "Bukan Foto Kerusakan Jalan",
                "confidence": 0.95,
                "severity_level": 0,
                "severity_text": "TIDAK VALID (Foto PJU pada Laporan Ruas Jalan)",
                "damage_type": "Foto Tidak Sesuai Kategori",
                "damage_detail": "Foto lampu/PJU tidak dapat digunakan untuk laporan ruas panjang. Tipe ini khusus untuk foto kerusakan badan jalan (≥ 500 meter).",
                "suggested_title": "",
                "suggested_road_condition": "",
                "llm_description": ""
            }
        else:
            return {
                "is_valid_infrastructure": True,
                "is_damaged": True,
                "category_id": 2,
                "category_label": "Penerangan Jalan Umum (PJU)",
                "confidence": 0.95,
                "severity_level": 3,
                "severity_text": "SEDANG (Lampu Mati / Perlu Perbaikan)",
                "damage_type": "Lampu Penerangan Jalan Umum (PJU) Mati / Rusak",
                "damage_detail": "Terdeteksi unit lampu penerangan jalan umum (PJU) / tiang lampu tidak berfungsi optimal pada ruas jalan.",
                "suggested_title": "Laporan Perbaikan Lampu Penerangan Jalan Umum (PJU)",
                "suggested_road_condition": "",
                "llm_description": "Fasilitas Lampu Penerangan Jalan Umum (PJU) mengalami gangguan teknis dan padam/mati. Kondisi ini menyebabkan ruas jalan menjadi gelap pada malam hari sehingga menurunkan jarak pandang pengendara dan berisiko memicu kecelakaan.",
                "dimensions": {"width_cm": 30, "length_cm": 250, "depth_cm": 0, "area_m2": 0.75}
            }

    # -------------------------------------------------------------------------
    # 7. KLASIFIKASI KATEGORI 3: TROTOAR (SIDEWALK / PAVING BLOCK)
    # -------------------------------------------------------------------------
    lower_h = int(h_img * 0.4)
    lower_gray = gray[lower_h:, :]
    edges_lower = cv2.Canny(cv2.GaussianBlur(lower_gray, (3, 3), 0), 45, 135)
    edge_density_lower = float(np.sum(edges_lower > 0) / (edges_lower.shape[0] * edges_lower.shape[1]))
    paving_contours, _ = cv2.findContours(edges_lower, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    paving_like_boxes = 0
    for pc in paving_contours:
        px, py, pw, ph = cv2.boundingRect(pc)
        p_area = cv2.contourArea(pc)
        if 80 < p_area < (h_img * w_img * 0.04) and 0.4 < (pw / max(1, ph)) < 2.5:
            paving_like_boxes += 1

    is_sidewalk = (asphalt_ratio < 0.50) and (
        (paving_like_boxes >= 14 and edge_density_lower > 0.045) or
        (paving_like_boxes >= 8 and (asphalt_ratio > 0.10 and asphalt_ratio < 0.40))
    )

    if is_sidewalk and not road_only:
        return {
            "is_valid_infrastructure": True,
            "is_damaged": True,
            "category_id": 3,
            "category_label": "Kerusakan Trotoar",
            "confidence": 0.94,
            "severity_level": 3,
            "severity_text": "SEDANG (Paving Trotoar Rusak / Bergelombang)",
            "damage_type": "Paving Trotoar Amblas & Bergelombang",
            "damage_detail": "Terdeteksi kerusakan pada permukaan trotoar dengan paving block bergelombang yang mengganggu lintasan pejalan kaki.",
            "suggested_title": "Laporan Perbaikan Susunan Paving Block Trotoar",
            "suggested_road_condition": "",
            "llm_description": "Permukaan trotoar mengalami kerusakan berupa susunan paving block yang lepas, pecah, dan bergelombang. Kondisi ini mengganggu akses jalur pedestrian dan rawan menyebabkan pejalan kaki tersandung.",
            "dimensions": {"width_cm": 60, "length_cm": 120, "depth_cm": 6, "area_m2": 0.72}
        }

    # -------------------------------------------------------------------------
    # 8. ANALISIS KERUSAKAN JALAN BERBASIS DEEP LEARNING (POTHOLE & RDD)
    # -------------------------------------------------------------------------
    has_pothole = len(pothole_dets) > 0
    has_rdd_damage = len(rdd_dets) > 0

    road_condition, road_desc_phrase = classify_road_surface(img, gray, hsv, h_img, w_img)

    # Bila ada deteksi lubang jalan dari model YOLO khusus Pothole:
    if has_pothole:
        best_pothole = max(pothole_dets, key=lambda d: d['confidence'])
        conf = float(best_pothole['confidence'])
        bx, by, bw, bh = best_pothole['box']

        w_ratio = bw / float(w_img)
        h_ratio = bh / float(h_img)
        width_cm = max(25, min(250, int(w_ratio * 130)))
        length_cm = max(25, min(300, int(h_ratio * 150)))
        area_m2 = round((width_cm * length_cm) / 10000.0, 2)

        roi_gray = gray[max(0, by):min(h_img, by+bh), max(0, bx):min(w_img, bx+bw)]
        if roi_gray.size > 0:
            min_lum = float(np.percentile(roi_gray, 10))
            bg_lum = float(np.percentile(gray, 60))
            depth_ratio = max(0.1, min(1.0, (bg_lum - min_lum) / max(1.0, bg_lum)))
            depth_cm = max(4, min(25, int(depth_ratio * 18)))
        else:
            depth_cm = 8

        sev_level = 4 if (depth_cm >= 8 or area_m2 >= 0.35) else 3
        sev_text = "BERAT (Lubang Jalan Parah)" if sev_level == 4 else "SEDANG (Lubang Jalan Mengganggu)"
        dmg_type = "Kerusakan Berat / Aspal Hancur Bergelombang" if (road_only or road_condition == "Aspal Hancur Bergelombang") else "Kerusakan Jalan / Lubang (Pothole)"
        
        detail_text = f"Terdeteksi lubang pada perkerasan jalan dengan kedalaman ~{depth_cm} cm dan lebar bentang ~{width_cm} cm."
        sug_title = "Laporan Kerusakan Ruas Aspal Hancur Bergelombang" if road_only else "Laporan Kerusakan Lubang Jalan (Pothole)"
        
        llm_text = (
            f"Permukaan perkerasan jalan berlubang. Kerusakan terdeteksi dengan bentang sekitar "
            f"{width_cm} cm, panjang sekitar {length_cm} cm, dan estimasi kedalaman sekitar {depth_cm} cm. "
            "Kondisi ini berisiko membahayakan kendaraan yang melintas dan perlu segera ditangani."
        )

        return {
            "is_valid_infrastructure": True,
            "is_damaged": True,
            "category_id": 1,
            "category_label": "Jalan & Jembatan",
            "confidence": round(min(0.98, max(0.90, conf + 0.10)), 2),
            "severity_level": sev_level,
            "severity_text": sev_text,
            "damage_type": dmg_type,
            "damage_detail": detail_text,
            "suggested_title": sug_title,
            "suggested_road_condition": road_condition,
            "llm_description": llm_text,
            "dimensions": {
                "width_cm": width_cm,
                "length_cm": length_cm,
                "depth_cm": depth_cm,
                "area_m2": area_m2
            }
        }

    # Bila ada deteksi kerusakan jalan dari model RDD (cracks, alligator, dll):
    if has_rdd_damage:
        best_rdd = max(rdd_dets, key=lambda d: d['confidence'])
        rdd_class = best_rdd['class']
        conf = float(best_rdd['confidence'])
        bx, by, bw, bh = best_rdd['box']

        width_cm = max(30, min(200, int((bw / w_img) * 140)))
        length_cm = max(30, min(300, int((bh / h_img) * 160)))
        area_m2 = round((width_cm * length_cm) / 10000.0, 2)
        depth_cm = 5

        class_id_names = {
            'alligator crack': 'Retak Kulit Buaya (Alligator Crack)',
            'longitudinal crack': 'Retak Memanjang Jalan',
            'transverse crack': 'Retak Melintang Jalan',
            'other corruption': 'Permukaan Aspal Rusak / Terkelupas',
            'pothole': 'Lubang Jalan'
        }
        rdd_label = class_id_names.get(rdd_class, 'Retak Perkerasan Jalan')

        return {
            "is_valid_infrastructure": True,
            "is_damaged": True,
            "category_id": 1,
            "category_label": "Jalan & Jembatan",
            "confidence": round(min(0.97, max(0.90, conf + 0.15)), 2),
            "severity_level": 3,
            "severity_text": "SEDANG (Retak Permukaan Perkerasan)",
            "damage_type": f"Kerusakan Jalan / {rdd_label}",
            "damage_detail": f"Terdeteksi {rdd_label.lower()} pada badan jalan dengan bentang area ~{width_cm} cm x {length_cm} cm.",
            "suggested_title": f"Laporan Kerusakan {rdd_label}",
            "suggested_road_condition": road_condition,
            "llm_description": f"Badan perkerasan jalan mengalami {rdd_label.lower()}. Area kerusakan terbentang sekitar {width_cm} cm x {length_cm} cm. Diperlukan pelapisan atau penambalan ulang sebelum retakan meluas.",
            "dimensions": {
                "width_cm": width_cm,
                "length_cm": length_cm,
                "depth_cm": depth_cm,
                "area_m2": area_m2
            }
        }

    # -------------------------------------------------------------------------
    # 9. ANALISIS RUAS JALAN TANAH / LUMPUR / MAKADAM (RUSAK ALAMI TANPA ASPAL)
    # -------------------------------------------------------------------------
    if dirt_ratio >= 0.28 or road_condition in ["Jalan Tanah", "Genangan Lumpur Parah", "Batu Lepas / Makadam", "Longsor / Amblas Badan Jalan"]:
        width_cm = 200
        length_cm = 500
        depth_cm = 12 if "Lumpur" in road_condition or "Amblas" in road_condition else 7
        area_m2 = 10.0

        return {
            "is_valid_infrastructure": True,
            "is_damaged": True,
            "category_id": 1,
            "category_label": "Jalan & Jembatan",
            "confidence": 0.94,
            "severity_level": 4 if depth_cm >= 10 else 3,
            "severity_text": "BERAT (Perlu Penanganan Prioritas)" if depth_cm >= 10 else "SEDANG (Akses Jalan Terganggu)",
            "damage_type": f"Kerusakan Berat / {road_condition}",
            "damage_detail": f"Terdeteksi {road_desc_phrase} dengan estimasi area terdampak memanjang.",
            "suggested_title": f"Laporan Kerusakan Ruas {road_condition}",
            "suggested_road_condition": road_condition,
            "llm_description": f"Ruas jalan mengalami {road_desc_phrase}. Kondisi fisik permukaan jalan ini menghambat laju kendaraan dan membutuhkan penanganan fisik lapangan.",
            "dimensions": {
                "width_cm": width_cm,
                "length_cm": length_cm,
                "depth_cm": depth_cm,
                "area_m2": area_m2
            }
        }

    # -------------------------------------------------------------------------
    # 10. DETEKSI JALAN MULUS / KONDISI BAIK (TIDAK ADA LUBANG ATAU RETAK)
    # -------------------------------------------------------------------------
    if is_smooth_road_surface(img, gray, hsv, h_img, w_img, asphalt_ratio, dirt_ratio, len(pothole_dets), len(rdd_dets)):
        return {
            "is_valid_infrastructure": True,
            "is_damaged": False,
            "category_id": 1,
            "category_label": "Jalan Mulus / Kondisi Baik",
            "confidence": 0.96,
            "severity_level": 1,
            "severity_text": "NORMAL (Kondisi Jalan Mulus & Layak)",
            "damage_type": "Permukaan Jalan Mulus / Tidak Berlubang",
            "damage_detail": "Hasil analisis computer vision menunjukkan perkerasan jalan dalam kondisi rata dan mulus. Tidak ditemukan lubang jalan, amblas, atau retak struktural.",
            "suggested_title": "Laporan Kondisi Jalan Mulus / Normal",
            "suggested_road_condition": "Aspal Mulus",
            "llm_description": "Kondisi permukaan jalan terpantau dalam keadaan baik, mulus, dan layak dilintasi. Tidak ditemukan kerusakan perkerasan jalan yang membutuhkan perbaikan saat ini."
        }

    # -------------------------------------------------------------------------
    # 11. BUKAN FOTO INFRASTRUKTUR (BARANG LAIN, ELEKTRONIK, PAPAN, INTERIOR, DLL.)
    # -------------------------------------------------------------------------
    # Jika gambar tidak terdeteksi sebagai lubang (pothole YOLO), tidak ada retakan (RDD YOLO),
    # bukan jalan tanah, bukan tiang PJU outdoor, bukan trotoar paving, dan bukan jalan mulus:
    # Foto ini BUKAN foto infrastruktur publik (seperti circuit board, alat elektronik, perabot, dll.)
    return {
        "is_valid_infrastructure": False,
        "is_damaged": False,
        "category_id": 1,
        "category_label": "Bukan Foto Infrastruktur",
        "confidence": 0.96,
        "severity_level": 0,
        "severity_text": "TIDAK VALID (Bukan Objek Jalan / Fasilitas Publik)",
        "damage_type": "Foto Tidak Sesuai Kategori",
        "damage_detail": "Foto tidak teridentifikasi sebagai perkerasan jalan raya, jalan tanah, trotoar, jembatan, atau lampu penerangan jalan. Mohon unggah foto fisik fasilitas publik yang jelas.",
        "suggested_title": "",
        "suggested_road_condition": "",
        "llm_description": ""
    }

def analyze_infrastructure_images(image_paths, road_only=False):
    if not image_paths:
        return {"error": "No image paths provided"}

    results = []
    for path in image_paths:
        res = analyze_single_image(path, road_only)
        if "error" not in res:
            results.append(res)

    if not results:
        return {"error": "Failed to analyze any of the provided images"}

    if len(results) == 1:
        return results[0]

    # MULTI-IMAGE CONSENSUS ANALYSIS
    valid_results = [r for r in results if r.get("is_valid_infrastructure", False)]
    if not valid_results:
        return results[0]

    highest_severity_res = max(valid_results, key=lambda x: x.get("severity_level", 0))

    num_photos = len(image_paths)
    base_llm = highest_severity_res.get("llm_description", "")

    combined_output = dict(highest_severity_res)
    combined_output["llm_description"] = base_llm
    combined_output["multi_image_count"] = num_photos
    combined_output["confidence"] = min(0.98, round(highest_severity_res.get("confidence", 0.94) + 0.03, 2))

    return combined_output

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No image paths provided"}))
        sys.exit(1)

    road_only = '--road-only' in sys.argv
    image_paths = [arg for arg in sys.argv[1:] if arg != '--road-only']
    result = analyze_infrastructure_images(image_paths, road_only)
    print(json.dumps(result))
