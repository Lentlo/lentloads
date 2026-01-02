<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Listing;
use App\Models\Category;

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
            $checks['listings_table'] = [
                'status' => 'ok',
                'message' => "Listings table accessible ({$listingCount} total, {$activeCount} active)"
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

        // 5. Storage (Public Disk)
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
                    'status' => 'warning',
                    'message' => 'Storage: exists=' . ($storageExists ? 'yes' : 'no') . ', writable=' . ($storageWritable ? 'yes' : 'no')
                ];
            }
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 6. Storage Symlink
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

        // 7. Cache
        try {
            Cache::put('health_check_test', 'ok', 10);
            $cacheValue = Cache::get('health_check_test');
            Cache::forget('health_check_test');

            $checks['cache'] = [
                'status' => $cacheValue === 'ok' ? 'ok' : 'error',
                'message' => $cacheValue === 'ok' ? 'Cache is working' : 'Cache read/write failed'
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage()
            ];
            $allPassed = false;
        }

        // 8. Recent Listings (with images check)
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

        // 9. Environment
        $checks['environment'] = [
            'status' => 'ok',
            'message' => 'App: ' . config('app.name') . ', Env: ' . config('app.env')
        ];

        // 10. PHP Version
        $checks['php_version'] = [
            'status' => 'ok',
            'message' => 'PHP ' . phpversion()
        ];

        // Summary
        $summary = [
            'overall_status' => $allPassed ? 'healthy' : 'issues_detected',
            'checked_at' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
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
