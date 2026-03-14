<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : session('tenant_id');
            if ($tenantId) {
                $builder->where('employees.tenant_id', (int) $tenantId);
            }
        });

        static::creating(function ($model) {
            if (!$model->tenant_id) {
                $model->tenant_id = app()->bound('current_tenant_id')
                    ? app('current_tenant_id')
                    : session('tenant_id');
            }
        });
    }
    
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
