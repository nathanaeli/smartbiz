<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'StockflowKP') }} | @yield('title', 'Auth')</title>

    <!-- Professional Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-dark: #0f172a;
            --brand-gray: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: var(--brand-dark);
            height: 100vh;
            margin: 0;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            min-height: 100vh;
        }

        /* --- Left Side: Brand Panel --- */
        .brand-panel {
            background: radial-gradient(circle at top left, #1e1b4b 0%, var(--brand-dark) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 80px;
            color: white;
            overflow: hidden;
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            width: 600px; height: 600px;
            background: var(--brand-primary);
            filter: blur(150px);
            opacity: 0.15;
            top: -200px; left: -200px;
            border-radius: 50%;
        }

        .brand-logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -0.04em;
            position: relative;
            z-index: 2;
        }

        .testimonial-text {
            font-size: 2.25rem;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 24px;
            letter-spacing: -0.03em;
            color: rgba(255,255,255,0.95);
        }

        /* --- Right Side: Form Panel --- */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--bg-light);
            position: relative;
            overflow-y: auto;
        }

        .auth-card {
            width: 100%;
            max-width: 480px;
            background: white;
            padding: 48px;
            border-radius: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Input Styling --- */
        .form-control {
            border: 1.5px solid #e2e8f0;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.95rem;
            background-color: #fcfdfe;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-brand {
            background: var(--brand-dark);
            color: white;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-brand:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        /* --- Mobile Responsive --- */
        @media (max-width: 991px) {
            .auth-grid {
                grid-template-columns: 1fr;
                min-height: 100vh;
                display: block; /* Stack naturally */
            }

            .brand-panel {
                display: none; /* Hide brand panel on mobile for cleaner login */
            }
            
            /* Optional: Show a condensed header on mobile if simpler isn't preferred
               For now, we'll keep it simple as requested 'responsive' often implies removing clutter.
            */

             .form-panel {
                min-height: 100vh;
                padding: 20px;
                background: #ffffff; /* White background on mobile */
            }

            .auth-card {
                box-shadow: none;
                border: none;
                padding: 10px;
                max-width: 100%;
            }
        }

        /* --- Smart Preloader Overlay --- */
        .submission-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.95);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: none; /* Hidden by default */
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

        /* --- Advanced Alert Styling --- */
        .premium-alert {
            border-radius: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            background: rgba(254, 242, 242, 0.8);
            backdrop-filter: blur(8px);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .premium-alert-success {
            border-color: rgba(34, 197, 94, 0.2);
            background: rgba(240, 253, 244, 0.8);
        }

        .alert-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .alert-icon-box.error { background: #fee2e2; color: #ef4444; }
        .alert-icon-box.success { background: #dcfce7; color: #22c55e; }

        .alert-content h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
        }

        .alert-content p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    @include('notify::components.notify')

    <div id="smartOverlay" class="submission-overlay">
        <div class="text-center animate__animated animate__fadeIn">
            <div class="position-relative d-inline-block mb-4">
                <div class="smart-spinner"></div>
                <i class="ri-shield-user-line position-absolute top-50 start-50 translate-middle text-primary fs-3"></i>
            </div>
            <h3 class="text-white fw-800 mb-2" id="overlayTitle">Authenticating</h3>
            <p class="text-white-50 small" id="overlaySubtext">Securing your session...</p>
        </div>
    </div>

    <div class="auth-grid">
        <!-- Brand Panel (Left) -->
        <div class="brand-panel">
            @section('left-panel')
            <div class="brand-logo">
                <a href="{{ url('/') }}" class="text-decoration-none text-white">
                    STOCKFLOW<span style="color: var(--brand-primary);">KP</span>
                </a>
            </div>

            <div class="testimonial-box animate__animated animate__fadeInLeft">
                <div class="mb-4">
                    <i class="ri-double-quotes-l h1 text-primary"></i>
                </div>
                <h2 class="testimonial-text">
                    "The intelligence platform that actually serves your growth."
                </h2>
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                        <i class="ri-shield-star-line text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold">Enterprise Edition</p>
                        <p class="mb-0 small text-white-50">v2.4.0 (Stable)</p>
                    </div>
                </div>
            </div>

            <div class="small text-white-50">
                &copy; {{ date('Y') }} StockflowKP. All rights reserved.
            </div>
            @show
        </div>

        <!-- Form Panel (Right) -->
        <div class="form-panel">
            <div class="auth-card">
                <!-- Mobile specific logo could go here if brand panel is hidden -->
                <div class="d-lg-none text-center mb-5">
                     <a href="{{ url('/') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
                         <span class="h4 fw-800 mb-0 tracking-tighter text-dark">STOCKFLOW<span class="text-primary">KP</span></span>
                     </a>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>
    <script src="{{ asset('assets/js/hope-ui.min.js') }}"></script>
    
    @yield('scripts')
</body>
</html>
