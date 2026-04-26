<?php

namespace App\Models\Customer;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity;

    protected $table = 'customers';

    public $incrementing = false;

    protected $casts = [
        'total_spent' => 'decimal:2',
        'address_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddress(): string
    {
        if (!empty($this->address_data['full_address'])) {
            return $this->address_data['full_address'];
        }

        $parts = [
            $this->address_data['street'] ?? null,
            $this->ward_name,
            $this->province_name,
        ];

        return implode(', ', array_filter($parts));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'full_name',
                'phone',
                'province_code',
                'ward_code',
                'province_name',
                'ward_name',
                'address_data',
                'total_spent'
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Customer {$eventName}");
    }
}
