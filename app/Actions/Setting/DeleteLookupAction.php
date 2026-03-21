<?php

namespace App\Actions\Setting;

use App\Models\Setting\Lookup;
use App\Services\Field\FileUploadService;

class DeleteLookupAction
{
    public function execute(Lookup $lookup): bool
    {
        // 1. Delete the image file from storage if it exists
        if ($lookup->image_path) {
            app(FileUploadService::class)->delete($lookup->image_path);
        }

        // 2. Delete the record from database
        return $lookup->delete();
    }
}
