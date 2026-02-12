<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - StockflowKP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .logo p {
            color: #666;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .reset-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .reset-btn:hover {
            transform: translateY(-1px);
        }

        .reset-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-message {
            text-align: center;
            padding: 20px;
        }

        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">
            <h1>StockflowKP</h1>
            <p>Reset Your Password</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="reset-form" style="display: {{ session('status') ? 'none' : 'block' }};">
            <form method="POST" action="{{ route('api.password.reset.update') }}" id="passwordResetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ $email }}" readonly style="background-color: #f8f9fa; color: #6c757d;">
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    <div class="password-requirements">
                        Password must be at least 8 characters long
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                </div>

                <button type="submit" class="reset-btn" id="resetBtn">
                    Reset Password
                </button>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Resetting password...</p>
                </div>
            </form>
        </div>

        <div id="success-message" class="success-message" style="display: {{ session('status') ? 'block' : 'none' }};">
            <div class="success-icon">✓</div>
            <h3>Password Reset Successful!</h3>
            <p>Your password has been reset successfully. You can now login with your new password.</p>
            <div style="margin-top: 20px;">
                <a href="{{ route('login') }}" class="reset-btn" style="display: inline-block; text-decoration: none; padding: 12px 24px;">
                    Go to Login
                </a>
            </div>
        </div>

        <div class="back-link">
            <a href="{{ route('login') }}">← Back to Login</a>
        </div>
    </div>

    <script>
        document.getElementById('passwordResetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const resetBtn = document.getElementById('resetBtn');
            const loading = document.getElementById('loading');

            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                e.preventDefault();
                return;
            }

            if (password.length < 8) {
                alert('Password must be at least 8 characters long!');
                e.preventDefault();
                return;
            }

            // Show loading state
            resetBtn.style.display = 'none';
            loading.style.display = 'block';
        });

        // Real-time password confirmation validation
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
