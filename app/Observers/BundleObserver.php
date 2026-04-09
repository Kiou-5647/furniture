<?php

namespace App\Observers;

use App\Models\Product\Bundle;

class BundleObserver
{
    public function deleting(Bundle $bundle): void
    {
        $bundle->contents()->delete();
    }

    public function restoring(Bundle $bundle): void
    {
        $bundle->contents()->withTrashed()->restore();
    }

    public function forceDeleting(Bundle $bundle): void
    {
        $bundle->contents()->forceDelete();
    }
}
