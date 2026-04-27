<?php

use App\Models\Vendor\Vendor;
use App\Services\Cache\CacheInvalidator;
use App\Support\CacheTag;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear all relevant tags before each test
    CacheTag::Vendors->flush();
    CacheTag::VendorsList->flush();
    CacheTag::ShopMenu->flush();
    CacheTag::Categories->flush();
    CacheTag::Geodata->flush();
});

// ==========================================
// CacheTag Enum
// ==========================================

it('generates correct cache keys', function () {
    expect(CacheTag::Vendors->key('options'))->toBe('product.vendors:options')
        ->and(CacheTag::VendorsList->key('vendors_list:abc123'))->toBe('vendors_list:vendors_list:abc123')
        ->and(CacheTag::Geodata->key('provinces'))->toBe('geodata:provinces');
});

it('has consistent tag naming across domains', function () {
    // Product domain
    expect(CacheTag::Vendors->value)->toStartWith('product.')
        ->and(CacheTag::Collections->value)->toStartWith('product.')
        ->and(CacheTag::VariantOptions->value)->toStartWith('product.');
});

// ==========================================
// Cache Invalidator
// ==========================================

it('clears vendor option and list cache on vendor change', function (string $action) {
    $invalidator = app(CacheInvalidator::class);

    // Seed cached data
    Cache::tags([CacheTag::Vendors->value])->remember(CacheTag::Vendors->key('test'), 3600, fn () => 'vendor-options');
    Cache::tags([CacheTag::VendorsList->value])->remember('vendors_list:test', 3600, fn () => 'vendor-list');

    $invalidator->onVendorChanged();

    expect(Cache::tags([CacheTag::Vendors->value])->get(CacheTag::Vendors->key('test')))->toBeNull()
        ->and(Cache::tags([CacheTag::VendorsList->value])->get('vendors_list:test'))->toBeNull();
})->with(['create', 'update', 'delete']);

it('clears shop menu when category changes', function () {
    $invalidator = app(CacheInvalidator::class);

    Cache::tags([CacheTag::ShopMenu->value])->remember(CacheTag::ShopMenu->key('test'), 3600, fn () => 'shop-menu');

    $invalidator->onCategoryChanged();

    expect(Cache::tags([CacheTag::ShopMenu->value])->get(CacheTag::ShopMenu->key('test')))->toBeNull();
});

it('clears geodata cache on province/ward change', function () {
    $invalidator = app(CacheInvalidator::class);

    Cache::tags([CacheTag::Geodata->value])->remember(CacheTag::Geodata->key('test'), 3600, fn () => 'geodata');

    $invalidator->onGeodataChanged();

    expect(Cache::tags([CacheTag::Geodata->value])->get(CacheTag::Geodata->key('test')))->toBeNull();
});

it('clears collections cache on collection change', function () {
    $invalidator = app(CacheInvalidator::class);

    Cache::tags([CacheTag::Collections->value])->remember(CacheTag::Collections->key('test'), 3600, fn () => 'collections');

    $invalidator->onCollectionChanged();

    expect(Cache::tags([CacheTag::Collections->value])->get(CacheTag::Collections->key('test')))->toBeNull();
});

// ==========================================
// Observer Integration
// ==========================================

it('triggers cache invalidation when vendor is created', function () {
    Cache::tags([CacheTag::Vendors->value])->remember(CacheTag::Vendors->key('options'), 3600, fn () => 'cached');
    Cache::tags([CacheTag::VendorsList->value])->remember('test-list', 3600, fn () => 'list');

    Vendor::create([
        'name' => 'Test Vendor',
        'address_data' => ['street' => '123 Test St', 'full_address' => '123 Test St, Test Ward'],
    ]);

    // After model creation, observer should have flushed both tags
    expect(Cache::tags([CacheTag::Vendors->value])->get(CacheTag::Vendors->key('options')))->toBeNull()
        ->and(Cache::tags([CacheTag::VendorsList->value])->get('test-list'))->toBeNull();
});

it('triggers cache invalidation when vendor is updated', function () {
    $vendor = Vendor::create([
        'name' => 'Original Name',
        'address_data' => ['street' => '123 Test St', 'full_address' => '123 Test St, Test Ward'],
    ]);

    // Re-seed cache after creation (since create triggers invalidation)
    Cache::tags([CacheTag::Vendors->value])->remember(CacheTag::Vendors->key('options'), 3600, fn () => 'cached');

    $vendor->update(['name' => 'Updated Name']);

    expect(Cache::tags([CacheTag::Vendors->value])->get(CacheTag::Vendors->key('options')))->toBeNull();
});
