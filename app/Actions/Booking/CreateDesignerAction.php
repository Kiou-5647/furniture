<?php

namespace App\Actions\Booking;

use App\Enums\UserType;
use App\Models\Auth\User;
use App\Models\Booking\Designer;
use App\Models\Booking\DesignerAvailability;
use App\Models\Employee\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateDesignerAction
{
    public function execute(array $data, ?UploadedFile $avatarFile = null): Designer
    {
        return DB::transaction(function () use ($data, $avatarFile) {
            $availabilities = $data['availabilities'] ?? [];
            unset($data['availabilities']);

            // Determine or create User
            if (isset($data['employee_id'])) {
                // Link to employee's user
                $employee = Employee::findOrFail($data['employee_id']);
                $data['user_id'] = $employee->user_id;
            } else {
                // Freelancer: create new User
                $user = User::create([
                    'type' => UserType::Customer,
                    'name' => $data['full_name'],
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(16)),
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]);

                $data['user_id'] = $user->id;
            }

            $avatarFile = $data['avatar'] ?? null;
            unset($data['avatar']);
            unset($data['email']);

            $designer = Designer::create($data);

            if ($avatarFile instanceof UploadedFile) {
                $designer->addMedia($avatarFile)->toMediaCollection('avatar');
            }

            // Save availabilities
            foreach ($availabilities as $slot) {
                DesignerAvailability::create([
                    'designer_id' => $designer->id,
                    'day_of_week' => $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }

            return $designer->load(['user', 'employee', 'availabilities']);
        });
    }
}
