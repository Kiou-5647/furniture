<?php

namespace App\Data\Hr;

use Illuminate\Http\Request;

readonly class CreateEmployeeData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $full_name,
        public ?string $phone = null,
        public ?string $department_id = null,
        public ?string $location_id = null,
        public ?string $hire_date = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name'),
            email: $request->string('email'),
            full_name: $request->string('full_name'),
            phone: $request->string('phone')->toString() ?: null,
            department_id: $request->string('department_id')->toString() ?: null,
            location_id: $request->string('location_id')->toString() ?: null,
            hire_date: $request->string('hire_date')->toString() ?: null,
        );
    }
}
