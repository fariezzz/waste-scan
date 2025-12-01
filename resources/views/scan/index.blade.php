@extends('layouts.main')
@section('bodyClass', 'no-scroll-desktop')
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
            });

            modeDetect.addEventListener('click', () => {
                currentMode = "detect";
                updateModeUI();
            });

            updateModeUI();


            // =======================
            // ELEM
            // =======================
            const video = document.getElementById('camera');
            const captureBtn = document.getElementById('captureButton');
            const uploadBtn = document.getElementById('uploadBtn');
            const imageInput = document.getElementById('imageInput');
            const controlsArea = document.getElementById('controlsArea');
            const cameraWrap = document.getElementById('cameraWrap');

            const resultView = document.getElementById('resultView');
            const resultLarge = document.getElementById('resultLarge');
            const resultText = document.getElementById('resultText');
            const chatReplyBox = document.getElementById('aiChatReply');
            const backBtn = document.getElementById('backToCamera');
            const showHistoryBtn = document.getElementById('showHistoryBtn');
            const gotoHistory = document.getElementById('gotoHistory');

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
                    alert('Gagal mengakses kamera.');
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
            }

            // =======================================================
            //  HELPER: SIMPAN RIWAYAT KE LOCAL STORAGE
            // =======================================================
            function saveToHistory(imageSrc, jenis, kategori, aiReply) {
                let history = JSON.parse(localStorage.getItem("scan_history")) || [];

                let newEntry = {
                    image: imageSrc,
                    jenis: jenis,
                    kategori: kategori,
                    jawaban_ai: aiReply,
                    tanggal: new Date().toLocaleString()
                };

                history.unshift(newEntry);
                localStorage.setItem("scan_history", JSON.stringify(history));
            }

            function fileToBase64(file) {
                return new Promise((resolve) => {
                    let reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.readAsDataURL(file);
                });
            }

            // =======================================================
            // VIEW SWITCH + ANIMASI
            // =======================================================
            function showResultView(imageSrc) {
                // set gambar dulu
                if (imageSrc) {
                    resultLarge.src = imageSrc;
                }

                // hide camera
                cameraWrap.classList.add('view-hide');
                controlsArea.classList.add('view-hide');

                setTimeout(() => {
                    cameraWrap.style.display = 'none';
                    controlsArea.style.display = 'none';

                    resultView.style.display = 'block';
                    // restart animasi
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

            backBtn.addEventListener('click', () => {
                hideResultView();
            });

            showHistoryBtn.addEventListener('click', () => {
                window.location.href = '/riwayat';
            });
            gotoHistory.addEventListener('click', () => {
                window.location.href = '/riwayat';
            });


            // =======================================================
            //  CAPTURE BUTTON
            // =======================================================
            captureBtn.addEventListener('click', async () => {

                chatReplyBox.innerHTML = "";
                resultText.innerHTML = "";

                if (!video || video.readyState < 2)
                    return alert('Kamera belum siap.');

                const canvas = document.createElement('canvas');
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;

                canvas.width = w;
                canvas.height = h;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, w, h);

                const base64Image = canvas.toDataURL('image/png');

                // ==== LANGSUNG PINDAH KE RESULT VIEW DULU ====
                resultText.innerHTML = "⏳ Memproses...";
                chatReplyBox.innerHTML = "🤖 Sedang memproses jawaban...";
                showResultView(base64Image);

                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));

                const formData = new FormData();
                formData.append("image", blob, "capture.jpg");
                formData.append("mode", currentMode);

                try {
                    const response = await fetch("/ai/process", {
                        method: "POST",
                        body: formData
                    });

                    const aiData = await response.json();

                    if (currentMode === "classify") {
                        resultText.innerHTML =
                            `<b>Jenis:</b> ${aiData.jenis} <br> <b>Kategori:</b> ${aiData.kategori}`;

                        const autoMsg =
                            `Aku nemu sampah kategori ${aiData.kategori} dengan jenis ${aiData.jenis}. Gimana cara ngolahnya?`;

                        chatReplyBox.innerHTML = "🤖 Sedang memproses jawaban...";

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

                        const chatJson = await chatRes.json();
                        const aiReply = chatJson.reply || "";

                        chatReplyBox.innerHTML = `
                    <div class="p-3 rounded" style="background:#E8FFD8; border-left:5px solid #6CC46C;">
                        <b>Pertanyaan:</b><br>
                        ${autoMsg}<br><br>
                        <b>Jawaban W.A.S.T.E AI:</b><br>
                        ${aiReply}
                    </div>
                `;

                        saveToHistory(base64Image, aiData.jenis, aiData.kategori, aiReply);

                    } else {
                        let detectionsHtml = "";
                        if (aiData.count && aiData.count > 0) {
                            detectionsHtml = `<b>Terdeteksi ${aiData.count} sampah:</b><br>` +
                                aiData.detections.map(
                                    d => `• ${d.class} (${(d.confidence*100).toFixed(1)}%)`
                                ).join("<br>");
                        } else {
                            detectionsHtml = "❌ Tidak ada sampah terdeteksi";
                        }

                        resultText.innerHTML = detectionsHtml;
                        chatReplyBox.innerHTML = "";

                        saveToHistory(base64Image, "deteksi", "-", detectionsHtml);
                    }

                } catch (err) {
                    console.error(err);
                    resultText.innerHTML = "⚠️ Gagal memproses gambar";
                    chatReplyBox.innerHTML = "";
                }
            });



            // =======================================================
            //  UPLOAD FILE
            // =======================================================
            uploadBtn.addEventListener('click', () => imageInput.click());

            imageInput.addEventListener('change', async () => {

                chatReplyBox.innerHTML = "";
                resultText.innerHTML = "";

                const file = imageInput.files[0];
                if (!file) return;

                const base64Image = await fileToBase64(file);

                // langsung pindah tampilan
                resultText.innerHTML = "⏳ Memproses...";
                chatReplyBox.innerHTML = "🤖 Sedang memproses jawaban...";
                showResultView(base64Image);

                const formData = new FormData();
                formData.append("image", file);
                formData.append("mode", currentMode);

                try {
                    const response = await fetch("/ai/process", {
                        method: "POST",
                        body: formData
                    });

                    const aiData = await response.json();

                    if (currentMode === "classify") {
                        resultText.innerHTML =
                            `<b>Jenis:</b> ${aiData.jenis} <br> <b>Kategori:</b> ${aiData.kategori}`;

                        const autoMsg =
                            `Aku nemu sampah kategori ${aiData.kategori} dengan jenis ${aiData.jenis}. Gimana cara ngolahnya?`;

                        chatReplyBox.innerHTML = "🤖 Sedang memproses jawaban...";

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

                        const chatJson = await chatRes.json();
                        const aiReply = chatJson.reply || "";

                        chatReplyBox.innerHTML = `
                    <div class="p-3 rounded" style="background:#E8FFD8; border-left:5px solid #6CC46C;">
                        <b>Pertanyaan:</b><br>
                        ${autoMsg}<br><br>
                        <b>Jawaban W.A.S.T.E AI:</b><br>
                        ${aiReply}
                    </div>
                `;

                        saveToHistory(base64Image, aiData.jenis, aiData.kategori, aiReply);

                    } else {
                        let detectionsHtml = "";
                        if (aiData.count && aiData.count > 0) {
                            detectionsHtml = `<b>Terdeteksi ${aiData.count} sampah:</b><br>` +
                                aiData.detections.map(
                                    d => `• ${d.class} (${(d.confidence*100).toFixed(1)}%)`
                                ).join("<br>");
                        } else {
                            detectionsHtml = "❌ Tidak ada sampah terdeteksi";
                        }

                        resultText.innerHTML = detectionsHtml;
                        chatReplyBox.innerHTML = "";

                        saveToHistory(base64Image, "deteksi", "-", detectionsHtml);
                    }

                } catch (err) {
                    console.error(err);
                    resultText.innerHTML = "⚠️ Gagal memproses gambar";
                    chatReplyBox.innerHTML = "";
                }
            });

            startCamera();
            window.addEventListener('beforeunload', stopCamera);
        });
    </script>
@endpush
