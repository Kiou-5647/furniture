<?php

namespace App\Http\Resources\Employee\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'is_active' => $this->user->is_active,
                'email_verified_at' => $this->user->email_verified_at?->format('d/m/Y H:i'),
                'roles' => $this->user->getRoleNames(),
                'permissions' => $this->user->getAllPermissions()->pluck('name'),
            ]),
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'code' => $this->department->code,
            ]),
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'store_location_id' => $this->store_location_id,
            'warehouse_location_id' => $this->warehouse_location_id,
            'avatar_url' => $this->when($this->hasMedia('avatar'), fn() => $this->getFirstMediaUrl('avatar')),
            'hire_date' => $this->hire_date?->format('d/m/Y'),
            'termination_date' => $this->termination_date?->format('d/m/Y'),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
