<?php

namespace App\Models\Employee;

use App\Models\Auth\User;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity;

    protected $table = 'employees';

    public $incrementing = false;

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function acceptedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'accepted_by');
    }

    public function managedLocations(): HasMany
    {
        return $this->hasMany(Location::class, 'manager_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'performed_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['full_name', 'phone', 'department_id', 'hire_date', 'termination_date'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
