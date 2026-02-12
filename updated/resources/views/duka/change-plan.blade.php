@extends('layouts.app')

@section('content')

    <div class="container-fluid py-5">
        <div class="row justify-content-center">

            <div class="col-12 col-lg-10">
                <div class="card shadow border-0" style="border-radius: 20px;">

                    <!-- Header -->
                    <div class="card-header text-white py-4"
                        style="background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 20px 20px 0 0;">
                        <h3 class="mb-0">
                            <i class="fas fa-exchange-alt me-2"></i> Change Plan for {{ $duka->name }}
                        </h3>
                        <p class="mb-0 opacity-75 fs-6">Upgrade or downgrade your subscription plan</p>
                    </div>

                    <!-- Body -->
                    <div class="card-body px-4 px-md-5 py-5">

                        {{-- Current Plan Info --}}
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Current Plan
                            </h6>
                            @if($duka->activeSubscription && $duka->activeSubscription->plan)
                                <p class="mb-1">
                                    <strong>{{ $duka->activeSubscription->plan->name }}</strong>
                                    - ${{ $duka->activeSubscription->plan->price }}/month
                                </p>
                                <p class="mb-0">
                                    Expires: {{ $duka->activeSubscription->end_date->format('d M Y') }}
                                </p>
                            @else
                                <p class="mb-0">No active plan</p>
                            @endif
                        </div>

                        {{-- Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Success --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- FORM START -->
                        <form action="{{ route('duka.update.plan', Crypt::encrypt($duka->id)) }}" method="POST">
                            @csrf

                            <!-- PLAN SELECTION -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-crown me-2 text-warning"></i> Choose New Plan
                                    </h5>
                                </div>

                                @foreach($plans as $plan)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 plan-card {{ old('plan_id') == $plan->id ? 'selected' : '' }}"
                                         style="cursor: pointer; transition: all 0.3s ease; border: 2px solid #e5e7eb; position: relative;"
                                         onclick="selectPlan({{ $plan->id }})">
                                        <div class="card-body text-center p-4">
                                            <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                                   id="plan_{{ $plan->id }}" class="d-none"
                                                   {{ old('plan_id') == $plan->id ? 'checked' : '' }} required>

                                            <!-- Plan Icon -->
                                            <div class="plan-icon mb-3">
                                                <i class="fas fa-crown text-warning" style="font-size: 2rem;"></i>
                                            </div>

                                            <h5 class="card-title fw-bold mb-2">{{ $plan->name }}</h5>
                                            <div class="price-section mb-3">
                                                <span class="price-amount text-primary fw-bold" style="font-size: 1.5rem;">${{ $plan->price }}</span>
                                                <span class="price-period text-muted">/month</span>
                                            </div>
                                            <p class="text-muted mb-2" style="font-size: 0.9rem;">{{ $plan->description }}</p>
                                            @if($duka->activeSubscription && $duka->activeSubscription->plan_id == $plan->id)
                                                <span class="badge bg-success">Current Plan</span>
                                            @endif

                                            <!-- Selection Indicator -->
                                            <div class="selection-indicator">
                                                <i class="fas fa-check-circle text-white" style="font-size: 1.2rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- DURATION SELECTION -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="fw-bold mb-3">
                                        <i class="fas fa-calendar me-2 text-info"></i> Subscription Duration
                                    </h5>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="duration" id="duration_1" value="1"
                                                       {{ old('duration', '1') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="duration_1">
                                                    1 Month
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="duration" id="duration_12" value="12"
                                                       {{ old('duration') == '12' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="duration_12">
                                                    12 Months (10% discount)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="duration" id="duration_36" value="36"
                                                       {{ old('duration') == '36' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="duration_36">
                                                    36 Months (20% discount)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- SUBMIT -->
                            <div class="row">
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-lg text-white py-3"
                                        style="background: linear-gradient(135deg, #f59e0b, #d97706); border-radius:12px;">
                                        <i class="fas fa-credit-card me-2"></i> Proceed to Payment
                                    </button>
                                </div>
                            </div>

                        </form>
                        <!-- FORM END -->

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function selectPlan(planId) {
            // Remove selected class from all plans
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked plan
            document.querySelector(`#plan_${planId}`).closest('.plan-card').classList.add('selected');

            // Check the radio button
            document.querySelector(`#plan_${planId}`).checked = true;
        }

        // Initialize selected state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="plan_id"]:checked');
            if (checkedRadio) {
                checkedRadio.closest('.plan-card').classList.add('selected');
            }
        });
    </script>

    <style>
        .plan-card {
            border-radius: 15px !important;
            overflow: hidden;
            position: relative;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .plan-card.selected {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1), 0 10px 30px rgba(99, 102, 241, 0.2) !important;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.02), rgba(139, 92, 246, 0.02));
        }

        .plan-card.selected .plan-icon i {
            color: #6366f1 !important;
        }

        .plan-card.selected .price-amount {
            color: #6366f1 !important;
        }

        .selection-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #6366f1;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease;
        }

        .plan-card.selected .selection-indicator {
            opacity: 1;
            transform: scale(1);
        }

        .plan-icon {
            transition: all 0.3s ease;
        }

        .price-section {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 4px;
        }

        .price-amount {
            font-size: 2rem !important;
            transition: all 0.3s ease;
        }

        .price-period {
            font-size: 1rem;
            opacity: 0.7;
        }
    </style>

@endsection
