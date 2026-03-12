<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class TelegramRequest extends Model
{
    use BelongsToTenant;
    
    protected $fillable = ['tenant_id', 'chat_id', 'username', 'first_name', 'last_name', 'phone', 'status'];
}
