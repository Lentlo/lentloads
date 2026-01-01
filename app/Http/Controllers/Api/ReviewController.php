<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, $userId)
    {
        $reviews = Review::with(['reviewer:id,name,avatar', 'listing:id,title,slug'])
            ->where('reviewed_id', $userId)
            ->approved()
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        $stats = [
            'average' => Review::where('reviewed_id', $userId)->approved()->avg('rating') ?? 0,
            'total' => Review::where('reviewed_id', $userId)->approved()->count(),
            'breakdown' => Review::where('reviewed_id', $userId)
                ->approved()
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
        ];

        return $this->successResponse([
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewed_id' => 'required|exists:users,id',
            'listing_id' => 'nullable|exists:listings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'type' => 'required|in:buyer,seller',
        ]);

        if ($validated['reviewed_id'] === auth()->id()) {
            return $this->errorResponse('You cannot review yourself', 422);
        }

        // Check if already reviewed
        $exists = Review::where('reviewer_id', auth()->id())
            ->where('reviewed_id', $validated['reviewed_id'])
            ->when($validated['listing_id'], fn($q) => $q->where('listing_id', $validated['listing_id']))
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('You have already reviewed this user', 422);
        }

        $review = Review::create([
            'reviewer_id' => auth()->id(),
            'reviewed_id' => $validated['reviewed_id'],
            'listing_id' => $validated['listing_id'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'type' => $validated['type'],
        ]);

        return $this->successResponse($review, 'Review submitted successfully', 201);
    }

    public function respond(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->reviewed_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($review->seller_response) {
            return $this->errorResponse('You have already responded to this review', 422);
        }

        $validated = $request->validate([
            'response' => 'required|string|max:500',
        ]);

        $review->respond($validated['response']);

        return $this->successResponse($review, 'Response added successfully');
    }

    public function myReviews(Request $request)
    {
        $type = $request->input('type', 'received');

        if ($type === 'received') {
            $reviews = Review::with(['reviewer:id,name,avatar', 'listing:id,title'])
                ->where('reviewed_id', auth()->id())
                ->approved()
                ->latest()
                ->paginate(20);
        } else {
            $reviews = Review::with(['reviewed:id,name,avatar', 'listing:id,title'])
                ->where('reviewer_id', auth()->id())
                ->latest()
                ->paginate(20);
        }

        return $this->paginatedResponse($reviews);
    }

    public function delete($id)
    {
        $review = Review::findOrFail($id);

        if ($review->reviewer_id !== auth()->id() && !auth()->user()->isModerator()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $review->delete();

        return $this->successResponse(null, 'Review deleted');
    }
}
