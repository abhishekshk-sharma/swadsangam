<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class TableCategory extends Model
{
    use BelongsToTenant;
    protected $fillable = ['name', 'description'];

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class, 'category_id');
    }
}
