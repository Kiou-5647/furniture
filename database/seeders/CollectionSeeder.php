<?php

namespace Database\Seeders;

use App\Models\Product\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCollections();
    }

    protected function seedCollections(): void
    {
        Collection::firstOrCreate(
            ['slug' => 'timber'],
            ['display_name' => 'Timber', 'description' => 'Bộ sưu tập Ghế Timber', 'is_active' => true]
        );

        $this->command->info('Created Timber collection.');
    }
}
