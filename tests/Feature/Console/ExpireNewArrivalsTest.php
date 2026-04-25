<?php

use App\Models\Product\Product;
use Illuminate\Support\Carbon;

it('expires products that have passed their new arrival date', function () {
    // 1. Setup: Create an expired product
    $expiredProduct = Product::factory()->create([
        'is_new_arrival' => true,
        'new_arrival_until' => Carbon::now()->subDays(1), // Yesterday
    ]);

    // 2. Setup: Create a product that is still valid
    $validProduct = Product::factory()->create([
        'is_new_arrival' => true,
        'new_arrival_until' => Carbon::now()->addDays(5), // 5 days from now
    ]);

    // 3. Action: Run the Artisan command
    $this->artisan('products:expire-new-arrivals')
        ->expectsOutput('Successfully expired 1 new arrival products.')
        ->assertExitCode(0);

    // 4. Assertions
    // The expired product should now be false
    expect($expiredProduct->refresh()->is_new_arrival)->toBeFalse();

    // The valid product should still be true
    expect($validProduct->refresh()->is_new_arrival)->toBeTrue();
});

it('does nothing when no products are expired', function () {
    // Create only valid products
    Product::factory()->create([
        'is_new_arrival' => true,
        'new_arrival_until' => Carbon::now()->addDays(1),
    ]);

    $this->artisan('products:expire-new-arrivals')
        ->expectsOutput('Successfully expired 0 new arrival products.')
        ->assertExitCode(0);
});
