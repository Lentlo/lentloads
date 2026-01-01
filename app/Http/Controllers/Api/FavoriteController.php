<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::with(['listing' => function ($q) {
            $q->with(['primaryImage', 'category:id,name', 'user:id,name,city']);
        }])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        $listings = $favorites->getCollection()->map(function ($favorite) {
            $listing = $favorite->listing;
            $listing->favorited_at = $favorite->created_at;
            return $listing;
        });

        $favorites->setCollection($listings);

        return $this->paginatedResponse($favorites);
    }

    public function toggle($listingId)
    {
        $listing = Listing::findOrFail($listingId);

        $favorite = Favorite::where('user_id', auth()->id())
            ->where('listing_id', $listingId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return $this->successResponse([
                'is_favorited' => false,
                'favorites_count' => $listing->fresh()->favorites_count,
            ], 'Removed from favorites');
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'listing_id' => $listingId,
        ]);

        return $this->successResponse([
            'is_favorited' => true,
            'favorites_count' => $listing->fresh()->favorites_count,
        ], 'Added to favorites');
    }

    public function check($listingId)
    {
        $isFavorited = Favorite::where('user_id', auth()->id())
            ->where('listing_id', $listingId)
            ->exists();

        return $this->successResponse([
            'is_favorited' => $isFavorited,
        ]);
    }

    public function bulkCheck(Request $request)
    {
        $request->validate([
            'listing_ids' => 'required|array',
            'listing_ids.*' => 'integer',
        ]);

        $favoritedIds = Favorite::where('user_id', auth()->id())
            ->whereIn('listing_id', $request->listing_ids)
            ->pluck('listing_id')
            ->toArray();

        return $this->successResponse([
            'favorited_ids' => $favoritedIds,
        ]);
    }

    public function clear()
    {
        Favorite::where('user_id', auth()->id())->delete();

        return $this->successResponse(null, 'All favorites cleared');
    }
}
