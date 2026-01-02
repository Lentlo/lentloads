<?php
/**
 * Simple Deploy Script (with secret token)
 * URL: https://phplaravel-1016958-6108537.cloudwaysapps.com/deploy.php?token=YOUR_SECRET_TOKEN
 */

// Disable output buffering for real-time output
if (ob_get_level()) ob_end_clean();
header('Content-Type: text/plain; charset=utf-8');
header('X-Accel-Buffering: no');

// Set your secret token here (change this!)
$secretToken = '07d5b8cee9e561ce7305fa2a489d0aaa55b77734';

// Verify token
if (($_GET['token'] ?? '') !== $secretToken) {
    http_response_code(403);
    die('Unauthorized');
}

// Change to project root
chdir(__DIR__ . '/..');

echo "=== Deployment Started: " . date('Y-m-d H:i:s') . " ===\n\n";
flush();

// Deployment commands
$commands = [
    'git fetch origin main' => 10,
    'git reset --hard origin/main' => 30,
    'composer dump-autoload --optimize --no-interaction' => 60,
    'php artisan config:cache' => 10,
    'php artisan route:cache' => 10,
];

foreach ($commands as $cmd => $timeout) {
    echo "Running: $cmd\n";
    flush();

    $output = [];
    $code = 0;
    exec($cmd . ' 2>&1', $output, $code);

    if (!empty($output)) {
        echo implode("\n", $output) . "\n";
    }
    echo "Exit code: $code\n\n";
    flush();

    if ($code !== 0) {
        echo "ERROR: Command failed!\n";
        break;
    }
}

echo "=== Deployment Complete: " . date('Y-m-d H:i:s') . " ===\n";
