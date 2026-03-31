<?php

namespace App\Actions\Product;

use App\Models\Product\Category;
use Illuminate\Http\UploadedFile;

class UpsertCategoryAction
{
    public function execute(array $data, ?Category $category = null): Category
    {
        $roomIds = $data['room_ids'] ?? [];
        unset($data['room_ids']);

        $imageFile = $data['image'] ?? null;
        unset($data['image']);

        if ($category && $category->id) {
            $category->update($data);
        } else {
            $category = Category::create($data);
        }

        if (! empty($roomIds)) {
            $category->rooms()->sync($roomIds);
        } else {
            $category->rooms()->detach();
        }

        if ($imageFile instanceof UploadedFile) {
            $category->addMedia($imageFile)->toMediaCollection('image');
        }

        return $category;
    }
}
