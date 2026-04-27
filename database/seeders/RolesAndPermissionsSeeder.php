<?php

namespace Database\Seeders;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedRolesAndPermissions();
    }

    protected function seedRolesAndPermissions(): void
    {
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.manage',
            'roles.view',
            'roles.manage',
            'permissions.view',
            'permissions.assign',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.manage',
            'products.publish',
            'categories.view',
            'categories.manage',
            'bundles.view',
            'bundles.manage',
            'customers.view',
            'customers.update',
            'customers.delete',
            'employees.view',
            'employees.manage',
            'inventory.view',
            'inventory.manage',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.manage',
            'invoices.view',
            'invoices.manage',
            'payments.view',
            'payments.manage',
            'shipments.view',
            'shipments.manage',
            'bookings.view',
            'bookings.manage',
            'bookings.approve',
            'designers.view',
            'designers.manage',
            'design_services.view',
            'design_services.manage',
            'employees.view',
            'employees.manage',
            'discounts.view',
            'discounts.manage',
            'settings.view',
            'settings.manage',
            'lookups.view',
            'lookups.manage',
            'shipping_methods.view',
            'shipping_methods.manage',
            'horizon.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $rolePermissions = [
            'super_admin' => null,
            'admin' => [
                'users.manage',
                'roles.manage',
                'permissions.assign',
                'products.manage',
                'products.publish',
                'categories.manage',
                'bundles.manage',
                'customers.view',
                'customers.update',
                'customers.delete',
                'employees.manage',
                'inventory.manage',
                'orders.view',
                'orders.create',
                'orders.update',
                'orders.manage',
                'shipments.view',
                'shipments.manage',
                'invoices.manage',
                'payments.manage',
                'bookings.manage',
                'bookings.approve',
                'designers.view',
                'design_services.view',
                'design_services.manage',
                'employees.manage',
                'discounts.view',
                'discounts.manage',
                'settings.manage',
                'lookups.manage',
                'shipping_methods.view',
                'shipping_methods.manage',
                'horizon.view',
            ],
            'store_manager' => [
                'products.view',
                'categories.view',
                'bundles.view',
                'customers.view',
                'customers.update',
                'inventory.view',
                'inventory.manage',
                'orders.view',
                'orders.create',
                'orders.update',
                'orders.manage',
                'shipments.view',
                'shipments.manage',
                'bookings.view',
                'bookings.manage',
                'designers.view',
                'design_services.view',
            ],
            'warehouse_staff' => [
                'products.view',
                'inventory.view',
                'inventory.manage',
                'orders.view',
                'shipments.view',
                'shipments.manage',
            ],
            'support' => [
                'customers.view',
                'customers.update',
                'products.view',
                'orders.view',
                'bookings.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($perms === null) {
                $role->givePermissionTo(Permission::pluck('name'));
            } else {
                $role->givePermissionTo($perms);
            }
        }

        $this->command->info('Created ' . count($rolePermissions) . ' roles.');
    }
}
