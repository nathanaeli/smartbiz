<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Security Settings | StokflowKP</title>

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
            background-color: #f8fafc; /* Professional light gray */
            color: var(--brand-slate);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* Smart Bento Card */
        .security-card {
            background: #ffffff;
            width: 100%;
            max-width: 500px;
            border-radius: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-smart {
            background: #fcfdfe;
            padding: 40px 40px 20px;
            text-align: center;
        }

        .icon-box {
            width: 64px;
            height: 64px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--brand-primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.75rem;
        }

        .card-body-smart {
            padding: 20px 40px 40px;
        }

        /* Advanced Form Elements */
        .label-advanced {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 8px;
            display: block;
        }

        .smart-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fcfdfe;
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
            border: none;
            border-radius: 16px;
            padding: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.4s;
            width: 100%;
        }

        .btn-smart-action:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        /* Smart Preloader Overlay */
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

        .brand-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.04em;
        }
    </style>
</head>

<body>

    <div id="smartOverlay" class="submission-overlay">
        <div class="text-center animate__animated animate__fadeIn">
            <div class="position-relative d-inline-block mb-4">
                <div class="smart-spinner"></div>
                <i class="ri-shield-keyhole-line position-absolute top-50 start-50 translate-middle text-primary fs-3"></i>
            </div>
            <h3 class="text-white fw-800 mb-2" id="overlayTitle">Encrypting Security</h3>
            <p class="text-white-50 small">Updating your enterprise credentials...</p>
        </div>
    </div>

    <div class="container py-5">
        <div class="security-card mx-auto">
            <div class="card-header-smart">
                <div class="brand-text mb-4">
                    STOKFLOW<span class="text-primary">KP</span>
                </div>
                <div class="icon-box">
                    <i class="ri-lock-password-line"></i>
                </div>
                <h3 class="fw-800 text-dark mb-1">Security Update</h3>
                <p class="text-muted small">Update your duka terminal access credentials.</p>
            </div>

            <div class="card-body-smart">
                <form method="POST" action="{{ route('password.update') }}" id="passwordForm">
                    @csrf

                    <div class="mb-4">
                        <label class="label-advanced">Current Password</label>
                        <input type="password" name="current_password"
                               class="form-control smart-input @error('current_password') is-invalid @enderror"
                               placeholder="••••••••" required>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4 opacity-25">

                    <div class="mb-4">
                        <label class="label-advanced">New Password</label>
                        <input type="password" name="password"
                               class="form-control smart-input @error('password') is-invalid @enderror"
                               placeholder="••••••••" required>
                        <div class="form-text small text-muted">Use 8+ characters for better security.</div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label class="label-advanced">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="form-control smart-input"
                               placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-smart-action">
                        Update Access Keys <i class="ri-arrow-right-line ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ url()->previous() }}" class="text-muted small text-decoration-none">
                <i class="ri-arrow-left-s-line"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>
    <script>
        document.getElementById('passwordForm').addEventListener('submit', function() {
            const overlay = document.getElementById('smartOverlay');
            const title = document.getElementById('overlayTitle');

            overlay.style.display = 'flex';

            // Cycle through professional status updates
            const updates = [
                "Verifying Old Keys",
                "Hashing New Protocol",
                "Securing Environment"
            ];

            let i = 0;
            setInterval(() => {
                if (i < updates.length) {
                    title.innerText = updates[i];
                    i++;
                }
            }, 1500);
        });
    </script>
</body>
</html>
