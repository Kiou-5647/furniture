<?php

namespace App\Actions\Sales;

use App\Data\Sales\CreateOrderData;
use App\Enums\OrderStatus;
use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Order;
use App\Services\Location\StockLocatorService;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(
        protected StockLocatorService $stockLocator,
    ) {}

    public function execute(CreateOrderData $data): Order
    {
        DB::beginTransaction();

        try {
            // Validate items and resolve source locations
            $validatedItems = $this->validateAndResolveItems($data);
            $totalAmount = collect($validatedItems)->sum(fn ($item) => $item['unit_price'] * $item['quantity']);
            $totalItems = collect($validatedItems)->sum('quantity');
            $shippingCost = (float) ($data->shipping_cost ?? 0);
            $grandTotal = $totalAmount + $shippingCost;

            // Build address data
            $addressData = [];
            if (! empty($data->building)) {
                $addressData['building'] = $data->building;
            }
            if (! empty($data->address_number)) {
                $addressData['address_number'] = $data->address_number;
            }

            // Determine initial status based on order source
            // Employee-created orders skip Pending → go straight to Processing
            // Online orders (customer self-service) start at Pending waiting for payment
            $initialStatus = $data->source === 'in_store'
                ? OrderStatus::Processing
                : OrderStatus::Pending;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $data->customer_id,
                'guest_name' => $data->guest_name,
                'guest_phone' => $data->guest_phone,
                'guest_email' => $data->guest_email,
                'notes' => $data->notes,
                'source' => $data->source,
                'store_location_id' => $data->store_location_id,
                'shipping_method_id' => $data->shipping_method_id,
                'shipping_cost' => $shippingCost,
                'province_code' => $data->province_code,
                'ward_code' => $data->ward_code,
                'province_name' => $data->province_name,
                'ward_name' => $data->ward_name,
                'address_data' => ! empty($addressData) ? $addressData : null,
                'total_amount' => $grandTotal,
                'total_items' => $totalItems,
                'status' => $initialStatus,
            ]);

            foreach ($validatedItems as $itemData) {
                $order->items()->create($itemData);
            }

            DB::commit();

            return $order->load(['items', 'customer']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate items and resolve source locations.
     * For in-store orders: source = store_location_id, only store-stock items allowed.
     * For shipping orders: source = closest location with stock, all items allowed.
     */
    protected function validateAndResolveItems(CreateOrderData $data): array
    {
        $validated = [];
        $isShipping = (bool) $data->shipping_method_id;
        $customerProvinceCode = $data->province_code;

        foreach ($data->items as $item) {
            $purchasable = $this->resolvePurchasable(
                $item['purchasable_type'],
                $item['purchasable_id']
            );

            if (! $purchasable) {
                throw new \RuntimeException('Sản phẩm không khả dụng.');
            }

            // For bundles: validate all bundle content variants have stock somewhere
            if ($purchasable instanceof Bundle) {
                $this->validateBundleAvailability($purchasable, $item, $isShipping, $data->store_location_id, $customerProvinceCode);
                $sourceLocationId = null; // Will be resolved when shipments are created
            } elseif ($purchasable instanceof ProductVariant) {
                if ($isShipping) {
                    // Shipping: find closest location with stock
                    $stockOptions = $this->stockLocator->findStockForItem(
                        $item['purchasable_type'],
                        $item['purchasable_id'],
                        $customerProvinceCode
                    );

                    if ($stockOptions->isEmpty()) {
                        throw new \RuntimeException('Sản phẩm "'.$purchasable->name.'" hết hàng trên toàn hệ thống.');
                    }

                    $sourceLocationId = $stockOptions->first()['location_id'];
                } elseif ($data->store_location_id) {
                    // In-store: must have stock at store location
                    $stockOptions = $this->stockLocator->findStockForItem(
                        $item['purchasable_type'],
                        $item['purchasable_id'],
                        null,
                        null,
                        $data->store_location_id
                    );

                    $storeStock = $stockOptions->firstWhere('location_id', $data->store_location_id);
                    if (! $storeStock || $storeStock['available_qty'] <= 0) {
                        throw new \RuntimeException('Sản phẩm "'.$purchasable->name.'" hết hàng tại cửa hàng.');
                    }

                    $sourceLocationId = $data->store_location_id;
                } else {
                    // Online order without shipping — no stock validation yet
                    $sourceLocationId = null;
                }
            }

            $validated[] = [
                'purchasable_type' => $item['purchasable_type'],
                'purchasable_id' => $item['purchasable_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'configuration' => $item['configuration'] ?? [],
                'source_location_id' => $sourceLocationId ?? null,
            ];
        }

        return $validated;
    }

    /**
     * Validate that a bundle's dependent variants have stock available.
     * For in-store: all variants must have stock at store location.
     * For shipping: at least one variant of each bundled product must have stock somewhere.
     */
    protected function validateBundleAvailability(Bundle $bundle, array $item, bool $isShipping, ?string $storeLocationId, ?string $customerProvinceCode): void
    {
        foreach ($bundle->contents as $content) {
            $product = $content->product;
            if (! $product) {
                throw new \RuntimeException('Gói sản phẩm "'.$bundle->name.'" có sản phẩm không hợp lệ.');
            }

            if ($isShipping) {
                // Check if ANY variant of this product has stock somewhere
                $hasStock = $product->variants()
                    ->whereHas('inventories', fn ($q) => $q->where('quantity', '>', 0))
                    ->exists();

                if (! $hasStock) {
                    throw new \RuntimeException('Sản phẩm "'.$product->name.'" trong gói "'.$bundle->name.'" hết hàng trên toàn hệ thống.');
                }
            } elseif ($storeLocationId) {
                // In-store: check if ANY variant has stock at store location
                $hasStock = $product->variants()
                    ->whereHas('inventories', fn ($q) => $q
                        ->where('location_id', $storeLocationId)
                        ->where('quantity', '>', 0))
                    ->exists();

                if (! $hasStock) {
                    throw new \RuntimeException('Sản phẩm "'.$product->name.'" trong gói "'.$bundle->name.'" hết hàng tại cửa hàng.');
                }
            }
            // Online without shipping — no stock check yet
        }
    }

    protected function resolvePurchasable(string $type, string $id): mixed
    {
        return match ($type) {
            'App\\Models\\Product\\ProductVariant' => ProductVariant::find($id),
            'App\\Models\\Product\\Bundle' => Bundle::find($id),
            default => throw new \InvalidArgumentException('Invalid purchasable type.'),
        };
    }
}
