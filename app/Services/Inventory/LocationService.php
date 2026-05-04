<?php

namespace App\Services\Inventory;

use App\Data\Inventory\LocationFilterData;
use App\Data\Inventory\LocationInventoryFilterData;
use App\Enums\LocationType;
use App\Enums\UserType;
use App\Models\Auth\User;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationService
{
    public function getFiltered(LocationFilterData $filter): LengthAwarePaginator
    {
        return Location::query()
            ->with(['manager'])
            ->withCount('inventories')
            ->when($filter->type, fn($q) => $q->where('type', $filter->type))
            ->when(! is_null($filter->is_active), fn($q) => $q->where('is_active', $filter->is_active))
            ->when($filter->search, fn($q) => $q->where('name', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(LocationFilterData $filter): LengthAwarePaginator
    {
        return Location::onlyTrashed()
            ->with(['manager'])
            ->withCount('inventories')
            ->when($filter->type, fn($q) => $q->where('type', $filter->type))
            ->when($filter->search, fn($q) => $q->where('name', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function create(array $data): Location
    {
        return Location::create([
            'code' => Location::generateCode($data['type']),
            'name' => $data['name'],
            'type' => $data['type'],
            'street' => $data['street'],
            'province_code' => $data['province_code'] ?? null,
            'province_name' => $data['province_name'] ?? null,
            'ward_code' => $data['ward_code'] ?? null,
            'ward_name' => $data['ward_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Location $location, array $data): Location
    {
        $location->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'street' => $data['street'],
            'province_code' => $data['province_code'] ?? null,
            'province_name' => $data['province_name'] ?? null,
            'ward_code' => $data['ward_code'] ?? null,
            'ward_name' => $data['ward_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $location;
    }

    public function getTypeOptions(): array
    {
        return LocationType::options();
    }

    public function getInventoryByLocation(Location $location, LocationInventoryFilterData $filter): LengthAwarePaginator
    {
        $query = Inventory::query()
            ->where('location_id', $location->id)
            ->with(['variant', 'variant.product']);

        $orderBy = $filter->order_by ?? 'quantity';
        $direction = $filter->order_direction ?? 'desc';

        if ($orderBy === 'product_name') {
            $query->join('product_variants', 'inventories.variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->select('inventories.*')
                ->orderBy('products.name', $direction);
        } elseif ($orderBy === 'total_value') {
            $query->orderBy(\Illuminate\Support\Facades\DB::raw('quantity * cost_per_unit'), $direction);
        } else {
            $query->orderBy($orderBy, $direction);
        }

        return $query->when($filter->search, function ($q) use ($filter) {
            $q->where(function ($sq) use ($filter) {
                $sq->whereHas('variant.product', fn($pq) => $pq->where('name', 'ilike', "%{$filter->search}%"))
                    ->orWhereHas('variant', fn($vq) => $vq->where('name', 'ilike', "%{$filter->search}%")
                        ->orWhere('sku', 'ilike', "%{$filter->search}%"));
            });
        })
            ->paginate($filter->per_page ?? 12);
    }

    public function getLocationStats(Location $location): array
    {
        $statsQuery = \App\Models\Inventory\Inventory::query()
            ->where('location_id', $location->id);

        return [
            'total_sku' => $statsQuery->count(),
            'total_quantity' => $statsQuery->sum('quantity'),
            'total_value' => $statsQuery->sum(\Illuminate\Support\Facades\DB::raw('quantity * cost_per_unit')),
            'low_stock_count' => $statsQuery->where('quantity', '<=', 5)->count(),
        ];
    }

    public function getManagerOptions(): Collection
    {
        return User::query()
            ->where('type', UserType::Employee)
            ->where('is_active', true)
            ->with('employee:id,user_id,full_name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->employee?->id,
                'label' => $user->employee?->full_name ?? $user->name,
            ])
            ->filter(fn($item) => $item['id'] !== null)
            ->values();
    }
}
