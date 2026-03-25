<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAssignmentLog extends Model
{
    protected $fillable = ['tenant_id', 'order_id', 'from_user_id', 'to_user_id', 'note'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(Employee::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(Employee::class, 'to_user_id');
    }
}
