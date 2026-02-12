<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Notification - stockflowkp</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info-box { background: white; border: 1px solid #e1e5e9; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Login Notification</h1>
            <p>stockflowkp Security Alert</p>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>

            @if($isNewDevice)
                <div class="alert">
                    <strong>⚠️ New Device Login Detected!</strong><br>
                    We detected a login from a device we haven't seen before. If this wasn't you, please secure your account immediately.
                </div>
            @else
                <p>We wanted to let you know about a recent login to your stockflowkp account.</p>
            @endif

            <div class="info-box">
                <h3>Login Details:</h3>
                <p><strong>Time:</strong> {{ $loginTime->format('l, F j, Y \a\t g:i A T') }}</p>
                <p><strong>IP Address:</strong> {{ $ip }}</p>
                <p><strong>Device:</strong> {{ $device }}</p>
                <p><strong>Location:</strong> {{ $location }}</p>
            </div>

            <p>If you recognize this activity, no further action is needed.</p>

            <p>If you did not initiate this login:</p>
            <ul>
                <li>Change your password immediately</li>
                <li>Review your account activity</li>
                <li>Contact our support team if you need assistance</li>
            </ul>

            <p>For your security, we recommend enabling two-factor authentication on your account.</p>

            <p>Best regards,<br>
            <strong>stockflowkp Security Team</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated security notification from stockflowkp.<br>
            If you have any questions, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} stockflowkp. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
