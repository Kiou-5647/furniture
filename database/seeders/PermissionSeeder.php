<?php

namespace Database\Seeders;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = $this->getPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['description' => $permission['description'] ?? null]
            );
        }

        $this->createSuperAdminRole();
        $this->createRoles();
    }

    protected function getPermissions(): array
    {
        return [
            // Users & Auth
            ['name' => 'users.view', 'description' => 'View users list'],
            ['name' => 'users.create', 'description' => 'Create new users'],
            ['name' => 'users.update', 'description' => 'Update user information'],
            ['name' => 'users.delete', 'description' => 'Delete users'],

            // Roles & Permissions
            ['name' => 'roles.view', 'description' => 'View roles'],
            ['name' => 'roles.create', 'description' => 'Create roles'],
            ['name' => 'roles.update', 'description' => 'Update roles'],
            ['name' => 'roles.delete', 'description' => 'Delete roles'],
            ['name' => 'permissions.view', 'description' => 'View permissions'],
            ['name' => 'permissions.assign', 'description' => 'Assign permissions'],

            // Lookups
            ['name' => 'lookup.view', 'description' => 'View lookups'],
            ['name' => 'lookup.create', 'description' => 'Create lookups'],
            ['name' => 'lookup.update', 'description' => 'Update lookups'],
            ['name' => 'lookup.delete', 'description' => 'Delete lookups'],

            // Products
            ['name' => 'products.view', 'description' => 'View products'],
            ['name' => 'products.create', 'description' => 'Create products'],
            ['name' => 'products.update', 'description' => 'Update products'],
            ['name' => 'products.delete', 'description' => 'Delete products'],
            ['name' => 'products.publish', 'description' => 'Publish/unpublish products'],

            // Categories
            ['name' => 'categories.view', 'description' => 'View categories'],
            ['name' => 'categories.create', 'description' => 'Create categories'],
            ['name' => 'categories.update', 'description' => 'Update categories'],
            ['name' => 'categories.delete', 'description' => 'Delete categories'],

            // Orders
            ['name' => 'orders.view', 'description' => 'View orders'],
            ['name' => 'orders.create', 'description' => 'Create orders'],
            ['name' => 'orders.update', 'description' => 'Update orders'],
            ['name' => 'orders.cancel', 'description' => 'Cancel orders'],
            ['name' => 'orders.refund', 'description' => 'Process refunds'],

            // Inventory
            ['name' => 'inventory.view', 'description' => 'View inventory'],
            ['name' => 'inventory.update', 'description' => 'Update inventory'],
            ['name' => 'inventory.transfer', 'description' => 'Transfer inventory'],
            ['name' => 'inventory.adjust', 'description' => 'Adjust inventory'],

            // Customers
            ['name' => 'customers.view', 'description' => 'View customers'],
            ['name' => 'customers.update', 'description' => 'Update customer info'],
            ['name' => 'customers.delete', 'description' => 'Delete customers'],

            // Vendors
            ['name' => 'vendors.view', 'description' => 'View vendors'],
            ['name' => 'vendors.create', 'description' => 'Create vendors'],
            ['name' => 'vendors.update', 'description' => 'Update vendors'],
            ['name' => 'vendors.delete', 'description' => 'Delete vendors'],
            ['name' => 'vendors.verify', 'description' => 'Verify vendors'],

            // Services
            ['name' => 'services.view', 'description' => 'View services'],
            ['name' => 'services.create', 'description' => 'Create services'],
            ['name' => 'services.update', 'description' => 'Update services'],
            ['name' => 'services.delete', 'description' => 'Delete services'],

            // Bookings
            ['name' => 'bookings.view', 'description' => 'View bookings'],
            ['name' => 'bookings.update', 'description' => 'Update bookings'],
            ['name' => 'bookings.cancel', 'description' => 'Cancel bookings'],

            // Shipping
            ['name' => 'shipping.view', 'description' => 'View shipping'],
            ['name' => 'shipping.update', 'description' => 'Update shipping'],
            ['name' => 'shipping.configure', 'description' => 'Configure shipping'],

            // Reports
            ['name' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'reports.export', 'description' => 'Export reports'],

            // Settings
            ['name' => 'settings.view', 'description' => 'View settings'],
            ['name' => 'settings.update', 'description' => 'Update settings'],

            // Content
            ['name' => 'content.view', 'description' => 'View content'],
            ['name' => 'content.manage', 'description' => 'Manage content'],
        ];
    }

    protected function createSuperAdminRole(): void
    {
        $permissions = $this->getPermissions();
        $role = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        foreach ($permissions as $permission) {
            $role->givePermissionTo($permission['name']);
        }
    }

    protected function createRoles(): void
    {
        $rolesPermissions = [
            'admin' => [
                'users.view', 'users.update',
                'roles.view', 'permissions.view',
                'products.view', 'products.create', 'products.update', 'products.delete', 'products.publish',
                'categories.view', 'categories.create', 'categories.update', 'categories.delete',
                'orders.view', 'orders.create', 'orders.update', 'orders.cancel', 'orders.refund',
                'inventory.view', 'inventory.update', 'inventory.transfer', 'inventory.adjust',
                'customers.view', 'customers.update',
                'vendors.view', 'vendors.create', 'vendors.update', 'vendors.delete', 'vendors.verify',
                'services.view', 'services.create', 'services.update', 'services.delete',
                'bookings.view', 'bookings.update', 'bookings.cancel',
                'shipping.view', 'shipping.update', 'shipping.configure',
                'reports.view', 'reports.export',
                'settings.view', 'settings.update',
                'content.view', 'content.manage',
            ],

            'warehouse_manager' => [
                'inventory.view', 'inventory.update', 'inventory.transfer', 'inventory.adjust',
                'orders.view',
                'shipping.view', 'shipping.update',
            ],

            'content_manager' => [
                'products.view', 'products.create', 'products.update', 'products.publish',
                'categories.view', 'categories.create', 'categories.update', 'categories.delete',
                'services.view', 'services.create', 'services.update', 'services.delete',
                'content.view', 'content.manage',
            ],

            'designer' => [
                'services.view', 'services.create', 'services.update',
                'bookings.view', 'bookings.update',
            ],

            'support' => [
                'orders.view', 'orders.update',
                'customers.view', 'customers.update',
                'products.view',
            ],

            'vip' => [
                'products.view',
                'orders.view', 'orders.create',
                'services.view', 'services.create',
                'bookings.view',
            ],

            'wholesale' => [
                'products.view',
                'orders.view', 'orders.create',
            ],

            'vendor_owner' => [
                'vendors.view', 'vendors.update',
                'products.view', 'products.create', 'products.update',
                'orders.view', 'orders.update',
            ],

            'vendor_user' => [
                'products.view', 'products.create', 'products.update',
                'orders.view',
            ],
        ];

        foreach ($rolesPermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );

            if ($permissions !== null) {
                $role->givePermissionTo($permissions);
            }
        }
    }
}
