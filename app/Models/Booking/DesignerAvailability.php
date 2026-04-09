<?php

namespace App\Models\Booking;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignerAvailability extends Model
{
    use HasUuids;

    protected $table = 'designer_availabilities';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }
}
