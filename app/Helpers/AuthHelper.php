<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function user()
    {
        return Auth::guard('super_admin')->user() 
            ?? Auth::guard('admin')->user() 
            ?? Auth::guard('employee')->user();
    }

    public static function id()
    {
        $user = self::user();
        return $user ? $user->id : null;
    }

    public static function check()
    {
        return self::user() !== null;
    }
}
