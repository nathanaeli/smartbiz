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
            Secure Your <br> <span class="text-primary">Account.</span>
        </h1>
        <p class="lead text-white-50 mb-5" style="max-width: 450px;">
            Set a new, strong password to protect your enterprise data.
        </p>
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="ri-lock-password-line text-primary fs-4"></i>
            </div>
            <div>
                <p class="mb-0 fw-bold">Encryption Updated</p>
                <p class="mb-0 small text-white-50">Latest Security Standards</p>
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
        <h2 class="fw-800 tracking-tighter display-6">Reset Password</h2>
        <p class="text-secondary">Please enter your new password below.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" id="resetForm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">New Password</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password" required>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            <div class="mt-2" style="font-size: 0.8rem;">
                <div style="height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden;">
                    <div id="strengthFill" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                </div>
                <small id="strengthText" class="text-muted mt-1 d-block">Password strength</small>
            </div>
        </div>

        <div class="mb-4">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" required>
        </div>

        <button type="submit" class="btn-brand" id="resetBtn">
            <span id="btnText">Reset Password</span>
            <i class="ri-save-line" id="btnIcon"></i>
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
        const form = document.getElementById('resetForm');
        const overlay = document.getElementById('smartOverlay');
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        // Password strength checker
        if(passwordInput && strengthFill && strengthText) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                let width = (strength / 5) * 100;
                strengthFill.style.width = width + '%';

                if (strength <= 2) {
                    strengthFill.style.background = '#ef4444'; // Red
                    strengthText.textContent = 'Weak password';
                    strengthText.style.color = '#ef4444';
                } else if (strength <= 3) {
                    strengthFill.style.background = '#f59e0b'; // Amber
                    strengthText.textContent = 'Medium strength';
                    strengthText.style.color = '#f59e0b';
                } else {
                    strengthFill.style.background = '#22c55e'; // Green
                    strengthText.textContent = 'Strong password';
                    strengthText.style.color = '#22c55e';
                }
            });
        }

        if(form && overlay) {
            form.addEventListener('submit', function() {
                overlay.style.display = 'flex';
                
                 // Update visual context
                const title = document.getElementById('overlayTitle');
                const sub = document.getElementById('overlaySubtext');
                
                if(title) title.innerText = "Updating Credentials";
                if(sub) sub.innerText = "Securing your account...";
            });
        }
    </script>
@endsection
