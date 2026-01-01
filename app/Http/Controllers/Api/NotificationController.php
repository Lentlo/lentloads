<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = auth()->user()
            ->notifications()
            ->when($request->unread, fn($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate(20);

        return $this->paginatedResponse($notifications);
    }

    public function unreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();

        return $this->successResponse(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return $this->successResponse(null, 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return $this->successResponse(null, 'All notifications marked as read');
    }

    public function delete($id)
    {
        auth()->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return $this->successResponse(null, 'Notification deleted');
    }

    public function clear()
    {
        auth()->user()->notifications()->delete();

        return $this->successResponse(null, 'All notifications cleared');
    }

    // Push notification subscription
    public function subscribePush(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'endpoint' => $validated['endpoint'],
            ],
            [
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_key' => $validated['keys']['auth'],
            ]
        );

        return $this->successResponse(null, 'Push subscription saved');
    }

    public function unsubscribePush(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', auth()->id())
            ->where('endpoint', $request->endpoint)
            ->delete();

        return $this->successResponse(null, 'Push subscription removed');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_messages' => 'boolean',
            'email_favorites' => 'boolean',
            'email_listings' => 'boolean',
            'push_messages' => 'boolean',
            'push_favorites' => 'boolean',
            'push_listings' => 'boolean',
        ]);

        auth()->user()->update([
            'notification_preferences' => $validated,
        ]);

        return $this->successResponse(null, 'Preferences updated');
    }
}
