@extends('layouts.auth')

@section('title', 'Change Password')

@section('left-panel')
<div class="brand-panel d-flex flex-column justify-content-between h-100">
    <div class="brand-logo">
        <a href="{{ url('/') }}" class="text-decoration-none text-white">
            STOCKFLOW<span style="color: var(--brand-primary);">KP</span>
        </a>
    </div>

    <div class="content-box animate__animated animate__fadeInLeft">
        <h1 class="text-white" style="font-size: 2.2rem; font-weight: 800; line-height: 1.2; letter-spacing: -0.04em; margin-bottom: 24px;">
            Enterprise <br> <span class="text-primary">Security.</span>
        </h1>
        <p class="lead text-white-50 mb-5" style="max-width: 450px;">
            Regularly updating your access keys ensures maximum protection for your business data.
        </p>
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                <i class="ri-shield-check-line text-primary fs-4"></i>
            </div>
            <div>
                <p class="mb-0 fw-bold">Proactive Defense</p>
                <p class="mb-0 small text-white-50">Zero-Trust Architecture</p>
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
        <h2 class="fw-800 tracking-tighter display-6">Security Update</h2>
        <p class="text-secondary">Update your duka terminal access credentials.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" id="passwordForm">
        @csrf

        <div class="mb-4">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Current Password</label>
            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••" required>
            @error('current_password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">New Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
            <div class="form-text small text-muted">Use 8+ characters for better security.</div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-5">
            <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-brand">
            <span id="btnText">Update Access Keys</span>
            <i class="ri-arrow-right-line ms-2" id="btnIcon"></i>
        </button>
    </form>

    <div class="text-center mt-5 pt-4 border-top">
        <a href="{{ url()->previous() }}" class="text-muted small text-decoration-none">
            <i class="ri-arrow-left-s-line"></i> Back to Dashboard
        </a>
    </div>
@endsection

@section('scripts')
    <script>
        const form = document.getElementById('passwordForm');
        const overlay = document.getElementById('smartOverlay');
        const title = document.getElementById('overlayTitle');

        if(form && overlay) {
            form.addEventListener('submit', function() {
                overlay.style.display = 'flex';
                
                // Update Overlay Text
                if(title) title.innerText = "Encrypting Security";

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
        }
    </script>
@endsection
