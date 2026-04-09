<?php

namespace App\Data\Booking;

use Illuminate\Http\Request;

readonly class CreateBookingData
{
    public function __construct(
        public string $customer_id,
        public string $designer_id,
        public string $service_id,
        public ?string $start_at = null,
        public ?string $end_at = null,
        public ?string $deadline_at = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            customer_id: $request->string('customer_id'),
            designer_id: $request->string('designer_id'),
            service_id: $request->string('service_id'),
            start_at: $request->string('start_at')->toString() ?: null,
            end_at: $request->string('end_at')->toString() ?: null,
            deadline_at: $request->string('deadline_at')->toString() ?: null,
        );
    }
}
