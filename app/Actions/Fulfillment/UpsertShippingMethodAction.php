<?php

namespace App\Actions\Fulfillment;

use App\Data\Fulfillment\CreateShippingMethodData;
use App\Models\Fulfillment\ShippingMethod;
use Illuminate\Support\Facades\DB;

class UpsertShippingMethodAction
{
    public function execute(CreateShippingMethodData $data, ?ShippingMethod $shippingMethod = null): ShippingMethod
    {
        return DB::transaction(function () use ($data, $shippingMethod) {
            if ($shippingMethod && $shippingMethod->id) {
                $shippingMethod->update([
                    'code' => $data->code,
                    'name' => $data->name,
                    'price' => $data->price,
                    'estimated_delivery_days' => $data->estimated_delivery_days,
                    'is_active' => $data->is_active,
                ]);
            } else {
                $shippingMethod = ShippingMethod::create([
                    'code' => $data->code,
                    'name' => $data->name,
                    'price' => $data->price,
                    'estimated_delivery_days' => $data->estimated_delivery_days,
                    'is_active' => $data->is_active,
                ]);
            }

            return $shippingMethod->fresh();
        });
    }
}
