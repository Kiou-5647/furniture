<?php

namespace App\Models\Inventory;

use App\Enums\LocationType;
use App\Models\Hr\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Location extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'locations';

    protected function casts(): array
    {
        return [
            'type' => LocationType::class,
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'type', 'province_name', 'ward_name', 'phone', 'is_active', 'manager_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Location {$eventName}");
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getFullAddress(): string
    {
        $parts = [
            $this->street ?? null,
            $this->ward_name,
            $this->province_name,
        ];

        return implode(', ', $parts) ?: '—';
    }

    public static function generateCode(string $type): string
    {
        $prefix = match ($type) {
            LocationType::Warehouse->value => 'WH',
            LocationType::Retail->value => 'RT',
            LocationType::Vendor->value => 'VN',
            default => 'LOC',
        };

        $latest = self::where('code', 'like', "LOC-{$prefix}-%")
            ->orWhere('code', 'like', "{$prefix}-%")
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) Str::afterLast($latest->code, '-');
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('LOC-%s-%03d', $prefix, $nextNumber);
    }
}
