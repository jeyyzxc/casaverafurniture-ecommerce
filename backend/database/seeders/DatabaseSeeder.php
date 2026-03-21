<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    
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
