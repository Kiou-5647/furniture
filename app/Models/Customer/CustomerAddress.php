<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'customer_addresses';

    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'type',
        'delivery_instructions',
        'province_code',
        'ward_code',
        'province_name',
        'ward_name',
        'address_data',
        'is_default',
    ];

    protected $casts = [
        'address_data' => 'array',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
