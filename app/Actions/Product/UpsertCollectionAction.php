<?php

namespace App\Actions\Product;

use App\Models\Product\Collection;
use App\Services\Field\FileUploadService;
use Illuminate\Http\UploadedFile;

class UpsertCollectionAction
{
    public function execute(array $data, ?Collection $collection = null): Collection
    {
        if (isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $service = app(FileUploadService::class);
            if ($collection?->image_path) {
                $service->delete($collection->image_path);
            }
            $data['image_path'] = $service->upload($data['image_path'], 'collections', $data['slug']);
        } elseif (! isset($data['image_path']) || $data['image_path'] === null) {
            unset($data['image_path']);
        }

        if ($collection && $collection->id) {
            $collection->update($data);
        } else {
            $collection = Collection::create($data);
        }

        return $collection;
    }
}
