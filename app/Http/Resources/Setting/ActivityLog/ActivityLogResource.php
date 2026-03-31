<?php

namespace App\Http\Resources\Setting\ActivityLog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'description' => $this->description,
            'causer' => $this->causer ? [
                'id' => $this->causer->id,
                'name' => $this->causer->full_name ?? $this->causer->email ?? 'System',
            ] : null,
            'changes' => [
                'old' => $this->properties->get('old', []),
                'new' => $this->properties->get('attributes', []),
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
