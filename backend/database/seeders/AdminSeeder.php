<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Seed default admin account.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();

        // Create default Super Admin
        DB::table('admins')->insert([
            'first_name' => 'Casa Vera',
            'last_name' => 'Admin',
            'email' => 'admin@casavera.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'), // Default password: password
            'phone' => '+63 912 345 6789',
            'role_id' => $superAdminRole->id,
            'status' => 'active',
            'two_factor_enabled' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create additional test admin (for development)
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        
        DB::table('admins')->insert([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'test.admin@casavera.com',
            'email_verified_at' => $now,
            'password' => Hash::make('Test@123!'),
            'phone' => '+63 912 345 6780',
            'role_id' => $adminRole->id,
            'status' => 'active',
            'two_factor_enabled' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
