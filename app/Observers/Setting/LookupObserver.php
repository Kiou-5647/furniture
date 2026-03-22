<?php

namespace App\Observers\Setting;

use App\Models\Setting\Lookup;
use App\Services\Field\FileUploadService;

class LookupObserver
{
    public function forceDeleted(Lookup $lookup): void
    {
        if ($lookup->image_path) {
            app(FileUploadService::class)->delete($lookup->image_path);
        }
    }
}
