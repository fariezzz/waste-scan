import os
import requests
from dotenv import load_dotenv, find_dotenv

# === Load .env secara eksplisit dari lokasi pasti ===
current_dir = os.path.dirname(os.path.abspath(__file__))   # posisi file groq_api.py
env_path = os.path.join(current_dir, "..", ".env")         # naik satu folder ke Chatbot/.env
env_path = os.path.abspath(env_path)                       # ubah ke path absolut
print("Mencari .env di:", env_path)

load_dotenv(dotenv_path=env_path)

API_KEY = "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"
print("Loaded API Key.....", bool(API_KEY))  # harus True nanti

# URL endpoint Groq
API_URL = "https://api.groq.com/openai/v1/chat/completions"

# Fungsi utama kirim pesan ke Groq
def send_message(conversation, model="llama-3.3-70b-versatile"):
    if not API_KEY:
        raise ValueError("GROQ_API_KEY tidak ditemukan di file .env!")

    headers = {
        "Authorization": f"Bearer {API_KEY}",
        "Content-Type": "application/json"
    }

    payload = {
        "model": model,
        "messages": conversation
    }

    response = requests.post(API_URL, headers=headers, json=payload)

    if response.status_code == 200:
        
        """
        hasil kiriman data dari server dalam bentuk JSON
        
        {
  "id": "chatcmpl-123abc",
  "object": "chat.completion",
  "created": 1731258921,
  "model": "llama3-70b-8192",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "Sampah organik adalah bahan yang mudah terurai..."
      },
      "finish_reason": "stop"
    }
  ]
}

        """
        
        return response.json()["choices"][0]["message"]["content"]
    else:
        return f"[Error {response.status_code}] {response.text}"
