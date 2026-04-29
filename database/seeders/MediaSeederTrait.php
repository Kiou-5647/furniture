<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait MediaSeederTrait
{
    protected array $commonImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

    protected function attachMedia(Model $model, string $path, string $collection = 'image', string $disk = 'local'): void
    {
        $storage = Storage::disk($disk);
        $finalPath = $path;

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (empty($extension) || !in_array(strtolower($extension), $this->commonImageExtensions)) {
            foreach ($this->commonImageExtensions as $ext) {
                $attemptPath = $path . '.' . $ext;
                if ($storage->exists($attemptPath)) {
                    $finalPath = $attemptPath;
                    break;
                }
            }
        }

        if (! $storage->exists($finalPath)) {
            $this->command->warn("Media file not found at: {$finalPath}");

            return;
        }
        $this->command->info("Found: {$finalPath}");

        $model->addMedia($storage->path($finalPath))
            ->preservingOriginal()
            ->toMediaCollection($collection, 'public');
    }
}
