<?php

namespace Database\Seeders;

use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LookupSeeder extends Seeder
{
    use MediaSeederTrait;

    private string $imageBase = 'images/lookups';

    public function run(): void
    {
        $this->seedNamespaces();
        $this->seedLookup();
    }

    protected function seedNamespaces(): void
    {
        $path = base_path('docs/database/lookup_namespaces.php');
        if (!File::exists($path)) {
            $this->command->warn("Lookup namespaces file not found at: {$path}");
            return;
        }

        $lookup_namespaces = require $path;

        foreach ($lookup_namespaces as $ns) {
            LookupNamespace::updateOrCreate(
                ['slug' => $ns['slug']],
                [
                    ...$ns,
                    'created_at' => fake()->dateTimeBetween('-1 months', '-2 weeks'),
                    'updated_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
                ]
            );
        }
        $this->command->info('Seeded lookup namespaces');
    }

    protected function seedLookup(): void
    {
        $path = base_path('docs/database/lookups.php');
        if (!File::exists($path)) {
            $this->command->warn("Lookups file not found at: {$path}");
            return;
        }

        $lookups = require $path;

        foreach ($lookups as $lookup) {
            $lk = Lookup::updateOrCreate(
                ['namespace_id' => $lookup['namespace_id'], 'slug' => $lookup['slug']],
                [
                    ...$lookup,
                    'created_at' => fake()->dateTimeBetween('-1 months', '-2 weeks'),
                    'updated_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
                ]
            );

            $this->attachMedia(
                $lk,
                "{$this->imageBase}/{$lk->slug}",
                'image'
            );
        }
        $this->command->info('Seeded lookups');
    }
}
