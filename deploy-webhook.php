<?php
/**
 * GitHub Webhook Auto-Deploy Script
 *
 * Place this file in your public_html folder
 * GitHub webhook URL: https://phplaravel-1016958-6108537.cloudwaysapps.com/deploy-webhook.php
 */

// Security: Verify the request is from GitHub
$secret = getenv('DEPLOY_SECRET') ?: 'your-secret-key-here'; // Set this in .env
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Invalid signature');
}

// Only deploy on push to main branch
$data = json_decode($payload, true);
if (($data['ref'] ?? '') !== 'refs/heads/main') {
    die('Not main branch, skipping deploy');
}

// Log deployment
$logFile = __DIR__ . '/../storage/logs/deploy.log';
$log = date('Y-m-d H:i:s') . " - Deployment started\n";

// Change to project directory
chdir(__DIR__ . '/..');

// Run deployment commands
$commands = [
    'git pull origin main 2>&1',
    'composer install --no-dev --optimize-autoloader 2>&1',
    'php artisan config:cache 2>&1',
    'php artisan route:cache 2>&1',
    'php artisan view:cache 2>&1',
];

foreach ($commands as $command) {
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);
    $log .= "Command: $command\n";
    $log .= "Output: " . implode("\n", $output) . "\n";
    $log .= "Return code: $returnCode\n\n";
}

$log .= date('Y-m-d H:i:s') . " - Deployment completed\n";
$log .= "---\n";

file_put_contents($logFile, $log, FILE_APPEND);

echo 'Deployed successfully!';
