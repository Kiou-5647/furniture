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
            'Xem sản phẩm',
            'Quản lý sản phẩm',

            'Xem danh mục',
            'Quản lý danh mục',

            'Xem bộ sưu tập',
            'Quản lý bộ sưu tập',

            'Xem gói sản phẩm',
            'Quản lý gói sản phẩm',

            'Xem khách hàng',
            'Quản lý khách hàng',

            'Xem nhân viên',
            'Quản lý nhân viên',

            'Xem kho hàng',
            'Quản lý kho hàng',

            // Orders
            'Xem đơn hàng',
            'Tạo đơn hàng',
            'Sửa đơn hàng',
            'Xóa đơn hàng',

            'Xem hóa đơn',
            'Quản lý hóa đơn',

            'Xem thanh toán',
            'Quản lý thanh toán',

            // Shipments
            'Xem đơn vận chuyển',
            'Tạo đơn vận chuyển',
            'Sửa đơn vận chuyển',
            'Xóa đơn vận chuyển',

            // Bookings
            'Xem lịch thiết kế',
            'Tạo lịch thiết kế',
            'Sửa lịch thiết kế',
            'Xóa lịch thiết kế',

            'Xem nhà thiết kế',
            'Quản lý nhà thiết kế',

            'Xem nhà cung cấp',
            'Quản lý nhà cung cấp',

            'Xem phòng ban',
            'Quản lý phòng ban',

            'Xem khuyến mãi',
            'Quản lý khuyến mãi',

            'Xem hoàn tiền',
            'Quản lý hoàn tiền',

            'Cấu hình hệ thống',
            'Quản lý nhật ký',

            'Xem tra cứu',
            'Quản lý tra cứu',

            'Xem phương thức vận chuyển',
            'Quản lý phương thức vận chuyển',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $rolePermissions = [
            'Quản trị viên' => null,
            'Quản lý' => [
                'Xem đơn hàng',
                'Tạo đơn hàng',
                'Sửa đơn hàng',
                'Xóa đơn hàng',

                'Xem đơn vận chuyển',
                'Tạo đơn vận chuyển',
                'Sửa đơn vận chuyển',
                'Xóa đơn vận chuyển',

                'Xem lịch thiết kế',
                'Tạo lịch thiết kế',
                'Sửa lịch thiết kế',
                'Xóa lịch thiết kế',
            ],
            'Quản lý cửa hàng' => [
                'Xem đơn hàng',
                'Tạo đơn hàng',
                'Sửa đơn hàng',
                'Xóa đơn hàng',

                'Tạo đơn vận chuyển',
                'Sửa đơn vận chuyển',
            ],
            'Quản lý kho hàng' => [
                'Xem đơn vận chuyển',
                'Tạo đơn vận chuyển',
                'Sửa đơn vận chuyển',
                'Xóa đơn vận chuyển',
            ],

            'Nhân viên' => [
                'Xem đơn hàng',
                'Tạo đơn hàng',
                'Sửa đơn hàng',

                'Xem đơn vận chuyển',
                'Tạo đơn vận chuyển',
                'Sửa đơn vận chuyển',
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
