<?php

namespace Database\Seeders;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedEmployees();
    }

    protected function seedEmployees(): void
    {
        $store = Location::where('type', 'retail')->first();
        $warehouse = Location::where('type', 'warehouse')->first();
        $salesDept = Department::where('code', 'SALES')->first();

        $data = [
            ['email' => 'admin@furniture.com', 'name' => 'Admin', 'full_name' => 'Quản trị viên Hệ thống', 'role' => 'Quản trị viên', 'dept' => $salesDept?->id, 'loc' => $store?->id, 'type' => 'store'],
            ['email' => 'manager@furniture.com', 'name' => 'Manager', 'full_name' => 'Quản lý Điều hành', 'role' => 'Quản lý', 'dept' => $salesDept?->id, 'loc' => $store?->id, 'type' => 'store'],
            ['email' => 'store.manager@furniture.com', 'name' => 'Store Manager', 'full_name' => 'Quản lý Cửa hàng', 'role' => 'Quản lý cửa hàng', 'dept' => $salesDept?->id, 'loc' => $store?->id, 'type' => 'store'],
            ['email' => 'warehouse.manager@furniture.com', 'name' => 'Warehouse Manager', 'full_name' => 'Quản lý Kho hàng', 'role' => 'Quản lý kho hàng', 'dept' => $salesDept?->id, 'loc' => $warehouse?->id, 'type' => 'warehouse'],
            ['email' => 'staff@furniture.com', 'name' => 'Staff', 'full_name' => 'Nhân viên Vận hành', 'role' => 'Nhân viên', 'dept' => $salesDept?->id, 'loc' => $store?->id, 'type' => 'store'],
        ];

        foreach ($data as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'type' => 'employee',
                    'name' => $d['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::firstWhere('name', $d['role']);
            if ($role && ! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            $employeeData = [
                'full_name' => $d['full_name'],
                'phone' => '090' . rand(1000000, 9999999),
                'department_id' => $d['dept'],
                'hire_date' => now()->subMonths(rand(3, 24)),
            ];

            if ($d['type'] === 'retail') {
                $employeeData['store_location_id'] = $d['loc'];
            } elseif ($d['type'] === 'warehouse') {
                $employeeData['warehouse_location_id'] = $d['loc'];
            }

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                $employeeData
            );
        }

        $this->command->info('Created ' . count($data) . ' employees.');
    }
}
