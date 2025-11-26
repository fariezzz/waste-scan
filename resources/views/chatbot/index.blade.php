@extends('layouts.main')

@section('content')
    <div class="chatbot-page container d-flex flex-column justify-content-center align-items-center">

        <div id="welcome-box" class="text-center mb-3">
            <img src="{{ asset('img/logo.png') }}" class="welcome-logo">
            <p class="welcome-text mt-3">Hai! Mau bertanya tentang apa nih? 😊</p>
        </div>

        <div id="chat-container" class="chat-box">
            <div id="chat-body" class="chat-body"></div>

            <div class="chat-input-area">
                <div class="chat-input-wrapper">
                    <button id="reset-chat" class="reset-btn-input d-none">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#7BAA00"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 5V1L7 6L12 11V7C15.31 7 18 9.69 18 13C18 16.31 15.31 19 12 19C8.69 19 6 16.31 6 13H4C4 17.42 7.58 21 12 21C16.42 21 20 17.42 20 13C20 8.58 16.42 5 12 5Z" />
                        </svg>
                    </button>

                    <input type="text" id="chat-input" placeholder="Tulis pesan...">

                    <button id="chat-send-btn">
                        <img src="{{ asset('icons/send.svg') }}" width="28">
                    </button>
                </div>
            </div>

        </div>


    </div>

    <div id="reset-modal" class="reset-modal-overlay d-none">
        <div class="reset-modal-box">
            <h4>Reset Chat?</h4>
            <p>Semua riwayat percakapan akan dihapus, yakin nih?</p>

            <div class="reset-modal-actions">
                <button id="cancel-reset" class="btn-cancel">Batal</button>
                <button id="confirm-reset" class="btn-confirm">Reset</button>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const messageInput = document.getElementById("chat-input");
            const sendBtn = document.getElementById("chat-send-btn");
            const chatBody = document.getElementById("chat-body");
            const welcomeBox = document.getElementById("welcome-box");
            const resetBtn = document.getElementById("reset-chat");
            const resetModal = document.getElementById("reset-modal");
            const cancelReset = document.getElementById("cancel-reset");
            const confirmReset = document.getElementById("confirm-reset");

            let chatHistory = JSON.parse(localStorage.getItem("chat_history")) || [];

            let firstMessageSent = chatHistory.length > 0;

            if (firstMessageSent) {
                welcomeBox.classList.add("d-none");
                resetBtn.classList.remove("d-none")
            }

            function saveChatHistory() {
                localStorage.setItem("chat_history", JSON.stringify(chatHistory));
            }

            function appendMessage(text, sender = "user", skipSave = false) {
                const msg = document.createElement("div");
                msg.classList.add("bubble", sender === "user" ? "bubble-user" : "bubble-bot");
                msg.innerText = text;

                chatBody.appendChild(msg);
                chatBody.scrollTop = chatBody.scrollHeight;

                if (!skipSave) {
                    chatHistory.push({
                        sender,
                        text
                    });
                    saveChatHistory();
                }
            }

            chatHistory.forEach(m => appendMessage(m.text, m.sender, true));

            function sendMessage() {
                const text = messageInput.value.trim();
                if (!text) return;

                appendMessage(text, "user");
                messageInput.value = "";

                if (!firstMessageSent) {
                    firstMessageSent = true;
                    welcomeBox.classList.add("d-none");
                    resetBtn.classList.remove("d-none")
                }

                fetch("/chatbot", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            message: text
                        })
                    })
                    .then(res => res.json())
                    .then(data => appendMessage(data.reply, "bot"))
                    .catch(err => console.error(err));
            }

            sendBtn.addEventListener("click", sendMessage);
            messageInput.addEventListener("keypress", e => {
                if (e.key === "Enter") sendMessage();
            });

            function showResetButton() {
                resetBtn.classList.remove("d-none");
            }

            // Show modal
            resetBtn.addEventListener("click", () => {
                resetModal.classList.remove("d-none");
            });

            // Close modal
            cancelReset.addEventListener("click", () => {
                resetModal.classList.add("d-none");
            });

            // Confirm reset
            confirmReset.addEventListener("click", () => {
                resetModal.classList.add("d-none");

                // Bubble fade-out
                const bubbles = document.querySelectorAll(".bubble");
                bubbles.forEach(b => b.classList.add("fade-out"));

                setTimeout(() => {
                    chatBody.innerHTML = "";
                    localStorage.removeItem("chat_history");
                    welcomeBox.classList.remove("d-none");
                    firstMessageSent = false;
                    resetBtn.classList.add("d-none");
                }, 250);
            });
        });
    </script>
@endpush
