@extends('layouts.auth')

@section('title', __('register.title'))

@section('left-panel')
<div class="brand-panel d-flex flex-column justify-content-between h-100">
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

        <div class="plan-glass-card mt-5 animate__animated animate__fadeInLeft" style="
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        ">
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

    <div class="pt-4 border-top border-white border-opacity-10 opacity-50 small mt-auto">
        {{ __('register.footer_copyright') }}
    </div>
</div>
@endsection

@section('content')
    <!-- Language Switcher Top Right using absolute positioning relative to the card/panel -->
    <div class="position-absolute top-0 end-0 m-4 d-none d-md-block">
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

    <div class="mb-5 text-center text-lg-start">
        <h2 class="fw-800 display-6">{{ __('register.form_title') }}</h2>
        <p class="text-muted lead">{{ __('register.form_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('register.post') }}" id="registrationForm">
        @csrf
        <input type="hidden" name="plan_id" value="{{ $selectedPlan->id }}">

        <div class="row g-4">
            <div class="col-12">
                <label class="small fw-bold text-uppercase text-muted mb-2 d-block">{{ __('register.label_business_name') }}</label>
                <input type="text" name="business_name" class="form-control" placeholder="{{ __('register.placeholder_business_name') }}" required>
            </div>

            <div class="col-12">
                <label class="small fw-bold text-uppercase text-muted mb-2 d-block">{{ __('register.label_full_name') }}</label>
                <input type="text" name="name" class="form-control" placeholder="{{ __('register.placeholder_full_name') }}" required>
            </div>

            <div class="col-12">
                <label class="small fw-bold text-uppercase text-muted mb-2 d-block">{{ __('register.label_email') }}</label>
                <input type="email" name="email" class="form-control" placeholder="{{ __('register.placeholder_email') }}" required>
            </div>

            <div class="col-md-6">
                <label class="small fw-bold text-uppercase text-muted mb-2 d-block">{{ __('register.label_password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="small fw-bold text-uppercase text-muted mb-2 d-block">{{ __('register.label_confirm_password') }}</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn-brand mt-5 mb-4 shadow-sm">
            <span>{{ __('register.btn_submit') }}</span>
            <i class="ri-arrow-right-line"></i>
        </button>

        <p class="text-center text-muted small">
            {!! __('register.agreement_text', ['terms' => '<a href="/terms" class="text-dark fw-bold">'.__('register.terms').'</a>', 'privacy' => '<a href="/privacy" class="text-dark fw-bold">'.__('register.privacy').'</a>']) !!}
        </p>
    </form>
@endsection

@section('scripts')
    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const overlay = document.getElementById('smartOverlay'); // Use layout overlay ID
            const title = document.getElementById('overlayTitle');   // Use layout IDs
            const sub = document.getElementById('overlaySubtext');

            if(overlay) {
                // Show Overlay
                overlay.style.display = 'flex';

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
            }
        });
    </script>
@endsection
