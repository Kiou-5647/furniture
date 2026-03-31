<?php

namespace App\Support\MediaLibrary;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        // Extracts the class name, lowercases it, and pluralizes it.
        // E.g., App\Models\Product\Category -> "categories"
        // E.g., App\Models\Auth\User -> "users"
        $modelDirectory = Str::plural(Str::lower(class_basename($media->model_type)));

        // Resulting Path: categories/123e4567-e89b-12d3.../1/
        // Meaning: {Models}/{Model_UUID}/{Media_ID}/
        return $modelDirectory.'/'.$media->model_id.'/'.$media->getKey();
    }
}
