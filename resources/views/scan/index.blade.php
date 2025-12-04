@extends('layouts.main')
@section('bodyClass', 'mb-10')
@section('content')
    <div id="cameraWrap">
        <div id="cameraPreview">
            <video id="camera" autoplay playsinline style="width:100%; height:100%; object-fit:cover;"></video>
        </div>

        <div class="text-center mt-3">
            <div class="mode-switch">
                <button id="modeClassify" class="mode-btn active">KLASIFIKASI SAMPAH</button>
                <button id="modeDetect" class="mode-btn">DETEKSI SAMPAH</button>
            </div>
        </div>

        <div class="d-flex justify-content-center align-items-center mt-4 controls-area" id="controlsArea">
            <div id="uploadBtn" class="control-btn text-center mb-3">
                <span>UPLOAD</span>
                <div class="icon">
                    <img src="{{ asset('icons/upload.svg') }}" alt="Logo">
                </div>

                {{-- <i class="bi bi-upload icon"></i> --}}
                <input type="file" id="imageInput" accept="image/*" style="display:none;">
            </div>

            <div id="captureButton" class="my-3 mx-5" role="button"></div>

            <div class="control-btn text-center mb-3" role="button" id="showHistoryBtn">
                <span>RIWAYAT</span>
                <div class="icon">
                    <img src="{{ asset('icons/riwayat.svg') }}" alt="Logo">
                </div>

                {{-- <i class="bi bi-upload icon"></i> --}}
            </div>
        </div>
    </div>

    <div id="resultView" style="display:none; padding: 12px;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button id="backToCamera" class="btn btn-light">← Kembali</button>
            <button id="gotoHistory" class="btn btn-outline-primary">Lihat Riwayat</button>
        </div>

        <div class="text-center mb-3">
            <img id="resultLarge" src="" alt="Hasil capture">
        </div>

        <div id="resultText" class="text-center" style="font-size: 18px; display:block; margin-bottom:12px;"></div>

        <div id="aiChatReply" class="text-start" style="font-size: 16px; display:block;"></div>
    </div>

    <img id="result" class="mt-3 w-100 rounded" style="border:1px solid #ccc; display:none;" />
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const modeClassify = document.getElementById('modeClassify');
            const modeDetect = document.getElementById('modeDetect');

            let currentMode = "classify";

            function updateModeUI() {
                modeClassify.classList.toggle("active", currentMode === "classify");
                modeDetect.classList.toggle("active", currentMode !== "classify");
            }

            modeClassify.onclick = () => {
                currentMode = "classify";
                updateModeUI();
            };
            modeDetect.onclick = () => {
                currentMode = "detect";
                updateModeUI();
            };
            updateModeUI();

            // =======================
            // ELEMENTS
            // =======================
            const video = document.getElementById('camera');
            const captureBtn = document.getElementById('captureButton');
            const uploadBtn = document.getElementById('uploadBtn');
            const imageInput = document.getElementById('imageInput');

            const cameraWrap = document.getElementById('cameraWrap');
            const controlsArea = document.getElementById('controlsArea');

            const resultView = document.getElementById('resultView');
            const resultLarge = document.getElementById('resultLarge');
            const resultText = document.getElementById('resultText');
            const chatReplyBox = document.getElementById('aiChatReply');
            const backBtn = document.getElementById('backToCamera');
            const gotoHistory = document.getElementById('gotoHistory');
            const showHistoryBtn = document.getElementById('showHistoryBtn');

            // =======================
            // CAMERA INIT
            // =======================
            let stream = null;

            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: "environment"
                        }
                    });
                    video.srcObject = stream;
                } catch (e) {
                    alert("Gagal mengakses kamera.");
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
            }

            // =======================
            // RESIZE UTILITY
            // =======================
            function resizeImageFile(file, maxSize = 800) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {

                            let {
                                width,
                                height
                            } = img;

                            if (width > height && width > maxSize) {
                                height = height * (maxSize / width);
                                width = maxSize;
                            } else if (height > width && height > maxSize) {
                                width = width * (maxSize / height);
                                height = maxSize;
                            }

                            const canvas = document.createElement("canvas");
                            canvas.width = width;
                            canvas.height = height;

                            const ctx = canvas.getContext("2d");
                            ctx.drawImage(img, 0, 0, width, height);

                            canvas.toBlob(blob => resolve(blob), "image/jpeg", 0.85);
                        };
                        img.onerror = reject;
                        img.src = e.target.result;
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            function fileToBase64(file) {
                return new Promise(res => {
                    const r = new FileReader();
                    r.onload = () => res(r.result);
                    r.readAsDataURL(file);
                });
            }

            // =======================
            // VIEW ANIMATION
            // =======================
            function showResultView(imageSrc) {
                if (imageSrc) resultLarge.src = imageSrc;

                cameraWrap.classList.add('view-hide');
                controlsArea.classList.add('view-hide');

                setTimeout(() => {
                    cameraWrap.style.display = 'none';
                    controlsArea.style.display = 'none';
                    resultView.style.display = 'block';

                    resultView.classList.remove('view-hide');
                    void resultView.offsetWidth;
                    resultView.classList.add('view-show');
                }, 120);
            }

            function hideResultView() {
                resultView.classList.remove('view-show');
                resultView.classList.add('view-hide');

                setTimeout(() => {
                    resultView.style.display = 'none';

                    cameraWrap.style.display = 'block';
                    controlsArea.style.display = 'flex';

                    cameraWrap.classList.remove('view-hide');
                    controlsArea.classList.remove('view-hide');

                    void cameraWrap.offsetWidth;
                    cameraWrap.classList.add('view-show');
                    controlsArea.classList.add('view-show');
                }, 120);
            }

            backBtn.onclick = hideResultView;
            showHistoryBtn.onclick = () => window.location.href = "/riwayat";
            gotoHistory.onclick = () => window.location.href = "/riwayat";

            // =======================
            // HISTORY SYSTEM — FINAL STRUCTURE
            // =======================
            function saveToHistory(entry) {
                let history = JSON.parse(localStorage.getItem("scan_history")) || [];
                history.unshift(entry);
                localStorage.setItem("scan_history", JSON.stringify(history));
            }

            // =======================
            // CAPTURE BUTTON
            // =======================
            captureBtn.onclick = async () => {
                chatReplyBox.innerHTML = "";
                resultText.innerHTML = "";

                if (!video || video.readyState < 2) return alert("Kamera belum siap.");

                // capture canvas
                const canvas = document.createElement("canvas");
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(video, 0, 0);

                // resize for upload
                const resizedBlob = await new Promise(resolve =>
                    canvas.toBlob(resolve, "image/jpeg", 0.85)
                );
                const base64Image = await fileToBase64(resizedBlob);

                // show UI
                resultText.innerHTML = "⏳ Memproses...";
                chatReplyBox.innerHTML = currentMode === "classify" ?
                    "🤖 Sedang memproses jawaban..." :
                    "";
                showResultView(base64Image);

                const formData = new FormData();
                formData.append("image", resizedBlob, "capture.jpg");
                formData.append("mode", currentMode);

                let aiData = null;
                try {
                    const res = await fetch("/ai/process", {
                        method: "POST",
                        body: formData
                    });
                    aiData = await res.json();
                } catch (err) {
                    resultText.innerHTML = "⚠️ Gagal memproses gambar (server).";
                    chatReplyBox.innerHTML = "";
                    return;
                }

                // =====================
                // CLASSIFY
                // =====================
                if (currentMode === "classify") {
                    const autoMsg =
                        `Aku nemu sampah kategori ${aiData.kategori} dengan jenis ${aiData.jenis}. ` +
                        `Gimana cara mengolahnya?`;

                    resultText.innerHTML =
                        `<b>Jenis:</b> ${aiData.jenis}<br><b>Kategori:</b> ${aiData.kategori}`;

                    let chatJson = {
                        reply: "Maaf, AI sedang tidak bisa dihubungi."
                    };

                    try {
                        const chatRes = await fetch("/chatbot", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                message: autoMsg
                            })
                        });
                        chatJson = await chatRes.json();
                    } catch {}

                    chatReplyBox.innerHTML = `
                <div class="p-3 rounded" style="background:#E8FFD8;border-left:5px solid #6CC46C;">
                    <b>Pertanyaan:</b><br>${autoMsg}<br><br>
                    <b>Jawaban W.A.S.T.E AI:</b><br>${chatJson.reply}
                </div>
            `;

                    saveToHistory({
                        image: base64Image,
                        jenis_scan: "Klasifikasi",
                        jenis_sampah: aiData.jenis,
                        kategori_sampah: aiData.kategori,
                        hasil_ai: chatJson.reply,
                        tanggal: new Date().toLocaleString()
                    });
                }

                // =====================
                // DETECTION
                // =====================
                else {
                    let detectionsHtml = "";

                    if (aiData.count > 0) {
                        detectionsHtml =
                            `<b>Terdeteksi ${aiData.count} sampah:</b><br>` +
                            aiData.detections
                            .map(d => `• ${d.class} (${(d.confidence * 100).toFixed(1)}%)`)
                            .join("<br>");
                    } else {
                        detectionsHtml = "❌ Tidak ada sampah terdeteksi";
                    }

                    resultText.innerHTML = detectionsHtml;
                    chatReplyBox.innerHTML = "";

                    saveToHistory({
                        image: base64Image,
                        jenis_scan: "Deteksi",
                        jenis_sampah: "-",
                        kategori_sampah: "-",
                        hasil_ai: detectionsHtml,
                        tanggal: new Date().toLocaleString()
                    });
                }
            };

            // =======================
            // UPLOAD SYSTEM
            // =======================
            uploadBtn.onclick = () => imageInput.click();

            imageInput.onchange = async () => {
                const file = imageInput.files[0];
                if (!file) return;

                const resizedBlob = await resizeImageFile(file);
                const base64 = await fileToBase64(resizedBlob);

                resultText.innerHTML = "⏳ Memproses...";
                chatReplyBox.innerHTML = currentMode === "classify" ? "🤖 Sedang memproses jawaban..." : "";
                showResultView(base64);

                const fd = new FormData();
                fd.append("image", resizedBlob, "upload.jpg");
                fd.append("mode", currentMode);

                let aiData = null;
                try {
                    const res = await fetch("/ai/process", {
                        method: "POST",
                        body: fd
                    });
                    aiData = await res.json();
                } catch {
                    resultText.innerHTML = "⚠️ Gagal memproses gambar";
                    chatReplyBox.innerHTML = "";
                    return;
                }

                // CLASSIFY
                if (currentMode === "classify") {
                    resultText.innerHTML =
                        `<b>Jenis:</b> ${aiData.jenis}<br><b>Kategori:</b> ${aiData.kategori}`;

                    const autoMsg =
                        `Aku nemu sampah kategori ${aiData.kategori} dengan jenis ${aiData.jenis}. Gimana cara mengolahnya?`;

                    let chatJson = {
                        reply: "AI tidak dapat dihubungi."
                    };

                    try {
                        const resChat = await fetch("/chatbot", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                message: autoMsg
                            })
                        });
                        chatJson = await resChat.json();
                    } catch {}

                    chatReplyBox.innerHTML = `
                <div class="p-3 rounded" style="background:#E8FFD8;border-left:5px solid #6CC46C;">
                    <b>Pertanyaan:</b><br>${autoMsg}<br><br>
                    <b>Jawaban W.A.S.T.E AI:</b><br>${chatJson.reply}
                </div>
            `;

                    saveToHistory({
                        image: base64,
                        jenis_scan: "Klasifikasi",
                        jenis_sampah: aiData.jenis,
                        kategori_sampah: aiData.kategori,
                        hasil_ai: chatJson.reply,
                        tanggal: new Date().toLocaleString()
                    });
                }

                // DETECT
                else {
                    const detectionsHtml =
                        aiData.count > 0 ?
                        `<b>Terdeteksi ${aiData.count} sampah:</b><br>` +
                        aiData.detections
                        .map(d => `• ${d.class} (${(d.confidence * 100).toFixed(1)}%)`)
                        .join("<br>") :
                        "❌ Tidak ada sampah terdeteksi";

                    resultText.innerHTML = detectionsHtml;
                    chatReplyBox.innerHTML = "";

                    saveToHistory({
                        image: base64,
                        jenis_scan: "Deteksi",
                        jenis_sampah: "-",
                        kategori_sampah: "-",
                        hasil_ai: detectionsHtml,
                        tanggal: new Date().toLocaleString()
                    });
                }
            };

            startCamera();
            window.addEventListener('beforeunload', stopCamera);
        });
    </script>
@endpush
