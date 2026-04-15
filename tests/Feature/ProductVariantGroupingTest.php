<?php

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;

beforeEach(function () {
    $this->product = Product::create([
        'name' => 'Ghế Timber',
        'status' => 'published',
        'min_price' => 12990000,
        'max_price' => 17990000,
        'option_groups' => [
            [
                'name' => 'Chất liệu',
                'namespace' => 'chat-lieu',
                'is_swatches' => false,
                'options' => [
                    ['value' => 'da', 'label' => 'Da'],
                    ['value' => 'vai', 'label' => 'Vải'],
                ],
            ],
            [
                'name' => 'Màu sắc',
                'namespace' => 'mau-sac',
                'is_swatches' => true,
                'options' => [
                    ['value' => 'charme-black', 'label' => 'Charme Black'],
                    ['value' => 'charme-chocolat', 'label' => 'Charme Chocolat'],
                    ['value' => 'charme-green', 'label' => 'Charme Green'],
                    ['value' => 'charme-tan', 'label' => 'Charme Tan'],
                    ['value' => 'olio-green', 'label' => 'Olio Green'],
                    ['value' => 'pebble-gray', 'label' => 'Pebble Gray'],
                    ['value' => 'rain-cloud-gray', 'label' => 'Rain Cloud Gray'],
                ],
            ],
        ],
    ]);
});

it('groups variants into cards by non-swatches combination with only matching swatches', function () {
    // Da has 4 colors, Vải has 3 colors (like the real Timber product)
    $variants = [
        ['chat-lieu' => 'da', 'mau-sac' => 'charme-black'],
        ['chat-lieu' => 'da', 'mau-sac' => 'charme-chocolat'],
        ['chat-lieu' => 'da', 'mau-sac' => 'charme-green'],
        ['chat-lieu' => 'da', 'mau-sac' => 'charme-tan'],
        ['chat-lieu' => 'vai', 'mau-sac' => 'olio-green'],
        ['chat-lieu' => 'vai', 'mau-sac' => 'pebble-gray'],
        ['chat-lieu' => 'vai', 'mau-sac' => 'rain-cloud-gray'],
    ];

    foreach ($variants as $i => $opt) {
        ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'SKU-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            'name' => 'Test Variant',
            'slug' => 'v-'.($i + 1),
            'price' => 15000000,
            'option_values' => $opt,
        ]);
    }

    $result = $this->product->getGroupedVariantOptions();

    // Should have 2 cards (Da, Vải)
    expect($result)->toHaveCount(2)
        ->and(array_is_list($result))->toBeTrue();

    // First card: Da with 4 swatches
    $daCard = $result[0];
    expect($daCard['option_values']['chat-lieu'])->toBe('da')
        ->and($daCard['variant_count'])->toBe(4)
        ->and($daCard['swatch_options'])->toHaveCount(4);

    $daSwatchValues = collect($daCard['swatch_options'])->pluck('value')->toArray();
    expect($daSwatchValues)->toContain('charme-black', 'charme-chocolat', 'charme-green', 'charme-tan')
        ->and($daSwatchValues)->not->toContain('olio-green');

    // Second card: Vải with 3 swatches
    $vaiCard = $result[1];
    expect($vaiCard['option_values']['chat-lieu'])->toBe('vai')
        ->and($vaiCard['variant_count'])->toBe(3)
        ->and($vaiCard['swatch_options'])->toHaveCount(3);

    $vaiSwatchValues = collect($vaiCard['swatch_options'])->pluck('value')->toArray();
    expect($vaiSwatchValues)->toContain('olio-green', 'pebble-gray', 'rain-cloud-gray')
        ->and($vaiSwatchValues)->not->toContain('charme-black');
});

