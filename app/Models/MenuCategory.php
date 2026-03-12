<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class MenuCategory extends Model
{
    use BelongsToTenant;
    protected $fillable = ['name', 'description'];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_category_id');
    }
}
