<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::active()->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            ],
            'listings' => [
                'total' => Listing::count(),
                'active' => Listing::active()->count(),
                'pending' => Listing::pending()->count(),
                'new_today' => Listing::whereDate('created_at', today())->count(),
            ],
            'reports' => [
                'pending' => Report::pending()->count(),
                'total' => Report::count(),
            ],
            'messages' => [
                'total_conversations' => Conversation::count(),
                'today' => Conversation::whereDate('created_at', today())->count(),
            ],
        ];

        // Recent activities
        $recentUsers = User::latest()->limit(5)->get(['id', 'name', 'email', 'created_at']);
        $recentListings = Listing::with('user:id,name')->latest()->limit(5)->get(['id', 'title', 'user_id', 'status', 'created_at']);
        $pendingReports = Report::with(['reporter:id,name', 'reportable'])->pending()->latest()->limit(5)->get();

        return $this->successResponse([
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'recent_listings' => $recentListings,
            'pending_reports' => $pendingReports,
        ]);
    }

    public function stats(Request $request)
    {
        $period = $request->input('period', '7'); // days

        // Listings chart data
        $listingsChart = Listing::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Users chart data
        $usersChart = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue chart (from transactions)
        $revenueChart = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total')
        )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Category distribution
        $categoryDistribution = Listing::active()
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top cities
        $topCities = Listing::active()
            ->select('city', DB::raw('COUNT(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return $this->successResponse([
            'listings_chart' => $listingsChart,
            'users_chart' => $usersChart,
            'revenue_chart' => $revenueChart,
            'category_distribution' => $categoryDistribution,
            'top_cities' => $topCities,
        ]);
    }
}
