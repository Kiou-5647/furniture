<?php

namespace Database\Seeders;

use App\Models\Booking\DesignService;
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
                    'auto_confirm_bookings' => false,
                    'is_active' => true,
                ]
            );
        }

        DesignService::firstOrCreate(
            ['name' => 'Tư vấn thiết kế nội thất'],
            [
                'name' => 'Tư vấn thiết kế nội thất',
                'type' => 'consultation',
                'is_schedule_blocking' => true,
                'base_price' => 1000000,
                'deposit_percentage' => 30,
                'estimated_hours' => 1,
            ]
        );

        $this->command->info('Created 1 designer + 1 design service.');
    }
}
