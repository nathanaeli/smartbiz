<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('contact.title') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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

        /* Nav adapted from landing */
        .navbar {
            backdrop-filter: blur(20px) saturate(180%);
            background: rgba(255, 255, 255, 0.75);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 0;
        }

        /* Hero adapted from landing */
        .contact-hero {
            padding: 160px 0 60px;
            background: radial-gradient(45% 45% at 50% 50%, rgba(99, 102, 241, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #020617;
        }

        /* Bento Grid Layout */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
            margin-bottom: 80px;
        }

        .bento-card {
            background: var(--bento-bg);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bento-card:hover {
            border-color: var(--brand-primary);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
        }

        .form-card { grid-column: span 8; }
        .info-card { grid-column: span 4; }

        @media (max-width: 991px) {
            .form-card, .info-card { grid-column: span 12; }
        }

        /* Form Styling */
        .form-label { font-weight: 600; color: var(--brand-dark); margin-bottom: 8px; }
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background: #fcfdfe;
        }
        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-black {
            background: #020617;
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }
        .btn-black:hover { transform: translateY(-2px); background: #1e293b; }

        /* Icon Styling */
        .icon-circle {
            width: 48px;
            height: 48px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--brand-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <span class="h4 fw-800 mb-0 tracking-tighter">{{ __('welcome.brand_name') }}<span class="text-primary">{{ __('welcome.brand_suffix') }}</span></span>
            </a>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="/#features">{{ __('welcome.nav_platform') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#pricing">{{ __('welcome.nav_pricing') }}</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#">{{ __('welcome.nav_support') }}</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <!-- Language Switcher -->
                    <div class="dropdown me-3">
                        <button class="btn btn-sm btn-outline-custom d-flex align-items-center gap-2" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 100px;">
                            <i class="ri-global-line"></i>
                            <span class="d-none d-lg-inline">{{ strtoupper(app()->getLocale()) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                            <li><a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">🇬🇧 English</a></li>
                            <li><a class="dropdown-item {{ app()->getLocale() == 'fr' ? 'active' : '' }}" href="{{ route('lang.switch', 'fr') }}">🇫🇷 Français</a></li>
                            <li><a class="dropdown-item {{ app()->getLocale() == 'sw' ? 'active' : '' }}" href="{{ route('lang.switch', 'sw') }}">🇹🇿 Kiswahili</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-black">{{ __('welcome.nav_cta') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="contact-hero text-center">
        <div class="container">
            <div class="hero-badge" data-aos="fade-down" style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 16px; border-radius: 100px; display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--brand-primary); margin-bottom: 2rem;">
                <span style="height: 8px; width: 8px; background: var(--brand-primary); border-radius: 50%; display: inline-block;"></span> {{ __('contact.hero_compliance_badge') }}
            </div>
            <h1 class="hero-title" data-aos="fade-up">{!! nl2br(__('contact.hero_title')) !!}</h1>
            <p class="lead text-secondary mx-auto mb-5" style="max-width: 600px;" data-aos="fade-up" data-aos-delay="100">
                {{ __('contact.hero_subtitle') }}
            </p>
        </div>
    </header>

    <main class="container">
        <div class="bento-grid">
            <div class="bento-card form-card" data-aos="fade-up">
                <h3 class="fw-800 mb-4">{{ __('contact.form_title') }}</h3>

                @if(session('success'))
                    <div class="alert alert-success border-0 rounded-4 mb-4" style="background: #d1fae5; color: #065f46;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('contact.label_fullname') }}</label>
                            <input type="text" name="name" class="form-control" placeholder="{{ __('contact.placeholder_fullname') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('contact.label_email') }}</label>
                            <input type="email" name="email" class="form-control" placeholder="{{ __('contact.placeholder_email') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('contact.label_subject') }}</label>
                            <select name="subject" class="form-control form-select shadow-none">
                                <option>{{ __('contact.subject_technical') }}</option>
                                <option>{{ __('contact.subject_sales') }}</option>
                                <option>{{ __('contact.subject_billing') }}</option>
                                <option>{{ __('contact.subject_partnership') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('contact.label_message') }}</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="{{ __('contact.placeholder_message') }}" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-black btn-lg px-5">{{ __('contact.btn_send') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="info-card d-flex flex-column gap-4" data-aos="fade-up" data-aos-delay="100">
                <div class="bento-card h-100 p-4">
                    <div class="icon-circle"><i class="ri-mail-line"></i></div>
                    <h5 class="fw-800">{{ __('contact.sidebar_email_title') }}</h5>
                    <p class="text-muted small">{{ __('contact.sidebar_email_text') }}</p>
                    <a href="mailto:support@smartbiz.com" class="text-primary fw-bold text-decoration-none">support@smartbiz.com</a>
                </div>

                <div class="bento-card h-100 p-4">
                    <div class="icon-circle"><i class="ri-map-pin-line"></i></div>
                    <h5 class="fw-800">{{ __('contact.sidebar_location_title') }}</h5>
                    <p class="text-muted small">{!! nl2br(__('contact.sidebar_location_text')) !!}</p>
                </div>

                <div class="bento-card h-100 p-4" style="background: var(--brand-dark); color: white;">
                    <div class="icon-circle" style="background: rgba(255,255,255,0.1); color: white;"><i class="ri-whatsapp-line"></i></div>
                    <h5 class="fw-800 text-white">{{ __('contact.sidebar_chat_title') }}</h5>
                    <p class="text-white-50 small">{{ __('contact.sidebar_chat_text') }}</p>
                    <a href="#" class="text-white fw-bold text-decoration-none">+254 700 123 456</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-5 border-top bg-light">
        <div class="container text-center text-md-start">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="h5 fw-800">Stockflowkp</span>
                    <p class="text-muted small mt-2 mb-0">© 2024 Smartbiz. Enterprise Duka Solutions.</p>
                </div>
                <div class="col-md-6 text-md-end mt-4 mt-md-0">
                    <a href="/privacy" class="nav-link d-inline-block p-0 me-3">{{ __('welcome.footer_privacy') }}</a>
                    <a href="/terms" class="nav-link d-inline-block p-0 me-3">{{ __('welcome.footer_terms') }}</a>
                    <a href="/contact" class="nav-link d-inline-block p-0">{{ __('welcome.footer_contact') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>
    <script src="{{ asset('assets/js/hope-ui.min.js') }}"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>
