<?php

namespace App\Actions\Product;

use App\Models\Product\Category;
use App\Services\Field\FileUploadService;
use Illuminate\Http\UploadedFile;

class UpsertCategoryAction
{
    public function execute(array $data, ?Category $category = null): Category
    {
        $roomIds = $data['room_ids'] ?? [];
        unset($data['room_ids']);

        if (isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $service = app(FileUploadService::class);
            if ($category?->image_path) {
                $service->delete($category->image_path);
            }
            $data['image_path'] = $service->upload($data['image_path'], 'categories', $data['slug']);
        } elseif (! isset($data['image_path']) || $data['image_path'] === null) {
            unset($data['image_path']);
        }

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

        return $category;
    }
}
