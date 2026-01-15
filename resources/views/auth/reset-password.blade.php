<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password – stockflowkp | Intelligent Duka Management System</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=2.0.0') }}">

    <!-- Laravel Notify CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/mckenziearts/laravel-notify/dist/notify.css') }}">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #eef3ff 0%, #f9faff 100%);
            overflow-x: hidden;
        }

        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .left-box {
            background: linear-gradient(135deg, #dce6ff 0%, #e8f0ff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px;
            text-align: center;
        }

        .left-box img {
            max-width: 75%;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        .login-card {
            background: #fff;
            width: 100%;
            max-width: 600px;
            border-radius: 32px;
            padding: 60px 50px;
            min-height: 700px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow:
                0px 10px 25px rgba(0, 0, 0, 0.06),
                0px 20px 60px rgba(0, 0, 0, 0.10),
                0px 0px 0px 1px rgba(255, 255, 255, 0.4) inset;
        }

        .login-button {
            background: linear-gradient(45deg, #4f46e5, #7c3aed);
            color: #fff;
            border-radius: 50px;
            padding: 16px;
            font-weight: 600;
            font-size: 18px;
            transition: .2s ease;
        }

        .login-button:hover {
            opacity: .92;
            transform: translateY(-2px);
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }

        .strength-weak { color: #dc2626; }
        .strength-medium { color: #d97706; }
        .strength-strong { color: #16a34a; }

        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: width 0.3s ease;
        }

        /* ----------------------------
              PRELOADER STYLING
        -----------------------------*/
        #preloader {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(3px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loader {
            border: 6px solid #e5e7eb;
            border-top: 6px solid #4f46e5;
            border-radius: 50%;
            width: 60px; height: 60px;
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media(max-width: 991px){
            .left-box { display: none; }
            .login-card { padding: 40px 30px; margin: 30px auto; border-radius: 20px; }
        }
    </style>
</head>

<body>

@include('notify::components.notify')

<!-- PRELOADER OVERLAY -->
<div id="preloader">
    <div class="loader"></div>
</div>

<section class="container-fluid login-section">
    <div class="row w-100 justify-content-center">

        <div class="col-lg-6 left-box">
            <div>
                <img src="{{ asset('assets/images/dashboard/spot_illo-login.png') }}" alt="stockflowkp Password Reset">
                <h2 class="left-title">Create New Password</h2>
                <p class="left-desc">
                    Enter your new password below. Make sure it's strong and secure.
                </p>
            </div>
        </div>

        <div class="col-lg-6 d-flex justify-content-center align-items-center">
            <div class="login-card">

                <h3 class="fw-bold text-center mb-4" style="color:#4f46e5;">Reset Your Password</h3>

                <p class="text-center mb-4" style="color: #64748b;">
                    Please enter your new password below.
                </p>

                <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email"
                               value="{{ old('email', $email) }}"
                               class="form-control form-control-lg"
                               style="border-radius:14px;"
                               required>
                        @error('email')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control form-control-lg"
                               style="border-radius:14px;"
                               placeholder="Enter new password"
                               required>
                        @error('password')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror

                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <small id="strengthText" class="text-muted">Password strength</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="form-control form-control-lg"
                               style="border-radius:14px;"
                               placeholder="Confirm new password"
                               required>
                        @error('password_confirmation')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn login-button w-100 mt-2" id="resetBtn" type="submit">
                        Reset Password
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p>Remember your password?
                        <a href="{{ route('login') }}" class="fw-bold" style="color:#4f46e5;">
                            Back to Login
                        </a>
                    </p>
                </div>

            </div>
        </div>

    </div>
</section>

<script src="{{ asset('vendor/mckenziearts/laravel-notify/js/notify.js') }}"></script>

<script>
    const form = document.getElementById('resetForm');
    const preloader = document.getElementById('preloader');
    const resetBtn = document.getElementById('resetBtn');
    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let feedback = [];

        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        let width = (strength / 5) * 100;
        strengthFill.style.width = width + '%';

        strengthFill.className = 'strength-fill';

        if (strength <= 2) {
            strengthFill.style.background = '#dc2626';
            strengthText.textContent = 'Weak password';
            strengthText.className = 'strength-weak';
        } else if (strength <= 3) {
            strengthFill.style.background = '#d97706';
            strengthText.textContent = 'Medium strength';
            strengthText.className = 'strength-medium';
        } else {
            strengthFill.style.background = '#16a34a';
            strengthText.textContent = 'Strong password';
            strengthText.className = 'strength-strong';
        }
    });

    form.addEventListener('submit', function () {
        resetBtn.disabled = true;
        preloader.style.display = 'flex';
    });
</script>

</body>
</html>
