<?php

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\Category;
use App\Models\Sales\Discount;
use App\Services\Sales\PriceCalculationService;

test('it calculates the lowest price among multiple discounts', function () {
    // 1. Setup
    $category = Category::factory()->create();
    $variant = ProductVariant::factory()->create([
        'price' => 10000000,
        'product_id' => Product::factory()->create(['category_id' => $category->id])->id,
    ]);

    // 2. Create a 10% category discount (Result: 9,000,000)
    Discount::create([
        'name' => '10% Off',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
        'discountable_type' => Category::class,
        'discountable_id' => $category->id,
    ]);

    // 3. Create a 2M fixed discount (Result: 8,000,000)
    Discount::create([
        'name' => '2M Off',
        'type' => 'fixed_amount',
        'value' => 2000000,
        'is_active' => true,
        'discountable_type' => Category::class,
        'discountable_id' => $category->id,
    ]);

    $service = app(PriceCalculationService::class);
    $result = $service->calculatePriceDetails($variant);

    // It should pick the 2M off because it's lower
    expect($result['effective_price'])->toBe(8000000.0);
    expect($result['discount']['name'])->toBe('2M Off');
});
