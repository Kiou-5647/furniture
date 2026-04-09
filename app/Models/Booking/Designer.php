<?php

namespace App\Models\Booking;

use App\Models\Auth\User;
use App\Models\Employee\Employee;
use App\Models\Vendor\VendorUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designer extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'designers';

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'auto_confirm_bookings' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(VendorUser::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(DesignerAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->employee?->full_name ?? $this->vendorUser?->full_name ?? 'Unknown';
    }

    public function isHostDesigner(): bool
    {
        return $this->employee_id !== null;
    }

    public function isVendorDesigner(): bool
    {
        return $this->vendor_user_id !== null;
    }
}
