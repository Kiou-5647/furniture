<?php

namespace App\Http\Resources\Employee\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
            ]),
            'vendor_user' => $this->whenLoaded('vendorUser', fn () => [
                'id' => $this->vendorUser->id,
                'full_name' => $this->vendorUser->full_name,
            ]),
            'display_name' => $this->display_name,
            'hourly_rate' => $this->hourly_rate,
            'auto_confirm_bookings' => $this->auto_confirm_bookings,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
