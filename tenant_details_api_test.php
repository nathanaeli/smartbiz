<?php

/**
 * Simple test script for tenant details API functionality
 * Run this from the Laravel application directory
 */

echo "Testing Tenant Details API Endpoint\n";
echo "====================================\n\n";

// Test data
$baseUrl = 'http://192.168.99.223:8000'; // Your server URL
$authToken = 'YOUR_AUTH_TOKEN_HERE'; // Replace with actual Sanctum token

// Test 1: Get Tenant Details API
echo "1. Testing Tenant Details API...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/tenant/details');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $authToken,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Example usage with cURL commands
echo "2. Example cURL command for testing:\n\n";
echo "curl -X GET '$baseUrl/api/tenant/details' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json'\n\n";

echo "3. How to get authentication token:\n";
echo "First login using the login API to get a token:\n\n";
echo "curl -X POST $baseUrl/api/login \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -d '{\n";
echo "    \"email\": \"tenant@example.com\",\n";
echo "    \"password\": \"password123\"\n";
echo "  }'\n\n";

echo "Test completed!\n";
