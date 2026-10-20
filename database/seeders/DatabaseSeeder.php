<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Starting data: one account per role, 20 tables and menu categories.
     * Change these passwords before the system is used at Elite Cafe.
     */
    public function run(): void
    {
        $staff = [
            ['name' => 'Mohammad Nijam',  'username' => 'admin',      'role' => 'admin'],
            ['name' => 'Demo Cashier',    'username' => 'cashier',    'role' => 'cashier'],
            ['name' => 'Demo Waiter',     'username' => 'waiter',     'role' => 'waiter'],
            ['name' => 'Demo Kitchen',    'username' => 'kitchen',    'role' => 'kitchen'],
            ['name' => 'Demo Storekeeper','username' => 'storekeeper','role' => 'storekeeper'],
        ];

        foreach ($staff as $person) {
            User::create($person + ['password' => 'Password@123', 'is_active' => true]);
        }

        // Tables T01 to T20 (Elite Cafe has 20+ tables)
        for ($i = 1; $i <= 20; $i++) {
            DB::table('restaurant_tables')->insert([
                'table_no' => sprintf('T%02d', $i),
                'section' => $i <= 14 ? 'Indoor' : 'Outdoor',
                'capacity' => 4,
                'status' => 'free',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['Rice & Curry', 'Kottu', 'Fried Rice & Noodles', 'Short Eats', 'Beverages', 'Desserts'] as $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
