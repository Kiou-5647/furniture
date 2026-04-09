<?php

namespace App\Models\Booking;

use App\Enums\DesignServiceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DesignService extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'design_services';

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'deposit_percentage' => 'integer',
            'estimated_minutes' => 'integer',
            'is_schedule_blocking' => 'boolean',
            'type' => DesignServiceType::class,
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
