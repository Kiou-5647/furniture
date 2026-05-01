<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Sales\CancelOrderAction;
use App\Data\Sales\OrderFilterData;
use App\Enums\OrderStatus;
use App\Http\Resources\Public\Sales\OrderDetailsResource;
use App\Http\Resources\Public\Sales\OrderResource;
use App\Models\Sales\Order;
use App\Services\Sales\OrderService;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function index(Request $request): Response
    {
        $filter = new OrderFilterData(
            customer_id: $request->user()->id,
            status: $request->query('status') ? OrderStatus::tryFrom($request->query('status')) : OrderStatus::Pending,
            source: $request->query('source') ?: null,
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? 15),
        );

        $orders = $this->orderService->getFiltered($filter);

        return Inertia::render('public/orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->all(),
        ]);
    }

    public function show(string $order_number)
    {
        $order = Order::with([
            'items.purchasable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    \App\Models\Product\ProductVariant::class => ['product'],
                    \App\Models\Product\Bundle::class => ['contents.productCard.product'],
                ]);
            },
            'items.shipmentItems.variant', // Load shipment items for status
            'shipments.items.variant',
            'invoices',
            'refunds'
        ])
            ->where('order_number', $order_number)
            ->firstOrFail();

        if ($order->customer_id !== Auth::user()->id) {
            abort(403);
        }

        // Collect all variant IDs to fetch reviews in one query
        $variantIds = [];
        foreach ($order->items as $item) {
            if ($item->purchasable instanceof \App\Models\Product\ProductVariant) {
                $variantIds[] = $item->purchasable->id;
            } elseif ($item->purchasable instanceof \App\Models\Product\Bundle) {
                foreach ($item->configuration ?? [] as $config) {
                    if (isset($config['variant_id'])) {
                        $variantIds[] = $config['variant_id'];
                    }
                }
            }
        }

        $customerReviews = \App\Models\Customer\Review::where('customer_id', Auth::user()->customer->id)
            ->whereIn('variant_id', array_unique($variantIds))
            ->get()
            ->keyBy('variant_id');

        return Inertia::render('public/orders/Show', [
            'order' => new OrderDetailsResource($order, $customerReviews),
        ]);
    }

    public function cancel(string $order_number, Request $request, CancelOrderAction $action): RedirectResponse
    {
        $order = Order::where('order_number', $order_number)->firstOrFail();

        if ($order->customer_id !== Auth::user()->id) {
            abort(403);
        }

        try {
            $action->execute($order, null);

            return back()->with('success', 'Đơn hàng đã được hủy thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
