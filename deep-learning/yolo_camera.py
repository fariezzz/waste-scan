import cv2
from ultralytics import YOLO
from collections import Counter

# ============================================
# 1) LOAD MODEL
# ============================================
MODEL_PATH = "Models/best.pt"   # sesuaikan dengan posisi best.pt lo

print(f"[INFO] Loading model dari: {MODEL_PATH}")
model = YOLO(MODEL_PATH)

# Nama kelas dari model (sesuai dataset YOLO)
# contoh: {0: 'aerosol', 1: 'kaleng aluminium', ...}
CLASS_NAMES = model.names


# ============================================
# 2) FUNGSI DETEKSI + HITUNG PER FRAME
# ============================================
def run_yolo_camera():
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("[ERROR] Kamera nggak kebuka. Cek device index / driver.")
        return

    print("\n📹 YOLO Waste Detector jalan!")
    print("ESC = keluar\n")

    while True:
        ret, frame = cap.read()
        if not ret:
            print("[ERROR] Gagal baca frame dari kamera.")
            break

        # ----------------------------------------
        # YOLO INFERENCE
        # ----------------------------------------
        # results: list, tapi kita cuma pakai yang pertama [0]
        results = model(frame, verbose=False)[0]
        boxes = results.boxes

        # Counter jumlah objek di frame ini
        counts = Counter()

        if boxes is not None and len(boxes) > 0:
            # pindah ke CPU & numpy biar gampang
            xyxy = boxes.xyxy.cpu().numpy()
            cls = boxes.cls.cpu().numpy().astype(int)
            conf = boxes.conf.cpu().numpy()

            for (x1, y1, x2, y2), c, cf in zip(xyxy, cls, conf):
                class_id = int(c)
                class_name = CLASS_NAMES.get(class_id, str(class_id))

                # Tambah hitungan
                counts[class_name] += 1

                # Gambar bounding box
                x1, y1, x2, y2 = map(int, [x1, y1, x2, y2])
                cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)

                label = f"{class_name} {cf:.2f}"
                cv2.putText(
                    frame, label, (x1, max(y1 - 5, 15)),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 2
                )

        # ----------------------------------------
        # TAMPILKAN JUMLAH OBJEK DI FRAME INI
        # ----------------------------------------
        y0 = 25
        cv2.rectangle(frame, (5, 5), (260, y0 + 20 * max(1, len(counts))), (0, 0, 0), -1)

        if counts:
            for i, (name, cnt) in enumerate(sorted(counts.items())):
                text = f"{name}: {cnt}"
                cv2.putText(
                    frame, text, (10, y0 + i * 20),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 255), 2
                )
        else:
            cv2.putText(
                frame, "Tidak ada sampah terdeteksi",
                (10, y0),
                cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 255), 2
            )

        # Tampilkan frame
        cv2.imshow("YOLO Waste Detector (Real-time)", frame)

        # ESC buat keluar
        key = cv2.waitKey(1) & 0xFF
        if key == 27:
            break

    cap.release()
    cv2.destroyAllWindows()


if __name__ == "__main__":
    run_yolo_camera()
