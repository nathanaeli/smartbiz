<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | StockflowKP Enterprise</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}">
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
            background-color: #ffffff;
            color: var(--brand-dark);
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .login-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            height: 100vh;
        }

        /* --- Left Side: High-End Brand Experience --- */
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
            width: 400px; height: 400px;
            background: var(--brand-primary);
            filter: blur(120px);
            opacity: 0.15;
            top: -100px; left: -100px;
        }

        .brand-logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -0.04em;
            position: relative;
            z-index: 2;
        }

        .hero-text {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.04em;
            margin-bottom: 24px;
        }

        /* --- Right Side: Form UI --- */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #f8fafc;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: white;
            padding: 50px;
            border-radius: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
        }

        .form-control {
            border: 1.5px solid #e2e8f0;
            padding: 14px 18px;
            border-radius: 16px;
            font-size: 1rem;
            background-color: #fcfdfe;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-smart-action {
            background: var(--brand-dark);
            color: white;
            padding: 18px;
            border-radius: 16px;
            font-weight: 700;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.4s;
        }

        .btn-smart-action:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        /* --- Smart Preloader Overlay --- */
        .submission-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.95);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: none;
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

        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 991px) {
            .login-grid { grid-template-columns: 1fr; }
            .brand-panel { display: none; }
            .login-card { border: none; box-shadow: none; padding: 20px; }
        }
    </style>
</head>

<body>

    @include('notify::components.notify')

    <div id="smartOverlay" class="submission-overlay">
        <div class="text-center animate__animated animate__fadeIn">
            <div class="position-relative d-inline-block mb-4">
                <div class="smart-spinner"></div>
                <i class="ri-mail-send-line position-absolute top-50 start-50 translate-middle text-primary fs-3"></i>
            </div>
            <h3 class="text-white fw-800 mb-2" id="overlayTitle">Processing Request</h3>
            <p class="text-white-50 small" id="overlaySubtext">Preparing your recovery link...</p>
        </div>
    </div>

    <main class="login-grid">
        <section class="brand-panel">
            <div class="brand-logo">
                <a href="{{ url('/') }}" class="text-decoration-none text-white">
                    STOCKFLOW<span style="color: var(--brand-primary);">KP</span>
                </a>
            </div>

            <div class="content-box animate__animated animate__fadeInLeft">
                <h1 class="hero-text text-white">
                    Don't worry, <br> we've got you <span class="text-primary">covered.</span>
                </h1>
                <p class="lead text-white-50 mb-5" style="max-width: 450px;">
                    Enterprise security means your data is always safe. Enter your email to regain access to your duka terminal.
                </p>
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                        <i class="ri-shield-keyhole-line text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold">Secure Recovery</p>
                        <p class="mb-0 small text-white-50">256-bit Encrypted Process</p>
                    </div>
                </div>
            </div>

            <div class="small text-white-50">
                &copy; {{ date('Y') }} StockflowKP. Built for the future of African Retail.
            </div>
        </section>

        <section class="form-panel">
            <div class="login-card">
                <div class="mb-5">
                    <h2 class="fw-800 tracking-tighter display-6">Forgot Password</h2>
                    <p class="text-secondary">Recover your account access.</p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                    @csrf

                    <div class="mb-4">
                        <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Work Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                            placeholder="name@company.com" required autofocus>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-smart-action" id="resetBtn">
                        <span id="btnText">Send Recovery Link</span>
                        <i class="ri-mail-send-line" id="btnIcon"></i>
                    </button>
                </form>

                <div class="text-center mt-5 pt-4 border-top">
                    <p class="small text-secondary">
                        Remember your password?
                        <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Back to Login</a>
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        const form = document.getElementById('forgotForm');
        const overlay = document.getElementById('smartOverlay');
        const title = document.getElementById('overlayTitle');
        const sub = document.getElementById('overlaySubtext');

        form.addEventListener('submit', function() {
            overlay.style.display = 'flex';

            const updates = [
                { t: "Verifying Email", s: "Checking system records..." },
                { t: "Generating Link", s: "Creating secure recovery token..." },
                { t: "Dispatching", s: "Sending email to your inbox..." }
            ];

            let i = 0;
            setInterval(() => {
                if (i < updates.length) {
                    title.innerText = updates[i].t;
                    sub.innerText = updates[i].s;
                    i++;
                }
            }, 1500);
        });
    </script>
</body>
</html>
