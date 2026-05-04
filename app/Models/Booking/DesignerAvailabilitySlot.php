<?php

namespace App\Models\Booking;

use App\Models\Hr\Designer;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;;

class DesignerAvailabilitySlot extends Model
{
    use HasUuids, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $table = 'designer_availability_slots';

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }
}
