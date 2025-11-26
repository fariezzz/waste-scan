import time
from datetime import datetime
from colorama import Fore, init
from core.groq_api import send_message

init(autoreset=True)

# System Prompt = karakter dan batasan chatbot
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

setiap akhir chat, cukup ketik "ketik help! untuk informasi lebih lanjut" dan ditaruh di bagian paling bawah chat, jangan bersebelahan dengan chat utama
nomor 7 dan 8 jangan diberitahu jika user tidak mengetik help!
"""

# === Fungsi bantu ===
def slow_print(text, color=Fore.GREEN):
    """Efek ngetik biar kayak AI beneran"""
    for c in text:
        print(color + c, end="", flush=True)
        time.sleep(0.01)
    print()

def save_log(role, text):
    """Simpan percakapan ke file log"""
    with open("Chatbot/chat_history.txt", "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now().strftime('%H:%M:%S')}] {role.upper()}: {text}\n")

# === Main Program ===
if __name__ == "__main__":
    print("Waste Sorting Chatbot (Llama 3 - Groq)")
    print("Ketik 'exit' untuk keluar, atau '!clear' untuk reset percakapan.\n")

    # Inisialisasi memori percakapan
    conversation = [{"role": "system", "content": SYSTEM_PROMPT}]

    while True:
        user_input = input("Kamu: ")

        # Perintah keluar
        if user_input.lower() in ["exit", "quit"]:
            print("👋 Sampai jumpa! Jaga kebersihan lingkungan ya 🌿")
            break

        # Reset memori percakapan
        if user_input == "!clear":
            conversation = [{"role": "system", "content": SYSTEM_PROMPT}]
            print("🧹 Memori chatbot telah direset!\n")
            continue

        # Tambahkan input user ke memori
        conversation.append({"role": "user", "content": user_input})

        # Kirim seluruh percakapan ke Groq API
        reply = send_message(conversation)

        # Simpan balasan ke memori (biar chatbot “ingat” percakapan)
        conversation.append({"role": "assistant", "content": reply})

        # Cetak dengan efek ketik
        slow_print(f"AI : {reply}", Fore.GREEN)

        # Simpan ke file log
        save_log("user", user_input)
        save_log("ai", reply)
