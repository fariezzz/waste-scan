<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand fw-bold mt-2" href="/">WASTE</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/scan">
                        <img src="{{ asset('icons/Scan.svg') }}" alt="" class="navbar-icon"> SCAN
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/riwayat">
                        <img src="{{ asset('icons/Riwayat Scan.svg') }}" alt="" class="navbar-icon">
                        RIWAYAT SCAN
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/edukasi">
                        <img src="{{ asset('icons/Edukasi.svg') }}" alt="Logo" class="navbar-icon">
                        EDUKASI
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
