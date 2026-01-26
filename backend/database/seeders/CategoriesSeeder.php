<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CategoriesSeeder extends Seeder
{
    /**
     * Seed product categories.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'parent_id' => null,
                'name' => 'Living Room',
                'slug' => 'living-room',
                'description' => 'Transform your living space with our exquisite collection of sofas, chairs, and accent furniture.',
                'image' => '/images/categories/living-room.jpg',
                'icon' => 'sofa',
                'color' => '#c9a050',
                'display_order' => 1,
                'is_visible' => true,
                'show_in_menu' => true,
                'show_in_homepage' => true,
                'meta_title' => 'Living Room Furniture | Casa Vera',
                'meta_description' => 'Discover luxury living room furniture including sofas, armchairs, coffee tables, and more.',
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => null,
                'name' => 'Dining Room',
                'slug' => 'dining-room',
                'description' => 'Elevate your dining experience with our elegant tables, chairs, and dining sets.',
                'image' => '/images/categories/dining-room.jpg',
                'icon' => 'utensils',
                'color' => '#8b5cf6',
                'display_order' => 2,
                'is_visible' => true,
                'show_in_menu' => true,
                'show_in_homepage' => true,
                'meta_title' => 'Dining Room Furniture | Casa Vera',
                'meta_description' => 'Shop luxury dining tables, chairs, and complete dining sets for your home.',
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => null,
                'name' => 'Bedroom',
                'slug' => 'bedroom',
                'description' => 'Create your perfect sanctuary with our beds, dressers, and bedroom accessories.',
                'image' => '/images/categories/bedroom.jpg',
                'icon' => 'bed',
                'color' => '#3b82f6',
                'display_order' => 3,
                'is_visible' => true,
                'show_in_menu' => true,
                'show_in_homepage' => true,
                'meta_title' => 'Bedroom Furniture | Casa Vera',
                'meta_description' => 'Find the perfect bed frame, nightstands, dressers, and bedroom furniture.',
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => null,
                'name' => 'Storage & Organization',
                'slug' => 'storage',
                'description' => 'Maximize your space with our stylish storage solutions and organizational furniture.',
                'image' => '/images/categories/storage.jpg',
                'icon' => 'archive',
                'color' => '#10b981',
                'display_order' => 4,
                'is_visible' => true,
                'show_in_menu' => true,
                'show_in_homepage' => false,
                'meta_title' => 'Storage Furniture | Casa Vera',
                'meta_description' => 'Organize your home with elegant cabinets, sideboards, and storage solutions.',
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => null,
                'name' => 'Lighting',
                'slug' => 'lighting',
                'description' => 'Illuminate your home with our stunning collection of lamps and lighting fixtures.',
                'image' => '/images/categories/lighting.jpg',
                'icon' => 'lightbulb',
                'color' => '#f59e0b',
                'display_order' => 5,
                'is_visible' => true,
                'show_in_menu' => true,
                'show_in_homepage' => true,
                'meta_title' => 'Lighting & Lamps | Casa Vera',
                'meta_description' => 'Shop designer chandeliers, table lamps, floor lamps, and lighting fixtures.',
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => null,
                'name' => 'Decor & Accessories',
                'slug' => 'decor',
                'description' => 'Add the finishing touches with our curated collection of decorative accessories.',
                'image' => '/images/categories/decor.jpg',
                'icon' => 'palette',
                'color' => '#ef4444',
                'display_order' => 6,
                'is_visible' => true,
                'show_in_menu' => true,
                'show_in_homepage' => false,
                'meta_title' => 'Home Decor & Accessories | Casa Vera',
                'meta_description' => 'Complete your space with vases, pillows, art, and decorative accessories.',
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('categories')->insert($categories);

        // Get parent category IDs
        $livingRoom = DB::table('categories')->where('slug', 'living-room')->first();
        $diningRoom = DB::table('categories')->where('slug', 'dining-room')->first();
        $bedroom = DB::table('categories')->where('slug', 'bedroom')->first();

        // Subcategories
        $subcategories = [
            // Living Room subcategories
            ['parent_id' => $livingRoom->id, 'name' => 'Sofas', 'slug' => 'sofas', 'display_order' => 1, 'color' => '#c9a050'],
            ['parent_id' => $livingRoom->id, 'name' => 'Armchairs', 'slug' => 'armchairs', 'display_order' => 2, 'color' => '#c9a050'],
            ['parent_id' => $livingRoom->id, 'name' => 'Coffee Tables', 'slug' => 'coffee-tables', 'display_order' => 3, 'color' => '#c9a050'],
            ['parent_id' => $livingRoom->id, 'name' => 'TV Stands', 'slug' => 'tv-stands', 'display_order' => 4, 'color' => '#c9a050'],

            // Dining Room subcategories
            ['parent_id' => $diningRoom->id, 'name' => 'Dining Tables', 'slug' => 'dining-tables', 'display_order' => 1, 'color' => '#8b5cf6'],
            ['parent_id' => $diningRoom->id, 'name' => 'Dining Chairs', 'slug' => 'dining-chairs', 'display_order' => 2, 'color' => '#8b5cf6'],
            ['parent_id' => $diningRoom->id, 'name' => 'Dining Sets', 'slug' => 'dining-sets', 'display_order' => 3, 'color' => '#8b5cf6'],
            ['parent_id' => $diningRoom->id, 'name' => 'Bar Furniture', 'slug' => 'bar-furniture', 'display_order' => 4, 'color' => '#8b5cf6'],

            // Bedroom subcategories
            ['parent_id' => $bedroom->id, 'name' => 'Beds', 'slug' => 'beds', 'display_order' => 1, 'color' => '#3b82f6'],
            ['parent_id' => $bedroom->id, 'name' => 'Nightstands', 'slug' => 'nightstands', 'display_order' => 2, 'color' => '#3b82f6'],
            ['parent_id' => $bedroom->id, 'name' => 'Dressers', 'slug' => 'dressers', 'display_order' => 3, 'color' => '#3b82f6'],
            ['parent_id' => $bedroom->id, 'name' => 'Wardrobes', 'slug' => 'wardrobes', 'display_order' => 4, 'color' => '#3b82f6'],
        ];

        foreach ($subcategories as &$sub) {
            $sub['is_visible'] = true;
            $sub['show_in_menu'] = true;
            $sub['show_in_homepage'] = false;
            $sub['product_count'] = 0;
            $sub['created_at'] = $now;
            $sub['updated_at'] = $now;
        }

        DB::table('categories')->insert($subcategories);

        // ===========================================
        // COLLECTIONS
        // ===========================================
        $collections = [
            [
                'name' => 'New Arrivals',
                'slug' => 'new-arrivals',
                'description' => 'Discover our latest furniture pieces fresh from the showroom.',
                'image' => '/images/collections/new-arrivals.jpg',
                'type' => 'automated',
                'rules' => json_encode(['is_new' => true, 'limit' => 12]),
                'display_order' => 1,
                'is_visible' => true,
                'show_in_homepage' => true,
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Best Sellers',
                'slug' => 'best-sellers',
                'description' => 'Our most popular and loved furniture pieces.',
                'image' => '/images/collections/best-sellers.jpg',
                'type' => 'automated',
                'rules' => json_encode(['is_bestseller' => true, 'limit' => 12]),
                'display_order' => 2,
                'is_visible' => true,
                'show_in_homepage' => true,
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Featured',
                'slug' => 'featured',
                'description' => 'Handpicked furniture pieces that deserve the spotlight.',
                'image' => '/images/collections/featured.jpg',
                'type' => 'automated',
                'rules' => json_encode(['is_featured' => true, 'limit' => 8]),
                'display_order' => 3,
                'is_visible' => true,
                'show_in_homepage' => true,
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sale',
                'slug' => 'sale',
                'description' => 'Luxury furniture at special prices. Limited time offers.',
                'image' => '/images/collections/sale.jpg',
                'type' => 'automated',
                'rules' => json_encode(['has_sale_price' => true]),
                'display_order' => 4,
                'is_visible' => true,
                'show_in_homepage' => false,
                'product_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('collections')->insert($collections);
    }
}
