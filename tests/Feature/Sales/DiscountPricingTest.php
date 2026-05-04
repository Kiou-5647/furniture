<?php

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Vendor\Vendor;
use App\Models\Sales\Discount;
use App\Services\Sales\PriceCalculationService;
use function Pest\Laravel\{get, post};

beforeEach(function () {
    $this->service = app(PriceCalculationService::class);
});

it('calculates base price when no discount exists', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(100000.0);
});

it('applies a universal discount', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    Discount::create([
        'name' => 'Universal Sale',
        'type' => 'percentage',
        'value' => 10,
        'discountable_id' => null,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(90000.0);
});

it('applies a product-specific discount', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    Discount::create([
        'name' => 'Product Sale',
        'type' => 'fixed_amount',
        'value' => 20000,
        'discountable_type' => Product::class,
        'discountable_id' => $product->id,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(80000.0);
});

it('applies a variant-specific discount', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    Discount::create([
        'name' => 'Variant Sale',
        'type' => 'percentage',
        'value' => 30,
        'discountable_type' => ProductVariant::class,
        'discountable_id' => $variant->id,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(70000.0);
});

it('applies category-specific discount', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    Discount::create([
        'name' => 'Category Sale',
        'type' => 'percentage',
        'value' => 15,
        'discountable_type' => Category::class,
        'discountable_id' => $category->id,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(85000.0);
});

it('applies collection-specific discount', function () {
    $collection = Collection::factory()->create();
    $product = Product::factory()->create(['collection_id' => $collection->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    Discount::create([
        'name' => 'Collection Sale',
        'type' => 'fixed_amount',
        'value' => 5000,
        'discountable_type' => Collection::class,
        'discountable_id' => $collection->id,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(95000.0);
});

it('applies vendor-specific discount', function () {
    $vendor = Vendor::factory()->create();
    $product = Product::factory()->create(['vendor_id' => $vendor->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    Discount::create([
        'name' => 'Vendor Sale',
        'type' => 'percentage',
        'value' => 20,
        'discountable_type' => Vendor::class,
        'discountable_id' => $vendor->id,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(80000.0);
});

it('picks the best discount among multiple applicable ones', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100000,
    ]);

    // Universal 10% -> 90k
    Discount::create([
        'name' => 'Universal',
        'type' => 'percentage',
        'value' => 10,
        'discountable_id' => null,
        'is_active' => true,
    ]);

    // Product 20% -> 80k
    Discount::create([
        'name' => 'Product',
        'type' => 'percentage',
        'value' => 20,
        'discountable_type' => Product::class,
        'discountable_id' => $product->id,
        'is_active' => true,
    ]);

    // Variant Fixed 30k -> 70k (BEST)
    Discount::create([
        'name' => 'Variant',
        'type' => 'fixed_amount',
        'value' => 30000,
        'discountable_type' => ProductVariant::class,
        'discountable_id' => $variant->id,
        'is_active' => true,
    ]);

    expect($this->service->calculateEffectivePrice($variant))->toBe(70000.0);
});
