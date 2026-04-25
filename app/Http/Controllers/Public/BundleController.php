<?php

namespace App\Http\Controllers\Public;

use App\Http\Resources\Public\Product\BundleResource;
use App\Models\Product\Bundle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BundleController
{
    public function show(Bundle $bundle): Response
    {
        // Load the bundle with its contents and the associated product cards + their variants
        $bundle->load([
            'contents.productCard.variants',
            'contents.productCard.product'
        ]);

        return Inertia::render('public/bundle/Show', [
            'bundle' => new BundleResource($bundle),
        ]);
    }
}
