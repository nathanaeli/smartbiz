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
                        <button class="btn btn-sm btn-outline-custom d-flex align-items-center gap-2" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-global-line"></i>
                            <span class="d-none d-lg-inline">{{ strtoupper(app()->getLocale()) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                            <li><a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">🇬🇧 English</a></li>
                            <li><a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}" href="{{ route('lang.switch', 'fr') }}">🇫🇷 Français</a></li>
                            <li><a class="dropdown-item {{ app()->getLocale() == 'sw' ? 'active' : '' }}" href="{{ route('lang.switch', 'sw') }}">🇹🇿 Kiswahili</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="nav-link me-3 d-none d-lg-block">{{ __('welcome.nav_login') }}</a>
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
            <p class="lead text-secondary mx-auto mb-5" style="max-width: 650px;" data-aos="fade-up"
                data-aos-delay="100">
                {{ __('welcome.hero_subtitle') }}
            </p>
            <div class="d-flex justify-content-center gap-3" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ route('register') }}" class="btn btn-black btn-lg px-5">{{ __('welcome.btn_get_started') }}</a>
                <button class="btn btn-outline-custom btn-lg px-5">{{ __('welcome.btn_book_demo') }}</button>
            </div>

            <div class="mt-5 pt-5 client-logos d-flex justify-content-center flex-wrap gap-5" data-aos="fade-in"
                data-aos-delay="400">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Logitech_logo.svg" alt="Logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" alt="Logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" alt="Logo">
            </div>
        </div>
    </header>

    <section id="features" class="py-5">
        <div class="container">
            <div class="bento-grid">
                <!-- Large Feature -->
                <div class="bento-card card-8" data-aos="fade-up">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="text-primary fw-bold small text-uppercase">{{ __('welcome.feature_inventory_label') }}</span>
                            <h3 class="fw-800 mt-2">{{ __('welcome.feature_inventory_title') }}</h3>
                            <p class="text-muted mt-3">{{ __('welcome.feature_inventory_description') }}</p>
                        </div>
                        <div class="col-md-6">
                            <img src="https://hopeui.iqonic.design/html/assets/images/dashboard/top-header.png"
                                class="img-fluid rounded-3 shadow-sm" alt="App Preview">
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
