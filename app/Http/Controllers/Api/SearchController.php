<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\SavedSearch;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        // Search listings
        $listings = Listing::with(['primaryImage', 'category:id,name', 'user:id,name,city'])
            ->active()
            ->notExpired()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // Search categories
        $categories = Category::where('name', 'like', "%{$query}%")
            ->active()
            ->limit(5)
            ->get();

        return $this->successResponse([
            'listings' => $listings,
            'categories' => $categories,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return $this->successResponse([]);
        }

        $suggestions = Listing::active()
            ->where('title', 'like', "%{$query}%")
            ->distinct()
            ->pluck('title')
            ->take(8);

        return $this->successResponse($suggestions);
    }

    public function savedSearches()
    {
        $searches = SavedSearch::with('category:id,name')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return $this->successResponse($searches);
    }

    public function saveSearch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'query' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'city' => 'nullable|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'filters' => 'nullable|array',
            'notify_email' => 'boolean',
            'notify_push' => 'boolean',
            'notify_frequency' => 'nullable|in:instant,daily,weekly',
        ]);

        $search = SavedSearch::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return $this->successResponse($search, 'Search saved successfully', 201);
    }

    public function updateSavedSearch(Request $request, $id)
    {
        $search = SavedSearch::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'notify_email' => 'boolean',
            'notify_push' => 'boolean',
            'notify_frequency' => 'nullable|in:instant,daily,weekly',
        ]);

        $search->update($validated);

        return $this->successResponse($search, 'Saved search updated');
    }

    public function deleteSavedSearch($id)
    {
        $search = SavedSearch::where('user_id', auth()->id())->findOrFail($id);
        $search->delete();

        return $this->successResponse(null, 'Saved search deleted');
    }

    public function runSavedSearch($id)
    {
        $search = SavedSearch::where('user_id', auth()->id())->findOrFail($id);

        $query = Listing::with(['primaryImage', 'category:id,name', 'user:id,name,city'])
            ->active()
            ->notExpired();

        if ($search->query) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search->query}%")
                    ->orWhere('description', 'like', "%{$search->query}%");
            });
        }

        if ($search->category_id) {
            $query->inCategory($search->category_id);
        }

        if ($search->city) {
            $query->inCity($search->city);
        }

        if ($search->min_price || $search->max_price) {
            $query->priceRange($search->min_price, $search->max_price);
        }

        $listings = $query->latest()->paginate(20);

        return $this->paginatedResponse($listings);
    }

    public function trending()
    {
        // Get trending search terms based on recent listings
        $trending = Listing::active()
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('city, COUNT(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('city');

        return $this->successResponse($trending);
    }
}
