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
        $roomIds = $data['room_ids'] ?? [];
        unset($data['image'], $data['room_ids']);

        DB::beginTransaction();

        try {
            if ($category && $category->id) {
                $category->update($data);
            } else {
                $category = Category::create($data);
            }

            $category->rooms()->sync($roomIds);

            if ($imageFile instanceof UploadedFile) {
                $category->addMedia($imageFile)->toMediaCollection('image');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $category;
    }
}
