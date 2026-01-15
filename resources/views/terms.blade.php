<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('terms.title') }}</title>

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

        /* Nav adapted from landing/contact */
        .navbar {
            backdrop-filter: blur(20px) saturate(180%);
            background: rgba(255, 255, 255, 0.75);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 0;
        }

        /* Hero adapted from contact/privacy */
        .policy-hero {
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
            margin-bottom: 100px;
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

        .card-main { grid-column: span 8; }
        .card-side { grid-column: span 4; }

        @media (max-width: 991px) {
            .card-main, .card-side { grid-column: span 12; }
        }

        /* Typography */
        .policy-section h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--brand-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .policy-section h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: var(--brand-primary);
            border-radius: 2px;
        }

        .policy-section h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--brand-dark);
        }

        .policy-section p, .policy-section li {
            color: var(--brand-gray);
            line-height: 1.8;
            margin-bottom: 1.2rem;
        }

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

        .language-pills {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 100px;
            padding: 4px;
            display: inline-flex;
        }

        .language-pills .nav-link {
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brand-gray);
        }

        .language-pills .nav-link.active {
            background: var(--brand-primary);
            color: #fff;
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
                    <li class="nav-item"><a class="nav-link" href="/contact">{{ __('welcome.nav_support') }}</a></li>
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
                    <a href="{{ route('login') }}" class="btn btn-dark rounded-pill px-4">{{ __('welcome.nav_login') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="policy-hero text-center">
        <div class="container">
            <div class="hero-badge" data-aos="fade-down" style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 16px; border-radius: 100px; display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: var(--brand-primary); margin-bottom: 2rem;">
                <span style="height: 8px; width: 8px; background: var(--brand-primary); border-radius: 50%; display: inline-block;"></span> {{ __('terms.hero_compliance_badge') }}
            </div>
            <h1 class="hero-title" data-aos="fade-up">{{ __('terms.hero_title') }}</h1>
            <p class="lead text-secondary mx-auto mb-5" style="max-width: 600px;" data-aos="fade-up" data-aos-delay="100">
                {{ __('terms.hero_subtitle') }}
            </p>
        </div>
    </header>

    <main class="container">
        <div class="bento-grid">

            <div class="bento-card card-main" data-aos="fade-up">
                <div class="policy-section">
                    <h2>{{ __('terms.agreement_title') }}</h2>
                    <p>{{ __('terms.agreement_text') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('terms.billing_title') }}</h2>
                    <p>{{ __('terms.billing_text') }}</p>
                    <ul>
                        <li>{{ __('terms.billing_list_1') }}</li>
                        <li>{{ __('terms.billing_list_2') }}</li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2>{{ __('terms.responsibilities_title') }}</h2>
                    <p>{{ __('terms.responsibilities_text') }}</p>
                    <h3>{{ __('terms.prohibited_title') }}</h3>
                    <p>{{ __('terms.prohibited_text') }}</p>
                </div>

                <div class="policy-section">
                    <h2>{{ __('terms.termination_title') }}</h2>
                    <p>{{ __('terms.termination_text') }}</p>
                </div>
            </div>

            <div class="card-side d-flex flex-column gap-4" data-aos="fade-up" data-aos-delay="100">

                <div class="bento-card p-4">
                    <div class="icon-circle"><i class="ri-scales-3-line"></i></div>
                    <h5 class="fw-800">{{ __('terms.sidebar_governing_law_title') }}</h5>
                    <p class="text-muted small">{{ __('terms.sidebar_governing_law_text') }}</p>
                </div>

                <div class="bento-card p-4" style="background: var(--brand-dark); color: white;">
                    <div class="icon-circle" style="background: rgba(255,255,255,0.1); color: white;"><i class="ri-history-line"></i></div>
                    <h5 class="fw-800 text-white">{{ __('terms.sidebar_last_revised_title') }}</h5>
                    <p class="text-white-50 small">{{ __('terms.sidebar_last_revised_text') }}</p>
                </div>

                <div class="bento-card p-4 h-100">
                    <div class="icon-circle"><i class="ri-government-line"></i></div>
                    <h5 class="fw-800">{{ __('terms.sidebar_legal_contact_title') }}</h5>
                    <p class="text-muted small mb-3">{{ __('terms.sidebar_legal_contact_text') }}</p>
                    <a href="mailto:legal@stokflowkp.com" class="text-primary fw-bold text-decoration-none">legal@stokflowkp.com</a>
                </div>

            </div>
        </div>
    </main>

    <footer class="py-5 border-top bg-light">
        <div class="container text-center text-md-start">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span class="h5 fw-800">Stokflow<span class="text-primary">KP</span></span>
                    <p class="text-muted small mt-2 mb-0">© 2026 Smartbiz. Enterprise Duka Solutions.</p>
                </div>
                <div class="col-md-6 text-md-end mt-4 mt-md-0">
                    <a href="/privacy" class="nav-link d-inline-block p-0 me-3">{{ __('welcome.footer_privacy') }}</a>
                    <a href="/terms" class="nav-link d-inline-block p-0 me-3 fw-bold text-primary">{{ __('welcome.footer_terms') }}</a>
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
