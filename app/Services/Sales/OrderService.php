<?php

namespace App\Services\Sales;

use App\Data\Sales\OrderFilterData;
use App\Enums\OrderStatus;
use App\Models\Auth\User;
use App\Models\Inventory\Location;
use App\Models\Product\Bundle;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Order;
use App\Services\Location\StockLocatorService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService
{
    public function getFiltered(OrderFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['order_number', 'total_amount', 'created_at', 'updated_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return Order::query()
            ->with(['customer.customer', 'acceptedBy', 'items.sourceLocation', 'storeLocation'])
            ->when($filter->customer_id, fn ($q) => $q->byCustomerId($filter->customer_id))
            ->when($filter->status, fn ($q) => $q->byStatus($filter->status))
            ->when($filter->source, fn ($q) => $q->bySource($filter->source))
            ->when($filter->store_location_id, fn ($q) => $q->byStoreLocation($filter->store_location_id))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::onlyTrashed()
            ->with(['customer.customer', 'acceptedBy'])
            ->when($filter->customer_id, fn ($q) => $q->byCustomerId($filter->customer_id))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Order
    {
        return Order::with([
            'customer.customer',
            'items.sourceLocation',
            'items.purchasable',
            'acceptedBy',
            'storeLocation',
            'shippingMethod',
            'shipments.items.orderItem.purchasable',
            'shipments.items.sourceLocation',
            'shipments.originLocation',
            'shipments.shippingMethod',
            'shipments.handledBy',
            'refunds',
            'invoices',
        ])->findOrFail($id);
    }

    /**
     * Get all locations with stock for a specific variant.
     */
    public function getVariantStockOptions(string $variantId): array
    {
        $locator = app(StockLocatorService::class);
        $stockOptions = $locator->findStockForItem(
            'App\\Models\\Product\\ProductVariant',
            $variantId
        );

        return $stockOptions->toArray();
    }

    public function getStatusOptions(): array
    {
        return OrderStatus::options();
    }

    public function getCustomerOptions(): Collection
    {
        return User::query()
            ->where('type', 'customer')
            ->where('is_active', true)
            ->with(['customer' => function ($q) {
                $q->with(['addresses' => function ($q) {
                    $q->orderBy('is_default', 'desc');
                }]);
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                $customerProfile = $user->customer;
                $defaultAddress = $customerProfile?->addresses->first();

                return [
                    'id' => $user->id,
                    'name' => $customerProfile->full_name ?? $user->name,
                    'email' => $user->email,
                    'phone' => $customerProfile->phone ?? null,
                    'default_address' => $defaultAddress ? [
                        'province_code' => $defaultAddress->province_code,
                        'province_name' => $defaultAddress->province_name,
                        'ward_code' => $defaultAddress->ward_code,
                        'ward_name' => $defaultAddress->ward_name,
                        'address_data' => $defaultAddress->address_data,
                    ] : null,
                ];
            });
    }

    public function getVariantOptions(): Collection
    {
        return Product::query()
            ->where('status', 'published')
            ->with(['variants' => fn ($q) => $q->where('status', 'active')->orderBy('price')])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'variants' => $p->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'purchasable_type' => 'App\\Models\\Product\\ProductVariant',
                ])->toArray(),
            ]);
    }

    public function getBundleOptions(): Collection
    {
        return Bundle::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'discount_value', 'discount_type'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'price' => $b->calculateBundlePrice(),
                'discount_type' => $b->discount_type,
                'discount_value' => $b->discount_value,
            ]);
    }

    public function getStoreLocationOptions(): Collection
    {
        return Location::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn ($loc) => [
                'id' => $loc->id,
                'label' => $loc->name.' ('.$loc->code.')',
            ]);
    }

    public function getOrderVariantStockOptions(Order $order): array
    {
        $variantIds = collect();
        foreach ($order->shipments as $shipment) {
            foreach ($shipment->items as $item) {
                if ($item->orderItem && str_contains($item->orderItem->purchasable_type, 'ProductVariant')) {
                    $variantIds->push($item->orderItem->purchasable_id);
                }
            }
        }

        $variantStockOptions = [];
        foreach ($variantIds->unique() as $variantId) {
            $variantStockOptions[$variantId] = $this->getVariantStockOptions($variantId);
        }

        return $variantStockOptions;
    }

    /**
     * Get all variants and bundles with stock info for the order catalog.
     */
    public function getCatalogItems(?string $locationId = null): array
    {
        $items = [];

        // Get all variants with stock at store and total stock
        $variants = ProductVariant::query()
            ->where('status', 'active')
            ->whereHas('product', fn ($q) => $q->where('status', 'published'))
            ->with(['product:id,name'])
            ->get();

        foreach ($variants as $variant) {
            $stockAtStore = 0;
            $stockTotal = 0;

            if ($locationId) {
                $inv = $variant->inventories()->where('location_id', $locationId)->first();
                $stockAtStore = $inv?->quantity ?? 0;
            }

            $stockTotal = $variant->inventories()->sum('quantity');

            $items[] = [
                'id' => $variant->id,
                'name' => $variant->product?->name.' — '.$variant->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'stock_at_store' => $stockAtStore,
                'stock_total' => $stockTotal,
                'image_url' => $variant->getFirstMediaUrl('primary_image') ?: null,
                'purchasable_type' => 'variant',
            ];
        }

        // Get all bundles with availability info
        $bundles = Bundle::query()
            ->where('is_active', true)
            ->with(['contents.product.variants.inventories'])
            ->get();

        foreach ($bundles as $bundle) {
            $availableAtStore = true;
            $availableSystemWide = true;

            foreach ($bundle->contents as $content) {
                $product = $content->product;
                if (! $product) {
                    $availableAtStore = false;
                    $availableSystemWide = false;

                    continue;
                }

                $hasStockAtStore = $locationId
                    ? $product->variants->some(fn ($v) => $v->inventories->where('location_id', $locationId)->sum('quantity') > 0)
                    : false;

                $hasStockTotal = $product->variants->some(fn ($v) => $v->inventories->sum('quantity') > 0);

                if (! $hasStockAtStore) {
                    $availableAtStore = false;
                }
                if (! $hasStockTotal) {
                    $availableSystemWide = false;
                }
            }

            $items[] = [
                'id' => $bundle->id,
                'name' => $bundle->name,
                'price' => $bundle->calculateBundlePrice(),
                'image_url' => $bundle->getFirstMediaUrl('primary_image') ?: null,
                'purchasable_type' => 'bundle',
                'available_at_store' => $availableAtStore,
                'available_system_wide' => $availableSystemWide,
                'discount_type' => $bundle->discount_type,
                'discount_value' => $bundle->discount_value,
            ];
        }

        return $items;
    }

    /**
     * Get bundle contents with variant options for variant selection dialog.
     */
    public function getBundleContents(): array
    {
        $bundles = Bundle::query()
            ->where('is_active', true)
            ->with(['contents.product.variants' => fn ($q) => $q->where('status', 'active')])
            ->get();

        $result = [];
        foreach ($bundles as $bundle) {
            $result[$bundle->id] = $bundle->contents->map(fn ($c) => [
                'product_id' => $c->product_id,
                'product_name' => $c->product?->name ?? 'Unknown',
                'quantity' => $c->quantity,
                'variants' => $c->product?->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'image_url' => $v->getFirstMediaUrl('primary_image') ?: null,
                ])->values()->toArray() ?? [],
            ])->values()->toArray();
        }

        return $result;
    }
}
