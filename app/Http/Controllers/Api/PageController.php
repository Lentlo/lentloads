<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function home()
    {
        // Featured categories - get without children first to avoid query interference
        $categories = Category::parents()
            ->active()
            ->featured()
            ->ordered()
            ->limit(8)
            ->get();

        // Calculate total listings count including ALL children
        foreach ($categories as $category) {
            $allChildIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();
            $allIds = array_merge([$category->id], $allChildIds);
            $category->total_active_listings_count = Listing::whereIn('category_id', $allIds)
                ->where('status', 'active')
                ->count();
        }

        // Now load children for display
        $categories->load(['children' => fn($q) => $q->active()->limit(5)]);

        // Featured listings
        $featuredListings = Listing::with(['primaryImage', 'category:id,name', 'user:id,name,city'])
            ->active()
            ->featured()
            ->limit(8)
            ->get();

        // Recent listings
        $recentListings = Listing::with(['primaryImage', 'category:id,name', 'user:id,name,city'])
            ->active()
            ->latest('published_at')
            ->limit(12)
            ->get();

        // Banners
        $banners = Banner::active()
            ->position('home_top')
            ->ordered()
            ->get();

        // Stats
        $stats = [
            'total_listings' => Listing::active()->count(),
            'total_users' => \App\Models\User::active()->count(),
            'total_categories' => Category::active()->count(),
        ];

        return $this->successResponse([
            'categories' => $categories,
            'featured_listings' => $featuredListings,
            'recent_listings' => $recentListings,
            'banners' => $banners,
            'stats' => $stats,
        ]);
    }

    public function page($slug)
    {
        $page = Page::where('slug', $slug)->active()->firstOrFail();

        return $this->successResponse($page);
    }

    public function pages()
    {
        $pages = Page::active()->ordered()->get(['id', 'title', 'slug']);

        return $this->successResponse($pages);
    }

    public function settings()
    {
        $settings = Setting::where('group', 'public')->pluck('value', 'key');

        return $this->successResponse($settings);
    }

    public function banners($position)
    {
        $banners = Banner::active()
            ->position($position)
            ->ordered()
            ->get();

        // Track impressions
        Banner::whereIn('id', $banners->pluck('id'))->increment('impressions');

        return $this->successResponse($banners);
    }

    public function trackBannerClick($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->incrementClicks();

        return $this->successResponse(['redirect' => $banner->link]);
    }

    public function contact()
    {
        return $this->successResponse([
            'email' => Setting::get('contact_email', 'support@lentloads.com'),
            'phone' => Setting::get('contact_phone'),
            'address' => Setting::get('contact_address'),
            'social' => [
                'facebook' => Setting::get('facebook_url'),
                'twitter' => Setting::get('twitter_url'),
                'instagram' => Setting::get('instagram_url'),
            ],
        ]);
    }
}
