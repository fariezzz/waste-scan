@extends('layouts.main')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-3 text-center" style="font-size: 2rem">RIWAYAT<br>S C A N</h2>

        <div class="d-flex justify-content-end mb-3 mx-2">
            <button id="btnHapusSemua" class="btn btn-danger btn-sm d-none">
                Hapus Semua Riwayat
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-center" id="riwayatTable">
                <thead>
                    <tr>
                        <td>No.</td>
                        <td>Tanggal</td>
                        <td>Jenis</td>
                        <td>Aksi</td>
                    </tr>
                </thead>

                <tbody id="riwayatBody">
                    {{-- Akan diisi JavaScript --}}
                </tbody>
            </table>
        </div>

        <div id="noHistory" class="alert alert-info text-center d-none mt-3">
            Belum ada riwayat scan.
        </div>
    </div>

    {{-- MODAL DETAIL (Bootstrap) --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content detail-modal">

                <!-- HEADER -->
                <div class="detail-header">
                    <div class="header-group">
                        <div class="header-title">No.</div>
                        <div id="modalNo" class="header-value"></div>
                    </div>

                    <div class="header-group">
                        <div class="header-title">Tanggal</div>
                        <div id="modalTanggal" class="header-value"></div>
                    </div>

                    <div class="header-delete">
                        <button id="btnHapus" class="hapus-btn">Hapus</button>
                    </div>
                </div>

                <!-- BODY -->
                <div class="detail-body text-center">
                    <img id="modalGambar" src="" class="detail-image mb-3" style="display:none;">
                    <p id="modalHasil" class="detail-text"></p>
                </div>

                <!-- FOOTER -->
                <div class="detail-footer">
                    <button type="button" class="btn btn-confirm fw-bold" data-bs-dismiss="modal">OK</button>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL CUSTOM HAPUS SEMUA --}}
    <div id="clearHistoryModal" class="reset-modal-overlay d-none">
        <div class="reset-modal-box">
            <h4>Hapus Semua Riwayat?</h4>
            <p>Semua data hasil scan akan dihapus permanen. Yakin nih?</p>

            <div class="reset-modal-actions">
                <button id="cancelClearHistory" class="btn-cancel">Batal</button>
                <button id="confirmClearHistory" class="btn-confirm">Hapus</button>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            let history = JSON.parse(localStorage.getItem("scan_history")) || [];

            const body = document.getElementById("riwayatBody");
            const emptyMsg = document.getElementById("noHistory");
            const clearAllBtn = document.getElementById("btnHapusSemua");

            const clearModal = document.getElementById("clearHistoryModal");
            const cancelClearBtn = document.getElementById("cancelClearHistory");
            const confirmClearBtn = document.getElementById("confirmClearHistory");

            if (history.length === 0) {
                emptyMsg.classList.remove("d-none");
                clearAllBtn.classList.add("d-none");
                return;
            } else {
                clearAllBtn.classList.remove("d-none");
            }

            // Tampilkan data ke tabel
            history.forEach((item, index) => {

                let row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.tanggal}</td>
                    <td>${item.jenis}</td>

                    <td>
                        <button 
                            class="btn p-0 openDetail"
                            data-index="${index}"
                            data-no="${index + 1}"
                            data-tanggal="${item.tanggal}"
                            data-hasil="Jenis: ${item.jenis}\nKategori: ${item.kategori}\n\nAI: ${item.jawaban_ai}"
                            data-gambar="${item.image}"
                            data-bs-toggle="modal"
                            data-bs-target="#detailModal"
                        >
                            <i class="bi bi-info-circle fs-5 text-primary"></i>
                        </button>
                    </td>
                </tr>
            `;

                body.insertAdjacentHTML("beforeend", row);
            });


            // Ketika tombol Detail diklik
            document.querySelectorAll(".openDetail").forEach(btn => {
                btn.addEventListener("click", () => {

                    document.getElementById("modalNo").textContent = btn.getAttribute("data-no");
                    document.getElementById("modalTanggal").textContent = btn.getAttribute(
                        "data-tanggal");

                    let hasil = btn.getAttribute("data-hasil").replace(/\n/g, "<br>");
                    document.getElementById("modalHasil").innerHTML = hasil;

                    let gambar = btn.getAttribute("data-gambar");
                    let img = document.getElementById("modalGambar");

                    img.src = gambar;
                    img.style.display = "block";

                    const index = parseInt(btn.getAttribute("data-index"));

                    document.getElementById("btnHapus").onclick = () => {
                        hapusRiwayat(index);
                    };
                });
            });

            // ==== TOMBOL HAPUS SEMUA → TAMPILKAN MODAL CUSTOM ====
            clearAllBtn.addEventListener("click", () => {
                clearModal.classList.remove("d-none");
            });

            // Batal hapus semua
            cancelClearBtn.addEventListener("click", () => {
                clearModal.classList.add("d-none");
            });

            // Konfirmasi hapus semua
            confirmClearBtn.addEventListener("click", () => {
                clearModal.classList.add("d-none");

                localStorage.removeItem("scan_history");

                body.innerHTML = "";
                emptyMsg.classList.remove("d-none");
                clearAllBtn.classList.add("d-none");
            });

        });

        function hapusRiwayat(index) {
            let history = JSON.parse(localStorage.getItem("scan_history")) || [];
            history.splice(index, 1);
            localStorage.setItem("scan_history", JSON.stringify(history));
            location.reload();
        }
    </script>
@endpush
