@extends('layouts.main')

@section('content')
    <div class="carousel-wrapper">
        <div class="carousel-track" id="carouselTrack">
            @foreach (range(1, 5) as $i)
                <div class="carousel-card">
                    <img src="{{ asset('img/slide' . $i . '.png') }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="hero-section d-flex flex-column justify-content-center align-items-center text-center">

        <h1 class="hero-title">W.A.S.T.E.</h1>

        <p class="hero-line mb-1">
            <span style="color:#4CAF50;">🍃 Mulai dari langkah kecil</span>
        </p>

        <p class="hero-line-2">
            <span id="typed">untuk menyelamatkan lingkunganmu</span>
        </p>

    </div>

    {{-- <div class="wave-divider">
        <svg viewBox="0 0 1440 100">
            <path fill="#000000" d="M0,64L1440,0L1440,120L0,120Z"></path>
        </svg>
    </div> --}}

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

    <section class="feature-section container text-center mt-5 mb-5">
        <div class="row justify-content-center">

            <div class="col-6 col-md-3 mb-4">
                <div class="feature-box">
                    <img src="/icons/scan-card.svg" class="feature-icon">
                    <p>Scan Otomatis</p>
                </div>
            </div>

            <div class="col-6 col-md-3 mb-4">
                <div class="feature-box">
                    <img src="/icons/riwayat-card.svg" class="feature-icon">
                    <p>Riwayat Cepat</p>
                </div>
            </div>

            <div class="col-6 col-md-3 mb-4">
                <div class="feature-box">
                    <img src="/icons/edukasi-card.svg" class="feature-icon">
                    <p>Edukasi Lengkap</p>
                </div>
            </div>

            <div class="col-6 col-md-3 mb-4">
                <div class="feature-box">
                    <img src="/icons/scan-card.svg" class="feature-icon">
                    <p>Eco-Friendly Guide</p>
                </div>
            </div>

        </div>
    </section>

    <section class="about-section container text-center mt-5 mb-5">
        <h2 class="about-title">Apa itu W.A.S.T.E.?</h2>
        <p class="about-desc">
            W.A.S.T.E. adalah platform cerdas yang membantu kamu mengenali jenis sampah,
            mempelajari cara pengolahannya, dan memulai kebiasaan ramah lingkungan.
        </p>
    </section>

    <section class="cta-section text-center container mt-4 mb-5">
        <div class="cta-box">
            <p class="cta-title">Mulai scan sekarang 📸</p>
            <p class="cta-desc">Arahkan kamera ke sampah dan biarkan AI mengidentifikasikannya.</p>
            <a href="/scan" class="btn btn-success px-4 mt-2">Mulai</a>
        </div>
    </section>

    <footer class="footer text-center py-4 mt-5">
        <p class="mb-0">W.A.S.T.E. — Mendukung Lingkungan Lebih Bersih</p>
        <small>© {{ date('Y') }} | Dibuat dengan ♻ oleh manusia dan AI</small>
    </footer>
@endsection


@push('script')
    <script>
        new Typed('#typed', {
            strings: [
                "untuk menyelamatkan lingkunganmu",
                "mulai dari langkah kecil",
                "untuk bumi yang lebih hijau"
            ],
            typeSpeed: 35,
            backSpeed: 20,
            loop: true
        });

        gsap.from(".hero-title", {
            opacity: 0,
            y: 20,
            duration: 1.2,
            ease: "power3.out"
        });

        gsap.from(".hero-line, .hero-line-2", {
            opacity: 0,
            y: 20,
            duration: 1,
            delay: 0.3,
            stagger: 0.2
        });

        const wrapper = document.querySelector('.carousel-wrapper');
        const track = document.getElementById('carouselTrack');

        const originals = Array.from(track.children);
        const n = originals.length;

        if (n === 0) {
            console.warn('Carousel: tidak ada card.');
        } else {
            originals.forEach(card => track.appendChild(card.cloneNode(true)));
            originals.slice().reverse().forEach(card => track.insertBefore(card.cloneNode(true), track.firstChild));

            let allCards = Array.from(track.children);

            let index = n;
            let cardWidth = allCards[0].getBoundingClientRect().width;
            let gap = 14;
            let step = cardWidth + gap;
            const transitionDuration = 0.3; // seconds
            const pauseDuration = 3000; // ms
            function computeOffsets() {
                cardWidth = allCards[0].getBoundingClientRect().width;
                step = cardWidth + gap;
                const wrapperCenter = wrapper.clientWidth / 2;
                centerOffset = wrapperCenter - (cardWidth / 2);
            }
            let centerOffset = 0;
            computeOffsets();

            function setTrackX(x, withTransition = false) {
                if (!withTransition) {
                    gsap.set(track, {
                        x: x
                    });
                } else {
                    gsap.to(track, {
                        duration: transitionDuration,
                        x: x,
                        ease: "power2.out"
                    });
                }
            }

            function getXForIndex(i) {
                return -i * step + centerOffset;
            }

            function highlight(i) {
                allCards = Array.from(track.children);
                allCards.forEach(c => {
                    gsap.to(c, {
                        scale: 0.90,
                        opacity: 0.4,
                        duration: 0.35,
                        ease: "power2.out"
                    });
                });
                const active = allCards[i];
                if (active) {
                    gsap.to(active, {
                        scale: 1,
                        opacity: 1,
                        duration: 0.35,
                        ease: "power2.out"
                    });
                }
            }

            computeOffsets();
            setTrackX(getXForIndex(index), false);
            highlight(index);

            let isAnimating = false;
            let loopTimer = null;

            function slideNext() {
                if (isAnimating) return;
                isAnimating = true;

                index++;

                gsap.to(track, {
                    duration: transitionDuration,
                    x: getXForIndex(index),
                    ease: "power2.out",
                    onComplete: () => {

                        if (index >= n * 2) {
                            index = n;

                            gsap.killTweensOf(track);

                            gsap.set(track, {
                                x: getXForIndex(index)
                            });
                        }

                        highlight(index);
                        isAnimating = false;
                    }
                });
            }

            function startLoop() {
                stopLoop();

                loopTimer = setInterval(() => {
                    slideNext();
                }, pauseDuration + transitionDuration * 1000);
            }

            function stopLoop() {
                if (loopTimer) clearInterval(loopTimer);
                loopTimer = null;
            }

            startLoop();

            let resizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                stopLoop();
                resizeTimer = setTimeout(() => {
                    computeOffsets();
                    gsap.set(track, {
                        x: getXForIndex(index)
                    });
                    startLoop();
                }, 120);
            });
        }
    </script>
@endpush
