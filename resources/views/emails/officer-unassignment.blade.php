<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You've been unassigned from a Duka</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #ffeaa7; padding: 15px; border-left: 4px solid #d63031; margin: 20px 0; }
        .button { display: inline-block; background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Assignment Update</h1>
            <p>You've been unassigned from a duka</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $officer->name }}</strong>,</p>

            <p>This email is to inform you that you have been unassigned from your role at the following duka:</p>

            <div class="highlight">
                <h3>🏪 Unassignment Details</h3>
                <p><strong>Duka:</strong> {{ $duka->name }}</p>
                <p><strong>Location:</strong> {{ $duka->location }}</p>
                <p><strong>Unassigned By:</strong> {{ $tenant->name }}</p>
                <p><strong>Unassignment Date:</strong> {{ now()->format('F d, Y') }}</p>
            </div>

            <p>As of now, you no longer have access to manage operations for this duka. However, you may still be assigned to other dukas or have access to other parts of the stockflowkp system.</p>

            <p>If you have any questions about this change or need to discuss your current assignments, please contact your administrator.</p>

            <p>You can check your current assignments by logging into the stockflowkp system:</p>
            <p style="text-align: center;">
                <a href="{{ url('/login') }}" class="button">🔐 Login to stockflowkp</a>
            </p>

            <p>Thank you for your service and contributions to this duka. We appreciate your dedication and hard work.</p>

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
