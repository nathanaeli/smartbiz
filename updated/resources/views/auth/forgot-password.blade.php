@extends('layouts.auth')

@section('title', 'Reset Password')

@section('left-panel')
<div class="brand-panel d-flex flex-column justify-content-between h-100">
    <div class="brand-logo">
        <a href="{{ url('/') }}" class="text-decoration-none text-white">
            STOCKFLOW<span style="color: var(--brand-primary);">KP</span>
        </a>
    </div>

    <div class="content-box animate__animated animate__fadeInLeft">
        <h1 class="text-white" style="font-size: 2.2rem; font-weight: 800; line-height: 1.2; letter-spacing: -0.04em; margin-bottom: 24px;">
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
</div>
@endsection

@section('content')
    <div class="mb-5">
        <h2 class="fw-800 tracking-tighter display-6">Forgot Password</h2>
        <p class="text-secondary">Recover your account access.</p>
    </div>

    @if (session('status'))
        <div class="premium-alert premium-alert-success mb-4 animate__animated animate__fadeIn">
            <div class="alert-icon-box success">
                <i class="ri-checkbox-circle-fill fs-4"></i>
            </div>
            <div class="alert-content">
                <h4>Operation Successful</h4>
                <p>{{ session('status') }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
        @csrf

        <div class="mb-4">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Work Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
                placeholder="name@company.com" required autofocus>
            @error('email')
                <div class="text-danger small mt-2 fw-semibold animate__animated animate__headShake" style="font-size: 0.8rem;">
                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn-brand" id="resetBtn">
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
@endsection

@section('scripts')
    <script>
        const form = document.getElementById('forgotForm');
        const overlay = document.getElementById('smartOverlay');
        const title = document.getElementById('overlayTitle');
        const sub = document.getElementById('overlaySubtext');

        if(form && overlay) {
            form.addEventListener('submit', function() {
                overlay.style.display = 'flex';

                // Update icon for email context
                const icon = overlay.querySelector('.ri-shield-user-line');
                if(icon) {
                    icon.className = 'ri-mail-send-line position-absolute top-50 start-50 translate-middle text-primary fs-3';
                }

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
        }
    </script>
@endsection
