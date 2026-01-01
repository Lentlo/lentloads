<?php
/**
 * Simple Deploy Script (with secret token)
 * URL: https://phplaravel-1016958-6108537.cloudwaysapps.com/deploy.php?token=YOUR_SECRET_TOKEN
 */

// Set your secret token here (change this!)
$secretToken = '07d5b8cee9e561ce7305fa2a489d0aaa55b77734';

// Verify token
if (($_GET['token'] ?? '') !== $secretToken) {
    http_response_code(403);
    die('Unauthorized');
}

// Change to project root
chdir(__DIR__ . '/..');

// Log file
$logFile = 'storage/logs/deploy.log';
$output = [];

// Deployment commands
$commands = [
    'git pull origin main',
    'composer dump-autoload --optimize',
    'php artisan config:cache',
    'php artisan route:cache',
];

echo "<pre>\n";
echo "=== Deployment Started: " . date('Y-m-d H:i:s') . " ===\n\n";

foreach ($commands as $cmd) {
    echo "Running: $cmd\n";
    exec($cmd . ' 2>&1', $output, $code);
    echo implode("\n", $output) . "\n";
    echo "Exit code: $code\n\n";
    $output = [];
}

echo "=== Deployment Complete ===\n";
echo "</pre>";
