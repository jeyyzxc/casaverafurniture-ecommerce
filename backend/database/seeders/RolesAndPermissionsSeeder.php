<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesAndPermissionsSeeder extends Seeder
{
    
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full access to all system features. Cannot be deleted.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to manage products, orders, and customers.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage products, orders, and view reports.',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Support Staff',
                'slug' => 'support',
                'description' => 'Can view orders, manage customer inquiries, and process refunds.',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Content Manager',
                'slug' => 'content-manager',
                'description' => 'Can manage CMS content, banners, and pages.',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Inventory Staff',
                'slug' => 'inventory',
                'description' => 'Can manage product inventory and stock levels.',
                'is_system' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('roles')->insert($roles);

        $permissions = [
            
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'Access admin dashboard'],
            ['name' => 'View Analytics', 'slug' => 'dashboard.analytics', 'module' => 'dashboard', 'description' => 'View sales and traffic analytics'],

            ['name' => 'View Products', 'slug' => 'products.view', 'module' => 'products', 'description' => 'View product list'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'module' => 'products', 'description' => 'Create new products'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'module' => 'products', 'description' => 'Edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'module' => 'products', 'description' => 'Delete products'],
            ['name' => 'Export Products', 'slug' => 'products.export', 'module' => 'products', 'description' => 'Export products to CSV'],

            ['name' => 'View Categories', 'slug' => 'categories.view', 'module' => 'categories', 'description' => 'View category list'],
            ['name' => 'Manage Categories', 'slug' => 'categories.manage', 'module' => 'categories', 'description' => 'Create, edit, delete categories'],

            ['name' => 'View Collections', 'slug' => 'collections.view', 'module' => 'collections', 'description' => 'View collections'],
            ['name' => 'Manage Collections', 'slug' => 'collections.manage', 'module' => 'collections', 'description' => 'Create, edit, delete collections'],

            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory', 'description' => 'View stock levels'],
            ['name' => 'Manage Inventory', 'slug' => 'inventory.manage', 'module' => 'inventory', 'description' => 'Adjust stock levels'],
            ['name' => 'View Stock Logs', 'slug' => 'inventory.logs', 'module' => 'inventory', 'description' => 'View stock movement history'],

            ['name' => 'View Orders', 'slug' => 'orders.view', 'module' => 'orders', 'description' => 'View order list'],
            ['name' => 'Process Orders', 'slug' => 'orders.process', 'module' => 'orders', 'description' => 'Update order status'],
            ['name' => 'Cancel Orders', 'slug' => 'orders.cancel', 'module' => 'orders', 'description' => 'Cancel orders'],
            ['name' => 'Export Orders', 'slug' => 'orders.export', 'module' => 'orders', 'description' => 'Export orders to CSV'],

            ['name' => 'View Payments', 'slug' => 'payments.view', 'module' => 'payments', 'description' => 'View payment list'],
            ['name' => 'Verify Payments', 'slug' => 'payments.verify', 'module' => 'payments', 'description' => 'Verify pending payments'],
            ['name' => 'Process Refunds', 'slug' => 'payments.refund', 'module' => 'payments', 'description' => 'Process refunds'],
            ['name' => 'Manage Payment Methods', 'slug' => 'payments.methods', 'module' => 'payments', 'description' => 'Configure payment methods'],

            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users', 'description' => 'View customer list'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users', 'description' => 'Edit customer accounts'],
            ['name' => 'Ban Users', 'slug' => 'users.ban', 'module' => 'users', 'description' => 'Ban/unban customers'],
            ['name' => 'Export Users', 'slug' => 'users.export', 'module' => 'users', 'description' => 'Export customer data'],

            ['name' => 'View Admins', 'slug' => 'admins.view', 'module' => 'admins', 'description' => 'View admin list'],
            ['name' => 'Create Admins', 'slug' => 'admins.create', 'module' => 'admins', 'description' => 'Create new admins'],
            ['name' => 'Edit Admins', 'slug' => 'admins.edit', 'module' => 'admins', 'description' => 'Edit admin accounts'],
            ['name' => 'Delete Admins', 'slug' => 'admins.delete', 'module' => 'admins', 'description' => 'Delete admin accounts'],
            ['name' => 'Manage Roles', 'slug' => 'admins.roles', 'module' => 'admins', 'description' => 'Manage roles and permissions'],

            ['name' => 'View Promotions', 'slug' => 'promotions.view', 'module' => 'promotions', 'description' => 'View promotions'],
            ['name' => 'Manage Promotions', 'slug' => 'promotions.manage', 'module' => 'promotions', 'description' => 'Create, edit, delete promotions'],

            ['name' => 'View Shipping', 'slug' => 'shipping.view', 'module' => 'shipping', 'description' => 'View shipping zones'],
            ['name' => 'Manage Shipping', 'slug' => 'shipping.manage', 'module' => 'shipping', 'description' => 'Configure shipping zones and rates'],

            ['name' => 'View Reviews', 'slug' => 'reviews.view', 'module' => 'reviews', 'description' => 'View product reviews'],
            ['name' => 'Moderate Reviews', 'slug' => 'reviews.moderate', 'module' => 'reviews', 'description' => 'Approve/reject reviews'],

            ['name' => 'View CMS', 'slug' => 'cms.view', 'module' => 'cms', 'description' => 'View CMS content'],
            ['name' => 'Manage Banners', 'slug' => 'cms.banners', 'module' => 'cms', 'description' => 'Manage banners'],
            ['name' => 'Manage Pages', 'slug' => 'cms.pages', 'module' => 'cms', 'description' => 'Manage static pages'],
            ['name' => 'Manage Homepage', 'slug' => 'cms.homepage', 'module' => 'cms', 'description' => 'Configure homepage sections'],

            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports', 'description' => 'View sales reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'reports', 'description' => 'Export reports'],

            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings', 'description' => 'View system settings'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'module' => 'settings', 'description' => 'Update system settings'],

            ['name' => 'View Activity Logs', 'slug' => 'logs.activity', 'module' => 'logs', 'description' => 'View activity logs'],
            ['name' => 'View Order Logs', 'slug' => 'logs.orders', 'module' => 'logs', 'description' => 'View order history logs'],

            ['name' => 'View Notifications', 'slug' => 'notifications.view', 'module' => 'notifications', 'description' => 'View notifications'],
            ['name' => 'Manage Notifications', 'slug' => 'notifications.manage', 'module' => 'notifications', 'description' => 'Manage notification settings'],
        ];

        foreach ($permissions as &$permission) {
            $permission['created_at'] = $now;
            $permission['updated_at'] = $now;
        }

        DB::table('permissions')->insert($permissions);

        $allPermissions = DB::table('permissions')->pluck('id')->toArray();

        $superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
        foreach ($allPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $superAdminRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        $adminExcluded = ['admins.create', 'admins.delete', 'admins.roles'];
        $adminPermissions = DB::table('permissions')
            ->whereNotIn('slug', $adminExcluded)
            ->pluck('id')
            ->toArray();

        foreach ($adminPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $adminRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $managerRole = DB::table('roles')->where('slug', 'manager')->first();
        $managerPermissions = DB::table('permissions')
            ->whereIn('module', ['dashboard', 'products', 'categories', 'collections', 'inventory', 'orders', 'promotions', 'reports'])
            ->pluck('id')
            ->toArray();

        foreach ($managerPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $managerRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $supportRole = DB::table('roles')->where('slug', 'support')->first();
        $supportPermissions = DB::table('permissions')
            ->whereIn('slug', [
                'dashboard.view', 'orders.view', 'orders.process', 'payments.view', 
                'payments.verify', 'payments.refund', 'users.view', 'users.edit',
                'reviews.view', 'reviews.moderate', 'notifications.view'
            ])
            ->pluck('id')
            ->toArray();

        foreach ($supportPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $supportRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $contentRole = DB::table('roles')->where('slug', 'content-manager')->first();
        $contentPermissions = DB::table('permissions')
            ->whereIn('module', ['dashboard', 'cms', 'reviews'])
            ->pluck('id')
            ->toArray();

        foreach ($contentPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $contentRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $inventoryRole = DB::table('roles')->where('slug', 'inventory')->first();
        $inventoryPermissions = DB::table('permissions')
            ->whereIn('slug', [
                'dashboard.view', 'products.view', 'products.edit',
                'inventory.view', 'inventory.manage', 'inventory.logs'
            ])
            ->pluck('id')
            ->toArray();

        foreach ($inventoryPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $inventoryRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
