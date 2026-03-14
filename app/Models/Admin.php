<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : session('tenant_id');
            if ($tenantId) {
                $builder->where('admins.tenant_id', (int) $tenantId);
            }
        });
    }
    
    protected $fillable = ['tenant_id', 'name', 'email', 'password', 'phone', 'is_active'];
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
        return true;
    }
}
