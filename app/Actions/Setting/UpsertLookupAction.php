<?php

namespace App\Actions\Setting;

use App\Models\Setting\Lookup;
use App\Services\Field\FileUploadService;
use Illuminate\Http\UploadedFile;

class UpsertLookupAction
{
    /**
     * Execute the action to create or update a lookup record.
     */
    public function execute(array $data, ?Lookup $lookup = null): Lookup
    {
        if (isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $service = app(FileUploadService::class);

            if ($lookup && $lookup->image_path) {
                $service->delete($lookup->image_path);
            }

            $filenamePrefix = $data['slug'] ?? ($lookup ? $lookup->slug : 'lookup');
            $data['image_path'] = $service->upload($data['image_path'], 'lookups', $filenamePrefix);
        } else {
            unset($data['image_path']);
        }

        return Lookup::updateOrCreate(['id' => $lookup?->id], $data);
    }
}
