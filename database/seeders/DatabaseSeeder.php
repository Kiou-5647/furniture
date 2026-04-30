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
            RolesAndPermissionsSeeder::class,
            GeodataSeeder::class,
            ProvinceRegionSeeder::class,
            LocationSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            DesignerSeeder::class,
            CustomerSeeder::class,
            VendorSeeder::class,
            LookupSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            TimberProductDataSeeder::class,
            InventorySeeder::class,
            ShippingMethodSeeder::class,
            InitialStockSeeder::class
        ]);
    }
}
