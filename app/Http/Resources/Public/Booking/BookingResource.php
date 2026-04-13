<?php

namespace App\Http\Resources\Public\Booking;

use App\Http\Resources\Employee\Booking\BookingResource as BaseBookingResource;
use Illuminate\Http\Request;

class BookingResource extends BaseBookingResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        unset($data['accepted_by'], $data['can_confirm'], $data['can_cancel']);

        $data['can_pay_deposit'] = $this->resource->canPayDeposit()
            && $this->resource->customer_id === $request->user()?->id;

        return $data;
    }
}
