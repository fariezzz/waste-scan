from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from ultralytics import YOLO
from ensemble import classify_image
from PIL import Image
import numpy as np
from io import BytesIO

print("🔥 FastAPI Loaded Successfully")

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

yolo_model = YOLO("deep-learning/models/best.pt")

@app.get("/")
def root():
    return {"message": "API is running"}

@app.post("/detect")
async def detect(image: UploadFile = File(...)):
    content = await image.read()
    img = Image.open(BytesIO(content)).convert("RGB")
    img = np.array(img)

    results = yolo_model.predict(img, verbose=False)[0]

    detections = []

    if results.boxes is not None and len(results.boxes) > 0:
        for *xyxy, conf, cls in results.boxes.data.tolist():
            detections.append({
                "class": yolo_model.names[int(cls)],
                "confidence": float(conf),
                "box": xyxy
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
    return {"jenis": jenis, "kategori": kategori}
