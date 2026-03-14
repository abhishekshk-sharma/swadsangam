<?php

use App\Helpers\AuthHelper;

if (!function_exists('current_user')) {
    function current_user()
    {
        return AuthHelper::user();
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id()
    {
        return AuthHelper::id();
    }
}
