@extends('layouts.main')

@section('content')
    <div class="hero-section d-flex flex-column justify-content-center align-items-center text-center">

        <h1 class="hero-title">W.A.S.T.E.</h1>

        <p class="hero-line mb-1">
            <span style="color:#4CAF50;">🍃 mulai dari langkah kecil</span>
        </p>

        <p class="hero-line-2">
            untuk menyelamatkan <span style="color:#6CC46C;">lingkunganmu</span>
        </p>

    </div>


    <div class="container px-3 mt-4">
        <div class="row justify-content-center text-center menu-row">

            <div class="col-12 col-md-4 mb-4">
                <a href="/scan" class="menu-row-link">
                    <div class="menu-row-card">
                        <div class="menu-row-icon" style="background: #C8F2D1;">
                            <img src="/icons/scan-card.svg" />
                        </div>
                        <span>S C A N</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4 mb-4">
                <a href="/riwayat" class="menu-row-link">
                    <div class="menu-row-card">
                        <div class="menu-row-icon" style="background: #E8D7FF;">
                            <img src="/icons/riwayat-card.svg" />
                        </div>
                        <span>R I W A Y A T<br>S C A N</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4 mb-4">
                <a href="/edukasi" class="menu-row-link">
                    <div class="menu-row-card">
                        <div class="menu-row-icon" style="background: #FFF1C9;">
                            <img src="/icons/edukasi-card.svg" />
                        </div>
                        <span>E D U K A S I</span>
                    </div>
                </a>
            </div>

        </div>
    </div>
@endsection
