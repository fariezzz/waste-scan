from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from pydantic import BaseModel
from datetime import datetime
from chatbot.core.groq_api import send_message
import os

app = FastAPI(title="W.A.S.T.E AI Chatbot")

# === Konfigurasi folder templates dan static ===
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
templates = Jinja2Templates(directory=os.path.join(BASE_DIR, "templates"))
app.mount("/static", StaticFiles(directory=os.path.join(BASE_DIR, "static")), name="static")

SYSTEM_PROMPT = """
Kamu adalah asisten AI pengelolaan sampah bernama W.A.S.T.E AI
Tugasmu:
1. Jawab pertanyaan tentang jenis, kategori, dan pengelolaan sampah.
2. Gunakan bahasa alami, edukatif, dan ramah lingkungan.
3. Jika user bertanya tentang jenis sampah, bantu jelaskan dan beri tips pengelolaan.
4. Bersikaplah santai, tapi tetap sopan dan informatif.
5. Personal dirimu harus menyenangkan yaaa.
6. Jangan selalu memperkenalkan diri, hanya ketika user meminta dan hanya di chat pertama saja.
"""

class ChatMessage(BaseModel):
    role: str
    content: str

class ChatRequest(BaseModel):
    messages: list[ChatMessage]

class ChatResponse(BaseModel):
    reply: str
    timestamp: str

def save_log(role: str, text: str):
    os.makedirs("chatbot", exist_ok=True)
    with open("chatbot/chat_history.txt", "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now().strftime('%H:%M:%S')}] {role.upper()}: {text}\n")

@app.get("/", response_class=HTMLResponse)
def home(request: Request):
    """Tampilkan halaman web chatbot"""
    return templates.TemplateResponse("chat.html", {"request": request})

@app.post("/", response_model=ChatResponse)
def chat(request: ChatRequest):
    try:
        # Siapkan percakapan
        conversation = [{"role": "system", "content": SYSTEM_PROMPT}]
        for msg in request.messages:
            conversation.append({"role": msg.role, "content": msg.content})

        # Kirim ke model Llama 3
        reply = send_message(conversation)

        # Simpan log ke file
        save_log("user", request.messages[-1].content)
        save_log("ai", reply)

        # Balikkan hasil ke frontend (atau Swagger)
        return ChatResponse(reply=reply, timestamp=datetime.now().isoformat())

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
