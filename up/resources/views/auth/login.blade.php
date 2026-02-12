@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="mb-5">
        <h2 class="fw-800 tracking-tighter display-6">Welcome back</h2>
        <p class="text-secondary">Access your enterprise dashboard.</p>
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

    @if (session('error'))
        <div class="premium-alert mb-4 animate__animated animate__shakeX">
            <div class="alert-icon-box error">
                <i class="ri-error-warning-fill fs-4"></i>
            </div>
            <div class="alert-content">
                <h4>Authentication Failed</h4>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
        @csrf

        <div class="mb-4">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
                placeholder="name@company.com" required autofocus>
            @error('email')
                <div class="text-danger small mt-2 fw-semibold animate__animated animate__headShake" style="font-size: 0.8rem;">
                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="small fw-bold text-uppercase text-muted mb-0">Password</label>
                <a href="{{ route('password.request') }}" class="small text-decoration-none fw-bold text-primary">
                    Forgot?
                </a>
            </div>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
            @error('password')
                <div class="text-danger small mt-2 fw-semibold animate__animated animate__headShake" style="font-size: 0.8rem;">
                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4 mt-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="remember">
                <label class="form-check-label small text-secondary" for="remember">Keep me signed in</label>
            </div>
        </div>

        <button type="submit" class="btn-brand" id="loginBtn">
            <span id="btnText">Sign in to Terminal</span>
            <i class="ri-arrow-right-line" id="btnIcon"></i>
        </button>
    </form>

    <div class="text-center mt-5 pt-4 border-top">
        <p class="small text-secondary">
            New here?
            <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Create an account</a>
        </p>
    </div>
@endsection

@section('scripts')
    <script>
        const form = document.getElementById('loginForm');
        const overlay = document.getElementById('smartOverlay');
        const title = document.getElementById('overlayTitle');
        const sub = document.getElementById('overlaySubtext');

        if(form) {
            form.addEventListener('submit', function() {
                // Show the Smart Preloader
                if(overlay) overlay.style.display = 'flex';

                // Cycle through professional status messages
                const updates = [
                    { t: "Authenticating", s: "Verifying your credentials..." },
                    { t: "Syncing Data", s: "Connecting to your branch servers..." },
                    { t: "Success", s: "Loading your environment..." }
                ];

                let i = 0;
                setInterval(() => {
                    if (i < updates.length) {
                        if(title) title.innerText = updates[i].t;
                        if(sub) sub.innerText = updates[i].s;
                        i++;
                    }
                }, 1500);
            });
        }
    </script>
@endsection
