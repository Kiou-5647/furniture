<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Foundational data (must run first)
            GeodataSeeder::class,      // Provinces & wards
            LookupSeeder::class,       // Lookups: groups, rooms, styles, colors, materials, etc.

            // Demo data (roles, users, products, inventory)
            DemoDataSeeder::class,
        ]);
    }
}
