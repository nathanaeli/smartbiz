<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Debug Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Registration Debug Test</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Backend Status:</strong> Registration logic is working correctly (tested via tinker).
                        </div>

                        <div class="alert alert-warning">
                            <strong>Possible Issues:</strong>
                            <ul class="mb-0">
                                <li>CSRF token problems</li>
                                <li>JavaScript preventing form submission</li>
                                <li>Validation errors not displaying</li>
                                <li>Network/connectivity issues</li>
                            </ul>
                        </div>

                        <h5>Debug Steps:</h5>
                        <ol>
                            <li>Open browser developer tools (F12)</li>
                            <li>Go to Console tab</li>
                            <li>Try registering on the main registration page</li>
                            <li>Check console for JavaScript errors</li>
                            <li>Check Network tab for failed requests</li>
                            <li>Check Laravel logs: <code>storage/logs/laravel.log</code></li>
                        </ol>

                        <div class="mt-4">
                            <a href="{{ route('register') }}" class="btn btn-primary">Go to Registration Page</a>
                            <a href="{{ route('login') }}" class="btn btn-secondary ms-2">Go to Login Page</a>
                        </div>

                        <div class="mt-4">
                            <h6>Recent Log Entries:</h6>
                            <pre class="bg-light p-3 small" style="max-height: 300px; overflow-y: auto;">
<?php
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20); // Last 20 lines
    foreach ($lines as $line) {
        echo htmlspecialchars($line);
    }
} else {
    echo "Log file not found.";
}
?>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
