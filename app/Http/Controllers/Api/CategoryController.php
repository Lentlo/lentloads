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
        // Get categories
        $categories = Category::parents()
            ->active()
            ->ordered()
            ->withCount('activeListings')
            ->get();

        // Load children first
        $categories->load(['children' => function ($q) {
            $q->active()->ordered()->withCount('activeListings');
        }]);

        // Optimized: Get all listing counts in 2 queries instead of N+1
        $parentIds = $categories->pluck('id')->toArray();

        // Get ALL child category IDs grouped by parent (one query)
        $childrenByParent = Category::whereIn('parent_id', $parentIds)
            ->get(['id', 'parent_id'])
            ->groupBy('parent_id');

        // Get all category IDs (parents + children)
        $allCategoryIds = $parentIds;
        foreach ($childrenByParent as $children) {
            $allCategoryIds = array_merge($allCategoryIds, $children->pluck('id')->toArray());
        }

        // Get listing counts grouped by category_id (one query)
        $listingCounts = Listing::whereIn('category_id', $allCategoryIds)
            ->where('status', 'active')
            ->selectRaw('category_id, COUNT(*) as count')
            ->groupBy('category_id')
            ->pluck('count', 'category_id');

        // Calculate totals in PHP (no more queries)
        foreach ($categories as $category) {
            $childIds = isset($childrenByParent[$category->id])
                ? $childrenByParent[$category->id]->pluck('id')->toArray()
                : [];
            $allIds = array_merge([$category->id], $childIds);
            $category->total_active_listings_count = collect($allIds)
                ->sum(fn($id) => $listingCounts[$id] ?? 0);
        }

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
