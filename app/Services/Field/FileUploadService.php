<?php

namespace App\Services\Field;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function upload(UploadedFile $file, string $folder, ?string $filename = null): string
    {
        if (! $filename) {
            $path = $file->store($folder, 'public');
        } else {
            // 2. Clean the custom filename and append the original extension
            $extension = $file->getClientOriginalExtension();
            $safeName = $filename.'-'.time().'.'.$extension;
            $path = $file->storeAs($folder, $safeName, 'public');
        }

        return Storage::url($path);
    }

    public function delete(?string $url): void
    {
        if (! $url) {
            return;
        }
        $path = str_replace(Storage::url(''), '', $url);
        Storage::disk('public')->delete($path);
    }
}
