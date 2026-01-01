<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Get categories without eager loading first
        $categories = Category::parents()
            ->active()
            ->ordered()
            ->withCount('activeListings')
            ->get();

        // Calculate total listings count including children
        foreach ($categories as $category) {
            $allChildIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();
            $allIds = array_merge([$category->id], $allChildIds);
            $category->total_active_listings_count = Listing::whereIn('category_id', $allIds)
                ->where('status', 'active')
                ->count();
        }

        // Now load children
        $categories->load(['children' => function ($q) {
            $q->active()->ordered()->withCount('activeListings');
        }]);

        return $this->successResponse($categories);
    }

    public function show($slug)
    {
        $category = Category::with(['children' => function ($q) {
            $q->active()->ordered()->withCount('activeListings');
        }, 'parent'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $breadcrumb = $category->getBreadcrumb();

        return $this->successResponse([
            'category' => $category,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function featured()
    {
        $categories = Category::featured()
            ->active()
            ->ordered()
            ->withCount('activeListings')
            ->limit(8)
            ->get();

        return $this->successResponse($categories);
    }

    public function tree()
    {
        $categories = Category::with('allChildren')
            ->parents()
            ->active()
            ->ordered()
            ->get();

        return $this->successResponse($categories);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $categories = Category::where('name', 'like', "%{$query}%")
            ->active()
            ->ordered()
            ->limit(10)
            ->get();

        return $this->successResponse($categories);
    }

    public function customFields($id)
    {
        $category = Category::findOrFail($id);

        return $this->successResponse([
            'fields' => $category->custom_fields ?? [],
        ]);
    }
}
