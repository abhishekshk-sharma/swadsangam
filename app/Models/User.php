<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'password', 'role', 'is_active', 'telegram_chat_id', 'telegram_username'];
    protected $hidden = ['password', 'remember_token'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isManager()
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }
}
