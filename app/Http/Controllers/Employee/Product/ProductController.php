<?php

namespace App\Http\Controllers\Employee\Product;

use App\Actions\Product\UpsertProductAction;
use App\Data\Product\ProductFilterData;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Employee\Product\ProductResource;
use App\Models\Product\Product;
use App\Services\Product\ProductService;
use App\Services\Setting\LookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController
{
    public function __construct(
        private ProductService $service,
        private LookupService $lookupService,
    ) {}

    public function index(Request $request): Response
    {
        $filter = ProductFilterData::fromRequest($request);

        return Inertia::render('employee/products/products/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'vendorOptions' => $this->service->getVendorOptions(),
            'categoryOptions' => $this->service->getCategoryOptions(),
            'collectionOptions' => $this->service->getCollectionOptions(),
            'locationOptions' => $this->service->getLocationOptions(),
            'variantOptions' => $this->service->getVariantOptions(),
            'featureOptions' => $this->service->getFeatureOptions(),
            'specNamespaces' => $this->service->getSpecNamespaces(),
            'allSpecLookupOptions' => $this->service->getAllSpecLookupOptions(),
            'lookupNamespaces' => $this->lookupService->getNamespaces(),
            'products' => Inertia::defer(fn () => ProductResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = ProductFilterData::fromRequest($request);

        return Inertia::render('employee/products/products/Trash', [
            'products' => Inertia::defer(fn () => ProductResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['vendor', 'category', 'collection']);

        return Inertia::render('employee/products/products/Show', [
            'product' => new ProductResource($product),
        ]);
    }

    public function store(StoreProductRequest $request, UpsertProductAction $action)
    {
        Gate::authorize('create', Product::class);

        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm sản phẩm mới.');
    }

    public function update(UpdateProductRequest $request, Product $product, UpsertProductAction $action)
    {
        Gate::authorize('manage', $product);

        $action->execute($request->validated(), $product);

        return back()->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function restore(Product $product)
    {
        Gate::authorize('manage', $product);

        $product->restore();

        return back()->with('success', 'Đã khôi phục sản phẩm.');
    }

    public function destroy(Product $product)
    {
        Gate::authorize('manage', $product);

        $product->delete();

        return back()->with('success', 'Đã xóa sản phẩm.');
    }

    public function forceDestroy(Product $product)
    {
        Gate::authorize('manage', $product);

        $product->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn sản phẩm.');
    }
}
