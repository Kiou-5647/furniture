<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait MediaSeederTrait
{
    /**
     * Attach a file from local storage to a model's media collection.
     *
     * @param  Model  $model  The model instance to attach media to.
     * @param  string  $path  The path relative to the specified disk (e.g., 'images/products/folder/image.jpg').
     * @param  string  $collection  The Spatie MediaLibrary collection name (e.g., 'primary_image').
     * @param  string  $disk  The storage disk to use (default: 'local').
     */
    protected function attachMedia(Model $model, string $path, string $collection = 'image', string $disk = 'local'): void
    {
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            $this->command->warn("Media file not found at: {$path}");

            return;
        }

        $model->addMedia($storage->path($path))
            ->preservingOriginal()
            ->toMediaCollection($collection, 'public');
    }
}
