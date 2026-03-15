<?php

namespace App\Models\Vendor;

use App\Models\Employee\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendors';

    public $incrementing = false;

    protected $fillable = [
        'verified_by',
        'name',
        'code',
        'contact_name',
        'email',
        'phone',
        'website',
        'webhook_url',
        'address',
        'notes',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'api_credentials',
        'shipping_regions',
        'tags',
        'payment_terms_days',
        'lead_time_days',
        'minimum_order_amount',
        'rating',
        'total_orders',
        'total_revenue',
        'is_active',
        'is_preferred',
        'verified_at',
    ];

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
}
