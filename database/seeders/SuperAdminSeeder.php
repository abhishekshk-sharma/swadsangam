<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default super admin if not exists
        if (!User::where('email', 'superadmin@swadsangam.com')->exists()) {
            User::create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@swadsangam.com',
                'password' => Hash::make('SuperAdmin@123'),
                'role' => 'super_admin',
                'is_active' => true,
                'tenant_id' => null,
            ]);

            $this->command->info('✅ Super Admin created successfully!');
            $this->command->info('📧 Email: superadmin@swadsangam.com');
            $this->command->info('🔑 Password: SuperAdmin@123');
            $this->command->warn('⚠️  Please change the password after first login!');
        } else {
            $this->command->warn('Super Admin already exists!');
        }
    }
}
