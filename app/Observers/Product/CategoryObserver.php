<?php

namespace App\Observers\Product;

use App\Models\Product\Category;
use App\Services\Field\FileUploadService;

class CategoryObserver
{
    public function forceDeleted(Category $category): void
    {
        if ($category->image_path) {
            app(FileUploadService::class)->delete($category->image_path);
        }
    }
}
