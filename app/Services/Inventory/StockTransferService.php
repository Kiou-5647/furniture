<?php

namespace App\Services\Inventory;

use App\Data\Inventory\StockTransferFilterData;
use App\Enums\StockTransferStatus;
use App\Models\Auth\User;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class StockTransferService
{
    protected function applyRoleFilter(Builder $query, User $user): void
    {
        if ($user->hasAnyRole(['Quản trị viên', 'Quản lý'])) {
            return;
        }

        if ($user->isEmployee()) {
            $employee = $user->employee;
            $query->where(function ($q) use ($employee) {
                // From location matches
                $q->where('from_location_id', $employee?->store_location_id)
                    ->orWhere('from_location_id', $employee?->warehouse_location_id)
                    // OR To location matches
                    ->orWhere('to_location_id', $employee?->store_location_id)
                    ->orWhere('to_location_id', $employee?->warehouse_location_id)
                    // OR Manager of from location
                    ->orWhereHas('fromLocation', fn($sub) => $sub->where('manager_id', $employee?->id))
                    // OR Manager of to location
                    ->orWhereHas('toLocation', fn($sub) => $sub->where('manager_id', $employee?->id));
            });
            return;
        }

        $query->whereRaw('1 = 0');
    }

    public function getFiltered(StockTransferFilterData $filter, User $user): LengthAwarePaginator
    {
        $query = StockTransfer::query()
            ->with(['fromLocation:id,code,name', 'toLocation:id,code,name', 'requestedBy:id,full_name'])
            ->withCount('items');

        $this->applyRoleFilter($query, $user);

        return $query
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
            ])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return StockTransferStatus::options();
    }

    public function getLocationOptions(): array
    {
        return Location::query()
            ->where('is_active', true)
            ->get()
            ->map(fn(Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'full_address' => $location->getFullAddress(),
                'label' => $location->name,
                'type' => $location->type,
            ])
            ->toArray();
    }

    public function getLocationOptionsForFromLocation(User $user): array
    {
        $query = Location::query()->where('is_active', true);

        if (!$user->hasAnyRole(['Quản trị viên', 'Quản lý'])) {
            if ($user->isEmployee()) {
                $employee = $user->employee;
                $query->where(function ($q) use ($employee) {
                    $q->where('id', $employee?->store_location_id)
                        ->orWhere('id', $employee?->warehouse_location_id)
                        ->orWhere('manager_id', $employee?->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn(Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'full_address' => $location->getFullAddress(),
                'label' => $location->name,
                'type' => $location->type,
            ])
            ->toArray();
    }
}
