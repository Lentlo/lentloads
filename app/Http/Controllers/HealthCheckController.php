<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Review;
use App\Models\Report;

class HealthCheckController extends Controller
{
    public function index()
    {
        $checks = [];
        $allPassed = true;

        // 1. Database Connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'message' => 'Connected to MySQL'
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 2. Users Table
        try {
            $userCount = User::count();
            $checks['users_table'] = [
                'status' => 'ok',
                'message' => "Users table accessible ({$userCount} users)"
            ];
        } catch (\Exception $e) {
            $checks['users_table'] = [
                'status' => 'error',
                'message' => 'Users table error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 3. Listings Table
        try {
            $listingCount = Listing::count();
            $activeCount = Listing::where('status', 'active')->count();
            $pendingCount = Listing::where('status', 'pending')->count();
            $checks['listings_table'] = [
                'status' => 'ok',
                'message' => "Listings: {$listingCount} total, {$activeCount} active, {$pendingCount} pending"
            ];
        } catch (\Exception $e) {
            $checks['listings_table'] = [
                'status' => 'error',
                'message' => 'Listings table error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 4. Categories Table
        try {
            $categoryCount = Category::count();
            $checks['categories_table'] = [
                'status' => 'ok',
                'message' => "Categories table accessible ({$categoryCount} categories)"
            ];
        } catch (\Exception $e) {
            $checks['categories_table'] = [
                'status' => 'error',
                'message' => 'Categories table error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 5. Messages & Conversations
        try {
            $conversationCount = Conversation::count();
            $messageCount = Message::count();
            $checks['messaging'] = [
                'status' => 'ok',
                'message' => "{$conversationCount} conversations, {$messageCount} messages"
            ];
        } catch (\Exception $e) {
            $checks['messaging'] = [
                'status' => 'error',
                'message' => 'Messaging tables error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 6. Reviews Table
        try {
            $reviewCount = Review::count();
            $checks['reviews'] = [
                'status' => 'ok',
                'message' => "{$reviewCount} reviews"
            ];
        } catch (\Exception $e) {
            $checks['reviews'] = [
                'status' => 'error',
                'message' => 'Reviews table error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 7. Reports Table
        try {
            $reportCount = Report::count();
            $pendingReports = Report::where('status', 'pending')->count();
            $checks['reports'] = [
                'status' => 'ok',
                'message' => "{$reportCount} total, {$pendingReports} pending"
            ];
        } catch (\Exception $e) {
            $checks['reports'] = [
                'status' => 'error',
                'message' => 'Reports table error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 8. Storage Directory
        try {
            $storagePath = storage_path('app/public');
            $storageExists = is_dir($storagePath);
            $storageWritable = is_writable($storagePath);

            if ($storageExists && $storageWritable) {
                $checks['storage'] = [
                    'status' => 'ok',
                    'message' => 'Storage directory exists and is writable'
                ];
            } else {
                $checks['storage'] = [
                    'status' => 'error',
                    'message' => 'Storage: exists=' . ($storageExists ? 'yes' : 'no') . ', writable=' . ($storageWritable ? 'yes' : 'no')
                ];
                $allPassed = false;
            }
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 9. Upload Test (actually write a file)
        try {
            $testFile = 'health_check_test_' . time() . '.txt';
            Storage::disk('public')->put($testFile, 'Health check test');
            $exists = Storage::disk('public')->exists($testFile);
            Storage::disk('public')->delete($testFile);

            $checks['upload_test'] = [
                'status' => $exists ? 'ok' : 'error',
                'message' => $exists ? 'File upload/delete working' : 'File upload test failed'
            ];
            if (!$exists) $allPassed = false;
        } catch (\Exception $e) {
            $checks['upload_test'] = [
                'status' => 'error',
                'message' => 'Upload test failed: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 10. Storage Symlink
        try {
            $symlinkPath = public_path('storage');
            $symlinkExists = file_exists($symlinkPath) || is_link($symlinkPath);

            $checks['storage_symlink'] = [
                'status' => $symlinkExists ? 'ok' : 'warning',
                'message' => $symlinkExists ? 'Public storage symlink exists' : 'Public storage symlink missing (run php artisan storage:link)'
            ];
        } catch (\Exception $e) {
            $checks['storage_symlink'] = [
                'status' => 'error',
                'message' => 'Symlink check failed: ' . $e->getMessage()
            ];
        }

        // 11. Disk Space
        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $freeGB = round($freeBytes / 1073741824, 2);
            $totalGB = round($totalBytes / 1073741824, 2);
            $usedPercent = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 1);

            $status = 'ok';
            if ($freeGB < 1) {
                $status = 'error';
                $allPassed = false;
            } elseif ($freeGB < 5) {
                $status = 'warning';
            }

            $checks['disk_space'] = [
                'status' => $status,
                'message' => "{$freeGB}GB free of {$totalGB}GB ({$usedPercent}% used)"
            ];
        } catch (\Exception $e) {
            $checks['disk_space'] = [
                'status' => 'error',
                'message' => 'Disk space check failed: ' . $e->getMessage()
            ];
        }

        // 12. Cache
        try {
            Cache::put('health_check_test', 'ok', 10);
            $cacheValue = Cache::get('health_check_test');
            Cache::forget('health_check_test');

            $checks['cache'] = [
                'status' => $cacheValue === 'ok' ? 'ok' : 'error',
                'message' => $cacheValue === 'ok' ? 'Cache is working (' . config('cache.default') . ')' : 'Cache read/write failed'
            ];
            if ($cacheValue !== 'ok') $allPassed = false;
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 13. Log Writable
        try {
            Log::channel('single')->info('Health check test');
            $logPath = storage_path('logs/laravel.log');
            $logWritable = is_writable($logPath) || is_writable(dirname($logPath));

            $checks['logs'] = [
                'status' => $logWritable ? 'ok' : 'warning',
                'message' => $logWritable ? 'Logs writable' : 'Log file may not be writable'
            ];
        } catch (\Exception $e) {
            $checks['logs'] = [
                'status' => 'warning',
                'message' => 'Log check: ' . $e->getMessage()
            ];
        }

        // 14. Debug Mode (should be OFF in production)
        $debugMode = config('app.debug');
        $isProduction = config('app.env') === 'production';

        if ($isProduction && $debugMode) {
            $checks['debug_mode'] = [
                'status' => 'warning',
                'message' => 'DEBUG MODE IS ON in production (security risk!)'
            ];
        } else {
            $checks['debug_mode'] = [
                'status' => 'ok',
                'message' => $debugMode ? 'Debug ON (dev mode)' : 'Debug OFF (secure)'
            ];
        }

        // 15. Mail Configuration
        try {
            $mailDriver = config('mail.default');
            $mailConfigured = !empty(config('mail.mailers.' . $mailDriver . '.host')) ||
                              in_array($mailDriver, ['log', 'array', 'sendmail']);

            $checks['mail'] = [
                'status' => $mailConfigured ? 'ok' : 'warning',
                'message' => $mailConfigured ? "Mail configured ({$mailDriver})" : "Mail not configured ({$mailDriver})"
            ];
        } catch (\Exception $e) {
            $checks['mail'] = [
                'status' => 'warning',
                'message' => 'Mail config check failed'
            ];
        }

        // 16. Queue Connection
        try {
            $queueDriver = config('queue.default');
            if ($queueDriver === 'sync') {
                $checks['queue'] = [
                    'status' => 'ok',
                    'message' => 'Queue: sync (immediate execution)'
                ];
            } else {
                // Try to check queue connection
                $checks['queue'] = [
                    'status' => 'ok',
                    'message' => "Queue: {$queueDriver}"
                ];
            }
        } catch (\Exception $e) {
            $checks['queue'] = [
                'status' => 'warning',
                'message' => 'Queue check: ' . $e->getMessage()
            ];
        }

        // 17. Session Configuration
        try {
            $sessionDriver = config('session.driver');
            $checks['session'] = [
                'status' => 'ok',
                'message' => "Session: {$sessionDriver}"
            ];
        } catch (\Exception $e) {
            $checks['session'] = [
                'status' => 'warning',
                'message' => 'Session config check failed'
            ];
        }

        // 18. Recent Listings (with images check)
        try {
            $recentListings = Listing::with('images')->latest()->take(5)->get();
            $withImages = $recentListings->filter(fn($l) => $l->images->count() > 0)->count();

            $checks['recent_listings'] = [
                'status' => 'ok',
                'message' => "Last 5 listings: {$withImages} have images"
            ];
        } catch (\Exception $e) {
            $checks['recent_listings'] = [
                'status' => 'error',
                'message' => 'Recent listings check failed: ' . $e->getMessage()
            ];
        }

        // 19. API Health (internal check)
        try {
            $apiUrl = config('app.url') . '/v1/categories';
            $response = Http::timeout(5)->get($apiUrl);

            $checks['api_health'] = [
                'status' => $response->successful() ? 'ok' : 'error',
                'message' => $response->successful() ? 'API responding (categories endpoint)' : 'API returned ' . $response->status()
            ];
            if (!$response->successful()) $allPassed = false;
        } catch (\Exception $e) {
            $checks['api_health'] = [
                'status' => 'warning',
                'message' => 'API check skipped: ' . class_basename($e)
            ];
        }

        // 20. PHP Configuration
        $memoryLimit = ini_get('memory_limit');
        $uploadMax = ini_get('upload_max_filesize');
        $postMax = ini_get('post_max_size');
        $maxExecution = ini_get('max_execution_time');

        $checks['php_config'] = [
            'status' => 'ok',
            'message' => "Memory: {$memoryLimit}, Upload: {$uploadMax}, Post: {$postMax}, Timeout: {$maxExecution}s"
        ];

        // 21. PHP Version
        $phpVersion = phpversion();
        $checks['php_version'] = [
            'status' => version_compare($phpVersion, '8.1', '>=') ? 'ok' : 'warning',
            'message' => 'PHP ' . $phpVersion
        ];

        // 22. Environment Info
        $checks['environment'] = [
            'status' => 'ok',
            'message' => 'App: ' . config('app.name') . ', Env: ' . config('app.env')
        ];

        // 23. Laravel Version
        $checks['laravel_version'] = [
            'status' => 'ok',
            'message' => 'Laravel ' . app()->version()
        ];

        // 24. Timezone
        $checks['timezone'] = [
            'status' => 'ok',
            'message' => 'Timezone: ' . config('app.timezone') . ' (Server: ' . date_default_timezone_get() . ')'
        ];

        // Summary
        $okCount = collect($checks)->where('status', 'ok')->count();
        $warningCount = collect($checks)->where('status', 'warning')->count();
        $errorCount = collect($checks)->where('status', 'error')->count();

        $summary = [
            'overall_status' => $allPassed ? 'healthy' : 'issues_detected',
            'checked_at' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'total_checks' => count($checks),
            'passed' => $okCount,
            'warnings' => $warningCount,
            'errors' => $errorCount,
        ];

        return response()->json([
            'summary' => $summary,
            'checks' => $checks
        ], $allPassed ? 200 : 500);
    }

    // Simple HTML page for browser viewing
    public function page()
    {
        return view('health-check');
    }
}
