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

            ['name' => 'bundles.view', 'description' => 'View product bundles'],
            ['name' => 'bundles.manage', 'description' => 'Manage product bundles'],

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

            // Commerce
            ['name' => 'orders.view', 'description' => 'View orders'],
            ['name' => 'orders.create', 'description' => 'Create orders'],
            ['name' => 'orders.update', 'description' => 'Update order status'],
            ['name' => 'orders.delete', 'description' => 'Delete orders'],
            ['name' => 'orders.force_delete', 'description' => 'Permanently delete orders'],
            ['name' => 'orders.restore', 'description' => 'Restore deleted orders'],
            ['name' => 'orders.manage', 'description' => 'Manage all order operations'],

            ['name' => 'shipping_methods.view', 'description' => 'View shipping methods'],
            ['name' => 'shipping_methods.manage', 'description' => 'Manage shipping methods'],

            // Finance
            ['name' => 'invoices.view', 'description' => 'View invoices'],
            ['name' => 'invoices.create', 'description' => 'Create invoices'],
            ['name' => 'invoices.update', 'description' => 'Update invoices'],
            ['name' => 'invoices.delete', 'description' => 'Delete invoices'],
            ['name' => 'invoices.force_delete', 'description' => 'Permanently delete invoices'],
            ['name' => 'invoices.manage', 'description' => 'Manage all invoice operations'],

            ['name' => 'payments.view', 'description' => 'View payments'],
            ['name' => 'payments.create', 'description' => 'Create payments'],
            ['name' => 'payments.update', 'description' => 'Update payments'],
            ['name' => 'payments.delete', 'description' => 'Delete payments'],
            ['name' => 'payments.force_delete', 'description' => 'Permanently delete payments'],
            ['name' => 'payments.manage', 'description' => 'Manage all payment operations'],

            // Fulfillment
            ['name' => 'shipments.view', 'description' => 'View shipments'],
            ['name' => 'shipments.create', 'description' => 'Create shipments'],
            ['name' => 'shipments.update', 'description' => 'Update shipments'],
            ['name' => 'shipments.delete', 'description' => 'Delete shipments'],
            ['name' => 'shipments.restore', 'description' => 'Restore shipments'],
            ['name' => 'shipments.force_delete', 'description' => 'Permanently delete shipments'],
            ['name' => 'shipments.manage', 'description' => 'Manage all shipment operations'],

            // Design Booking
            ['name' => 'bookings.view', 'description' => 'View bookings'],
            ['name' => 'bookings.create', 'description' => 'Create bookings'],
            ['name' => 'bookings.update', 'description' => 'Update bookings'],
            ['name' => 'bookings.delete', 'description' => 'Delete bookings'],
            ['name' => 'bookings.restore', 'description' => 'Restore bookings'],
            ['name' => 'bookings.force_delete', 'description' => 'Permanently delete bookings'],
            ['name' => 'bookings.manage', 'description' => 'Manage all booking operations'],
            ['name' => 'bookings.approve', 'description' => 'Approve bookings'],

            ['name' => 'designers.manage', 'description' => 'Manage designers'],

            ['name' => 'design_services.view', 'description' => 'View design services'],
            ['name' => 'design_services.manage', 'description' => 'Manage design services'],
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
                'bundles.manage',
                'orders.manage',
                'shipping_methods.manage',
                'invoices.manage',
                'payments.manage',
                'shipments.manage',
                'bookings.manage',
                'designers.view',
                'customers.view',
                'customers.update',
                'customers.delete',
                'vendors.manage',
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
                'bundles.manage',
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
                'bundles.view',
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
