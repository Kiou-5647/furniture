<?php

namespace App\Actions\Setting;

use App\Models\Setting\Lookup;
use App\Services\Field\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UpsertLookupAction
{
    /**
     * Execute the action to create or update a lookup record.
     */
    public function execute(array $data, ?Lookup $lookup = null): Lookup
    {
        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

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

        if ($lookup && ! isset($data['metadata'])) {
            $data['metadata'] = $lookup->metadata;
        }

        if ($lookup) {
            $lookup->update($data);

            return $lookup;
        }

        return Lookup::create($data);
    }
}
