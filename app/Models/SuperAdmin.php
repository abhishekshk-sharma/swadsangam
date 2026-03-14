<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'super_admins';
    
    protected $fillable = ['name', 'email', 'password', 'phone', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function isSuperAdmin()
    {
        return true;
    }

    public function isAdmin()
    {
        return true;
    }
}
