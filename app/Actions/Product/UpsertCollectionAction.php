<?php

namespace App\Actions\Product;

use App\Models\Product\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpsertCollectionAction
{
    public function execute(array $data, ?Collection $collection = null): Collection
    {
        $imageFile = $data['image'] ?? null;
        $bannerFile = $data['banner'] ?? null;
        unset($data['image'], $data['banner'], $data['image_path']);

        DB::beginTransaction();

        try {
            if ($collection && $collection->id) {
                $collection->update($data);
            } else {
                $collection = Collection::create($data);
            }

            if ($imageFile instanceof UploadedFile) {
                $collection->addMedia($imageFile)->toMediaCollection('image');
            }

            if ($bannerFile instanceof UploadedFile) {
                $collection->addMedia($bannerFile)->toMediaCollection('banner');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $collection;
    }
}
