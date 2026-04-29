<?php

namespace App\Actions\Product;

use App\Models\Product\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpsertCategoryAction
{
    public function execute(array $data, ?Category $category = null): Category
    {
        $imageFile = $data['image'] ?? null;
        $imageUrl = $data['image_url'] ?? null;
        $roomIds = $data['room_ids'] ?? [];
        $specsIds = $data['filterable_specs'] ?? [];
        unset($data['image'], $data['image_url'], $data['room_ids'], $data['filterable_specs']);

        DB::beginTransaction();

        try {
            if ($category && $category->id) {
                $category->update($data);
            } else {
                $category = Category::create($data);
            }

            $category->rooms()->sync($roomIds);
            $category->filterableSpecs()->sync($specsIds);

            if ($imageFile instanceof UploadedFile) {
                $category->addMedia($imageFile)->toMediaCollection('image');
            }

            if (!$imageUrl && !$imageFile) {
                $category->clearMediaCollection('image');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $category;
    }
}
