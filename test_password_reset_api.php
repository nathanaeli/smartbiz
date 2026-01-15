<?php

/**
 * Simple test script for password reset API functionality
 * Run this from the Laravel application directory
 */

echo "Testing Password Reset API Endpoints\n";
echo "=====================================\n\n";

// Test data
$testEmail = 'test@example.com'; // Change this to a valid email in your database
$baseUrl = 'http://localhost:8000'; // Change this to your local server URL

// Test 1: Forgot Password API
echo "1. Testing Forgot Password API...\n";
$forgotPasswordData = [
    'email' => $testEmail
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/forgot-password');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($forgotPasswordData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test 2: Reset Password API (this would require a valid token from email)
echo "2. Testing Reset Password API...\n";
echo "Note: This test requires a valid token from the email sent in test 1\n";

// Example usage with cURL commands
echo "\n3. Example cURL commands for testing:\n\n";
echo "Forgot Password:\n";
echo "curl -X POST $baseUrl/api/forgot-password \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -d '{\"email\": \"$testEmail\"}'\n\n";

echo "Reset Password:\n";
echo "curl -X POST $baseUrl/api/reset-password \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -d '{\n";
echo "    \"email\": \"$testEmail\",\n";
echo "    \"password\": \"newpassword123\",\n";
echo "    \"password_confirmation\": \"newpassword123\",\n";
echo "    \"token\": \"YOUR_RESET_TOKEN_FROM_EMAIL\"\n";
echo "  }'\n";

echo "\n4. Email Configuration Check:\n";
echo "Make sure your .env file has proper email configuration:\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=your_smtp_host\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=your_username\n";
echo "MAIL_PASSWORD=your_password\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "MAIL_FROM_ADDRESS=noreply@yourdomain.com\n";
echo "MAIL_FROM_NAME=\"Your App Name\"\n";

echo "\n5. Frontend URL Configuration:\n";
echo "Add to your .env file:\n";
echo "FRONTEND_URL=https://your-frontend-domain.com\n";

echo "\nTest completed!\n";
