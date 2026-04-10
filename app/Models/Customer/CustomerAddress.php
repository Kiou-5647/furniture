<?php

namespace App\Models\Customer;

use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class CustomerAddress extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'customer_addresses';

    public $incrementing = false;

    protected $casts = [
        'address_data' => 'array',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type',
                'delivery_instructions',
                'province_name',
                'ward_name',
                'address_data',
                'is_default',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function getFullAddress(): string
    {
        $parts = [
            $this->address_data['street'] ?? null,
            $this->ward_name,
            $this->province_name,
        ];

        return implode(', ', array_filter($parts));
    }
}