it('returns single card with all swatches when only swatches group exists', function () {
    $this->product->update([
        'option_groups' => [
            [
                'name' => 'Màu sắc',
                'namespace' => 'mau-sac',
                'is_swatches' => true,
                'options' => [
                    ['value' => 'red', 'label' => 'Đỏ'],
                    ['value' => 'green', 'label' => 'Xanh'],
                    ['value' => 'blue', 'label' => 'Xanh dương'],
                ],
            ],
        ],
    ]);

    // Create 3 variants, one for each color
    $variants = [
        ['mau-sac' => 'red'],
        ['mau-sac' => 'green'],
        ['mau-sac' => 'blue'],
    ];

    foreach ($variants as $i => $opt) {
        ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'SKU-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            'name' => 'Test Variant',
            'slug' => 'v-'.($i + 1),
            'price' => 15000000,
            'option_values' => $opt,
        ]);
    }

    $result = $this->product->getGroupedVariantOptions();

    // Should have 1 card with 3 swatches
    expect($result)->toHaveCount(1)
        ->and($result[0]['option_values'])->toBe([])
        ->and($result[0]['variant_count'])->toBe(3)
        ->and($result[0]['swatch_options'])->toHaveCount(3);

    $swatchValues = collect($result[0]['swatch_options'])->pluck('value')->toArray();
    expect($swatchValues)->toContain('red', 'green', 'blue');
});

it('handles partial swatch coverage in swatches-only mode', function () {
    $this->product->update([
        'option_groups' => [
            [
                'name' => 'Màu sắc',
                'namespace' => 'mau-sac',
                'is_swatches' => true,
                'options' => [
                    ['value' => 'red', 'label' => 'Đỏ'],
                    ['value' => 'green', 'label' => 'Xanh'],
                    ['value' => 'blue', 'label' => 'Xanh dương'],
                ],
            ],
        ],
    ]);

    // Only create 2 variants out of 3 possible colors
    ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'SKU-001',
        'name' => 'Red Variant',
        'slug' => 'v-red',
        'price' => 15000000,
        'option_values' => ['mau-sac' => 'red'],
    ]);

    ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'SKU-002',
        'name' => 'Green Variant',
        'slug' => 'v-green',
        'price' => 15000000,
        'option_values' => ['mau-sac' => 'green'],
    ]);

    $result = $this->product->getGroupedVariantOptions();

    // Should have 1 card with 2 swatches (blue missing)
    expect($result)->toHaveCount(1)
        ->and($result[0]['option_values'])->toBe([])
        ->and($result[0]['variant_count'])->toBe(2)
        ->and($result[0]['swatch_options'])->toHaveCount(2);

    $swatchValues = collect($result[0]['swatch_options'])->pluck('value')->toArray();
    expect($swatchValues)->toContain('red', 'green')
        ->and($swatchValues)->not->toContain('blue');
});

it('creates cards for each non-swatches combination when no swatches defined', function () {
    // 2 materials × 2 sizes = 4 combinations, no swatches
    $this->product->update([
        'option_groups' => [
            [
                'name' => 'Chất liệu',
                'namespace' => 'chat-lieu',
                'is_swatches' => false,
                'options' => [
                    ['value' => 'da', 'label' => 'Da'],
                    ['value' => 'vai', 'label' => 'Vải'],
                ],
            ],
            [
                'name' => 'Kích thước',
                'namespace' => 'kich-thuoc',
                'is_swatches' => false,
                'options' => [
                    ['value' => 'small', 'label' => 'Nhỏ'],
                    ['value' => 'large', 'label' => 'Lớn'],
                ],
            ],
        ],
    ]);

    // 4 variants
    $variants = [
        ['chat-lieu' => 'da', 'kich-thuoc' => 'small'],
        ['chat-lieu' => 'da', 'kich-thuoc' => 'large'],
        ['chat-lieu' => 'vai', 'kich-thuoc' => 'small'],
        ['chat-lieu' => 'vai', 'kich-thuoc' => 'large'],
    ];

    foreach ($variants as $i => $opt) {
        ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'SKU-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            'name' => 'Variant '.($i + 1),
            'slug' => 'v-'.($i + 1),
            'price' => 15000000,
            'option_values' => $opt,
        ]);
    }

    $result = $this->product->getGroupedVariantOptions();

    // Should be 4 cards (2×2 combinations)
    expect($result)->toHaveCount(4)
        ->and(array_is_list($result))->toBeTrue();

    // First card: Da + Small, no swatches
    $first = $result[0];
    expect($first)->toHaveKeys(['option_values', 'swatch_options', 'variant_count'])
        ->and($first['option_values']['chat-lieu'])->toBe('da')
        ->and($first['option_values']['kich-thuoc'])->toBe('small')
        ->and($first['swatch_options'])->toBe([]);
});

