<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RestaurantTable;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create tables
        for ($i = 1; $i <= 10; $i++) {
            RestaurantTable::create([
                'table_number' => 'T' . $i,
                'qr_code' => uniqid('table_')
            ]);
        }

        // Create menu items
        $menuItems = [
            ['name' => 'Margherita Pizza', 'category' => 'Main Course', 'price' => 12.99, 'description' => 'Classic tomato and mozzarella'],
            ['name' => 'Pepperoni Pizza', 'category' => 'Main Course', 'price' => 14.99, 'description' => 'Loaded with pepperoni'],
            ['name' => 'Caesar Salad', 'category' => 'Appetizer', 'price' => 8.99, 'description' => 'Fresh romaine with caesar dressing'],
            ['name' => 'Garlic Bread', 'category' => 'Appetizer', 'price' => 5.99, 'description' => 'Toasted bread with garlic butter'],
            ['name' => 'Pasta Carbonara', 'category' => 'Main Course', 'price' => 13.99, 'description' => 'Creamy pasta with bacon'],
            ['name' => 'Tiramisu', 'category' => 'Dessert', 'price' => 6.99, 'description' => 'Classic Italian dessert'],
            ['name' => 'Chocolate Cake', 'category' => 'Dessert', 'price' => 5.99, 'description' => 'Rich chocolate cake'],
            ['name' => 'Coca Cola', 'category' => 'Beverage', 'price' => 2.99, 'description' => 'Chilled soft drink'],
            ['name' => 'Orange Juice', 'category' => 'Beverage', 'price' => 3.99, 'description' => 'Fresh orange juice'],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }
    }
}
