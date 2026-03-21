<?php

use Illuminate\Support\Facades\Broadcast;

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
    
    return true;
});

Broadcast::channel('admin', function ($user) {
    
    return $user && class_exists(\App\Models\Admin::class) && $user instanceof \App\Models\Admin;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