it('handles partial variant coverage correctly', function () {
    // Only 2 variants out of 6 possible combinations
    ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'SKU-001',
        'name' => 'Variant 1',
        'slug' => 'v-001',
        'price' => 15000000,
        'option_values' => ['chat-lieu' => 'da', 'mau-sac' => 'charme-black'],
    ]);

    ProductVariant::create([
        'product_id' => $this->product->id,
        'sku' => 'SKU-002',
        'name' => 'Variant 2',
        'slug' => 'v-002',
        'price' => 15000000,
        'option_values' => ['chat-lieu' => 'vai', 'mau-sac' => 'olio-green'],
    ]);

    $result = $this->product->getGroupedVariantOptions();

    // Still 2 cards (one per material)
    expect($result)->toHaveCount(2);

    // Da card has 1 swatch
    expect($result[0]['variant_count'])->toBe(1)
        ->and($result[0]['swatch_options'])->toHaveCount(1)
        ->and($result[0]['swatch_options'][0]['value'])->toBe('charme-black');

    // Vải card has 1 swatch
    expect($result[1]['variant_count'])->toBe(1)
        ->and($result[1]['swatch_options'])->toHaveCount(1)
        ->and($result[1]['swatch_options'][0]['value'])->toBe('olio-green');
});

it('creates cards for multiple non-swatches × swatches combinations', function () {
    // 2 materials × 2 sizes × 3 colors = 12 possible variants
    $this->product->update([
        'option_groups' => [
            [
                'name' => 'Chất liệu',
                'namespace' => 'chat-lieu',
                'is_swatches' => false,
                'options' => [
                    ['value' => 'da', 'label' => 'Da'],
                    ['value' => 'vai', 'label' => 'Vải'],
                ],
            ],
            [
                'name' => 'Kích thước',
                'namespace' => 'kich-thuoc',
                'is_swatches' => false,
                'options' => [
                    ['value' => 'small', 'label' => 'Nhỏ'],
                    ['value' => 'large', 'label' => 'Lớn'],
                ],
            ],
            [
                'name' => 'Màu sắc',
                'namespace' => 'mau-sac',
                'is_swatches' => true,
                'options' => [
                    ['value' => 'black', 'label' => 'Đen'],
                    ['value' => 'green', 'label' => 'Xanh'],
                    ['value' => 'red', 'label' => 'Đỏ'],
                ],
            ],
        ],
    ]);

    // Create all 12 variants
    $idx = 0;
    foreach (['da', 'vai'] as $mat) {
        foreach (['small', 'large'] as $size) {
            foreach (['black', 'green', 'red'] as $color) {
                $idx++;
                ProductVariant::create([
                    'product_id' => $this->product->id,
                    'sku' => 'SKU-'.str_pad((string) $idx, 3, '0', STR_PAD_LEFT),
                    'name' => 'Variant '.$idx,
                    'slug' => 'v-'.$idx,
                    'price' => 15000000,
                    'option_values' => ['chat-lieu' => $mat, 'kich-thuoc' => $size, 'mau-sac' => $color],
                ]);
            }
        }
    }

    $result = $this->product->getGroupedVariantOptions();

    // Should be 4 cards (2 materials × 2 sizes), each with 3 swatches
    expect($result)->toHaveCount(4);

    foreach ($result as $card) {
        expect($card['swatch_options'])->toHaveCount(3);
        $values = collect($card['swatch_options'])->pluck('value')->toArray();
        expect($values)->toContain('black', 'green', 'red');
    }

    // Verify option_values combinations
    $combos = collect($result)->map(fn ($c) => $c['option_values'])->toArray();
    expect($combos)->toContain(
        ['chat-lieu' => 'da', 'kich-thuoc' => 'small'],
        ['chat-lieu' => 'da', 'kich-thuoc' => 'large'],
        ['chat-lieu' => 'vai', 'kich-thuoc' => 'small'],
        ['chat-lieu' => 'vai', 'kich-thuoc' => 'large'],
    );
});
