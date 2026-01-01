<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ContactView;
use Illuminate\Http\Request;

class AdminConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = Conversation::with([
            'buyer:id,name,email,avatar_url',
            'seller:id,name,email,avatar_url',
            'listing:id,title,slug',
            'latestMessage',
        ])->withCount('messages');

        // Search by user name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('buyer', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('seller', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('listing', fn($q) => $q->where('title', 'like', "%{$search}%"));
            });
        }

        // Filter by blocked status
        if ($request->filled('blocked')) {
            $query->where('is_blocked', $request->blocked === 'true');
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $conversations = $query->latest()->paginate($perPage);

        return $this->paginatedResponse($conversations);
    }

    public function show($id)
    {
        $conversation = Conversation::with([
            'buyer:id,name,email,phone,avatar_url',
            'seller:id,name,email,phone,avatar_url',
            'listing:id,title,slug,price',
            'messages.sender:id,name,avatar_url',
        ])->findOrFail($id);

        return $this->successResponse($conversation);
    }

    public function messages($id, Request $request)
    {
        $conversation = Conversation::findOrFail($id);

        $perPage = min((int) $request->input('per_page', 50), 200);
        $messages = $conversation->messages()
            ->with('sender:id,name,avatar_url')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->paginatedResponse($messages);
    }

    // Contact Views
    public function contactViews(Request $request)
    {
        $query = ContactView::with([
            'viewer:id,name,email,avatar_url',
            'owner:id,name,email,phone,avatar_url',
            'listing:id,title,slug',
        ]);

        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Filter by viewer
        if ($request->filled('viewer_id')) {
            $query->where('viewer_id', $request->viewer_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('viewer', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('owner', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $views = $query->latest()->paginate($perPage);

        return $this->paginatedResponse($views);
    }

    public function contactViewStats()
    {
        $stats = [
            'total_views' => ContactView::count(),
            'today' => ContactView::whereDate('created_at', today())->count(),
            'this_week' => ContactView::where('created_at', '>=', now()->subWeek())->count(),
            'this_month' => ContactView::where('created_at', '>=', now()->subMonth())->count(),
        ];

        // Top viewed owners
        $topOwners = ContactView::selectRaw('owner_id, COUNT(*) as view_count')
            ->with('owner:id,name,email')
            ->groupBy('owner_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        // Top active viewers
        $topViewers = ContactView::selectRaw('viewer_id, COUNT(*) as view_count')
            ->with('viewer:id,name,email')
            ->groupBy('viewer_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        return $this->successResponse([
            'stats' => $stats,
            'top_owners' => $topOwners,
            'top_viewers' => $topViewers,
        ]);
    }
}
