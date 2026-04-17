<?php

namespace App\Providers;

use App\Models\Booking\Booking;
use App\Models\Customer\Review;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use App\Models\Product\Bundle;
use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Models\Setting\Province;
use App\Models\Setting\Ward;
use App\Models\Vendor\Vendor;
use App\Observers\BookingObserver;
use App\Observers\BundleObserver;
use App\Observers\CacheInvalidationObserver;
use App\Observers\InvoiceObserver;
use App\Observers\LocationObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use App\Observers\ReviewObserver;
use App\Observers\ShipmentItemObserver;
use App\Observers\ShipmentObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::unguard();
        $this->configureDefaults();
        $this->registerObservers();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }

    protected function registerObservers(): void
    {
        // Domain-specific observers
        Bundle::observe(BundleObserver::class);
        Location::observe(LocationObserver::class);
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);

        // Cache invalidation observer (handles created, updated, deleted, restored)
        Bundle::observe(CacheInvalidationObserver::class);
        Lookup::observe(CacheInvalidationObserver::class);
        LookupNamespace::observe(CacheInvalidationObserver::class);
        Category::observe(CacheInvalidationObserver::class);
        Collection::observe(CacheInvalidationObserver::class);
        Vendor::observe(CacheInvalidationObserver::class);
        Location::observe(CacheInvalidationObserver::class);
        StockTransfer::observe(CacheInvalidationObserver::class);
        Province::observe(CacheInvalidationObserver::class);
        Ward::observe(CacheInvalidationObserver::class);

        // Fulfillment observers
        Shipment::observe(ShipmentObserver::class);
        ShipmentItem::observe(ShipmentItemObserver::class);

        // Sales observers
        Booking::observe(BookingObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Order::observe(OrderObserver::class);
        Review::observe(ReviewObserver::class);
    }
}
