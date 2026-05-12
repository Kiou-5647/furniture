<?php

namespace App\Http\Controllers\Public;

use App\Http\Requests\Public\Cart\AddToCartRequest;
use App\Http\Requests\Public\Cart\UpdateCartItemRequest;
use App\Services\Public\CartService;
use App\Actions\Public\AddToCartAction;
use App\Actions\Public\UpdateCartItemAction;
use App\Actions\Public\RemoveFromCartAction;
use App\Actions\Public\ClearCartAction;
use App\Data\Public\CartItemData;
use App\Http\Resources\Public\CartItemResource;
use App\Models\Public\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController
{
    public function __construct(
        protected CartService $cartService,
        protected AddToCartAction $addToCart,
        protected UpdateCartItemAction $updateCartItem,
        protected RemoveFromCartAction $removeFromCart,
        protected ClearCartAction $clearCart,
    ) {}

    public function index(Request $request): Response
    {
        $cart = $this->cartService->getOrCreateForUser($request->user());

        return Inertia::render('public/cart/Index', [
            'totals' => $this->cartService->calculateTotals($cart),
            'items' => CartItemResource::collection($this->cartService->getCartWithItems($cart)->items),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateForUser(
            $request->user(),
            $request->getSession()->getId()
        );

        return response()->json([
            'totals' => $this->cartService->calculateTotals($cart),
            'items' => CartItemResource::collection($this->cartService->getCartWithItems($cart)->items),
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateForUser(
            $request->user(),
            $request->getSession()->getId()
        );

        $data = CartItemData::fromRequest($request);

        try {
            $item = $this->addToCart->execute($cart, $data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => 'Added to cart',
            'item' => new CartItemResource($item),
            'totals' => $this->cartService->calculateTotals($cart),
        ]);
    }

    public function update(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        $item = CartItem::findOrFail($itemId);

        $isOwner = ($request->user() && $item->cart->user_id === $request->user()->id);
        $isGuestSession = (!$request->user() && $item->cart->session_id === $request->getSession()->getId());

        if (!$isOwner && !$isGuestSession) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $updatedItem = $this->updateCartItem->execute($item, $request->integer('quantity'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => 'Quantity updated',
            'item' => new CartItemResource($updatedItem),
        ]);
    }

    public function destroy(Request $request, string $itemId): JsonResponse
    {
        $item = CartItem::findOrFail($itemId);

        $isOwner = ($request->user() && $item->cart->user_id === $request->user()->id);
        $isGuestSession = (!$request->user() && $item->cart->session_id === $request->getSession()->getId());

        if (!$isOwner && !$isGuestSession) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->removeFromCart->execute($item);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        return response()->json(['message' => 'Item removed']);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateForUser($request->user());
        try {
            $this->clearCart->execute($cart);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        return response()->json(['message' => 'Cart cleared']);
    }
}
