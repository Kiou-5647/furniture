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
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function getFiltered(OrderFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['order_number', 'total_amount', 'created_at', 'updated_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return Order::query()
            ->with(['customer', 'acceptedBy', 'items.sourceLocation', 'storeLocation'])
            ->when($filter->customer_id, fn($q) => $q->byCustomerId($filter->customer_id))
            ->when($filter->status, fn($q) => $q->byStatus($filter->status))
            ->when($filter->source, fn($q) => $q->bySource($filter->source))
            ->when($filter->store_location_id, fn($q) => $q->byStoreLocation($filter->store_location_id))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::onlyTrashed()
            ->with(['customer', 'acceptedBy'])
            ->when($filter->customer_id, fn($q) => $q->byCustomerId($filter->customer_id))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Order
    {
        return Order::with([
            'customer',
            'items.sourceLocation',
            'items.purchasable',
            'acceptedBy',
            'storeLocation',
            'shippingMethod',
            'shipments.items.orderItem.purchasable',
            'shipments.items.variant',
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
            ->with(['customer'])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                $customerProfile = $user->customer;

                return [
                    'id' => $user->id,
                    'name' => $customerProfile->full_name ?? $user->name,
                    'email' => $user->email,
                    'phone' => $customerProfile->phone ?? null,
                    'default_address' => $customerProfile ? [
                        'province_code' => $customerProfile->province_code,
                        'province_name' => $customerProfile->province_name,
                        'ward_code' => $customerProfile->ward_code,
                        'ward_name' => $customerProfile->ward_name,
                        'street' => $customerProfile->street,
                    ] : null,
                ];
            });
    }

    public function getVariantOptions(): Collection
    {
        return Product::query()
            ->where('status', 'published')
            ->with(['variants' => fn($q) => $q->where('status', 'active')->orderBy('price')])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'variants' => $p->variants->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'price' => $v->getEffectivePrice(),
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
            ->map(fn($b) => [
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
            ->map(fn($loc) => [
                'id' => $loc->id,
                'label' => $loc->name . ' (' . $loc->code . ')',
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
            ->whereHas('product', fn($q) => $q->where('status', 'published'))
            ->with(['product.category', 'product.collection', 'product.vendor'])
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
                'name' => $variant->product?->name . ' — ' . $variant->name,
                'sku' => $variant->sku,
                'price' => $variant->getEffectivePrice(),
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
                $productCard = $content->productCard;
                if (! $productCard) {
                    $availableAtStore = false;
                    $availableSystemWide = false;
                    continue;
                }

                // 1. Check if ANY variant of this product has stock at the specific store
                $hasStockAtStore = $locationId
                    ? $productCard->variants->some(fn($v) => $v->inventories->where('location_id', $locationId)->sum('quantity') > 0)
                    : false;

                // 2. Check if ANY variant has stock anywhere in the system
                $hasStockTotal = $productCard->variants->some(fn($v) => $v->inventories->sum('quantity') > 0);

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
            ->with(['contents.productCard.variants' => fn($q) => $q->where('status', 'active')])
            ->get();

        $result = [];
        foreach ($bundles as $bundle) {
            $result[$bundle->id] = $bundle->contents->map(fn($c) => [
                'id' => $c->id,
                'product_card_id' => $c->productCard?->id,
                'product_name' => $c->productCard?->product?->name ?? 'Unknown',
                'quantity' => $c->quantity,
                'variants' => $c->productCard?->variants->map(fn($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'slug' => $v->slug,
                    'name' => $v->name,
                    'swatch_label' => $v->swatch_label,
                    'price' => $v->getEffectivePrice(),
                    'in_stock' => $v->getAvailableStock() > 0,
                    'primary_image_url' => $v->getFirstMediaUrl('primary_image'),
                    'swatch_image_url' => $v->getFirstMediaUrl('swatch_image', 'swatch')
                ])->values()->toArray() ?? [],
            ])->values()->toArray();
        }

        return $result;
    }

    public function getOrderItemsForShipment(Order $order): array
    {
        $shippableItems = [];

        Log::info('Start get order items!');

        foreach ($order->items as $item) {
            if ($item->purchasable_type === 'App\\Models\\Product\\Bundle') {
                $bundle = \App\Models\Product\Bundle::find($item->purchasable_id);
                $config = $item->configuration;

                // Ensure bundle exists and config is actually an array
                if (!$bundle || !is_array($config)) {
                    Log::warning("config is not array");
                    continue;
                }

                foreach ($bundle->contents as $content) {
                    $configValue = $config[$content->id];
                    $variantId = $configValue['variant_id'] ?? null;

                    if ($variantId) {
                        $variant = \App\Models\Product\ProductVariant::find($variantId);

                        // If the variant was deleted but is still in the bundle config,
                        // we must skip it to avoid a 500 error.
                        if (!$variant) continue;

                        $shippableItems[] = [
                            'bundle_name' => $bundle->name,
                            'order_item_id' => $item->id,
                            'variant_id' => $variant->id,
                            'sku' => $variant->sku,
                            'name' => $variant->product->name . ' ' . $variant->name,
                            'quantity' => (int)($content->quantity * $item->quantity),
                            'is_bundle_component' => true,
                            'stock_options' => $this->getVariantStockOptions($variant->id),
                        ];
                    }
                }
            } else {
                $variant = \App\Models\Product\ProductVariant::find($item->purchasable_id);
                if (!$variant) continue;

                $shippableItems[] = [
                    'bundle_name' => null,
                    'order_item_id' => $item->id,
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'name' => $variant->product->name . ' ' . $variant->name,
                    'quantity' => (int)$item->quantity,
                    'is_bundle_component' => false,
                    'stock_options' => $this->getVariantStockOptions($variant->id),
                ];
            }
        }

        Log::info('Sent!', $shippableItems);

        return $shippableItems;
    }
}
