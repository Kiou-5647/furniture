<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $lookups = DB::table('lookups')->pluck('id', 'slug')->toArray();
        $groupId = $lookups['ghe-ngoi'] ?? null;

        if (! $groupId) {
            $this->command->warn('Lookup "noi-that" not found. Skipping category seeding.');

            return;
        }

        $categories = [
            [
                'id' => '019d5348-d1de-736c-ad3c-03357c017382',
                'group_id' => $groupId,
                'product_type' => 'noi-that',
                'display_name' => 'Ghế Sofa',
                'slug' => 'sofas',
                'description' => null,
                'is_active' => true,
                'metadata' => json_encode([]),
                'filterable_specs' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
