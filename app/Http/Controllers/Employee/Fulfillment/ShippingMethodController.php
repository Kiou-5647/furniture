<?php

namespace App\Http\Controllers\Employee\Fulfillment;

use App\Models\Fulfillment\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ShippingMethodController
{
    public function index(): Response
    {
        $methods = ShippingMethod::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('employee/fulfillment/shipping-methods/Index', [
            'shippingMethods' => $methods,
        ]);
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('shipping_methods.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        ShippingMethod::create($request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:shipping_methods,code'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('success', 'Đã thêm phương thức vận chuyển.');
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        if (! Auth::user()->can('shipping_methods.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $shippingMethod->update($request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:shipping_methods,code,'.$shippingMethod->id],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]));

        return back()->with('success', 'Đã cập nhật phương thức vận chuyển.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        if (! Auth::user()->can('shipping_methods.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $shippingMethod->delete();

        return back()->with('success', 'Đã xóa phương thức vận chuyển.');
    }
}
