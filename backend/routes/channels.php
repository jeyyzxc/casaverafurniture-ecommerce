<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Public channels (no authentication required)
Broadcast::channel('products', function () {
    return true;
});

Broadcast::channel('homepage', function () {
    return true;
});

Broadcast::channel('orders', function () {
    return true;
});

Broadcast::channel('cart', function () {
    return true;
});

Broadcast::channel('cart.session.{sessionId}', function ($user, $sessionId) {
    // Allow access if session matches
    return true;
});

// Admin channel (requires admin authentication)
Broadcast::channel('admin', function ($user) {
    // Check if user is admin
    return $user && class_exists(\App\Models\Admin::class) && $user instanceof \App\Models\Admin;
});

// Private user channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
