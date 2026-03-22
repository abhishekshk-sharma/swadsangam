<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Employee;

// Web users (default guard)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Tenant-scoped orders channel — accessible by any authenticated employee of that tenant
Broadcast::channel('orders.{tenantId}', function ($user, $tenantId) {
    if ($user instanceof Employee) {
        return (int) $user->tenant_id === (int) $tenantId;
    }
    return false;
});
