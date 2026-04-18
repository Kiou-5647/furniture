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
        $store = Location::where('code', 'RT-001')->first();
        $salesDept = Department::where('code', 'SALES')->first();

        $data = [
            ['email' => 'admin@furniture.com', 'name' => 'Quản trị viên', 'full_name' => 'Quản trị viên', 'role' => 'super_admin', 'dept' => $salesDept?->id, 'loc' => $store?->id],
            ['email' => 'manager@furniture.com', 'name' => 'Lan', 'full_name' => 'Nguyễn Thị Lan', 'role' => 'store_manager', 'dept' => $salesDept?->id, 'loc' => $store?->id],
            ['email' => 'warehouse1@furniture.com', 'name' => 'Hùng', 'full_name' => 'Trần Văn Hùng', 'role' => 'warehouse_staff', 'dept' => null, 'loc' => null],
            ['email' => 'support1@furniture.com', 'name' => 'Mai', 'full_name' => 'Lê Thị Mai', 'role' => 'support', 'dept' => null, 'loc' => null],
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

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $d['full_name'],
                    'phone' => '090' . rand(1000000, 9999999),
                    'department_id' => $d['dept'],
                    'location_id' => $d['loc'],
                    'hire_date' => now()->subMonths(rand(3, 24)),
                ]
            );
        }

        $this->command->info('Created ' . count($data) . ' employees.');
    }
}
