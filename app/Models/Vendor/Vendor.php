<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Vendor extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'vendors';

    public $incrementing = false;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Vendor {$eventName}");
    }

    public function getFullAddress(): string
    {
        $parts = [
            $this->street ?? null,
            $this->ward_name,
            $this->province_name,
        ];

        $filteredParts = array_filter($parts);

        return implode(', ', $filteredParts) ?: '—';
    }

    public function discounts()
    {
        return $this->morphMany(\App\Models\Sales\Discount::class, 'discountable');
    }

    // Relationship to Products (Inverse)
    public function products()
    {
        return $this->hasMany(\App\Models\Product\Product::class);
    }
}
