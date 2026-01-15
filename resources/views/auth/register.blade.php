<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('register.title') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-dark: #020617;
            --brand-slate: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--brand-slate);
            margin: 0;
            overflow-x: hidden;
        }

        h1, h2, h3, .brand-logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.04em;
        }

        /* Sidebar Styling */
        .register-sidebar {
            background: radial-gradient(circle at top left, #1e1b4b 0%, var(--brand-dark) 100%);
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        .register-sidebar::before {
            content: "";
            position: absolute;
            width: 400px; height: 400px;
            background: var(--brand-primary);
            filter: blur(120px);
            opacity: 0.15;
            top: -100px; left: -100px;
        }

        /* Glassmorphism Card */
        .plan-glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Form Controls */
        .smart-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 18px;
            background-color: #fcfdfe;
            transition: all 0.3s ease;
        }

        .smart-input:focus {
            border-color: var(--brand-primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-smart-action {
            background: var(--brand-dark);
            color: #fff;
            border-radius: 16px;
            padding: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.4s;
            width: 100%;
            border: none;
        }

        .btn-smart-action:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        /* Preloader / Overlay Styles */
        .submission-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.95);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .smart-spinner {
            width: 70px;
            height: 70px;
            border: 4px solid rgba(99, 102, 241, 0.1);
            border-top: 4px solid var(--brand-primary);
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .label-advanced {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 8px;
            display: block;
        }
    </style>
</head>

<body>

    <div id="submissionOverlay" class="submission-overlay d-none">
        <div class="text-center animate__animated animate__fadeIn">
            <div class="position-relative d-inline-block mb-4">
                <div class="smart-spinner"></div>
                <i class="ri-shield-flash-line position-absolute top-50 start-50 translate-middle text-primary fs-3"></i>
            </div>
            <h3 class="text-white fw-800 mb-2" id="statusTitle">{{ __('register.status_init_title') }}</h3>
            <p class="text-white-50 small" id="statusSubtext">{{ __('register.status_init_sub') }}</p>
        </div>
    </div>

    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-5 d-none d-lg-flex register-sidebar text-white p-5 flex-column justify-content-between">
                <div>
                    <div class="brand-logo mb-5">
                        <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center">
                            <span class="text-white fw-800 tracking-tighter">STOKFLOW</span>
                            <span class="text-primary fw-800 tracking-tighter">KP</span>
                        </a>
                    </div>

                    <div class="mt-5">
                        <h1 class="display-5 fw-800 mb-4">{!! __('register.sidebar_title') !!}</h1>
                        <p class="lead opacity-75">{{ __('register.sidebar_subtitle') }}</p>
                    </div>

                    <div class="plan-glass-card mt-5 animate__animated animate__fadeInLeft">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="badge bg-primary px-3 py-2 rounded-pill small fw-bold">{{ __('register.plan_active') }}</span>
                            <div class="text-white-50 small"><i class="ri-lock-fill me-1"></i> {{ __('register.data_encrypted') }}</div>
                        </div>

                        <h3 class="fw-700 mb-1 text-white">{{ $selectedPlan->name }}</h3>
                        <p class="opacity-50 small mb-4">{{ __('register.plan_access') }}</p>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 rounded-4 p-3 border border-white border-opacity-10">
                                    <div class="text-primary small fw-bold mb-1">{{ __('register.label_stores') }}</div>
                                    <div class="fw-bold">{{ $selectedPlan->max_dukas }} {{ __('register.included') }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 rounded-4 p-3 border border-white border-opacity-10">
                                    <div class="text-primary small fw-bold mb-1">{{ __('register.label_status') }}</div>
                                    <div class="fw-bold">{{ __('register.trial_days') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-top border-white border-opacity-10 opacity-50 small">
                    {{ __('register.footer_copyright') }}
                </div>
            </div>

            <div class="col-lg-7 d-flex align-items-center justify-content-center bg-white p-4 p-md-5 position-relative">
                <!-- Language Switcher Top Right -->
                <div class="position-absolute top-0 end-0 m-4">
                    <div class="dropdown">
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
                </div>

                <div style="max-width: 480px; width: 100%;">
                    <div class="mb-5 text-center text-lg-start">
                        <h2 class="fw-800 display-6">{{ __('register.form_title') }}</h2>
                        <p class="text-muted lead">{{ __('register.form_subtitle') }}</p>
                    </div>

                    <form method="POST" action="{{ route('register.post') }}" id="registrationForm">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $selectedPlan->id }}">

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="label-advanced">{{ __('register.label_business_name') }}</label>
                                <input type="text" name="business_name" class="form-control smart-input" placeholder="{{ __('register.placeholder_business_name') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="label-advanced">{{ __('register.label_full_name') }}</label>
                                <input type="text" name="name" class="form-control smart-input" placeholder="{{ __('register.placeholder_full_name') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="label-advanced">{{ __('register.label_email') }}</label>
                                <input type="email" name="email" class="form-control smart-input" placeholder="{{ __('register.placeholder_email') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="label-advanced">{{ __('register.label_password') }}</label>
                                <input type="password" name="password" class="form-control smart-input" required>
                            </div>

                            <div class="col-md-6">
                                <label class="label-advanced">{{ __('register.label_confirm_password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control smart-input" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-smart-action mt-5 mb-4 shadow-sm">
                            <span>{{ __('register.btn_submit') }}</span>
                            <i class="ri-arrow-right-line"></i>
                        </button>

                        <p class="text-center text-muted small">
                            {!! __('register.agreement_text', ['terms' => '<a href="/terms" class="text-dark fw-bold">'.__('register.terms').'</a>', 'privacy' => '<a href="/privacy" class="text-dark fw-bold">'.__('register.privacy').'</a>']) !!}
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>
    <script src="{{ asset('assets/js/hope-ui.min.js') }}"></script>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const overlay = document.getElementById('submissionOverlay');
            const title = document.getElementById('statusTitle');
            const sub = document.getElementById('statusSubtext');

            // Show Overlay
            overlay.classList.remove('d-none');

            // Dynamic Status Messages
            const updates = [
                { t: "{{ __('register.status_1_title') }}", s: "{{ __('register.status_1_sub') }}" },
                { t: "{{ __('register.status_2_title') }}", s: "{{ __('register.status_2_sub') }}" },
                { t: "{{ __('register.status_3_title') }}", s: "{{ __('register.status_3_sub') }}" }
            ];

            let i = 0;
            const cycle = setInterval(() => {
                if (i < updates.length) {
                    title.innerText = updates[i].t;
                    sub.innerText = updates[i].s;
                    i++;
                } else {
                    clearInterval(cycle);
                }
            }, 2000);
        });
    </script>
</body>
</html>
