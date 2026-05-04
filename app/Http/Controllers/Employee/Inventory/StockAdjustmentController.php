<?php

namespace App\Http\Controllers\Employee\Inventory;

use App\Actions\Inventory\RecordStockMovementAction;
use App\Enums\StockMovementType;
use App\Models\Inventory\Location;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController
{
    public function __construct(
        private RecordStockMovementAction $recordStockMovement,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'location_id' => 'required|exists:locations,id',
            'type' => 'required|string',
            'quantity' => 'nullable|integer|min:0',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'force_update_price' => 'boolean',
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $location = Location::findOrFail($validated['location_id']);

        $typeStr = $validated['type'];
        $quantity = (int) ($validated['quantity'] ?? 0);
        $costPerUnit = isset($validated['cost_per_unit']) ? (float) $validated['cost_per_unit'] : null;

        if ($typeStr == 'add') {
            if ($quantity <= 0) {
                return redirect()->back()->withErrors(['error' => 'Số lượng thêm phải lớn hơn 0']);
            }
            $type = StockMovementType::Receive;
        } elseif ($typeStr == 'remove') {
            if ($quantity <= 0) {
                return redirect()->back()->withErrors(['error' => 'Số lượng giảm phải lớn hơn 0']);
            }
            $type = StockMovementType::Adjust;
        } elseif ($typeStr == 'cost') {
            $type = StockMovementType::Adjust;
            $quantity = 0;
        } else {
            return redirect()->back()->withErrors(['error' => 'Loại điều chỉnh không hợp lệ']);
        }

        try {
            $movement = $this->recordStockMovement->handle(
                $variant,
                $location,
                $type,
                $quantity,
                $validated['notes'] ?? null,
                Auth::user()?->employee,
                null,
                null,
                $costPerUnit,
                $validated['force_update_price'] ?? false,
            );

            return redirect()->back()->with([
                'message' => 'Điều chỉnh tồn kho thành công',
                'movement' => $movement,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }
}
