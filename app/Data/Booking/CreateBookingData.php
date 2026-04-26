<?php

namespace App\Data\Booking;

use Illuminate\Http\Request;

readonly class CreateBookingData
{
    public function __construct(
        public ?string $customer_id,
        public ?string $customer_name,
        public ?string $customer_email,
        public ?string $customer_phone,

        public ?string $province_code,
        public ?string $ward_code,
        public ?string $province_name,
        public ?string $ward_name,
        public ?string $street,

        public string $designer_id,
        public string $date,
        public string $start_time,
        public int $duration,
        public ?string $notes,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            customer_id: $request->input('customer_id'),
            customer_name: $request->input('customer_name'),
            customer_email: $request->input('customer_email'),
            customer_phone: $request->input('customer_phone'),

            // Map the missing address fields
            province_code: $request->input('province_code'),
            ward_code: $request->input('ward_code'),
            province_name: $request->input('province_name'),
            ward_name: $request->input('ward_name'),
            street: $request->input('street'),

            designer_id: $request->string('designer_id'),
            date: $request->string('date'),
            start_time: $request->string('start_time'),
            duration: $request->integer('duration'),
            notes: $request->input('notes'),
        );
    }
}
