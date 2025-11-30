@extends('layouts.main')

@section('content')
    <div id="cameraPreview">
        <video id="camera" autoplay playsinline style="width:100%; height:100%; object-fit:cover;"></video>
    </div>

    <div class="text-center mt-3">
        <div class="mode-switch">
            <button id="modeClassify" class="mode-btn active">KLASIFIKASI SAMPAH</button>
            <button id="modeDetect" class="mode-btn">DETEKSI SAMPAH</button>
        </div>
    </div>

    <div class="d-flex justify-content-center align-items-center mt-4 controls-area">
        <div id="uploadBtn" class="control-btn text-center mb-3">
            <span>UPLOAD</span>
            <i class="bi bi-upload icon"></i>
            <input type="file" id="imageInput" accept="image/*" style="display:none;">
        </div>

        <div id="captureButton" class="my-3 mx-5" role="button"></div>

        <div class="control-btn text-center mb-3">
            <span>HAPUS</span>
            <i class="bi bi-trash icon"></i>
        </div>
    </div>

    <img id="result" class="mt-3 w-100 rounded" style="border:1px solid #ccc; display:none;" />
    <div id="resultText" class="mt-3 text-center" style="font-size: 18px; display:none;"></div>
    <div id="aiChatReply" class="mt-3 text-start" style="font-size: 16px; display:none;"></div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // =======================
            // MODE SWITCH
            // =======================
            const modeClassify = document.getElementById('modeClassify');
            const modeDetect = document.getElementById('modeDetect');

            let currentMode = "classify";

            function updateModeUI() {
                if (currentMode === "classify") {
                    modeClassify.classList.add("active");
                    modeDetect.classList.remove("active");
                } else {
                    modeDetect.classList.add("active");
                    modeClassify.classList.remove("active");
                }
            }

            modeClassify.addEventListener('click', () => {
                currentMode = "classify";
                updateModeUI();
                console.log("Mode: classify");
            });

            modeDetect.addEventListener('click', () => {
                currentMode = "detect";
                updateModeUI();
                console.log("Mode: detect");
            });

            updateModeUI();


            // =======================
            // CAMERA SCRIPT
            // =======================
            const video = document.getElementById('camera');
            const resultImg = document.getElementById('result');
            const captureBtn = document.getElementById('captureButton');

            let stream = null;

            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment'
                        }
                    });
                    video.srcObject = stream;
                } catch (err) {
                    console.error('Camera Error:', err);
                    alert('Gagal mengakses kamera.');
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
            }

            // =======================
            // CAPTURE BUTTON
            // =======================
            captureBtn.addEventListener('click', async () => {
                if (!video || video.readyState < 2)
                    return alert('Kamera belum siap.');

                const canvas = document.createElement('canvas');
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;

                canvas.width = w;
                canvas.height = h;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, w, h);

                // tampilkan preview
                resultImg.src = canvas.toDataURL('image/png');
                resultImg.style.display = 'block';

                // konversi ke Blob
                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));

                // =======================
                // SEND TO LARAVEL
                // =======================
                const formData = new FormData();
                formData.append("image", blob, "capture.jpg");
                formData.append("mode", currentMode);

                const resultText = document.getElementById('resultText');
                resultText.innerHTML = "⏳ Memproses...";
                resultText.style.display = "block";

                try {
                    const response = await fetch("/ai/process", {
                        method: "POST",
                        body: formData
                    });

                    const data = await response.json();
                    console.log("HASIL AI:", data);

                    if (currentMode === "classify") {
                        resultText.innerHTML = `
                        <b>Jenis:</b> ${data.jenis} <br>
                        <b>Kategori:</b> ${data.kategori}
                    `;
                    } else {
                        if (data.count === 0) {
                            resultText.innerHTML = "❌ Tidak ada sampah terdeteksi";
                        } else {
                            resultText.innerHTML = `
                            <b>Terdeteksi ${data.count} sampah:</b><br>
                            ${data.detections.map(
                                d => `• ${d.class} (${(d.confidence * 100).toFixed(1)}%)`
                            ).join("<br>")}
                        `;
                        }
                    }

                } catch (err) {
                    console.error(err);
                    resultText.innerHTML = "⚠️ Gagal memproses gambar";
                }
            });


            // =======================
            // UPLOAD FILE MANUAL
            // =======================
            const uploadBtn = document.getElementById('uploadBtn');
            const imageInput = document.getElementById('imageInput');

            uploadBtn.addEventListener('click', () => {
                imageInput.click();
            });

            imageInput.addEventListener('change', async () => {
                const file = imageInput.files[0];
                if (!file) return;

                resultImg.src = URL.createObjectURL(file);
                resultImg.style.display = "block";

                const formData = new FormData();
                formData.append("image", file);
                formData.append("mode", currentMode);

                const resultText = document.getElementById('resultText');
                resultText.innerHTML = "⏳ Memproses...";
                resultText.style.display = "block";

                const response = await fetch("/ai/process", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (currentMode === "classify") {

                    resultText.innerHTML = `
                        <b>Jenis:</b> ${data.jenis} <br>
                        <b>Kategori:</b> ${data.kategori}
                    `;

                    // pesan otomatis
                    const autoMsg =
                        `Aku nemu sampah kategori ${data.kategori} dengan jenis ${data.jenis}. Gimana cara ngolahnya?`;

                    const chatReplyBox = document.getElementById("aiChatReply");
                    chatReplyBox.style.display = "block";
                    chatReplyBox.innerHTML = "🤖 Sedang memproses jawaban...";

                    // KIRIM KE CHATBOT
                    fetch("/chatbot", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                message: autoMsg
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            chatReplyBox.innerHTML = `
                                <div class="p-3 rounded" style="background:#E8FFD8; border-left: 5px solid #6CC46C;">
                                    <b>Pertanyaan:</b><br>
                                    ${autoMsg}<br><br>

                                    <b>Jawaban W.A.S.T.E AI:</b><br>
                                    ${data.reply}
                                </div>
                            `;
                        })
                        .catch(err => {
                            console.error(err);
                            chatReplyBox.innerHTML = "⚠️ Chatbot tidak bisa merespon sekarang.";
                        });
                } else {
                    if (data.count === 0) {
                        resultText.innerHTML = "❌ Tidak ada sampah terdeteksi";
                    } else {
                        resultText.innerHTML = `
                        <b>Terdeteksi ${data.count} sampah:</b><br>
                        ${data.detections.map(
                            d => `• ${d.class} (${(d.confidence * 100).toFixed(1)}%)`
                        ).join("<br>")}
                    `;
                    }
                }
            });

            startCamera();
            window.addEventListener('beforeunload', stopCamera);
        });
    </script>
@endpush
