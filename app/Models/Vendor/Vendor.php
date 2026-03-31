<?php

namespace App\Models\Vendor;

use App\Models\Employee\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Vendor extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'vendors';

    public $incrementing = false;

    protected $casts = [
        'api_credentials' => 'array',
        'shipping_regions' => 'array',
        'tags' => 'array',
        'minimum_order_amount' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'is_active' => 'boolean',
        'is_preferred' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }

    public function vendorUsers(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'is_active',
                'is_preferred',
                'verified_at',
                'rating',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Vendor {$eventName}");
    }
}
