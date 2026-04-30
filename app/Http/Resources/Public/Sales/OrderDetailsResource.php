<?php

namespace App\Http\Resources\Public\Sales;

use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
{
    public static $wrap = false;

    public function __construct($resource, protected $customerReviews = null)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_fully_paid' => $this->isFullyPaid(),
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'paid_at' => $this->paid_at,
            'status' => $this->status,
            'status_label' => $this->status->label(),
            'created_at' => $this->created_at->toDateTimeString(),

            'recipient' => [
                'name' => $this->guest_name,
                'phone' => $this->guest_phone,
                'email' => $this->guest_email,
                'address' => $this->getShippingAddressText(),
            ],

            'items' => $this->items->map(fn($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'purchasable_type' => $item->purchasable_type,
                'purchasable' => $this->resolvePurchasableData($item),
                'shipment_status' => $item->shipmentItems->last()?->status,
                'selected_variants' => $this->resolveBundleComponents($item),
            ]),

            'invoices' => $this->invoices->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount_due' => $invoice->amount_due,
                'amount_paid' => $invoice->amount_paid,
                'status' => $invoice->status,
                'created_at' => $invoice->created_at->toDateTimeString(),
            ]),

            'refunds' => $this->refunds->map(fn($refund) => [
                'id' => $refund->id,
                'amount' => $refund->amount,
                'reason' => $refund->reason,
                'status' => $refund->status,
                'created_at' => $refund->created_at->toDateTimeString(),
            ]),
        ];
    }

    protected function resolvePurchasableData($item): array
    {
        $purchasable = $item->purchasable;
        if (!$purchasable) return [];

        if ($purchasable instanceof ProductVariant) {
            return [
                'id' => $purchasable->id,
                'name' => ($purchasable->product ? "{$purchasable->product->name} - " : "") . $purchasable->name,
                'sku' => $purchasable->sku,
                'slug' => $purchasable->slug,
                'image_url' => $purchasable->getFirstMediaUrl('primary_image', 'thumb'),
                'review' => $this->resolveReviewData($purchasable->id),
            ];
        }

        if ($purchasable instanceof Bundle) {
            return [
                'id' => $purchasable->id,
                'name' => $purchasable->name,
                'sku' => $purchasable->sku ?? null,
                'slug' => $purchasable->slug ?? null,
                'image_url' => $purchasable->getFirstMediaUrl('primary_image', 'thumb'),
            ];
        }

        return [];
    }

    protected function resolveBundleComponents($item): array
    {
        if ($item->purchasable_type !== Bundle::class || empty($item->configuration)) {
            return [];
        }

        $bundle = $item->purchasable;
        $components = [];

        foreach ($bundle->contents as $content) {
            $variantId = $item->configuration[$content->id]['variant_id'] ?? null;
            if (!$variantId) continue;

            // Use the eager-loaded shipment items to find the variant
            // This avoids calling ProductVariant::find() in a loop
            $shipmentItem = $item->shipmentItems
                ->where('variant_id', $variantId)
                ->first();

            $variant = $shipmentItem?->variant;
            if (!$variant) continue;

            $components[] = [
                'id' => $variant->id,
                'name' => "{$variant->product->name} {$variant->name}",
                'sku' => $variant->sku,
                'slug' => $variant->slug,
                'image_url' => $variant->getFirstMediaUrl('primary_image', 'thumb'),
                'quantity' => $content->quantity,
                'price' => $item->configuration[$content->id]['price'] ?? 0,
                'shipment_status' => $shipmentItem?->status,
                'review' => $this->resolveReviewData($variant->id),
            ];
        }
        return $components;
    }

    protected function resolveReviewData(string $variantId): ?array
    {
        if (!$this->customerReviews || !$this->customerReviews->has($variantId)) {
            return null;
        }

        $review = $this->customerReviews->get($variantId);

        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'is_published' => $review->is_published,
        ];
    }
}
