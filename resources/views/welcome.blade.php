<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Bumbu Opie - Bumbu Masak Premium Indonesia</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#27ae60', // hijau daun
                        secondary: '#219150', // hijau tua
                        accent: '#b6e388', // hijau muda
                        gold: '#FFD700',
                        dark: '#1a3c1a',
                        light: '#f6fff6',
                    },
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- AOS Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        .gradient-bg {
            background: linear-gradient(120deg, #b6e388 0%, #27ae60 80%, #219150 100%);
        }
        .hero-blob {
            position: absolute; right: -60px; top: -60px; width: 400px; z-index: 0; opacity: 0.15;
        }
        .text-shadow {
            text-shadow: 0 4px 24px rgba(34, 139, 34, 0.18), 0 1.5px 0 #fff;
        }
        .gradient-text {
            background: linear-gradient(90deg, #27ae60 20%, #b6e388 80%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .glass {
            background: rgba(255,255,255,0.25);
            box-shadow: 0 8px 32px 0 rgba(39,174,96,0.12);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 1.5rem;
            border: 1.5px solid rgba(39,174,96,0.18);
        }
        .loader {
            border: 4px solid #b6e388;
            border-top: 4px solid #27ae60;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 767px) {
            #nav-menu {
                position: fixed;
                top: 0;
                right: 0;
                height: 100vh;
                width: 18rem;
                max-width: 100vw;
                background: #fff;
                border-radius: 1.5rem 0 0 1.5rem;
                box-shadow: 0 8px 32px 0 rgba(39,174,96,0.18);
                z-index: 50;
                padding-top: 0;
                display: flex;
                flex-direction: column;
            }
            #nav-menu nav {
                margin-top: 1rem;
            }
            #nav-menu .sidebar-link {
                font-size: 1.08rem;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            #nav-menu .sidebar-link.active, #nav-menu .sidebar-link:active {
                background: #b6e388;
                color: #219150;
            }
            #nav-overlay {
                display: block;
            }
        }
        @media (min-width: 768px) {
            #nav-menu {
                position: static !important;
                top: 0 !important;
                right: 0 !important;
                height: auto !important;
                width: auto !important;
                background: transparent !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding-top: 0 !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
                transform: none !important;
            }
            #nav-menu nav {
                margin-top: 0 !important;
                padding: 0 !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
                overflow: visible !important;
            }
            #nav-menu .sidebar-link {
                font-size: 1rem !important;
                font-weight: 500 !important;
                border-radius: 0.5rem !important;
                padding: 0.5rem 1rem !important;
                margin: 0 !important;
                background: none !important;
                color: #1a3c1a !important;
                box-shadow: none !important;
            }
            #nav-menu .sidebar-link.download-btn {
                background: #27ae60 !important;
                color: #fff !important;
                font-size: 1rem !important;
                font-weight: 700 !important;
                box-shadow: 0 2px 8px 0 rgba(39,174,96,0.13) !important;
                padding: 0.5rem 1.5rem !important;
                margin-left: 0.5rem !important;
            }
            #nav-menu .sidebar-link.download-btn:hover, #nav-menu .sidebar-link.download-btn:focus {
                background: #219150 !important;
                color: #fff !important;
            }
            #nav-menu .sidebar-link i {
                font-size: 1.1rem !important;
            }
            #nav-menu .sidebar-link.active, #nav-menu .sidebar-link:active {
                background: #b6e388 !important;
                color: #219150 !important;
            }
        }
        #nav-menu .download-btn {
            font-size: 1.15rem;
            font-weight: 800;
            background: linear-gradient(90deg, #27ae60 60%, #b6e388 100%);
            color: #fff;
            box-shadow: 0 4px 18px 0 rgba(39,174,96,0.13);
            border: none;
        }
        #nav-menu .download-btn:hover, #nav-menu .download-btn:focus {
            background: linear-gradient(90deg, #219150 60%, #FFD700 100%);
            color: #1a3c1a;
            box-shadow: 0 8px 32px 0 rgba(39,174,96,0.18);
        }
        #nav-menu .download-btn i {
            color: #FFD700;
            text-shadow: 0 2px 8px #27ae6033;
        }
        .close-btn {
            background: #fff;
            color: #27ae60;
            border: 2px solid #27ae60;
            font-size: 2rem;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .close-btn:hover, .close-btn:focus {
            background: #27ae60;
            color: #fff;
            box-shadow: 0 4px 18px 0 #27ae6033;
            border-color: #27ae60;
        }
    </style>
    <script src="https://unpkg.com/lottie-web@5.12.2/build/player/lottie.min.js"></script>
</head>
<body class="bg-light text-dark font-sans">
    <!-- Navigation -->
    <nav class="bg-white/90 shadow-lg fixed w-full z-50 backdrop-blur-md" data-aos="fade-down" data-aos-duration="800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo-bumbu.png') }}" alt="Logo" class="h-10 mr-2">
                    <h1 class="text-2xl font-bold text-primary tracking-wide">Bumbu Opie</h1>
                </div>
                <!-- Hamburger button (mobile) -->
                <button id="nav-toggle" class="md:hidden text-primary focus:outline-none focus:ring-2 focus:ring-primary/40 p-2 rounded-lg border-2 border-accent transition duration-200" aria-label="Toggle menu">
                    <svg id="nav-icon" class="w-8 h-8 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path id="nav-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <!-- Overlay -->
                <div id="nav-overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 transition-opacity duration-300 opacity-0 pointer-events-none md:hidden"></div>
                <!-- Sidebar menu (mobile) & Navbar menu (desktop) -->
                <aside id="nav-menu" class="fixed top-0 right-0 h-full w-72 max-w-full bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out md:static md:translate-x-0 md:shadow-none md:bg-transparent md:w-auto md:h-auto md:block rounded-l-3xl md:rounded-none flex flex-col md:flex-row md:items-center md:justify-end md:gap-2 md:py-0 md:px-0 md:relative md:top-0 md:right-0 md:h-auto md:w-auto md:shadow-none md:bg-transparent md:rounded-none md:transform-none">
                    <!-- Sidebar header (mobile only) -->
                    <div class="flex items-center justify-between px-6 pt-6 pb-2 md:hidden">
                        <span class="text-2xl font-bold text-primary tracking-wide flex items-center">
                            <img src="{{ asset('images/logo-bumbu.png') }}" alt="Logo" class="h-8 mr-2">
                            Bumbu Opie
                        </span>
                        <button id="nav-close" class="close-btn text-primary border-2 border-primary bg-white hover:bg-primary hover:text-white text-3xl p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-primary/40 transition shadow-sm hover:shadow-lg duration-200"><i class="fas fa-times"></i></button>
                    </div>
                    <!-- Menu list -->
                    <nav class="flex-1 overflow-y-auto px-6 pb-6 pt-2 md:pt-0 md:pb-0 md:px-0 md:flex md:items-center md:gap-2 md:overflow-visible">
                        <a href="#beranda" class="sidebar-link flex items-center gap-3 text-dark hover:text-primary px-3 py-3 rounded-xl text-base font-medium transition md:rounded-md md:py-2 md:px-3 md:text-sm md:font-normal"><i class="fas fa-home text-lg md:text-base"></i>Beranda</a>
                        <a href="#tentang" class="sidebar-link flex items-center gap-3 text-dark hover:text-primary px-3 py-3 rounded-xl text-base font-medium transition md:rounded-md md:py-2 md:px-3 md:text-sm md:font-normal"><i class="fas fa-info-circle text-lg md:text-base"></i>Tentang</a>
                        <a href="#fitur" class="sidebar-link flex items-center gap-3 text-dark hover:text-primary px-3 py-3 rounded-xl text-base font-medium transition md:rounded-md md:py-2 md:px-3 md:text-sm md:font-normal"><i class="fas fa-star text-lg md:text-base"></i>Fitur</a>
                        <a href="#download" class="sidebar-link flex items-center gap-3 download-btn bg-gradient-to-r from-primary to-accent text-white px-3 py-3 rounded-xl text-lg font-extrabold hover:from-secondary hover:to-gold hover:text-dark transition duration-300 shadow-lg mt-2 md:mt-0 md:rounded-md md:py-2 md:px-4 md:text-base md:font-bold md:shadow-md md:bg-primary md:bg-none md:from-none md:to-none md:hover:bg-secondary md:hover:text-white"><i class="fab fa-android text-2xl md:text-lg text-gold"></i>Download App</a>
                    </nav>
                    <!-- Sidebar footer (mobile only) -->
                    <div class="hidden md:block"></div>
                    <div class="mt-auto pb-4 px-6 md:hidden">
                        <div class="flex gap-4 justify-center">
                            <a href="#" class="text-gray-400 hover:text-primary"><i class="fab fa-facebook text-xl"></i></a>
                            <a href="#" class="text-gray-400 hover:text-primary"><i class="fab fa-instagram text-xl"></i></a>
                            <a href="#" class="text-gray-400 hover:text-primary"><i class="fab fa-whatsapp text-xl"></i></a>
                        </div>
                        <div class="text-xs text-gray-400 text-center mt-2">&copy; 2025 Bumbu Opie</div>
                    </div>
                </aside>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="pt-32 pb-24 gradient-bg relative overflow-hidden flex items-center min-h-[80vh]" data-aos="fade-up" data-aos-duration="900">
        <svg class="absolute left-[-80px] top-[-60px] w-72 md:w-96 opacity-20 blur-2xl z-0" viewBox="0 0 200 200" fill="none"><path d="M100 10C120 40 180 60 190 100C200 140 140 180 100 190C60 200 20 140 10 100C0 60 80 40 100 10Z" fill="#219150"/></svg>
        <svg class="absolute right-[-60px] bottom-[-60px] w-72 md:w-96 opacity-20 blur-2xl z-0" viewBox="0 0 200 200" fill="none"><path d="M100 30C110 50 160 70 170 100C180 130 130 170 100 180C70 190 30 130 20 100C10 70 90 50 100 30Z" fill="#b6e388"/></svg>
        <svg class="hero-blob hidden md:block" viewBox="0 0 400 400" fill="none"><ellipse cx="200" cy="200" rx="200" ry="200" fill="#fff"/></svg>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center min-h-[60vh]">
                <div class="flex flex-col justify-center h-full" data-aos="fade-right" data-aos-duration="900">
                    <h1 class="text-5xl md:text-7xl font-extrabold mb-8 leading-tight text-shadow text-white drop-shadow-lg" data-aos="fade-down" data-aos-delay="100">
                        Bumbu Masak <span class="gradient-text">Premium</span><br>
                        <span class="text-gold border-b-8 border-gold pb-1 inline-block">Khas Pekanbaru</span>
                    </h1>
                    <p class="text-xl mb-10 text-white/90 drop-shadow-md" data-aos="fade-up" data-aos-delay="300">
                        Nikmati cita rasa autentik masakan Indonesia dengan bumbu berkualitas tinggi. Pesan hanya lewat aplikasi mobile kami!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 w-full max-w-xl" data-aos="fade-up" data-aos-delay="600">
                        <a href="#download" class="glass bg-primary/80 text-white px-7 py-3 rounded-lg text-lg font-bold hover:bg-gold hover:text-primary transition-all duration-300 flex items-center justify-center shadow-xl group focus:outline-none focus:ring-4 focus:ring-primary/40 w-full sm:w-auto" aria-label="Download APK">
                            <i class="fab fa-android mr-2 text-xl animate-pulse"></i>
                            Download APK
                            <span class="ml-2 group-hover:translate-x-1 transition-transform text-xl">→</span>
                        </a>
                        <a href="#tentang" class="glass border-2 border-primary text-primary px-7 py-3 rounded-lg text-lg font-bold hover:bg-primary hover:text-white transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-primary/40 w-full sm:w-auto" aria-label="Pelajari Lebih Lanjut">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="relative flex justify-center items-center" data-aos="fade-left">
                    <!-- Animasi Lottie dihapus sesuai permintaan -->
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="py-16 bg-white" data-aos="fade-up" data-aos-delay="100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-primary mb-4">Mengapa Memilih Bumbu Opie?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Kami berkomitmen menghadirkan bumbu masak berkualitas tinggi dengan cita rasa autentik Indonesia
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-aos="fade-up" data-aos-delay="700">
                <div class="text-center p-8 rounded-2xl bg-light shadow-lg hover:scale-105 hover:shadow-2xl transition duration-300" data-aos="zoom-in" data-aos-delay="100">
                    <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-md">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Bahan Alami</h3>
                    <p class="text-gray-600">Dibuat dari rempah-rempah pilihan tanpa pengawet berbahaya</p>
                </div>
                <div class="text-center p-8 rounded-2xl bg-light shadow-lg hover:scale-105 hover:shadow-2xl transition duration-300" data-aos="zoom-in" data-aos-delay="200">
                    <div class="bg-secondary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-md">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Halal & Terjamin</h3>
                    <p class="text-gray-600">Sudah bersertifikat halal dan higienis untuk konsumsi keluarga</p>
                </div>
                <div class="text-center p-8 rounded-2xl bg-light shadow-lg hover:scale-105 hover:shadow-2xl transition duration-300" data-aos="zoom-in" data-aos-delay="300">
                    <div class="bg-accent text-dark w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-md">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Pengiriman Cepat</h3>
                    <p class="text-gray-600">Dikirim langsung ke rumah Anda dengan layanan kurir terpercaya</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-16 bg-light" data-aos="fade-up" data-aos-delay="200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-primary mb-4">Fitur Aplikasi Mobile</h2>
                <p class="text-xl text-gray-600">
                    Nikmati kemudahan berbelanja bumbu masak melalui aplikasi mobile kami
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-delay="800">
                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-primary mb-4">
                        <i class="fas fa-shopping-cart text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Belanja Mudah</h3>
                    <p class="text-gray-600 text-sm">Pesan bumbu dengan mudah melalui smartphone</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-secondary mb-4">
                        <i class="fas fa-credit-card text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Pembayaran Aman</h3>
                    <p class="text-gray-600 text-sm">Bayar dengan berbagai metode pembayaran</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-accent mb-4">
                        <i class="fas fa-truck text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Lacak Pesanan</h3>
                    <p class="text-gray-600 text-sm">Pantau status pengiriman pesanan Anda</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-primary mb-4">
                        <i class="fas fa-headset text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Layanan 24/7</h3>
                    <p class="text-gray-600 text-sm">Customer service siap membantu Anda</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="download" class="py-20 gradient-bg relative overflow-hidden" data-aos="zoom-in" data-aos-delay="300">
        <svg class="absolute left-0 bottom-0 w-80 opacity-10" viewBox="0 0 400 400" fill="none"><ellipse cx="200" cy="200" rx="200" ry="200" fill="#fff"/></svg>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-4xl font-bold text-white mb-6" data-aos="fade-up">Download Aplikasi Sekarang</h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Dapatkan pengalaman berbelanja bumbu masak terbaik dengan mengunduh aplikasi mobile kami
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center" data-aos="zoom-in" data-aos-delay="200">
                <a id="download-apk-btn" href="{{ route('download.apk') }}" class="bg-white text-primary px-10 py-5 rounded-xl text-xl font-bold hover:bg-accent hover:text-dark transition duration-300 flex items-center shadow-2xl border-2 border-primary animate-pulse relative overflow-hidden">
                    <span class="icon-android"><i class="fab fa-android text-3xl mr-4"></i></span>
                    <span class="download-text">Download APK</span>
                    <span class="spinner hidden ml-4">
                        <span class="loader"></span>
                    </span>
                </a>
                <div class="text-white text-center">
                    <div class="text-sm opacity-80">Versi terbaru</div>
                    <div class="font-semibold">v1.0.0</div>
                </div>
            </div>
            <div class="mt-8 text-white/80">
                <p class="text-sm">* Aplikasi tersedia untuk Android 6.0 ke atas</p>
                <p class="text-sm">* Ukuran file: ~25MB</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-16 bg-white" data-aos="fade-up" data-aos-delay="400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-primary mb-4">Apa Kata Mereka?</h2>
                <p class="text-xl text-gray-600">Testimoni dari pelanggan setia kami</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-light p-8 rounded-2xl shadow-lg hover:scale-105 hover:shadow-2xl transition duration-300" data-aos="flip-left" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white font-bold mr-4 text-xl">
                            S
                        </div>
                        <div>
                            <h4 class="font-semibold">Sarah Amanda</h4>
                            <p class="text-sm text-gray-600">Ibu Rumah Tangga</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Bumbu Opie bikin masakan saya jadi lebih enak! Aplikasinya juga mudah digunakan."</p>
                </div>
                <div class="bg-light p-8 rounded-2xl shadow-lg hover:scale-105 hover:shadow-2xl transition duration-300" data-aos="flip-left" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center text-white font-bold mr-4 text-xl">
                            B
                        </div>
                        <div>
                            <h4 class="font-semibold">Budi Santoso</h4>
                            <p class="text-sm text-gray-600">Chef Restoran</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Kualitas bumbu sangat konsisten. Cocok untuk kebutuhan restoran kami."</p>
                </div>
                <div class="bg-light p-8 rounded-2xl shadow-lg hover:scale-105 hover:shadow-2xl transition duration-300" data-aos="flip-left" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center text-dark font-bold mr-4 text-xl">
                            M
                        </div>
                        <div>
                            <h4 class="font-semibold">Maya Indah</h4>
                            <p class="text-sm text-gray-600">Food Blogger</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"Pengiriman cepat dan bumbu selalu fresh. Recommended banget!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-12" data-aos="fade-up" data-aos-delay="500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('images/logo-bumbu.png') }}" alt="Logo" class="h-10 mr-2">
                        <h3 class="text-2xl font-bold text-primary">Bumbu Opie</h3>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Bumbu masak premium Pekanbaru dengan cita rasa autentik untuk keluarga Pekanbaru.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-primary">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Produk</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-primary">Bumbu Nasi Goreng</a></li>
                        <li><a href="#" class="hover:text-primary">Bumbu Rendang</a></li>
                        <li><a href="#" class="hover:text-primary">Bumbu Soto</a></li>
                        <li><a href="#" class="hover:text-primary">Bumbu Rawon</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-primary">Pengiriman</a></li>
                        <li><a href="#" class="hover:text-primary">Pembayaran</a></li>
                        <li><a href="#" class="hover:text-primary">Customer Service</a></li>
                        <li><a href="#" class="hover:text-primary">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            +62 813-8905-1503
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            info@bumbuopie.com
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            Pekanbaru, Indonesia
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; 2025 Bumbu Opie. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- AOS Animate On Scroll JS -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 900,
            once: true,
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const downloadBtn = document.getElementById('download-apk-btn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function(e) {
                    // Tampilkan spinner dan ubah teks
                    const icon = downloadBtn.querySelector('.icon-android');
                    const text = downloadBtn.querySelector('.download-text');
                    const spinner = downloadBtn.querySelector('.spinner');
                    icon.style.display = 'none';
                    text.textContent = 'Mengunduh...';
                    spinner.classList.remove('hidden');
                    // Setelah 2 detik, kembalikan ke semula (untuk UX, file tetap terdownload)
                    setTimeout(() => {
                        icon.style.display = '';
                        text.textContent = 'Download APK';
                        spinner.classList.add('hidden');
                    }, 2000);
                });
            }
            // Hamburger menu toggle + sidebar animasi + close X + highlight menu aktif
            const navToggle = document.getElementById('nav-toggle');
            const navMenu = document.getElementById('nav-menu');
            const navOverlay = document.getElementById('nav-overlay');
            const navIconPath = document.getElementById('nav-icon-path');
            const navClose = document.getElementById('nav-close');
            let menuOpen = false;
            function openMenu() {
                navMenu.classList.remove('translate-x-full');
                navMenu.classList.add('translate-x-0');
                navOverlay.classList.remove('opacity-0', 'pointer-events-none');
                navOverlay.classList.add('opacity-100', 'pointer-events-auto');
                navIconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12');
                menuOpen = true;
            }
            function closeMenu() {
                navMenu.classList.add('translate-x-full');
                navMenu.classList.remove('translate-x-0');
                navOverlay.classList.add('opacity-0', 'pointer-events-none');
                navOverlay.classList.remove('opacity-100', 'pointer-events-auto');
                navIconPath.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                menuOpen = false;
            }
            navToggle && navToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                menuOpen ? closeMenu() : openMenu();
            });
            navClose && navClose.addEventListener('click', function() {
                closeMenu();
            });
            navOverlay && navOverlay.addEventListener('click', function() {
                if (menuOpen) closeMenu();
            });
            // Tutup menu saat klik link di mobile
            const navLinks = navMenu.querySelectorAll('.sidebar-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768 && menuOpen) {
                        closeMenu();
                    }
                });
            });
            // Highlight menu aktif saat klik
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
