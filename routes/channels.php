<?php

use Illuminate\Support\Facades\Broadcast;

// Default web user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public tenant-scoped orders channel.
// No auth closure needed — it's a public channel.
// Mobile clients are already authenticated via Sanctum on the HTTP API.
Broadcast::channel('orders.{tenantId}', function () {
    return true;
});
