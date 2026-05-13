<?php

namespace App\Data\Customer;

use Illuminate\Http\Request;

readonly class CreateCustomerData
{
    public function __construct(
        public string $full_name,
        public string $email,
        public string $phone,
        public ?string $street,
        public ?string $province_code,
        public ?string $province_name,
        public ?string $ward_code,
        public ?string $ward_name,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            full_name: $request->string('full_name')->toString(),
            email: $request->string('email')->toString(),
            phone: $request->string('phone')->toString(),
            street: $request->string('street')->toString() ?: null,
            province_code: $request->string('province_code')->toString() ?: null,
            province_name: $request->string('province_name')->toString() ?: null,
            ward_code: $request->string('ward_code')->toString() ?: null,
            ward_name: $request->string('ward_name')->toString() ?: null,
        );
    }

    public function toArray(): array
    {
        return [
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'street' => $this->street,
            'province_code' => $this->province_code,
            'province_name' => $this->province_name,
            'ward_code' => $this->ward_code,
            'ward_name' => $this->ward_name,
        ];
    }
}
