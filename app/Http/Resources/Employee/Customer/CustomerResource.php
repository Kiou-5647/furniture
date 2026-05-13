<?php

namespace App\Http\Resources\Employee\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class CustomerResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' =>  [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'is_active' => $this->user->is_active,
            ],
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'province_code' => $this->province_code,
            'province_name' => $this->province_name,
            'ward_code' => $this->ward_code,
            'ward_name' => $this->ward_name,
            'street' => $this->street,
            'address' => $this->getFullAddress(),
            'total_spent' => $this->total_spent,
            'can_update' => Gate::allows('update', $this->resource),
            'can_deactivate' => Gate::allows('delete', $this->resource),
            'avatar_url' => $this->when($this->hasMedia('avatar'), fn() => $this->getFirstMediaUrl('avatar')),
            'created_at' => $this->created_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
            'updated_at' => $this->updated_at?->timezone($request->attributes->get('user_timezone', 'UTC'))->format('d/m/Y-H:i:s'),
        ];
    }
}
