<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loan Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .loan-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .urgent { background-color: #fff3cd; border-color: #ffc107; }
        .warning { background-color: #f8d7da; border-color: #dc3545; }
        .success { background-color: #d1ecf1; border-color: #17a2b8; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $loanData['duka_name'] }}</h1>
            <h2>
                @if($messageType == 'overdue_warning')
                    ⚠️ Loan Payment Overdue Warning
                @elseif($messageType == 'final_notice')
                    🚨 FINAL NOTICE: Immediate Payment Required
                @elseif($messageType == 'payment_confirmation')
                    ✅ Payment Confirmation
                @else
                    💰 Loan Payment Reminder
                @endif
            </h2>
        </div>

        <div class="content">
            <p>Dear {{ $loanData['customer_name'] }},</p>

            @if($messageType == 'overdue_warning')
                <p><strong>This is an urgent notification that your loan payment is overdue.</strong></p>
                <p>We hope this message finds you well. We're writing to remind you about your outstanding loan balance that requires immediate attention.</p>
            @elseif($messageType == 'final_notice')
                <p><strong>FINAL NOTICE: Your account requires immediate payment to avoid further action.</strong></p>
                <p>This is your final reminder regarding the outstanding balance on your loan. Immediate payment is required to avoid additional fees or collection actions.</p>
            @elseif($messageType == 'payment_confirmation')
                <p><strong>Thank you for your recent payment!</strong></p>
                <p>We have successfully received your payment. Here are the updated details of your loan account.</p>
            @else
                <p>We hope this message finds you well. We're writing to provide you with an update on your loan account status.</p>
            @endif

            <div class="loan-details @if($messageType == 'overdue_warning') urgent @elseif($messageType == 'final_notice') warning @elseif($messageType == 'payment_confirmation') success @endif">
                <h3>Loan Details</h3>
                <table>
                    <tr><th>Loan ID:</th><td>{{ $loanData['loan_id'] }}</td></tr>
                    <tr><th>Loan Date:</th><td>{{ $loanData['loan_date'] }}</td></tr>
                    <tr><th>Due Date:</th><td>{{ $loanData['due_date'] }}</td></tr>
                    <tr><th>Days Overdue:</th><td>{{ $loanData['days_overdue'] }}</td></tr>
                    <tr><th>Original Amount:</th><td>{{ number_format($loanData['original_amount'], 2) }} TZS</td></tr>
                    <tr><th>Amount Paid:</th><td>{{ number_format($loanData['amount_paid'], 2) }} TZS</td></tr>
                    <tr><th>Outstanding Balance:</th><td><strong>{{ number_format($loanData['outstanding_balance'], 2) }} TZS</strong></td></tr>
                    <tr><th>Status:</th><td><span style="color: {{ $loanData['aging_category'] == 'Current' ? 'green' : ($loanData['aging_category'] == 'Overdue 1' ? 'orange' : 'red') }};">{{ $loanData['aging_category'] }}</span></td></tr>
                </table>
            </div>

            @if(count($loanData['products']) > 0)
            <div class="loan-details">
                <h4>Products Purchased</h4>
                <table>
                    <thead>
                        <tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($loanData['products'] as $product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $product['quantity'] }}</td>
                            <td>{{ number_format($product['unit_price'], 2) }} TZS</td>
                            <td>{{ number_format($product['total'], 2) }} TZS</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(count($loanData['payments']) > 0)
            <div class="loan-details">
                <h4>Recent Payments</h4>
                <table>
                    <thead>
                        <tr><th>Date</th><th>Amount</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($loanData['payments'], -5) as $payment) <!-- Show last 5 payments -->
                        <tr>
                            <td>{{ $payment['date'] }}</td>
                            <td>{{ number_format($payment['amount'], 2) }} TZS</td>
                            <td>{{ $payment['notes'] ?: 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($messageType != 'payment_confirmation')
                <div style="text-align: center; margin: 20px 0;">
                    <a href="#" class="button">Make Payment Now</a>
                </div>

                <p><strong>Payment Instructions:</strong></p>
                <ul>
                    <li>You can make payments at {{ $loanData['duka_name'] }} during business hours</li>
                    <li>Contact us at {{ $loanData['customer_phone'] ?: 'our phone number' }} for assistance</li>
                    <li>Partial payments are accepted</li>
                </ul>

                @if($messageType == 'final_notice')
                    <p style="color: #dc3545;"><strong>Please note: Failure to make payment within 7 days may result in additional fees or legal action.</strong></p>
                @endif
            @endif

            <p>If you have already made a payment or believe this message is in error, please contact us immediately.</p>

            <p>Thank you for your business and prompt attention to this matter.</p>

            <p>Best regards,<br>
            <strong>{{ $loanData['duka_name'] }} Management Team</strong><br>
            {{ $loanData['customer_phone'] ?: 'Contact us for support' }}</p>
        </div>

        <div class="footer">
            <p>This is an automated message from {{ $loanData['duka_name'] }}. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ $loanData['duka_name'] }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
