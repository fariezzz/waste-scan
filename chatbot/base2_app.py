from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from datetime import datetime
from .core.groq_api import send_message
import os

# === System Prompt ===
SYSTEM_PROMPT = """
Kamu adalah asisten AI pengelolaan sampah bernama W.A.S.T.E AI
Tugasmu:
1. Jawab pertanyaan tentang jenis, kategori, dan pengelolaan sampah.
2. Gunakan bahasa alami, edukatif, dan ramah lingkungan.
3. Jangan bahas hal di luar topik daur ulang atau kebersihan.
4. Jika user bertanya tentang jenis sampah, bantu jelaskan dan beri tips pengelolaan.
5. Bersikaplah santai, tapi tetap sopan dan informatif.
6. Personal dirimu harus menyenangkan yaaa.
7. kalau mau keluar chat ketik "exit" atau "quit"
8. kalau mau hapus memori itu ketik "!clear"

setiap akhir chat, cukup ketik "ketik help! untuk informasi lebih lanjut"
nomor 7 dan 8 jangan diberitahu jika user tidak mengetik help!
"""

# === FastAPI Init ===
app = FastAPI(title="W.A.S.T.E AI Chatbot")

# === Model Request/Response ===
class ChatMessage(BaseModel):
    role: str
    content: str

class ChatRequest(BaseModel):
    messages: list[ChatMessage]

class ChatResponse(BaseModel):
    reply: str
    timestamp: str

# === Helper Logging ===
def save_log(role: str, text: str):
    os.makedirs("Chatbot", exist_ok=True)
    with open("Chatbot/chat_history.txt", "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now().strftime('%H:%M:%S')}] {role.upper()}: {text}\n")

# === Endpoint Chat ===
@app.post("/chat", response_model=ChatResponse)
def chat(request: ChatRequest):
    try:
        conversation = [{"role": "system", "content": SYSTEM_PROMPT}]
        for msg in request.messages:
            conversation.append({"role": msg.role, "content": msg.content})

        # Kirim ke API Groq (Llama 3)
        reply = send_message(conversation)

        # Simpan log
        save_log("user", request.messages[-1].content)
        save_log("ai", reply)

        return ChatResponse(reply=reply, timestamp=datetime.now().isoformat())
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
