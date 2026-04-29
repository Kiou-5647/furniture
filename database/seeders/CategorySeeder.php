<?php

namespace Database\Seeders;

use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use Database\Seeders\MediaSeederTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    use MediaSeederTrait;

    private string $imageBase = 'images/categories';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedRoomPlacements();
        $this->seedFilterableSpecs();
    }

    protected function seedCategories(): void
    {
        $path = base_path('docs/database/categories.php');
        if (!File::exists($path)) {
            $this->command->warn("Categories file not found at: {$path}");
            return;
        }

        require $path;

        foreach ($categories as $category) {
            // Use slug as the unique identifier to avoid duplicate key errors
            $cat = Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'id' => $category['id'],
                    'group_id' => $category['group_id'],
                    'product_type' => $category['product_type'],
                    'display_name' => $category['display_name'],
                    'description' => $category['description'],
                    'is_active' => $category['is_active'],
                    'created_at' => fake()->dateTimeBetween('-1 months', '-2 weeks'),
                    'updated_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
                ]
            );
            $this->attachMedia(
                $cat,
                "{$this->imageBase}/{$cat['slug']}",
                'image'
            );
        }
        $this->command->info('Seeded categories');
    }

    protected function seedRoomPlacements(): void
    {
        $path = base_path('docs/database/category_room_placement.php');
        if (!File::exists($path)) {
            $this->command->warn("Room placement file not found at: {$path}");
            return;
        }

        require $path;

        foreach ($placements as $placement) {
            // Since doc IDs might be integers/wrong, we rely on relational data
            // We find the category and lookup by the IDs provided in the doc
            $category = Category::find($placement['category_id']);
            $lookup = Lookup::find($placement['room_id']);

            if ($category && $lookup) {
                // Use a pivot table logic or a specific model if exists
                // Assuming a many-to-many relationship: category_room_placements
                $category->rooms()->syncWithoutDetaching([$lookup->id]);
            } else {
                $this->command->warn("Could not find Category or Lookup for placement: " . json_encode($placement));
            }
        }
        $this->command->info('Seeded category room placements');
    }

    protected function seedFilterableSpecs(): void
    {
        $path = base_path('docs/database/category_filterable_specs.php');
        if (!File::exists($path)) {
            $this->command->warn("Filterable specs file not found at: {$path}");
            return;
        }

        require $path;

        foreach ($specs as $spec) {
            $category = Category::find($spec['category_id']);
            $lookup = LookupNamespace::find($spec['namespace_id']);

            if ($category && $lookup) {
                // Assuming a many-to-many relationship: category_filterable_specs
                $category->filterableSpecs()->syncWithoutDetaching([$lookup->id]);
            } else {
                $this->command->warn("Could not find Category or Lookup for spec: " . json_encode($spec));
            }
        }
        $this->command->info('Seeded category filterable specs');
    }
}
