<?php

namespace App\Http\Controllers\Employee\Product;

use App\Actions\Product\UpsertProductAction;
use App\Data\Product\ProductFilterData;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\EmployeeProductResource;
use App\Models\Product\Product;
use App\Services\Product\ProductService;
use App\Services\Setting\LookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'products' => Inertia::defer(fn () => EmployeeProductResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = ProductFilterData::fromRequest($request);

        return Inertia::render('employee/products/products/Trash', [
            'products' => Inertia::defer(fn () => EmployeeProductResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(StoreProductRequest $request, UpsertProductAction $action)
    {
        $action->execute($request->validated());

        return back()->with('success', 'Đã thêm sản phẩm mới.');
    }

    public function update(UpdateProductRequest $request, Product $product, UpsertProductAction $action)
    {
        $action->execute($request->validated(), $product);

        return back()->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function restore(Product $product)
    {
        if (! Auth::user()->can('products.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $product->restore();

        return back()->with('success', 'Đã khôi phục sản phẩm.');
    }

    public function destroy(Product $product)
    {
        if (! Auth::user()->can('products.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $product->delete();

        return back()->with('success', 'Đã xóa sản phẩm.');
    }

    public function forceDestroy(Product $product)
    {
        if (! Auth::user()->can('products.manage')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa sản phẩm!');
        }

        $product->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn sản phẩm.');
    }
}
