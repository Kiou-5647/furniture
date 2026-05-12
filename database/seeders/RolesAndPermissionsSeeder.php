<?php

namespace Database\Seeders;

use App\Constants\Permission as ConstantsPermission;
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
            // Products
            ConstantsPermission::PRODUCT['SELECT'],
            ConstantsPermission::PRODUCT['CREATE'],
            ConstantsPermission::PRODUCT['UPDATE'],
            ConstantsPermission::PRODUCT['DELETE'],

            // Categories
            ConstantsPermission::CATEGORY['SELECT'],
            ConstantsPermission::CATEGORY['CREATE'],
            ConstantsPermission::CATEGORY['UPDATE'],
            ConstantsPermission::CATEGORY['DELETE'],

            // Collections
            ConstantsPermission::COLLECTION['SELECT'],
            ConstantsPermission::COLLECTION['CREATE'],
            ConstantsPermission::COLLECTION['UPDATE'],
            ConstantsPermission::COLLECTION['DELETE'],

            // Bundles
            ConstantsPermission::BUNDLE['SELECT'],
            ConstantsPermission::BUNDLE['CREATE'],
            ConstantsPermission::BUNDLE['UPDATE'],
            ConstantsPermission::BUNDLE['DELETE'],

            'Xem khách hàng',
            'Quản lý khách hàng',

            // Employees
            ConstantsPermission::EMPLOYEE['SELECT'],
            ConstantsPermission::EMPLOYEE['CREATE'],
            ConstantsPermission::EMPLOYEE['UPDATE'],
            ConstantsPermission::EMPLOYEE['DELETE'],

            // Departments
            ConstantsPermission::DEPARTMENT['SELECT'],
            ConstantsPermission::DEPARTMENT['CREATE'],
            ConstantsPermission::DEPARTMENT['UPDATE'],
            ConstantsPermission::DEPARTMENT['DELETE'],

            // Designers
            ConstantsPermission::DESIGNER['SELECT'],
            ConstantsPermission::DESIGNER['CREATE'],
            ConstantsPermission::DESIGNER['UPDATE'],
            ConstantsPermission::DESIGNER['DELETE'],

            // Orders
            ConstantsPermission::ORDER['SELECT'],
            ConstantsPermission::ORDER['CREATE'],
            ConstantsPermission::ORDER['UPDATE'],
            ConstantsPermission::ORDER['DELETE'],

            // Invoices
            ConstantsPermission::INVOICE['SELECT'],
            ConstantsPermission::INVOICE['CREATE'],
            ConstantsPermission::INVOICE['UPDATE'],
            ConstantsPermission::INVOICE['DELETE'],

            // Payments
            ConstantsPermission::PAYMENT['SELECT'],

            // Shipments
            ConstantsPermission::SHIPMENT['SELECT'],
            ConstantsPermission::SHIPMENT['CREATE'],
            ConstantsPermission::SHIPMENT['UPDATE'],
            ConstantsPermission::SHIPMENT['DELETE'],

            // Bookings
            ConstantsPermission::BOOKING['SELECT'],
            ConstantsPermission::BOOKING['CREATE'],
            ConstantsPermission::BOOKING['UPDATE'],
            ConstantsPermission::BOOKING['DELETE'],

            'Xem kho hàng',
            'Quản lý kho hàng',

            'Xem nhà cung cấp',
            'Quản lý nhà cung cấp',

            // Discounts
            ConstantsPermission::DISCOUNT['SELECT'],
            ConstantsPermission::DISCOUNT['CREATE'],
            ConstantsPermission::DISCOUNT['UPDATE'],
            ConstantsPermission::DISCOUNT['DELETE'],

            'Xem hoàn tiền',
            'Quản lý hoàn tiền',

            'Cấu hình hệ thống',

            // Lookups
            ConstantsPermission::LOOKUP['SELECT'],
            ConstantsPermission::LOOKUP['CREATE'],
            ConstantsPermission::LOOKUP['UPDATE'],
            ConstantsPermission::LOOKUP['DELETE'],

            'Xem phương thức vận chuyển',
            'Quản lý phương thức vận chuyển',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $rolePermissions = [
            'Quản trị viên' => null,
            'Quản lý' => [
                // Departments
                ConstantsPermission::DEPARTMENT['SELECT'],
                ConstantsPermission::DEPARTMENT['CREATE'],
                ConstantsPermission::DEPARTMENT['UPDATE'],

                // Employees
                ConstantsPermission::EMPLOYEE['SELECT'],
                ConstantsPermission::EMPLOYEE['CREATE'],
                ConstantsPermission::EMPLOYEE['UPDATE'],

                // Designers
                ConstantsPermission::DESIGNER['SELECT'],
                ConstantsPermission::DESIGNER['CREATE'],
                ConstantsPermission::DESIGNER['UPDATE'],
                ConstantsPermission::DESIGNER['DELETE'],

                // Categories
                ConstantsPermission::CATEGORY['SELECT'],
                ConstantsPermission::CATEGORY['CREATE'],
                ConstantsPermission::CATEGORY['UPDATE'],
                ConstantsPermission::CATEGORY['DELETE'],

                // Collections
                ConstantsPermission::COLLECTION['SELECT'],
                ConstantsPermission::COLLECTION['CREATE'],
                ConstantsPermission::COLLECTION['UPDATE'],
                ConstantsPermission::COLLECTION['DELETE'],

                // Products
                ConstantsPermission::PRODUCT['SELECT'],
                ConstantsPermission::PRODUCT['CREATE'],
                ConstantsPermission::PRODUCT['UPDATE'],
                ConstantsPermission::PRODUCT['DELETE'],

                // Bundles
                ConstantsPermission::BUNDLE['SELECT'],
                ConstantsPermission::BUNDLE['CREATE'],
                ConstantsPermission::BUNDLE['UPDATE'],
                ConstantsPermission::BUNDLE['DELETE'],

                // Orders
                ConstantsPermission::ORDER['SELECT'],
                ConstantsPermission::ORDER['CREATE'],
                ConstantsPermission::ORDER['UPDATE'],
                ConstantsPermission::ORDER['DELETE'],

                // Invoices
                ConstantsPermission::INVOICE['SELECT'],
                ConstantsPermission::INVOICE['CREATE'],
                ConstantsPermission::INVOICE['UPDATE'],
                ConstantsPermission::INVOICE['DELETE'],

                // Shipments
                ConstantsPermission::SHIPMENT['SELECT'],
                ConstantsPermission::SHIPMENT['CREATE'],
                ConstantsPermission::SHIPMENT['UPDATE'],
                ConstantsPermission::SHIPMENT['DELETE'],

                // Bookings
                ConstantsPermission::BOOKING['SELECT'],
                ConstantsPermission::BOOKING['CREATE'],
                ConstantsPermission::BOOKING['UPDATE'],
                ConstantsPermission::BOOKING['DELETE'],

                // Lookups
                ConstantsPermission::LOOKUP['SELECT'],
                ConstantsPermission::LOOKUP['CREATE'],
                ConstantsPermission::LOOKUP['UPDATE'],
                ConstantsPermission::LOOKUP['DELETE'],
            ],
            'Quản lý cửa hàng' => [
                // Categories
                ConstantsPermission::CATEGORY['SELECT'],
                ConstantsPermission::CATEGORY['CREATE'],
                ConstantsPermission::CATEGORY['UPDATE'],

                // Collections
                ConstantsPermission::COLLECTION['SELECT'],
                ConstantsPermission::COLLECTION['CREATE'],
                ConstantsPermission::COLLECTION['UPDATE'],

                // Products
                ConstantsPermission::PRODUCT['SELECT'],
                ConstantsPermission::PRODUCT['CREATE'],
                ConstantsPermission::PRODUCT['UPDATE'],

                // Bundles
                ConstantsPermission::BUNDLE['SELECT'],
                ConstantsPermission::BUNDLE['CREATE'],
                ConstantsPermission::BUNDLE['UPDATE'],

                // Orders
                ConstantsPermission::ORDER['SELECT'],
                ConstantsPermission::ORDER['CREATE'],
                ConstantsPermission::ORDER['UPDATE'],
                ConstantsPermission::ORDER['DELETE'],

                // Invoices
                ConstantsPermission::INVOICE['SELECT'],
                ConstantsPermission::INVOICE['CREATE'],
                ConstantsPermission::INVOICE['UPDATE'],

                // Shipments
                ConstantsPermission::SHIPMENT['CREATE'],
                ConstantsPermission::SHIPMENT['UPDATE'],

                // Lookups
                ConstantsPermission::LOOKUP['SELECT'],
                ConstantsPermission::LOOKUP['CREATE'],
                ConstantsPermission::LOOKUP['UPDATE'],
            ],
            'Quản lý kho hàng' => [
                // Products
                ConstantsPermission::PRODUCT['SELECT'],

                // Bundles
                ConstantsPermission::BUNDLE['SELECT'],

                // Shipments
                ConstantsPermission::SHIPMENT['SELECT'],
                ConstantsPermission::SHIPMENT['CREATE'],
                ConstantsPermission::SHIPMENT['UPDATE'],
                ConstantsPermission::SHIPMENT['DELETE'],
            ],

            'Nhân viên' => [
                // Categories
                ConstantsPermission::CATEGORY['SELECT'],

                // Collections
                ConstantsPermission::COLLECTION['SELECT'],

                // Products
                ConstantsPermission::PRODUCT['SELECT'],

                // Bundles
                ConstantsPermission::BUNDLE['SELECT'],

                // Orders
                ConstantsPermission::ORDER['SELECT'],
                ConstantsPermission::ORDER['CREATE'],
                ConstantsPermission::ORDER['UPDATE'],

                // Invoices
                ConstantsPermission::INVOICE['SELECT'],
                ConstantsPermission::INVOICE['CREATE'],
                ConstantsPermission::INVOICE['UPDATE'],

                // Shipments
                ConstantsPermission::SHIPMENT['SELECT'],
                ConstantsPermission::SHIPMENT['CREATE'],
                ConstantsPermission::SHIPMENT['UPDATE'],

                // Lookups
                ConstantsPermission::LOOKUP['SELECT'],
            ]
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
