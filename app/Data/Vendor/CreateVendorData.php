<?php

namespace App\Data\Vendor;

use Illuminate\Http\Request;

readonly class CreateVendorData
{
    public function __construct(
        public string $name,
        public ?string $contact_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $website = null,
        public ?string $province_code = null,
        public ?string $ward_code = null,
        public ?string $street = null,
        public ?string $bank_name = null,
        public ?string $bank_account_number = null,
        public ?string $bank_account_holder = null,
        public bool $is_active = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            contact_name: $request->string('contact_name')->toString() ?: null,
            email: $request->string('email')->toString() ?: null,
            phone: $request->string('phone')->toString() ?: null,
            website: $request->string('website')->toString() ?: null,
            province_code: $request->string('province_code')->toString() ?: null,
            ward_code: $request->string('ward_code')->toString() ?: null,
            street: $request->string('street')->toString(),
            bank_name: $request->string('bank_name')->toString() ?: null,
            bank_account_number: $request->string('bank_account_number')->toString() ?: null,
            bank_account_holder: $request->string('bank_account_holder')->toString() ?: null,
            is_active: $request->boolean('is_active', true),
        );
    }

    /**
     * Convert DTO to array for model mass assignment.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'province_code' => $this->province_code,
            'ward_code' => $this->ward_code,
            'street' => $this->street,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_holder' => $this->bank_account_holder,
            'is_active' => $this->is_active,
        ];
    }
}
