<?php

namespace App\Actions\Setting;

use App\Models\Setting\Lookup;
use Illuminate\Http\UploadedFile;

class UpsertLookupAction
{
    /**
     * Execute the action to create or update a lookup record.
     */
    public function execute(array $data, ?Lookup $lookup = null): Lookup
    {
        // 1. Extract the uploaded file (if any)
        $imageFile = $data['image'] ?? null;
        unset($data['image'], $data['image_path']);
        // 2. Create or Update the model
        if ($lookup && $lookup->id) {
            $lookup->update($data);
        } else {
            $lookup = Lookup::create($data);
        }
        // 3. Attach the Media!
        if ($imageFile instanceof UploadedFile) {
            $lookup->addMedia($imageFile)->toMediaCollection('image');
        }

        return $lookup;
    }
}
