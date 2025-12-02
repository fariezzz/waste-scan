from fastapi import FastAPI, UploadFile, File
import torch
from ultralytics import YOLO
from PIL import Image
import numpy as np
from ensemble import classify_image
from fastapi import Request
from io import BytesIO

app = FastAPI()

yolo_model = YOLO("deep-learning/models/best.pt")

@app.post("/detect")
async def detect(image: UploadFile = File(...)):
    content = await image.read()
    img = Image.open(BytesIO(content)).convert("RGB")
    img = np.array(img)

    results = yolo_model(img, verbose=False)[0]
    detections = []

    if results.boxes is not None:
        for box in results.boxes:
            x1, y1, x2, y2 = box.xyxy[0].tolist()
            cls = int(box.cls[0])
            conf = float(box.conf[0])
            class_name = yolo_model.names[cls]

            detections.append({
                "class": class_name,
                "confidence": conf,
                "box": [x1, y1, x2, y2]
            })

    return {
        "count": len(detections),
        "detections": detections
    }


@app.post("/classify")
async def classify(image: UploadFile = File(...)):
    content = await image.read()
    img = Image.open(BytesIO(content)).convert("RGB")

    img_path = "temp.jpg"
    img.save(img_path)

    jenis, kategori = classify_image(img_path)

    return {
        "jenis": jenis,
        "kategori": kategori
    }