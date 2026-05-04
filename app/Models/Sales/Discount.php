<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Discount extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;

    // Unguarded model
    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly([
                'name',
                'type',
                'value',
                'start_at',
                'end_at',
                'is_active',
                'discountable_id',
                'discountable_type'
            ]);
    }

    public static function getDiscountableTypes(): array
    {
        return [
            \App\Models\Product\ProductVariant::class => 'Biến thể sản phẩm',
            \App\Models\Product\Product::class => 'Sản phẩm',
            \App\Models\Product\Category::class => 'Danh mục',
            \App\Models\Product\Collection::class => 'Bộ sưu tập',
            \App\Models\Vendor\Vendor::class => 'Nhà cung cấp',
        ];
    }

    /**
     * Get the parent discountable model (Collection, Category, Vendor, etc.).
     */
    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $date))
            ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $date));
    }
}
