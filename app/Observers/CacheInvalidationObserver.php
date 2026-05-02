<?php

namespace App\Observers;

use App\Services\Cache\CacheInvalidator;
use Illuminate\Database\Eloquent\Model;

class CacheInvalidationObserver
{
    public function __construct(
        private CacheInvalidator $invalidator,
    ) {}

    public function created(Model $model): void
    {
        $this->handle($model);
    }

    public function updated(Model $model): void
    {
        $this->handle($model);
    }

    public function deleted(Model $model): void
    {
        $this->handle($model);
    }

    public function restored(Model $model): void
    {
        $this->handle($model);
    }

    protected function handle(Model $model): void
    {
        $class = class_basename($model);

        match ($class) {
            'Lookup' => $this->invalidator->onLookupChanged(),
            'LookupNamespace' => $this->invalidator->onLookupNamespaceChanged($model),
            'Category' => $this->invalidator->onCategoryChanged($model),
            'Collection' => $this->invalidator->onCollectionChanged(),
            'Vendor' => $this->invalidator->onVendorChanged(),
            'Location' => $this->invalidator->onLocationChanged(),
            'StockTransfer' => $this->invalidator->onStockTransferChanged(),
            'Province', 'Ward' => $this->invalidator->onGeodataChanged(),
            'Product', 'ProductVariant' => $this->invalidator->onProductChanged(),
            default => null,
        };
    }
}
