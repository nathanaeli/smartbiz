<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Modern reset and fonts */
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 40px 20px; background-color: #f9fafb; color: #111827; }
        .card { max-width: 440px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04); overflow: hidden; }

        /* High-end Header */
        .header { background-color: #ffffff; padding: 40px 30px 20px; text-align: center; }
        .logo-text { font-size: 22px; font-weight: 800; letter-spacing: -0.025em; color: #4f46e5; text-transform: uppercase; }
        .logo-text span { color: #111827; }

        .content { padding: 0 40px 40px; }
        .h1 { font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 12px; text-align: center; }
        .p { font-size: 15px; line-height: 1.6; color: #4b5563; text-align: center; margin-bottom: 24px; }

        /* "Smart" Trial Box */
        .trial-badge {
            background: #f5f3ff;
            border: 1px dashed #c4b5fd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .trial-badge h3 { margin: 0; color: #5b21b6; font-size: 18px; font-weight: 700; }
        .trial-badge p { margin: 4px 0 0; color: #7c3aed; font-size: 13px; font-weight: 500; opacity: 0.8; }

        /* The "Stripe-style" Button */
        .btn {
            display: block;
            background: #111827;
            color: #ffffff !important;
            padding: 14px 24px;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.2s;
        }

        .footer { padding: 32px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; margin: 4px 0; }
        .footer a { color: #6b7280; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="logo-text">STOCKFLOW<span>KP</span></div>
        </div>

        <div class="content">
            <div class="h1">Welcome aboard!</div>
            <p class="p">Hello <strong>{{ $user->name }}</strong>, your intelligent Shop management dashboard is ready for action.</p>

            <div class="trial-badge">
                <h3>{{ $trialDays }}-Day Premium Trial</h3>
                <p>Full access until {{ now()->addDays($trialDays)->format('M d, Y') }}</p>
            </div>

            <a href="{{ url('/login') }}" class="btn">Launch Dashboard</a>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} StockflowKP Enterprise</p>
            <p>Questions? <a href="mailto:support@smartbiz.com">Contact Support</a></p>
        </div>
    </div>
</body>
</html>
