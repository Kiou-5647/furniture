<?php

namespace Database\Seeders;

use App\Models\Hr\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDepartments();
    }

    protected function seedDepartments(): void
    {
        foreach (
            [
                ['name' => 'Phòng Kinh doanh', 'code' => 'SALES', 'is_active' => true],
                ['name' => 'Phòng Kho vận', 'code' => 'WAREHOUSE', 'is_active' => true],
                ['name' => 'Phòng Thiết kế', 'code' => 'DESIGN', 'is_active' => true],
                ['name' => 'Phòng Hỗ trợ', 'code' => 'SUPPORT', 'is_active' => true],
            ] as $d
        ) {
            Department::firstOrCreate(['code' => $d['code']], $d);
        }

        $this->command->info('Created 4 departments.');
    }
}
