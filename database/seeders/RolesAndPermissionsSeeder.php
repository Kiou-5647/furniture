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

            'Xem đơn hàng',
            'Quản lý đơn hàng',

            'Xem hóa đơn',
            'Quản lý hóa đơn',

            'Xem thanh toán',
            'Quản lý thanh toán',

            'Xem vận chuyển',
            'Quản lý vận chuyển',

            'Xem lịch thiết kế',
            'Quản lý lịch thiết kế',

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
                'Quản lý sản phẩm',
                'Quản lý danh mục',
                'Quản lý gói sản phẩm',
                'Xem khách hàng',
                'Quản lý khách hàng',
                'Xem nhân viên',
                'Quản lý nhân viên',
                'Quản lý kho hàng',
                'Xem đơn hàng',
                'Quản lý đơn hàng',
                'Xem vận chuyển',
                'Quản lý vận chuyển',
                'Quản lý hóa đơn',
                'Quản lý thanh toán',
                'Quản lý lịch thiết kế',
                'Xem nhà thiết kế',
                'Quản lý nhân viên',
                'Xem khuyến mãi',
                'Quản lý khuyến mãi',
                'Cấu hình hệ thống',
                'Quản lý tra cứu',
                'Xem phương thức vận chuyển',
                'Quản lý phương thức vận chuyển',
            ],
            'Quản lý cửa hàng' => [
                'Xem sản phẩm',
                'Xem danh mục',
                'Xem gói sản phẩm',
                'Xem khách hàng',
                'Quản lý khách hàng',
                'Xem kho hàng',
                'Quản lý kho hàng',
                'Xem đơn hàng',
                'Quản lý đơn hàng',
                'Xem vận chuyển',
                'Quản lý vận chuyển',
                'Xem lịch thiết kế',
                'Quản lý lịch thiết kế',
                'Xem nhà thiết kế',
            ],
            'Nhân viên' => null,
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
