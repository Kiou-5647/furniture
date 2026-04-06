<?php

namespace App\Actions\Setting;

use App\Models\Setting\Lookup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpsertLookupAction
{
    public function execute(array $data, ?Lookup $lookup = null): Lookup
    {
        $imageFile = $data['image'] ?? null;
        unset($data['image'], $data['image_path']);

        DB::beginTransaction();

        try {
            if ($lookup && $lookup->id) {
                $lookup->update($data);
            } else {
                $lookup = Lookup::create($data);
            }

            if ($imageFile instanceof UploadedFile) {
                $lookup->addMedia($imageFile)->toMediaCollection('image');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $lookup;
    }
}
