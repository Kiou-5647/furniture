<?php

namespace Database\Seeders;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Carbon\Carbon;
use Database\Seeders\MediaSeederTrait;
use Illuminate\Database\Seeder;

class TimberProductDataSeeder extends Seeder
{
    use MediaSeederTrait;

    private string $imageBase = 'images/products/';

    public function run(): void
    {
        require base_path('docs/database/products.php');
        require base_path('docs/database/product_variants.php');

        foreach ($productsData as $data) {
            $product = $this->seedProduct($data);

            // Find variants belonging to this product
            $productVariants = array_filter($variantsData, fn($v) => $v['product_id'] === $product->id);

            foreach ($productVariants as $vData) {
                $this->seedVariant($product, $vData);
            }
        }
    }

    private function seedProduct(array $data): Product
    {
        // Decode JSONB columns
        $data['features'] = json_decode($data['features'], true);
        $data['specifications'] = json_decode($data['specifications'], true);
        $data['option_groups'] = json_decode($data['option_groups'], true);
        $data['filterable_options'] = json_decode($data['filterable_options'], true);
        $data['care_instructions'] = json_decode($data['care_instructions'], true);
        $data['assembly_info'] = json_decode($data['assembly_info'], true);

        $createdAt = Carbon::now()->subMonths(rand(1, 3));

        $oneMonthAgo = Carbon::now()->subMonth();
        $updatedAt = Carbon::createFromTimestamp(
            rand($createdAt->timestamp, $oneMonthAgo->timestamp)
        );
        $publishedDate = $updatedAt->copy();
        $newArrivalUntil = $publishedDate->copy()->addMonths(rand(1, 3));
        $isNewArrival = $newArrivalUntil->isFuture();

        $data['created_at'] = $createdAt;
        $data['updated_at'] = $updatedAt;
        $data['published_date'] = $publishedDate;
        $data['new_arrival_until'] = $newArrivalUntil;
        $data['is_new_arrival'] = $isNewArrival;

        return Product::updateOrCreate(
            ['id' => $data['id']],
            $data
        );
    }

    private function seedVariant(Product $product, array $data): void
    {
        // Decode JSONB columns
        $data['weight'] = json_decode($data['weight'], true);
        $data['dimensions'] = json_decode($data['dimensions'], true);
        $data['option_values'] = json_decode($data['option_values'], true);
        $data['features'] = json_decode($data['features'], true);
        $data['specifications'] = json_decode($data['specifications'], true);
        $data['care_instructions'] = json_decode($data['care_instructions'], true);
        $data['views_count'] = rand(200, 400);

        $variant = ProductVariant::updateOrCreate(
            ['id' => $data['id']],
            array_merge($data, ['product_id' => $product->id])
        );

        $this->attachVariantMedia($variant);
    }

    private function attachVariantMedia(ProductVariant $variant): void
    {
        // Since the new data files don't provide the 'folder' path,
        // we use a conventional path based on the SKU or Name.
        // You may need to adjust this path to match your storage structure.
        $folder = $variant->slug;
        $basePath = "{$this->imageBase}/{$folder}";

        $mediaMapping = [
            'primary_image' => 'primary_image.jpg',
            'hover_image' => 'hover_image.jpg',
            'dimension_image' => 'dimension_image.jpg',
            'swatch_image' => 'swatch_image.jpg',
        ];

        foreach ($mediaMapping as $collection => $filename) {
            $variant->clearMediaCollection($collection);
            $this->attachMedia($variant, "{$basePath}/{$filename}", $collection, clear: false);
        }

        $variant->clearMediaCollection('gallery');
        // Handle gallery images (1-10)
        for ($i = 1; $i <= 10; $i++) {
            $this->attachMedia($variant, "{$basePath}/gallery_{$i}.jpg", 'gallery', clear: false);
        }
    }
}
