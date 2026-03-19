<?php

namespace App\Actions\Setting;

use App\Models\Setting\Lookup;
use App\Services\Field\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpsertLookupAction
{
    /**
     * Execute the action to create or update a lookup record.
     */
    public function execute(array $data, ?Lookup $lookup = null): Lookup
    {
        if (isset($data['key'])) {
            $data['key'] = Str::slug($data['key']);
        }

        $metadata = $lookup ? ($lookup->metadata ?? []) : ($data['metadata'] ?? []);

        if (isset($data['metadata']['image']) && $data['metadata']['image'] instanceof UploadedFile) {
            Log::info('Image detected');

            $service = app(FileUploadService::class);
            if ($lookup && isset($lookup->metadata['image'])) {
                $service->delete($lookup->metadata['image']);
            }

            $filenamePrefix = $data['key'] ?? ($lookup ? $lookup->key : 'lookup');
            $metadata['image'] = $service->upload($data['metadata']['image'], 'lookups', $filenamePrefix);
        }

        $data['metadata'] = $metadata;

        if ($lookup) {
            $lookup->update($data);

            return $lookup;
        }

        // 3. Otherwise, create a new record
        return Lookup::create($data);
    }
}
