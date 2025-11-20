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
        <div class="control-btn text-center mt-3">
            <i class="bi bi-upload icon"></i>
            <p>UPLOAD</p>
        </div>

        <div id="previewCircle" class="mb-1"></div>

        <div class="control-btn text-center mt-3">
            <i class="bi bi-trash icon"></i>
            <p>HAPUS</p>
        </div>
    </div>

    <img id="result" class="mt-3 w-100 rounded" style="border:1px solid #ccc; display:none;" />

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // ===== MODE SWITCH LOGIC =====
            const modeClassify = document.getElementById('modeClassify');
            const modeDetect = document.getElementById('modeDetect');

            let currentMode = "classify"; // default

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
                console.log("Mode: Klasifikasi Sampah");
            });

            modeDetect.addEventListener('click', () => {
                currentMode = "detect";
                updateModeUI();
                console.log("Mode: Deteksi Sampah");
            });

            updateModeUI(); // Apply initial state


            // ===== CAMERA SCRIPT KAMU (TIDAK DIUBAH) =====
            const video = document.getElementById('camera');
            const resultImg = document.getElementById('result');
            const captureBtn = document.getElementById('captureBtn');
            const downloadBtn = document.getElementById('downloadBtn');

            let stream = null;

            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment'
                        }
                    });
                    video.srcObject = stream;
                    video.style.transform = 'scaleX(-1)';
                } catch (err) {
                    console.error('Camera Error: ', err);
                    alert('Gagal mengakses kamera. Cek izin (permissions) atau coba browser lain.');
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
            }

            captureBtn.addEventListener('click', () => {
                if (!video || video.readyState < 2) return alert('Kamera belum siap. Tunggu sebentar.');

                const canvas = document.createElement('canvas');
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');

                ctx.translate(w, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, w, h);
                ctx.setTransform(1, 0, 0, 1, 0, 0);

                const dataURL = canvas.toDataURL('image/png');
                resultImg.src = dataURL;
                resultImg.style.display = 'block';
                downloadBtn.disabled = false;

                downloadBtn.onclick = () => {
                    const a = document.createElement('a');
                    a.href = dataURL;
                    a.download = 'scan_sampah.png';
                    a.click();
                };
            });

            startCamera();
            window.addEventListener('beforeunload', stopCamera);

        });
    </script>
@endsection
