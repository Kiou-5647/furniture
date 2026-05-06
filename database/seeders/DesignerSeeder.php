<?php

namespace Database\Seeders;

use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use Illuminate\Database\Seeder;

class DesignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDesigners();
    }

    protected function seedDesigners(): void
    {
        $emp = Employee::first();
        if ($emp) {
            Designer::firstOrCreate(
                ['user_id' => $emp->user_id],
                [
                    'user_id' => $emp->user_id,
                    'employee_id' => $emp->id,
                    'full_name' => $emp->full_name,
                    'phone' => '090' . rand(1000000, 9999999),
                    'hourly_rate' => 500000,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Created 1 designer');
    }
}
