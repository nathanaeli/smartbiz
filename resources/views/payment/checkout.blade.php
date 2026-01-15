<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Payment - stockflowkp</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            margin: 0 10px;
        }
        .step.active {
            background: #28a745;
            color: white;
        }
        .btn-success {
            font-size: 1.2rem;
            padding: 12px;
            border-radius: 10px;
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            transition: transform 0.2s;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        h2 {
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .info-card {
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #28a745;
            background: #f8fff9;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="container py-5">

    <h2 class="mb-4 text-center">Complete Your Payment</h2>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active">1</div>
        <div class="step">2</div>
        <div class="step">3</div>
    </div>

    <div class="card">
        <div class="card-body p-4">

            <!-- Tenant Information -->
            <div class="info-card">
                <h5 class="mb-3"><i class="bi bi-person-circle"></i> Account Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-building"></i> Business:</strong> {{ $tenant->name }}</p>
                        <p><strong><i class="bi bi-envelope"></i> Email:</strong> {{ $tenant->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-shop"></i> Duka:</strong> {{ $duka->name }}</p>
                        <p><strong><i class="bi bi-geo-alt"></i> Location:</strong> {{ $duka->location }}</p>
                    </div>
                </div>
            </div>

            <!-- Subscription Summary -->
            <div class="info-card">
                <h5 class="mb-3"><i class="bi bi-receipt"></i> Subscription Summary</h5>
                <div class="summary-item">
                    <span><strong>Plan:</strong></span>
                    <span>{{ $subscription->plan_name }}</span>
                </div>
                <div class="summary-item">
                    <span><strong>Duration:</strong></span>
                    <span>{{ $subscription->end_date->diffInMonths($subscription->start_date) }} months</span>
                </div>
                <div class="summary-item">
                    <span><strong>Start Date:</strong></span>
                    <span>{{ $subscription->start_date->format('M d, Y') }}</span>
                </div>
                <div class="summary-item">
                    <span><strong>End Date:</strong></span>
                    <span>{{ $subscription->end_date->format('M d, Y') }}</span>
                </div>
                <div class="summary-item">
                    <span><strong>Status:</strong></span>
                    <span><span class="badge bg-warning">{{ ucfirst($subscription->status) }}</span></span>
                </div>
                <hr>
                <div class="summary-item total">
                    <span><strong>Total Amount:</strong></span>
                    <span>{{ number_format($subscription->amount) }} TZS</span>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="info-card">
                <h5 class="mb-3"><i class="bi bi-credit-card"></i> Choose Payment Method</h5>
                <form id="paymentForm" method="POST" action="{{ route('payment.process') }}">
                    @csrf
                    <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">

                    <div class="payment-method" data-method="card">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" checked>
                            <label class="form-check-label" for="card">
                                <strong><i class="bi bi-credit-card"></i> Credit/Debit Card</strong>
                                <br><small class="text-muted">Visa, Mastercard, American Express</small>
                            </label>
                        </div>
                    </div>

                    <div class="payment-method" data-method="mpesa">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa">
                            <label class="form-check-label" for="mpesa">
                                <strong><i class="bi bi-phone"></i> M-Pesa</strong>
                                <br><small class="text-muted">Mobile money payment</small>
                            </label>
                        </div>
                    </div>

                    <div class="payment-method" data-method="airtel">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="airtel" value="airtel">
                            <label class="form-check-label" for="airtel">
                                <strong><i class="bi bi-phone"></i> Airtel Money</strong>
                                <br><small class="text-muted">Mobile money payment</small>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-3" id="payBtn">
                        <i class="bi bi-lock"></i> Pay Now Securely
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('tenant.dashboard') }}" class="text-white"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Form submission with loading state
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('payBtn');
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
        btn.disabled = true;
    });
</script>

</body>
</html>
