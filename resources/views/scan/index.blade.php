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

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // GANTI MODE
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
                console.log("Mode: Klasifikasi Sampah");
            });

            modeDetect.addEventListener('click', () => {
                currentMode = "detect";
                updateModeUI();
                console.log("Mode: Deteksi Sampah");
            });

            updateModeUI();

            // CAMERA SCRIPT
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
                    // video.style.transform = 'scaleX(-1)';
                } catch (err) {
                    console.error('Camera Error:', err);
                    alert('Gagal mengakses kamera. Cek izin browser.');
                }
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
            }

            captureBtn.addEventListener('click', () => {
                if (!video || video.readyState < 2)
                    return alert('Kamera belum siap. Tunggu sebentar.');

                const canvas = document.createElement('canvas');
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;

                canvas.width = w;
                canvas.height = h;

                const ctx = canvas.getContext('2d');

                // ctx.translate(w, 0);
                // ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, w, h);
                // ctx.setTransform(1, 0, 0, 1, 0, 0);

                resultImg.src = canvas.toDataURL('image/png');
                resultImg.style.display = 'block';
            });

            startCamera();
            window.addEventListener('beforeunload', stopCamera);

            // UPLOAD IMAGE
            const uploadBtn = document.getElementById('uploadBtn');
            const imageInput = document.getElementById('imageInput');

            uploadBtn.addEventListener('click', () => {
                imageInput.click();
            });

            imageInput.addEventListener('change', () => {
                const file = imageInput.files[0];
                if (!file) return;

                if (!file.type.startsWith("image/")) {
                    alert("File harus berupa foto!");
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    resultImg.src = e.target.result;
                    resultImg.style.display = "block";
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection
