<?php

namespace App\Models\Inventory;

use App\Enums\StockTransferStatus;
use App\Models\Employee\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class StockTransfer extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'stock_transfers';

    protected function casts(): array
    {
        return [
            'status' => StockTransferStatus::class,
            'received_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['transfer_number', 'status', 'notes', 'received_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Stock transfer {$eventName}");
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function ship(Employee $employee): void
    {
        $this->status = StockTransferStatus::InTransit;
        $this->save();
    }

    public function receive(Employee $employee): void
    {
        $this->status = StockTransferStatus::Received;
        $this->received_by = $employee->id;
        $this->received_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        $this->status = StockTransferStatus::Cancelled;
        $this->save();
    }

    public static function generateTransferNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "TRF-{$date}-";

        $latest = self::where('transfer_number', 'like', "{$prefix}%")
            ->orderBy('transfer_number', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) Str::afterLast($latest->transfer_number, '-');
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }
}
