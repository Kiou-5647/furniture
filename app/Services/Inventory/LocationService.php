<?php

namespace App\Services\Inventory;

use App\Data\Inventory\LocationFilterData;
use App\Enums\LocationType;
use App\Enums\UserType;
use App\Models\Auth\User;
use App\Models\Inventory\Location;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as EloquentCollection;

class LocationService
{
    public function getFiltered(LocationFilterData $filter): LengthAwarePaginator
    {
        return Location::query()
            ->with(['manager'])
            ->withCount('inventories')
            ->when($filter->type, fn ($q) => $q->where('type', $filter->type))
            ->when(! is_null($filter->is_active), fn ($q) => $q->where('is_active', $filter->is_active))
            ->when($filter->search, fn ($q) => $q->where('name', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'created_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function getTrashedFiltered(LocationFilterData $filter): LengthAwarePaginator
    {
        return Location::onlyTrashed()
            ->with(['manager'])
            ->withCount('inventories')
            ->when($filter->type, fn ($q) => $q->where('type', $filter->type))
            ->when($filter->search, fn ($q) => $q->where('name', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page ?? 15);
    }

    public function create(array $data): Location
    {
        $addressData = [];
        if (! empty($data['building'])) {
            $addressData['building'] = $data['building'];
        }
        if (! empty($data['address_number'])) {
            $addressData['address_number'] = $data['address_number'];
        }

        return Location::create([
            'code' => Location::generateCode($data['type']),
            'name' => $data['name'],
            'type' => $data['type'],
            'address_data' => $addressData,
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
        $addressData = [];
        if (! empty($data['building'])) {
            $addressData['building'] = $data['building'];
        }
        if (! empty($data['address_number'])) {
            $addressData['address_number'] = $data['address_number'];
        }

        $location->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'address_data' => $addressData,
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

    public function getManagerOptions(): EloquentCollection
    {
        return User::query()
            ->where('type', UserType::Employee)
            ->where('is_active', true)
            ->with('employee:id,user_id,full_name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->employee?->id,
                'label' => $user->employee?->full_name ?? $user->name,
            ])
            ->filter(fn ($item) => $item['id'] !== null)
            ->values();
    }
}
