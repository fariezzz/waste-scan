@extends('layouts.main')

@section('content')

    <div class="container mt-4">

        <h2 class="mb-3 text-center">Riwayat Scan</h2>

        @if (count($riwayat) > 0)
            <div class="table-responsive">
                <table class="table align-middle text-center" id="riwayatTable">

                    <thead>
                        <tr>
                            <td>No.</td>
                            <td>Tanggal</td>
                            <td>Jenis Scan</td>
                            <td>Aksi</td>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($riwayat as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['tanggal'] }}</td>
                                <td>{{ $item['jenis'] }}</td>
                                <td>
                                    <button class="btn p-0 openDetail" data-hasil="{{ $item['hasil'] }}"
                                        data-gambar="{{ $item['gambar'] ?? '' }}" data-bs-toggle="modal"
                                        data-bs-target="#detailModal">
                                        <i class="bi bi-info-circle fs-5 text-primary"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                Belum ada riwayat scan.
            </div>
        @endif

    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4" style="background:#F7E9FF;">

                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Detail Scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">

                    <img id="modalGambar" src="" class="img-fluid rounded mb-3 shadow-sm"
                        style="max-height:200px; object-fit:cover; display:none;">
                    <p id="modalHasil" class="text-start"></p>

                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary px-4 fw-bold" data-bs-dismiss="modal">OK</button>
                </div>

            </div>
        </div>
    </div>

@endsection


@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            document.querySelectorAll(".openDetail").forEach(btn => {
                btn.addEventListener("click", () => {

                    let hasil = btn.getAttribute("data-hasil");
                    let gambar = btn.getAttribute("data-gambar");

                    document.getElementById("modalHasil").textContent = hasil;

                    let img = document.getElementById("modalGambar");

                    if (gambar && gambar !== "") {
                        img.src = "{{ asset('') }}" + gambar;
                        img.style.display = "block";
                    } else {
                        img.style.display = "none";
                    }
                });
            });

        });
    </script>
@endpush
