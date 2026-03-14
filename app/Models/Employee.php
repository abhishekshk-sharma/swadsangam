<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';
    
    protected $fillable = [
        'tenant_id', 
        'name', 
        'email', 
        'password', 
        'phone', 
        'role', 
        'telegram_chat_id', 
        'telegram_username', 
        'is_active'
    ];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isSuperAdmin()
    {
        return false;
    }

    public function isAdmin()
    {
        return false;
    }

    public function isWaiter()
    {
        return $this->role === 'waiter';
    }

    public function isChef()
    {
        return $this->role === 'chef';
    }

    public function isCashier()
    {
        return $this->role === 'cashier';
    }
}
