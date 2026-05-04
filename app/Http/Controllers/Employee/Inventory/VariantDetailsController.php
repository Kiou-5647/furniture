<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VariantDetailsController
{
    public function show(Request $request, string $id): JsonResponse
    {
        $variant = ProductVariant::with('product.vendor')->findOrFail($id);

        return response()->json([
            'id' => $variant->id,
            'name' => $variant->name,
            'product_name' => $variant->product->name,
            'vendor_name' => $variant->product->vendor?->name,
        ]);
    }
}
