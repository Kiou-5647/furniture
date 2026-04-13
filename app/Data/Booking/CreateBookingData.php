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
        public string $designer_id,
        public string $service_id,
        public string $date,
        public int $start_hour,
        public int $end_hour,
        public ?string $deadline_at = null,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            customer_id: $request->input('customer_id') ?: null,
            customer_name: $request->input('customer_name') ?: null,
            customer_email: $request->input('customer_email') ?: null,
            customer_phone: $request->input('customer_phone') ?: null,
            designer_id: $request->string('designer_id'),
            service_id: $request->string('service_id'),
            date: $request->string('date'),
            start_hour: $request->integer('start_hour'),
            end_hour: $request->integer('end_hour'),
            deadline_at: $request->input('deadline_at') ?: null,
            notes: $request->input('notes') ?: null,
        );
    }
}
