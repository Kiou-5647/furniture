<?php

namespace App\Actions\Sales;

use App\Data\Sales\CreateOrderData;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\OrderStatus;
use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Services\Location\StockLocatorService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(
        protected GeneralSettings $settings,
        protected StockLocatorService $stockLocator,
    ) {}

    public function execute(CreateOrderData $data): Order
    {
        DB::beginTransaction();

        try {
            $validatedItems = $this->validateAndResolveItems($data);
            $totalAmount = collect($validatedItems)->sum(fn($item) => $item['unit_price'] * $item['quantity']);
            $totalItems = collect($validatedItems)->sum('quantity');

            $shippingCost = (float) ($data->shipping_cost ?? 0);

            if (! empty($data->shipping_method_id) && $totalAmount >= $this->settings->freeship_threshold) {
                $shippingCost = 0;
            }

            $grandTotal = $totalAmount + $shippingCost;

            // Build address data
            $addressData = [];
            $addressData['street'] = $data->street;
            $addressData['full_address'] = $data->street . ', ' . $data->ward_name . ', ' . $data->province_name;

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
                'payment_method' => $data->payment_method ?? 'cash',
                'province_code' => $data->province_code,
                'ward_code' => $data->ward_code,
                'province_name' => $data->province_name,
                'ward_name' => $data->ward_name,
                'address_data' => ! empty($addressData) ? $addressData : null,
                'total_amount' => $grandTotal,
                'total_items' => $totalItems,
                'status' => $initialStatus,
            ]);

            // Create invoice for all orders
            Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoiceable_type' => Order::class,
                'invoiceable_id' => $order->id,
                'type' => InvoiceType::Full,
                'amount_due' => $grandTotal,
                'amount_paid' => 0,
                'status' => InvoiceStatus::Open,
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
                    // If location is explicitly provided, use it directly
                    if (! empty($item['source_location_id'])) {
                        $sourceLocationId = $item['source_location_id'];
                    } else {
                        // Auto-resolve best location
                        $sourceLocationId = $this->stockLocator->resolveBestLocation(
                            $item['purchasable_type'],
                            $item['purchasable_id'],
                            $customerProvinceCode,
                            $data->store_location_id
                        );

                        // If no clear nearest, leave null — employee will set it later
                        if (! $sourceLocationId) {
                            $sourceLocationId = null;
                        }
                    }
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
                        throw new \RuntimeException('Sản phẩm "' . $purchasable->name . '" hết hàng tại cửa hàng.');
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
        $config = $item['configuration'] ?? [];

        if (empty($config)) {
            throw new \RuntimeException('Gói sản phẩm "' . $bundle->name . '" thiếu thông tin tùy chọn phiên bản.');
        }

        foreach ($bundle->contents as $content) {
            $variantId = $config[$content->id] ?? null;

            if (!$variantId) {
                throw new \RuntimeException('Gói sản phẩm "' . $bundle->name . '" chưa chọn phiên bản cho sản phẩm ' . $content->productCard->product->name);
            }

            $requiredQty = $content->quantity * $item['quantity'];

            if ($isShipping) {
                // Check if the SPECIFIC selected variant has enough stock anywhere in the system
                $hasStock = $this->stockLocator->findStockForItem(
                    'App\\Models\\Product\\ProductVariant',
                    $variantId
                )->sum('available_qty') >= $requiredQty;

                if (!$hasStock) {
                    throw new \RuntimeException("Phiên bản đã chọn của sản phẩm '{$content->productCard->product->name}' trong gói '{$bundle->name}' đã hết hàng.");
                }
            } elseif ($storeLocationId) {
                // In-store: check if the SPECIFIC selected variant has enough stock at the store location
                $stockOptions = $this->stockLocator->findStockForItem(
                    'App\\Models\\Product\\ProductVariant',
                    $variantId,
                    null,
                    null,
                    $storeLocationId
                );

                $storeStock = $stockOptions->firstWhere('location_id', $storeLocationId);

                if (!$storeStock || $storeStock['available_qty'] < $requiredQty) {
                    throw new \RuntimeException("Phiên bản đã chọn của sản phẩm '{$content->productCard->product->name}' trong gói '{$bundle->name}' hết hàng tại cửa hàng.");
                }
            }
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
