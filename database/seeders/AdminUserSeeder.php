<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (User::where('email', 'admin@fotlist.com')->exists()) {
            $this->command->info('Admin user already exists.');
            return;
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Admin Fotlist',
            'email' => 'admin@fotlist.com',
            'password' => bcrypt('admin12345'), // Default password - should be changed
            'role' => 'admin',
            'wallet_balance' => 0,
            'status' => 'active',
        ]);

        $this->command->info("Admin user created successfully!");
        $this->command->info("Email: {$admin->email}");
        $this->command->info("Password: admin12345");
        $this->command->warn("Please change the default password for security!");
    }
}
