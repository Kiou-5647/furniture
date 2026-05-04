<?php

namespace App\Services\Inventory;

use App\Data\Inventory\StockTransferFilterData;
use App\Enums\StockTransferStatus;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use App\Support\CacheKeys;
use App\Support\CacheTag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StockTransferService
{
    public function getFiltered(StockTransferFilterData $filter): LengthAwarePaginator
    {
        $user = Auth::user();
        $employee = $user?->employee;

        return StockTransfer::query()
            ->with(['fromLocation:id,code,name', 'toLocation:id,code,name', 'requestedBy:id,full_name'])
            ->withCount('items')
            ->when(!$user->hasAnyRole(['Quản trị viên', 'Quản lý']), function ($q) use ($employee) {
                $storeId = $employee?->store_location_id;
                $warehouseId = $employee?->warehouse_location_id;

                return $q->where(function ($sub) use ($storeId, $warehouseId) {
                    $sub->where('from_location_id', $storeId)
                        ->orWhere('from_location_id', $warehouseId)
                        ->orWhere('to_location_id', $storeId)
                        ->orWhere('to_location_id', $warehouseId);
                });
            })
            ->when($filter->status, fn($q) => $q->byStatus(StockTransferStatus::from($filter->status)))
            ->when($filter->from_location_id, fn($q) => $q->byFromLocation($filter->from_location_id))
            ->when($filter->to_location_id, fn($q) => $q->byToLocation($filter->to_location_id))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->when($filter->date_from && $filter->date_to, fn($q) => $q->byDateRange($filter->date_from, $filter->date_to))
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getById(string $id): StockTransfer
    {
        return StockTransfer::query()
            ->with([
                'fromLocation:id,code,name,type',
                'toLocation:id,code,name,type',
                'requestedBy:id,full_name',
                'receivedBy:id,full_name',
                'items.variant:id,sku,name,product_id',
                'items.variant.product:id,name',
            ])
            ->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return StockTransferStatus::options();
    }

    public function getLocationOptions(): array
    {
        return Cache::tags([CacheTag::Locations->value])
            ->remember(CacheTag::Locations->key('transfer_options'), CacheKeys::TTL, fn() => Location::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'type'])
                ->map(fn(Location $location) => [
                    'id' => $location->id,
                    'label' => "{$location->code} - {$location->name}",
                    'type' => $location->type->value,
                ])
                ->toArray());
    }
}
