<?php

use App\Models\Auth\User;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\Bundle;
use App\Models\Public\Cart;
use App\Models\Public\CartItem;

beforeEach(function () {
    $this->user = User::factory()->create(['type' => 'customer']);

    // Setup a Product and a Variant
    $this->product = Product::factory()->create(['name' => 'Ergonomic Chair']);
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'name' => 'Midnight Black, XL',
        'price' => 5000000,
    ]);

    // Setup a Bundle
    $this->bundle = Bundle::factory()->create([
        'name' => 'Home Office Set',
        'discount_type' => 'percentage',
        'discount_value' => 10, // 10% off
    ]);
});

/**
 * AUTHENTICATION TESTS
 */
test('guests get an empty cart structure from the data endpoint', function () {
    $response = $this->get('/gio-hang/data');

    $response->assertStatus(200)
        ->assertJson([
            'totals' => ['item_count' => 0, 'subtotal' => 0, 'total' => 0],
            'items' => [],
        ]);
});

/**
 * ADD TO CART TESTS
 */
test('authenticated users can add a product variant to the cart', function () {
    $this->actingAs($this->user);

    $response = $this->post('/gio-hang', [
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('item.unit_price', 5000000);

    $this->assertDatabaseHas('cart_items', [
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);
});

test('authenticated users can add a bundle to the cart with configuration', function () {
    $this->actingAs($this->user);

    $response = $this->post('/gio-hang', [
        'purchasable_type' => Bundle::class,
        'purchasable_id' => $this->bundle->id,
        'quantity' => 1,
        'configuration' => ['variant_1' => 'id_abc', 'variant_2' => 'id_xyz'],
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('cart_items', [
        'purchasable_type' => Bundle::class,
        'purchasable_id' => $this->bundle->id,
    ]);
});

test('adding the same variant increments the quantity', function () {
    $this->actingAs($this->user);

    // Add once
    $this->post('/gio-hang', [
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    // Add again
    $this->post('/gio-hang', [
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('cart_items', [
        'purchasable_id' => $this->variant->id,
        'quantity' => 3,
    ]);
});

/**
 * CART MANAGEMENT TESTS
 */
test('user can update item quantity', function () {
    $this->actingAs($this->user);
    $cart = Cart::getOrCreate($this->user);
    $item = CartItem::create([
        'cart_id' => $cart->id,
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $this->patch("/gio-hang/{$item->id}", ['quantity' => 5])
        ->assertStatus(200);

    $this->assertDatabaseHas('cart_items', [
        'id' => $item->id,
        'quantity' => 5,
    ]);
});

test('user can remove an item from the cart', function () {
    $this->actingAs($this->user);
    $cart = Cart::getOrCreate($this->user);
    $item = CartItem::create([
        'cart_id' => $cart->id,
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $this->delete("/gio-hang/{$item->id}")
        ->assertStatus(200);

    $this->assertModelMissing(CartItem::class, $item->id);
});

/**
 * RESOURCE & NAMING TESTS
 */
test('cart items return a composite name for variants', function () {
    $this->actingAs($this->user);
    $cart = Cart::getOrCreate($this->user);

    CartItem::create([
        'cart_id' => $cart->id,
        'purchasable_type' => ProductVariant::class,
        'purchasable_id' => $this->variant->id,
        'quantity' => 1,
    ]);

    $response = $this->get('/gio-hang/data');

    // Expected: "Ergonomic Chair - Midnight Black, XL"
    $response->assertJsonFragment(['name' => "{$this->variant->name}"]);
});
