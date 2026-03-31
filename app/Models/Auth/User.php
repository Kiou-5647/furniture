<?php

namespace App\Models\Auth;

use App\Enums\UserType;
use App\Models\Customer\Customer;
use App\Models\Employee\Employee;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, HasUuids, LogsActivity, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected $table = 'users';

    public $incrementing = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => UserType::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isEmployee(): bool
    {
        return $this->type === UserType::Employee;
    }

    public function isVendor(): bool
    {
        return $this->type === UserType::Vendor;
    }

    public function isCustomer(): bool
    {
        return $this->type === UserType::Customer;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function vendorUser(): HasOne
    {
        return $this->hasOne(VendorUser::class);
    }

    public function vendor(): HasOneThrough
    {
        return $this->hasOneThrough(
            Vendor::class,
            VendorUser::class,
            'user_id',
            'id',
            'id',
            'vendor_id'
        );
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $profile = match ($this->type->value) {
            'employee' => $this->employee,
            'customer' => $this->customer,
            'vendor' => $this->vendorUser,
            default => null,
        };

        return $profile?->getFirstMediaUrl('avatar', 'thumb') ?: null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'type', 'is_active', 'is_verified', 'last_login_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "User account {$eventName}");
    }
}
