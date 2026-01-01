<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{uuid}', function ($user, $uuid) {
    $conversation = \App\Models\Conversation::where('uuid', $uuid)->first();
    return $conversation && $conversation->isParticipant($user);
});

Broadcast::channel('listings', function ($user) {
    return true; // Public channel for new listings
});
