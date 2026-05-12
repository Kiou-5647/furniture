<?php

namespace App\Http\Controllers\Employee\Product;

use App\Actions\Product\UpsertCategoryAction;
use App\Data\Product\CategoryFilterData;
use App\Http\Requests\Product\Category\StoreCategoryRequest;
use App\Http\Requests\Product\Category\UpdateCategoryRequest;
use App\Http\Resources\Employee\Product\CategoryResource;
use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Services\Product\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController
{
    public function __construct(private CategoryService $service) {}

    public function index(Request $request, ?string $groupSlug = null)
    {
        if (!Gate::allows('viewAny', Category::class)) {
            return back()->with('error', 'Bạn không có quyền xem danh mục sản phẩm.');
        }

        $group = $groupSlug ? Lookup::where('slug', $groupSlug)->first() : null;
        $filter = CategoryFilterData::fromRequest($request, $group?->id);

        return Inertia::render('employee/products/categories/Index', [
            'categories' => Inertia::defer(fn() => CategoryResource::collection(
                $this->service->getFiltered($filter)
            )),
            'categoryGroups' => $this->service->getCategoryGroups(),
            'roomOptions' => $this->service->getRoomOptions(),
            'specOptions' => $this->service->getFilterableSpecOptions(),
            'filters' => $filter,
            'currentGroup' => $group,
        ]);
    }

    public function store(StoreCategoryRequest $request, UpsertCategoryAction $action)
    {
        if (!Gate::allows('create', Category::class)) {
            return back()->with('error', 'Bạn không có quyền tạo danh mục mới.');
        }

        try {
            $action->execute($request->validated());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã thêm danh mục mới.');
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpsertCategoryAction $action)
    {
        if (!Gate::allows('update', $category)) {
            return back()->with('error', 'Bạn không có quyền cập nhật danh mục này.');
        }

        try {
            $action->execute($request->validated(), $category);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        if (!Gate::allows('delete', $category)) {
            return back()->with('error', 'Bạn không có quyền xóa danh mục này.');
        }
        $category->delete();

        return back()->with('success', 'Đã xóa danh mục.');
    }
}
