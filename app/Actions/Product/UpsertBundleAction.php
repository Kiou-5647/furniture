<?php

namespace App\Actions\Product;

use App\Models\Product\Bundle;
use Illuminate\Support\Facades\DB;

class UpsertBundleAction
{
    public function execute(array $data, ?Bundle $bundle = null): Bundle
    {
        $contents = $data['contents'] ?? [];
        unset($data['contents']);

        return DB::transaction(function () use ($data, $contents, $bundle) {
            if ($bundle && $bundle->id) {
                $bundle->update($data);
            } else {
                $bundle = Bundle::create($data);
            }

            $this->syncContents($bundle, $contents);

            return $bundle->load('contents');
        });
    }

    protected function syncContents(Bundle $bundle, array $contents): void
    {
        $existingIds = $bundle->contents()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($contents as $contentData) {
            $contentId = $contentData['id'] ?? null;

            if ($contentId && in_array($contentId, $existingIds)) {
                $content = $bundle->contents()->find($contentId);
                $content->update([
                    'product_id' => $contentData['product_id'],
                    'quantity' => $contentData['quantity'],
                ]);
            } else {
                $content = $bundle->contents()->create([
                    'product_id' => $contentData['product_id'],
                    'quantity' => $contentData['quantity'],
                ]);
            }

            $submittedIds[] = $content->id;
        }

        $toDelete = array_diff($existingIds, $submittedIds);
        if (! empty($toDelete)) {
            $bundle->contents()->whereIn('id', $toDelete)->delete();
        }
    }
}
