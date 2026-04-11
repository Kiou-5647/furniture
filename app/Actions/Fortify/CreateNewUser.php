<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Mail\EmployeeWelcome;
use App\Models\Auth\User;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // Type is required for admin creation
        Validator::make($input, [
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
            'type' => ['required', 'string', 'in:employee,customer'],
        ])->validate();
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'type' => $input['type'],
        ]);
        match ($input['type']) {
            'customer' => (new CreateCustomerProfile)->create($user, $input),
            'employee' => $this->createEmployeeProfileAndVerify($user, $input),
        };

        return $user;
    }

    /**
     * Create employee profile and auto-verify the user.
     */
    protected function createEmployeeProfileAndVerify(User $user, array $input): Employee
    {
        $employee = (new CreateEmployeeProfile)->create($user, $input);

        // Auto-verify employees
        $user->update([
            'email_verified_at' => now(),
        ]);
        // Send welcome email with password reset instructions
        Mail::to($user->email)->send(new EmployeeWelcome($user));

        return $employee;
    }
}
