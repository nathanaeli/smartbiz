<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('auth.brand_name'))</title>

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Core Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=2.0.0') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/dark.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/dist/flatpickr.min.css') }}" />
    <!-- Laravel Notify CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/mckenziearts/laravel-notify/dist/notify.css') }}">

    <!-- System Layout Styles -->
    <style>
        :root {
            --sb-primary: #4f46e5;
            --sb-secondary: #7c3aed;
            --sb-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --sb-surface: #ffffff;
            --sb-bg: #f3f4f6;
            --sb-text-main: #1f2937;
            --sb-text-muted: #6b7280;
            --sb-border: #e5e7eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--sb-bg);
            overflow-x: hidden;
            color: var(--sb-text-main);
        }

        /* BACKGROUND SECTION */
        .system-section {
            background-image: url('{{ asset('assets/images/dashboard/land.png') }}');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }

        /* GRADIENT OVERLAY */
        .system-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
        }

        /* MAIN CARD */
        .system-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            z-index: 10;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: smoothIn .7s ease-out;
            overflow: hidden;
        }

        @keyframes smoothIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* BRAND */
        .brand-icon {
            font-size: 3.5rem;
            background: var(--sb-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-heading {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--sb-text-main);
            letter-spacing: -0.02em;
        }

        .sub-heading {
            color: var(--sb-text-muted);
            font-weight: 500;
            font-size: 1.1rem;
        }

        /* TITLES */
        .section-heading {
            font-size: 1rem;
            font-weight: 700;
            color: var(--sb-text-main);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-heading::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--sb-border);
        }

        /* MODERN INPUTS */
        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.5rem;
        }

        /* Smart Input Group */
        .smart-input-group {
            position: relative;
            background: #f9fafb;
            border: 1px solid var(--sb-border);
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .smart-input-group:focus-within {
            background: #fff;
            border-color: var(--sb-primary);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.08);
            transform: translateY(-2px);
        }

        .smart-input-group .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.25rem;
            transition: color 0.3s ease;
            pointer-events: none;
            z-index: 5;
        }

        .smart-input-group:focus-within .input-icon {
            color: var(--sb-primary);
        }

        .smart-input-group .form-control,
        .smart-input-group .form-select {
            border: none;
            background: transparent;
            padding: 16px 16px 16px 50px; /* Space for icon */
            height: auto;
            font-size: 1rem;
            color: var(--sb-text-main);
            box-shadow: none !important;
            width: 100%;
            border-radius: 12px;
        }

        .price-display {
            font-weight: 800;
            color: var(--sb-primary);
            font-size: 1.1rem;
        }

        /* BUTTON */
        .btn-create-account {
            background: var(--sb-gradient);
            border-radius: 16px;
            padding: 16px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            transition: .4s;
            box-shadow:
                0 10px 20px -5px rgba(79, 70, 229, 0.4);
        }

        .btn-create-account:hover {
            transform: translateY(-4px);
            box-shadow:
                0 15px 30px -5px rgba(79, 70, 229, 0.5);
        }

        /* BACK BUTTON */
        .back-button {
            background: rgba(255,255,255,0.1) !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.2) !important;
            border-radius: 12px;
            backdrop-filter: blur(4px);
            transition: all 0.3s;
        }

        .back-button:hover {
            background: rgba(255,255,255,0.2) !important;
            transform: translateX(-3px);
        }

        /* FOOTER */
        .card-footer-text {
            font-size: 0.85rem;
            color: var(--sb-text-muted) !important;
        }

        /* DECORATIVE CIRCLES FOR SIDEBAR */
        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .circle-1 { width: 300px; height: 300px; top: -100px; right: -50px; }
        .circle-2 { width: 200px; height: 200px; bottom: 50px; left: -50px; }

        .feature-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        @yield('styles')
    </style>
</head>

<body class="uikit">

<section class="system-section">
    <div class="system-overlay"></div>

    <div class="container" style="position: relative; z-index: 3;">
        @yield('content')
    </div>
</section>

<!-- JS -->
<script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.js') }}"></script>
<!-- Laravel Notify JS -->
<script src="{{ asset('vendor/mckenziearts/laravel-notify/js/notify.js') }}"></script>

@yield('scripts')

</body>
</html>
