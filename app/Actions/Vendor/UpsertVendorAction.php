<?php

namespace App\Actions\Vendor;

use App\Data\Vendor\CreateVendorData;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpsertVendorAction
{
    public function execute(CreateVendorData $data, ?Vendor $vendor = null): Vendor
    {
        return DB::transaction(function () use ($data, $vendor) {
            if ($vendor) {
                $vendor->update($data->toArray());

                return $vendor;
            }

            return Vendor::create($data->toArray());
        });
    }
}
