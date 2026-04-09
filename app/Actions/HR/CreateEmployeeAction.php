<?php

namespace App\Actions\HR;

use App\Data\HR\CreateEmployeeData;
use App\Enums\UserType;
use App\Mail\EmployeeWelcomeEmail;
use App\Models\Auth\User;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateEmployeeAction
{
    public function execute(CreateEmployeeData $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $plainPassword = Str::random(16);

            $user = User::create([
                'type' => UserType::Employee,
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($plainPassword),
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'department_id' => $data->department_id,
                'full_name' => $data->full_name,
                'phone' => $data->phone,
                'hire_date' => $data->hire_date ?: now()->format('Y-m-d'),
            ]);

            Mail::to($user->email)->send(
                new EmployeeWelcomeEmail(
                    name: $data->full_name,
                    email: $user->email,
                    password: $plainPassword,
                    loginUrl: route('login'),
                )
            );

            return $employee->load(['user', 'department']);
        });
    }
}
