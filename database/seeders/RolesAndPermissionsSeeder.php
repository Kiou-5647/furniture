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
            //Permissions
            ConstantsPermission::PERMISSION['GRANT'],

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

            // Customers
            ConstantsPermission::CUSTOMER['SELECT'],
            ConstantsPermission::CUSTOMER['CREATE'],
            ConstantsPermission::CUSTOMER['UPDATE'],
            ConstantsPermission::CUSTOMER['DELETE'],

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

            // Locations
            ConstantsPermission::LOCATION['SELECT'],
            ConstantsPermission::LOCATION['CREATE'],
            ConstantsPermission::LOCATION['UPDATE'],
            ConstantsPermission::LOCATION['DELETE'],

            // Vendors
            ConstantsPermission::VENDOR['SELECT'],
            ConstantsPermission::VENDOR['CREATE'],
            ConstantsPermission::VENDOR['UPDATE'],
            ConstantsPermission::VENDOR['DELETE'],

            // Discounts
            ConstantsPermission::DISCOUNT['SELECT'],
            ConstantsPermission::DISCOUNT['CREATE'],
            ConstantsPermission::DISCOUNT['UPDATE'],
            ConstantsPermission::DISCOUNT['DELETE'],

            // Refunds
            ConstantsPermission::REFUND['SELECT'],
            ConstantsPermission::REFUND['CREATE'],
            ConstantsPermission::REFUND['UPDATE'],
            ConstantsPermission::REFUND['DELETE'],

            // Lookups
            ConstantsPermission::LOOKUP['SELECT'],
            ConstantsPermission::LOOKUP['CREATE'],
            ConstantsPermission::LOOKUP['UPDATE'],
            ConstantsPermission::LOOKUP['DELETE'],

            // ShippingMethods
            ConstantsPermission::SHIPPING_METHOD['SELECT'],
            ConstantsPermission::SHIPPING_METHOD['CREATE'],
            ConstantsPermission::SHIPPING_METHOD['UPDATE'],
            ConstantsPermission::SHIPPING_METHOD['DELETE'],

            // Stock
            ConstantsPermission::STOCK['SELECT'],
            ConstantsPermission::STOCK['CREATE'],
            ConstantsPermission::STOCK['UPDATE'],
            ConstantsPermission::STOCK['DELETE'],

            ConstantsPermission::SETTING['MANAGE'],
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

                // Customers
                ConstantsPermission::CUSTOMER['SELECT'],
                ConstantsPermission::CUSTOMER['CREATE'],
                ConstantsPermission::CUSTOMER['UPDATE'],
                ConstantsPermission::CUSTOMER['DELETE'],

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

                // Locations
                ConstantsPermission::LOCATION['SELECT'],
                ConstantsPermission::LOCATION['CREATE'],
                ConstantsPermission::LOCATION['UPDATE'],
                ConstantsPermission::LOCATION['DELETE'],

                // Vendors
                ConstantsPermission::VENDOR['SELECT'],
                ConstantsPermission::VENDOR['CREATE'],
                ConstantsPermission::VENDOR['UPDATE'],
                ConstantsPermission::VENDOR['DELETE'],

                // Discounts
                ConstantsPermission::DISCOUNT['SELECT'],
                ConstantsPermission::DISCOUNT['CREATE'],
                ConstantsPermission::DISCOUNT['UPDATE'],
                ConstantsPermission::DISCOUNT['DELETE'],

                // Refunds
                ConstantsPermission::REFUND['SELECT'],
                ConstantsPermission::REFUND['CREATE'],
                ConstantsPermission::REFUND['UPDATE'],
                ConstantsPermission::REFUND['DELETE'],

                // ShippingMethods
                ConstantsPermission::SHIPPING_METHOD['SELECT'],
                ConstantsPermission::SHIPPING_METHOD['CREATE'],
                ConstantsPermission::SHIPPING_METHOD['UPDATE'],
                ConstantsPermission::SHIPPING_METHOD['DELETE'],

                // Stock
                ConstantsPermission::STOCK['SELECT'],
                ConstantsPermission::STOCK['CREATE'],
                ConstantsPermission::STOCK['UPDATE'],
                ConstantsPermission::STOCK['DELETE'],
            ],
            'Quản lý cửa hàng' => [
                // Customers
                ConstantsPermission::CUSTOMER['SELECT'],
                ConstantsPermission::CUSTOMER['CREATE'],
                ConstantsPermission::CUSTOMER['UPDATE'],

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

                // Stock
                ConstantsPermission::STOCK['UPDATE'],

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

                // Discounts
                ConstantsPermission::DISCOUNT['SELECT'],
                ConstantsPermission::DISCOUNT['CREATE'],
                ConstantsPermission::DISCOUNT['UPDATE'],
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

                // Locations
                ConstantsPermission::LOCATION['SELECT'],
                ConstantsPermission::LOCATION['CREATE'],
                ConstantsPermission::LOCATION['UPDATE'],

                // Vendors
                ConstantsPermission::VENDOR['SELECT'],
                ConstantsPermission::VENDOR['CREATE'],
                ConstantsPermission::VENDOR['UPDATE'],

                // ShippingMethods
                ConstantsPermission::SHIPPING_METHOD['SELECT'],

                // Stock
                ConstantsPermission::STOCK['SELECT'],
                ConstantsPermission::STOCK['CREATE'],
                ConstantsPermission::STOCK['UPDATE'],
            ],

            'Nhân viên' => [
                // Customers
                ConstantsPermission::CUSTOMER['SELECT'],

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

                // Discounts
                ConstantsPermission::DISCOUNT['SELECT'],

                // Locations
                ConstantsPermission::LOCATION['SELECT'],

                // Stock
                ConstantsPermission::STOCK['SELECT'],
                ConstantsPermission::STOCK['CREATE'],
                ConstantsPermission::STOCK['UPDATE'],
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
