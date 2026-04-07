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

        $this->createRoles();
    }

    protected function getPermissions(): array
    {
        return [
            // Users & Access
            ['name' => 'users.view', 'description' => 'View users list'],
            ['name' => 'users.create', 'description' => 'Create new users'],
            ['name' => 'users.update', 'description' => 'Update user information'],
            ['name' => 'users.delete', 'description' => 'Delete users'],
            ['name' => 'users.manage', 'description' => 'Full user management (all-in-one)'],

            ['name' => 'roles.view', 'description' => 'View roles'],
            ['name' => 'roles.manage', 'description' => 'Manage roles'],

            ['name' => 'permissions.view', 'description' => 'View permissions'],
            ['name' => 'permissions.assign', 'description' => 'Assign permissions to roles'],

            ['name' => 'activities.view', 'description' => 'View activity logs'],
            ['name' => 'activities.manage', 'description' => 'Manage activity logs'],

            // System Settings
            ['name' => 'lookups.view', 'description' => 'View lookups'],
            ['name' => 'lookups.manage', 'description' => 'Manage lookups'],

            ['name' => 'settings.view', 'description' => 'View system settings'],
            ['name' => 'settings.manage', 'description' => 'Configure global system settings'],

            ['name' => 'horizon.view', 'description' => 'Access infrastructure queue dashboard'],

            // Core: Products & Taxonomies
            ['name' => 'products.view', 'description' => 'View product details'],
            ['name' => 'products.create', 'description' => 'Create new products'],
            ['name' => 'products.update', 'description' => 'Edit existing products'],
            ['name' => 'products.delete', 'description' => 'Remove products'],
            ['name' => 'products.manage', 'description' => 'Manage all products'],
            ['name' => 'products.publish', 'description' => 'Publish/unpublish products'],

            ['name' => 'categories.view', 'description' => 'View product categories'],
            ['name' => 'categories.manage', 'description' => 'Manage category hierarchy'],

            ['name' => 'collections.view', 'description' => 'View product collections'],
            ['name' => 'collections.manage', 'description' => 'Manage curated collections'],

            // Entities: Customers, Vendors, Employees
            ['name' => 'customers.view', 'description' => 'View customer profiles'],
            ['name' => 'customers.update', 'description' => 'Update customer information'],
            ['name' => 'customers.delete', 'description' => 'Delete customers'],

            ['name' => 'vendors.view', 'description' => 'View vendor profiles'],
            ['name' => 'vendors.create', 'description' => 'Create new vendors'],
            ['name' => 'vendors.update', 'description' => 'Update vendor info'],
            ['name' => 'vendors.delete', 'description' => 'Delete vendors'],
            ['name' => 'vendors.manage', 'description' => 'Manage vendors'],
            ['name' => 'vendors.verify', 'description' => 'Verify vendor status'],

            ['name' => 'employees.view', 'description' => 'View employee list'],
            ['name' => 'employees.manage', 'description' => 'Manage employee profiles and departments'],

            // Inventory
            ['name' => 'inventory.view', 'description' => 'View inventory and stock levels'],
            ['name' => 'inventory.manage', 'description' => 'Manage inventory, locations, and stock transfers'],
        ];
    }

    protected function createRoles(): void
    {
        $rolesPermissions = [
            'super_admin' => null,
            'admin' => [
                'users.manage',
                'roles.manage',
                'permissions.view',
                'permissions.assign',
                'products.manage',
                'categories.manage',
                'collections.manage',
                'customers.view',
                'customers.update',
                'customers.delete',
                'vendors.manage', // Future implementation of vendors.manage
                'employees.manage',
                'settings.manage',
                'lookups.manage',
                'horizon.view',
                'inventory.manage',
            ],
            'head_of_production' => [
                'products.manage',
                'categories.manage',
                'collections.manage',
                'vendors.view',
                'vendors.verify',
                'inventory.manage',
            ],
            'content_manager' => [
                'products.view',
                'products.create',
                'products.update',
                'products.publish',
                'categories.view',
                'collections.view',
            ],
            'vendor_owner' => [
                'vendors.view',
                'vendors.update',
                'products.view',
                'products.create',
                'products.update', // No delete
            ],
            'support' => [
                'customers.view',
                'customers.update',
                'products.view',
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
