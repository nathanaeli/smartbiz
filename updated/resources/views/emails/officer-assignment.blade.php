<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You've been assigned to a Duka</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #e8f4fd; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
        .button { display: inline-block; background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to Your New Assignment!</h1>
            <p>You've been assigned to manage a duka</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $officer->name }}</strong>,</p>

            <p>Congratulations! You have been assigned to work at a duka in the stockflowkp system. Here are the details of your assignment:</p>

            <div class="highlight">
                <h3>📍 Assignment Details</h3>
                <p><strong>Duka:</strong> {{ $duka->name }}</p>
                <p><strong>Location:</strong> {{ $duka->location }}</p>
                <p><strong>Your Role:</strong> {{ $assignment->role }}</p>
                <p><strong>Assigned By:</strong> {{ $tenant->name }}</p>
                <p><strong>Assignment Date:</strong> {{ $assignment->created_at->format('F d, Y') }}</p>
            </div>

            <p>As an officer, you now have access to manage this duka's operations including:</p>
            <ul>
                <li>📦 Managing products and inventory</li>
                <li>💰 Handling sales and transactions</li>
                <li>👥 Managing customer relationships</li>
                <li>📊 Viewing reports and analytics</li>
            </ul>

            <p>You can access the stockflowkp system by logging into your account at:</p>
            <p style="text-align: center;">
                <a href="{{ url('/login') }}" class="button">🔐 Login to stockflowkp</a>
            </p>

            <p>If you have any questions about your role or need assistance getting started, please don't hesitate to contact your administrator.</p>

            <p>We're excited to have you on board and look forward to your contributions to the team's success!</p>

            <p>Best regards,<br>
            <strong>The stockflowkp Team</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated message from stockflowkp. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} stockflowkp - Duka Management System</p>
        </div>
    </div>
</body>
</html>
