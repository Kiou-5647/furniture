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
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'portfolio_url' => $this->portfolio_url,
            'display_name' => $this->display_name,
            'avatar_url' => $this->when($this->hasMedia('avatar'), fn () => $this->getFirstMediaUrl('avatar')),
            'hourly_rate' => $this->hourly_rate,
            'auto_confirm_bookings' => $this->auto_confirm_bookings,
            'is_active' => $this->is_active,
            'availabilities' => $this->whenLoaded('availabilities', fn () => $this->availabilities->mapWithKeys(fn ($a) => [
                $a->day_of_week => [
                    'id' => $a->id,
                    'day_of_week' => $a->day_of_week,
                    'start_time' => $a->start_time,
                    'end_time' => $a->end_time,
                ],
            ])->toArray()
            ),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
