<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * This seeder creates all necessary initial data for a production-ready system:
     * - Roles and Permissions
     * - Default Admin account
     * - Payment Methods
     * - Shipping Zones
     * - Initial Categories
     * - Site Settings
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            PaymentMethodsSeeder::class,
            ShippingZonesSeeder::class,
            CategoriesSeeder::class,
            SiteSettingsSeeder::class,
            ProductsSeeder::class,
        ]);
    }
}
