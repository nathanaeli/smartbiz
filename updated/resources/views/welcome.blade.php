<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('welcome.page_title') }}</title>

    <!-- Professional Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Icons & Core -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-dark: #0f172a;
            --brand-gray: #64748b;
            --bg-light: #f8fafc;
            --bento-bg: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--brand-dark);
            letter-spacing: -0.01em;
        }

        /* High-End Navigation */
        .navbar {
            backdrop-filter: blur(20px) saturate(180%);
            background: rgba(255, 255, 255, 0.75);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 12px 0;
            background: rgba(255, 255, 255, 0.9);
        }

        .nav-link {
            color: var(--brand-dark) !important;
            font-weight: 500;
            font-size: 0.95rem;
            margin: 0 15px;
        }

        /* Hero Section - The "Stripe" Look */
        .hero-area {
            padding: 160px 0 100px;
            background: radial-gradient(45% 45% at 50% 50%, rgba(99, 102, 241, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
            overflow: hidden;
        }

        .hero-badge {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 6px 16px;
            border-radius: 100px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--brand-primary);
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.25rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            color: #020617;
            margin-bottom: 1.5rem;
        }

        /* Bento Grid Style Features */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }

        .bento-card {
            background: var(--bento-bg);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .bento-card:hover {
            border-color: var(--brand-primary);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
        }

        .card-12 {
            grid-column: span 12;
        }

        .card-8 {
            grid-column: span 8;
        }

        .card-4 {
            grid-column: span 4;
        }

        .card-6 {
            grid-column: span 6;
        }

        @media (max-width: 991px) {

            .card-8,
            .card-4,
            .card-6 {
                grid-column: span 12;
            }
        }

        /* Professional Buttons */
        .btn-black {
            background: #020617;
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
            border: none;
        }

        .btn-black:hover {
            background: #1e293b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-custom {
            border: 1px solid #e2e8f0;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            color: var(--brand-dark);
            transition: 0.3s;
        }

        /* Trust Bar */
        .client-logos img {
            filter: grayscale(1) opacity(0.6);
            max-height: 35px;
            transition: 0.3s;
        }

        .client-logos img:hover {
            filter: grayscale(0) opacity(1);
        }

        /* Pricing UI */
        .price-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 48px;
            height: 100%;
        }

        .price-card.featured {
            background: #020617;
            color: #fff;
            border: none;
        }

        .dot-indicator {
            height: 8px;
            width: 8px;
            background: var(--brand-primary);
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
    </style>
    <style>
        /* Mobile Optimizations */
        @media (max-width: 991px) {

            .card-8,
            .card-4,
            .card-6 {
                grid-column: span 12;
            }

            .navbar-collapse {
                background: #ffffff;
                padding: 24px;
                border-radius: 16px;
                margin-top: 16px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .navbar-nav .nav-link {
                padding: 12px 0;
                border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            }

            .navbar-nav .nav-item:last-child .nav-link {
                border-bottom: none;
            }

            .navbar-collapse>.d-flex {
                /* fix buttons in nav on mobile */
                flex-direction: column;
                align-items: stretch !important;
                gap: 12px;
                margin-top: 16px;
            }

            .navbar-collapse>.d-flex .btn,
            .navbar-collapse>.d-flex .nav-link {
                width: 100%;
                text-align: center;
                margin: 0 !important;
            }
        }

        @media (max-width: 767px) {
            .hero-area {
                padding: 120px 0 60px;
            }

            .hero-title {
                font-size: 2.25rem;
                /* Smaller title on mobile */
            }

            .bento-card {
                padding: 24px;
                /* Reduced padding on cards */
            }

            .bento-grid {
                gap: 16px;
                /* Smaller gap on mobile */
            }

            .price-card {
                padding: 24px;
            }

            .btn-lg {
                padding: 12px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('assets/images/logo.png') }}" alt="" height="30" class="me-2 d-none">
                <span class="h4 fw-800 mb-0 tracking-tighter">{{ __('welcome.brand_name') }}<span class="text-primary">{{ __('welcome.brand_suffix') }}</span></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navContent">
                <i class="ri-menu-line"></i>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">{{ __('welcome.nav_platform') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">{{ __('welcome.nav_pricing') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#support">{{ __('welcome.nav_support') }}</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <!-- Language Switcher -->
                    <div class="dropdown me-3">
                        <button class="btn btn-sm btn-outline-custom d-flex align-items-center justify-content-center gap-2" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-global-line"></i>
                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                            <li><a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">🇬🇧 English</a></li>
                            <li><a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}" href="{{ route('lang.switch', 'fr') }}">🇫🇷 Français</a></li>
                            <li><a class="dropdown-item {{ app()->getLocale() == 'sw' ? 'active' : '' }}" href="{{ route('lang.switch', 'sw') }}">🇹🇿 Kiswahili</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="nav-link me-3">{{ __('welcome.nav_login') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-black">{{ __('welcome.nav_cta') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-area text-center">
        <div class="container">
            <div class="hero-badge" data-aos="fade-down">
                <span class="dot-indicator"></span> {{ __('welcome.badge_new') }}
            </div>
            <h1 class="hero-title" data-aos="fade-up">
                {{ __('welcome.hero_title') }}
            </h1>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register') }}" class="btn btn-black btn-lg px-5">{{ __('welcome.btn_get_started') }}</a>
                <button class="btn btn-outline-custom btn-lg px-5">{{ __('welcome.btn_book_demo') }}</button>
            </div>


        </div>
    </header>

    <section id="features" class="py-5">
        <div class="container">
            <div class="bento-grid">
                <!-- Large Feature -->
                <div class="bento-card card-8" data-aos="fade-up">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <span class="text-primary fw-bold small text-uppercase">{{ __('welcome.feature_inventory_label') }}</span>
                            <h3 class="fw-800 mt-2">{{ __('welcome.feature_inventory_title') }}</h3>
                            <p class="text-muted mt-3">{{ __('welcome.feature_inventory_description') }}</p>
                        </div>

                    </div>
                </div>

                <!-- Small Feature -->
                <div class="bento-card card-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="icon-box mb-4 text-primary"><i class="ri-shield-check-fill h1"></i></div>
                    <h4 class="fw-800">{{ __('welcome.feature_fraud_title') }}</h4>
                    <p class="text-muted">{{ __('welcome.feature_fraud_description') }}</p>
                </div>

                <!-- Small Feature -->
                <div class="bento-card card-4" data-aos="fade-up">
                    <div class="icon-box mb-4 text-primary"><i class="ri-smartphone-line h1"></i></div>
                    <h4 class="fw-800">{{ __('welcome.feature_mobile_title') }}</h4>
                    <p class="text-muted">{{ __('welcome.feature_mobile_description') }}</p>
                </div>

                <!-- Mid Feature -->
                <div class="bento-card card-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h3 class="fw-800">{{ __('welcome.feature_multi_duka_title') }}</h3>
                            <p class="text-muted">{{ __('welcome.feature_multi_duka_description') }}</p>
                        </div>
                        <div class="col-md-5 text-end">
                            <div class="bg-light p-4 rounded-4 text-center">
                                <h1 class="text-primary fw-800">{{ __('welcome.feature_multi_duka_stat') }}</h1>
                                <span class="small fw-bold">{{ __('welcome.feature_multi_duka_stat_label') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Download App Section -->
    <section id="download-app" class="pb-5">
        <div class="container">
            <div class="bento-card card-12 p-0 position-relative overflow-hidden border-0"
                style="background: linear-gradient(120deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); color: white; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">

                <!-- Background Mesh Gradients -->
                <div class="position-absolute top-0 start-0 w-100 h-100" style="overflow: hidden; pointer-events: none;">
                    <div style="position: absolute; top: -10%; left: -10%; width: 50%; height: 50%; background: #4f46e5; filter: blur(120px); opacity: 0.3; border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -10%; right: -10%; width: 50%; height: 50%; background: #ec4899; filter: blur(120px); opacity: 0.2; border-radius: 50%;"></div>
                </div>

                <div class="row align-items-center position-relative z-1 p-4 p-md-5">
                    <!-- Left Content -->
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4"
                            style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);">
                            <span class="dot-indicator bg-success shadow-sm" style="margin-right: 0;"></span>
                            <span class="small fw-bold tracking-wide text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Native Android App</span>
                        </div>

                        <h2 class="display-5 fw-900 mb-3 text-white lh-sm">
                            Run your business <br>
                            <span class="text-transparent bg-clip-text" style="background-image: linear-gradient(to right, #818cf8, #c084fc); -webkit-background-clip: text; background-clip: text; color: transparent;">smarter & faster.</span>
                        </h2>

                        <p class="text-white-50 mb-5 lead fw-light" style="max-width: 480px;">
                            Experience the power of StockFlow KP in your pocket. Real-time analytics, instant invoicing, and inventory management—all just a tap away.
                        </p>

                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a href="https://drive.google.com/uc?export=download&id=1fDFa2ZFoX37zJmfa9Q3CAFvneryPQkBc"
                                class="btn btn-primary btn-lg border-0 d-inline-flex align-items-center justify-content-center gap-2 shadow-lg position-relative overflow-hidden group"
                                style="background: white; color: #0f172a; font-weight: 700; padding: 14px 28px;">
                                <i class="ri-google-play-fill fs-4 text-primary"></i>
                                <span class="d-flex flex-column align-items-start lh-1">
                                    <span style="font-size: 0.7rem; text-transform: uppercase; color: #64748b;">Download APK</span>
                                    <span>Direct Download</span>
                                </span>
                            </a>

                            <div class="d-flex align-items-center gap-3 px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.05);">
                                <div class="bg-white rounded p-1">
                                    <!-- Simple CSS Generated QR Placeholder -->
                                    <div style="width: 40px; height: 40px; background-image: repeating-linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000), repeating-linear-gradient(45deg, #000 25%, #fff 25%, #fff 75%, #000 75%, #000); background-position: 0 0, 10px 10px; background-size: 10px 10px; opacity: 0.8;"></div>
                                </div>
                                <div class="text-start">
                                    <div class="small fw-bold text-white">Scan to Get</div>
                                    <div class="text-xs text-white-50">Instant Install</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex align-items-center gap-4 text-white-50 small border-top pt-4" style="border-color: rgba(255,255,255,0.1) !important;">
                            <span class="d-flex align-items-center gap-2" data-bs-toggle="tooltip" title="Scanning for malware...">
                                <i class="ri-shield-check-fill text-emerald-400" style="color: #34d399;"></i>
                                Verified Safe
                            </span>
                            <span class="d-flex align-items-center gap-2">
                                <i class="ri-cpu-line text-blue-400" style="color: #60a5fa;"></i>
                                v1.0.2 Stable
                            </span>
                            <span class="d-flex align-items-center gap-2">
                                <i class="ri-android-fill text-green-400" style="color: #4ade80;"></i>
                                8.0+
                            </span>
                        </div>
                    </div>

                    <!-- Right Visual - CSS Phone Mockup -->
                    <div class="col-lg-6 d-none d-lg-block position-relative" style="height: 500px;">
                        <!-- Floating Review Card -->
                        <div class="position-absolute top-50 start-0 translate-middle-y z-2 bg-white text-dark p-3 rounded-4 shadow-xl"
                            style="width: 220px; right: auto; left: -20px; animation: float 6s ease-in-out infinite;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ri-star-fill text-warning"></i>
                                <i class="ri-star-fill text-warning"></i>
                                <i class="ri-star-fill text-warning"></i>
                                <i class="ri-star-fill text-warning"></i>
                                <i class="ri-star-fill text-warning"></i>
                            </div>
                            <p class="small fw-bold mb-1">"Best inventory app I've used!"</p>
                            <p class="text-xs text-muted mb-0">- Juma, Duka Owner</p>
                        </div>

                        <!-- CSS Phone -->
                        <div class="position-absolute top-50 start-50 translate-middle"
                            style="width: 280px; height: 560px; background: #000; border-radius: 40px; border: 8px solid #333; box-shadow: 0 0 0 2px #555, 0 30px 60px rgba(0,0,0,0.5); overflow: hidden; transform: rotate(-6deg) translateY(20px);">

                            <!-- Notch -->
                            <div class="position-absolute top-0 start-50 translate-middle-x bg-dark z-3" style="width: 120px; height: 25px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;"></div>

                            <!-- Screen Content (Matches StockFlow KP Screenshot) -->
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-4 position-relative" style="background: radial-gradient(circle at top right, #1e3a8a, #0f172a);">

                                <!-- Stars/Particles -->
                                <div class="position-absolute" style="top: 10%; left: 20%; width: 2px; height: 2px; background: white; opacity: 0.5; border-radius: 50%;"></div>
                                <div class="position-absolute" style="top: 30%; right: 15%; width: 3px; height: 3px; background: white; opacity: 0.3; border-radius: 50%;"></div>
                                <div class="position-absolute" style="bottom: 20%; left: 10%; width: 2px; height: 2px; background: white; opacity: 0.4; border-radius: 50%;"></div>

                                <!-- Logo Box -->
                                <div class="bg-white rounded-4 p-3 mb-4 shadow-lg d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="ri-shopping-cart-2-fill text-primary display-5"></i>
                                    </div>
                                </div>

                                <!-- App Name -->
                                <h3 class="fw-bold text-white mb-2">StockFlow KP</h3>
                                <p class="text-white-50 small mb-4">Streamline Your Inventory Management</p>

                                <!-- Tags -->
                                <div class="d-flex justify-content-center gap-2 mb-5 w-100">
                                    <span class="badge bg-transparent border border-secondary text-white-50 fw-light rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                        <i class="ri-speed-line me-1"></i>Efficient
                                    </span>
                                    <span class="badge bg-transparent border border-secondary text-white-50 fw-light rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                        <i class="ri-shield-check-line me-1"></i>Reliable
                                    </span>
                                    <span class="badge bg-transparent border border-secondary text-white-50 fw-light rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                        <i class="ri-sparkling-fill me-1"></i>Smart
                                    </span>
                                </div>

                                <!-- Buttons -->
                                <div class="w-100 d-flex flex-column gap-3">
                                    <button class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-lg" style="background: linear-gradient(90deg, #3b82f6, #06b6d4); border: none;">
                                        Get Started <i class="ri-arrow-right-line ms-1"></i>
                                    </button>
                                    <button class="btn btn-outline-light w-100 rounded-pill py-2 fw-semibold" style="border-color: rgba(255,255,255,0.2);">
                                        Create Account
                                    </button>
                                </div>

                                <p class="text-white-50 mt-4 position-absolute bottom-0 mb-3" style="font-size: 0.6rem;">Inventory management made simple</p>
                            </div>
                        </div>

                        <!-- Floating Download Status -->
                        <div class="position-absolute bottom-0 end-0 mb-5 me-5 bg-dark text-white p-3 rounded-4 shadow-lg border border-secondary"
                            style="width: 200px; animation: float 5s ease-in-out infinite 1s;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="spinner-border text-success spinner-border-sm" role="status"></div>
                                <div class="lh-1">
                                    <div class="text-xs text-white-50">Downloading...</div>
                                    <div class="fw-bold small">stockflowkp.apk</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-100 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="hero-title" style="font-size: 3rem;">{{ __('welcome.pricing_title') }}</h2>
                <p class="text-muted">{{ __('welcome.pricing_subtitle') }}</p>
            </div>
            <div class="row g-4">
                @foreach($plans as $index => $plan)
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="price-card {{ $index == 1 ? 'featured shadow-lg' : '' }}">
                        <h5 class="fw-700">{{ $plan->name }}</h5>
                        <p class="{{ $index == 1 ? 'text-white-50' : 'text-muted' }}">{{ $plan->description }}</p>

                        <div class="my-4">
                            @if($plan->price > 0)
                            <span class="display-5 fw-800">{{ number_format($plan->price, 0) }}</span>
                            <span class="{{ $index == 1 ? 'text-white-50' : 'text-muted' }}">{{ __('welcome.pricing_per_month') }}</span>
                            @else
                            <span class="display-5 fw-800">{{ __('welcome.pricing_custom') }}</span>
                            @endif
                        </div>

                        <ul class="list-unstyled mb-5">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="ri-checkbox-circle-fill {{ $index == 1 ? 'text-primary' : 'text-success' }} fs-5 me-2"></i>
                                <span><strong>{{ $plan->max_dukas }}</strong> {{ $plan->max_dukas > 1 ? __('welcome.pricing_dukas_plural') : __('welcome.pricing_dukas_included') }} {{ __('welcome.pricing_included') }}</span>
                            </li>

                            @if($plan->features && $plan->features->isNotEmpty())
                            @foreach($plan->features as $feature)
                            <li class="mb-3 d-flex align-items-center">
                                <i class="ri-check-line {{ $index == 1 ? 'text-primary' : 'text-success' }} me-2"></i>
                                <span>{{ $feature->name }}</span>
                            </li>
                            @endforeach
                            @endif
                        </ul>

                        <a href="{{ route('register', ['plan' => $plan->id]) }}"
                            class="btn {{ $index == 1 ? 'btn-primary btn-lg border-0' : 'btn-outline-custom' }} w-100"
                            {{ $index == 1 ? 'style="background: var(--brand-primary);"' : '' }}>
                            {{ $plan->price > 0 ? __('welcome.btn_start_trial') : __('welcome.btn_contact_sales') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="py-5 border-top bg-light mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span class="h5 fw-800">{{ __('welcome.footer_brand') }}</span>
                    <p class="text-muted small mt-2">{{ __('welcome.footer_copyright') }}</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="/privacy" class="nav-link d-inline-block">{{ __('welcome.footer_privacy') }}</a>
                    <a href="/terms" class="nav-link d-inline-block">{{ __('welcome.footer_terms') }}</a>
                    <a href="/contact" class="nav-link d-inline-block">{{ __('welcome.footer_contact') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>
    <script src="{{ asset('assets/js/hope-ui.min.js') }}"></script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>