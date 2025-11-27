<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Sampah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/stle.css') }}">
</head>

<body>
    @include('partials.navbar')
    <div class="container {{ request()->is('chatbot') ? '' : 'py-4 mb-5' }}">
        @yield('content')
    </div>

    @if (!request()->is('chatbot'))
        <a id="chatbot-btn" class="chatbot-btn" href="/chatbot">
            <img src="{{ asset('icons/Chatbot.svg') }}" alt="Logo" class="chatbot-icon">
        </a>
        <div class="chatbot-label">Chatbot</div>
    @endif


    {{-- <div id="chatbot-window" class="chatbot-window">
        <div class="chat-header">
            Chatbot
            <span id="chat-close" style="cursor:pointer;">×</span>
        </div>
        <div class="chat-body" id="chat-body"></div>
        <div class="chat-input">
            <input type="text" id="chat-message" placeholder="Tulis pesan..." />
            <button id="chat-send">Send</button>
        </div>
    </div> --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    {{-- <script>
        const chatBtn = document.getElementById('chatbot-btn');
        const chatWindow = document.getElementById('chatbot-window');
        const chatClose = document.getElementById('chat-close');
        const chatSend = document.getElementById('chat-send');
        const chatBody = document.getElementById('chat-body');
        const chatMessage = document.getElementById('chat-message');
        let chatOpened = false;

        chatBtn.onclick = () => {
            if (chatOpened == false) {
                chatOpened = true;
                chatWindow.style.display = 'flex';
            } else {
                chatOpened = false
                chatWindow.style.display = 'none';
            }
        };

        chatClose.onclick = () => {
            chatWindow.style.display = 'none';
        };

        chatSend.onclick = sendMessage;

        chatMessage.addEventListener("keypress", e => {
            if (e.key === "Enter") sendMessage();
        });

        function sendMessage() {
            const text = chatMessage.value.trim();
            if (!text) return;

            chatBody.innerHTML += `<div class="bubble bubble-user">${text}</div>`;
            chatMessage.value = "";
            chatBody.scrollTop = chatBody.scrollHeight;

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
                .then(data => {
                    chatBody.innerHTML += `<div class="bubble bubble-bot">${data.reply}</div>`;
                    chatBody.scrollTop = chatBody.scrollHeight;
                });
        }
    </script> --}}

    @stack('script')
</body>

</html>
