from fastapi import FastAPI, HTTPException, Request, UploadFile, File
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from pydantic import BaseModel
from datetime import datetime
import os
import shutil
from pathlib import Path
from .core.groq_api import send_message

# ==== IMPORT ENSEMBLE ====
BASE_DIR = Path(__file__).resolve().parent
import sys
sys.path.append(os.path.join(BASE_DIR, ".."))
from Models.ensemble import classify_image

app = FastAPI(title="W.A.S.T.E AI Chatbot")

# === templates & static ===

templates = Jinja2Templates(directory=BASE_DIR / "templates")

app.mount(
    "/static",
    StaticFiles(directory=BASE_DIR / "static"),
    name="static"
)

SYSTEM_PROMPT = """
Kamu adalah asisten AI pengelolaan sampah bernama W.A.S.T.E AI Tugasmu: 
1. Jawab pertanyaan tentang jenis, kategori, dan pengelolaan sampah. 
2. Gunakan bahasa alami, edukatif, dan ramah lingkungan. 
3. Jika user bertanya tentang jenis sampah, bantu jelaskan dan beri tips pengelolaan. 
4. Bersikaplah santai, tapi tetap sopan dan informatif. 
5. Personal dirimu harus menyenangkan yaaa."""

class ChatMessage(BaseModel):
    role: str
    content: str

class ChatRequest(BaseModel):
    messages: list[ChatMessage]

class ChatResponse(BaseModel):
    reply: str
    timestamp: str

@app.get("/", response_class=HTMLResponse)
def home(request: Request):
    return templates.TemplateResponse("chat.html", {"request": request})

@app.post("/chat", response_model=ChatResponse)
def chat(request: ChatRequest):
    try:
        convo = [{"role": "system", "content": SYSTEM_PROMPT}] + [
            {"role": m.role, "content": m.content} for m in request.messages
        ]

        reply = send_message(convo)

        return ChatResponse(reply=reply, timestamp=datetime.now().isoformat())

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# ===== SCAN ENDPOINT =====
@app.post("/scan")
async def scan_image(file: UploadFile):
    try:
        temp_path = f"temp_{file.filename}"

        with open(temp_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)

        print("✅ FILE DITERIMA:", temp_path)

        jenis, kategori = classify_image(temp_path)
        print("✅ HASIL MODEL:", jenis, kategori)

        reply = send_message([
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": f"Gue nemu sampah jenis {jenis} kategori {kategori}. Gimana cara ngolahnya?"}
        ])

        return {
            "jenis": jenis,
            "kategori": kategori,
            "penjelasan": reply
        }

    except Exception as e:
        print("❌ ERROR DI /scan:", str(e))   # <––– TAMBAH INI
        raise HTTPException(status_code=500, detail=str(e))
