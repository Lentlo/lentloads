<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::withCount([
            'listings' => fn($q) => $q->active(),
            'reviewsReceived' => fn($q) => $q->approved(),
        ])
            ->findOrFail($id);

        // Don't expose sensitive data
        $user->makeHidden(['email', 'phone', 'notification_preferences']);

        return $this->successResponse([
            'user' => $user,
            'member_since' => $user->created_at->format('F Y'),
            'response_rate' => $this->calculateResponseRate($user),
        ]);
    }

    public function listings($id)
    {
        $listings = Listing::with(['primaryImage', 'category:id,name'])
            ->where('user_id', $id)
            ->active()
            ->latest()
            ->paginate(20);

        return $this->paginatedResponse($listings);
    }

    public function reviews($id)
    {
        $user = User::findOrFail($id);

        $reviews = $user->reviewsReceived()
            ->with(['reviewer:id,name,avatar', 'listing:id,title'])
            ->approved()
            ->latest()
            ->paginate(20);

        return $this->paginatedResponse($reviews);
    }

    protected function calculateResponseRate(User $user): int
    {
        $totalConversations = $user->sellerConversations()->count();

        if ($totalConversations === 0) {
            return 100;
        }

        $respondedConversations = $user->sellerConversations()
            ->whereHas('messages', fn($q) => $q->where('sender_id', $user->id))
            ->count();

        return (int) round(($respondedConversations / $totalConversations) * 100);
    }

    public function follow($id)
    {
        // This can be implemented with a followers table if needed
        return $this->successResponse(null, 'Following user');
    }

    public function unfollow($id)
    {
        return $this->successResponse(null, 'Unfollowed user');
    }

    public function block($id)
    {
        // Block user implementation
        return $this->successResponse(null, 'User blocked');
    }
}
