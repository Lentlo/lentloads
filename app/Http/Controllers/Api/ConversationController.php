<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::with([
            'listing:id,title,slug,price',
            'listing.primaryImage',
            'buyer:id,name,avatar',
            'seller:id,name,avatar',
            'latestMessage',
        ])
            ->forUser(auth()->id())
            ->notBlocked()
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('is_read', false)
                    ->where('sender_id', '!=', auth()->id());
            }])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->paginate(20);

        return $this->paginatedResponse($conversations);
    }

    public function show($uuid)
    {
        $conversation = Conversation::with([
            'listing:id,title,slug,price,status',
            'listing.primaryImage',
            'buyer:id,name,avatar,city',
            'seller:id,name,avatar,city,is_verified_seller',
        ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!$conversation->isParticipant(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Mark as read
        $conversation->markAsRead(auth()->user());

        $messages = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return $this->successResponse([
            'conversation' => $conversation,
            'messages' => $messages,
            'other_user' => $conversation->getOtherUser(auth()->user()),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'message' => 'required|string|max:1000',
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);

        if ($listing->user_id === auth()->id()) {
            return $this->errorResponse('You cannot message yourself', 422);
        }

        if ($listing->status !== 'active') {
            return $this->errorResponse('This listing is no longer available', 422);
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $listing->user_id,
        ]);

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $validated['message'],
            'type' => 'text',
        ]);

        // Increment contact count
        $listing->incrementContacts();

        // Broadcast event for real-time
        // broadcast(new NewMessage($message))->toOthers();

        return $this->successResponse([
            'conversation' => $conversation->load(['listing.primaryImage', 'buyer', 'seller']),
            'message' => $message,
        ], 'Message sent successfully', 201);
    }

    public function sendMessage(Request $request, $uuid)
    {
        $conversation = Conversation::where('uuid', $uuid)->firstOrFail();

        if (!$conversation->isParticipant(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($conversation->is_blocked) {
            return $this->errorResponse('This conversation is blocked', 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'type' => 'nullable|in:text,offer',
            'offer_amount' => 'required_if:type,offer|nullable|numeric|min:0',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $validated['message'],
            'type' => $validated['type'] ?? 'text',
            'offer_amount' => $validated['offer_amount'] ?? null,
            'offer_status' => isset($validated['offer_amount']) ? 'pending' : null,
        ]);

        // broadcast(new NewMessage($message))->toOthers();

        return $this->successResponse($message->load('sender'), 'Message sent');
    }

    public function respondToOffer(Request $request, $messageId)
    {
        $message = Message::findOrFail($messageId);
        $conversation = $message->conversation;

        if (!$conversation->isParticipant(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($message->sender_id === auth()->id()) {
            return $this->errorResponse('You cannot respond to your own offer', 422);
        }

        if (!$message->isOffer() || $message->offer_status !== 'pending') {
            return $this->errorResponse('Invalid offer', 422);
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,reject',
        ]);

        if ($validated['action'] === 'accept') {
            $message->acceptOffer();
            $responseText = 'Offer accepted';
        } else {
            $message->rejectOffer();
            $responseText = 'Offer rejected';
        }

        // Send system message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $responseText,
            'type' => 'system',
        ]);

        return $this->successResponse($message, $responseText);
    }

    public function delete($uuid)
    {
        $conversation = Conversation::where('uuid', $uuid)->firstOrFail();

        if (!$conversation->isParticipant(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $conversation->deleteFor(auth()->user());

        return $this->successResponse(null, 'Conversation deleted');
    }

    public function block($uuid)
    {
        $conversation = Conversation::where('uuid', $uuid)->firstOrFail();

        if (!$conversation->isParticipant(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $conversation->block(auth()->user());

        return $this->successResponse(null, 'User blocked');
    }

    public function unblock($uuid)
    {
        $conversation = Conversation::where('uuid', $uuid)->firstOrFail();

        if ($conversation->blocked_by !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $conversation->unblock();

        return $this->successResponse(null, 'User unblocked');
    }

    public function unreadCount()
    {
        $count = Message::whereHas('conversation', function ($q) {
            $q->forUser(auth()->id())->notBlocked();
        })
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['count' => $count]);
    }
}
