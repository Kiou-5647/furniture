<?php

namespace Database\Seeders;

use App\Models\Product\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CollectionSeeder extends Seeder
{
    use MediaSeederTrait;

    private string $imageBase = 'images/collections';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCollections();
    }

    protected function seedCollections(): void
    {
        $path = base_path('docs/database/collections.php');
        if (!File::exists($path)) {
            $this->command->warn("Collections file not found at: {$path}");
            return;
        }

        require $path;

        foreach ($collections as $collection) {
            $model = Collection::updateOrCreate(
                ['slug' => $collection['slug']],
                [
                    ...$collection,
                    'created_at' => fake()->dateTimeBetween('-1 months', '-2 weeks'),
                    'updated_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
                ],
            );

            $this->attachMedia(
                $model,
                "{$this->imageBase}/{$collection['slug']}",
                'image'
            );
        }
        $this->command->info("Collections seeded");
    }
}
