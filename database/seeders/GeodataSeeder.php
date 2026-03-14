<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeodataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data.json')), true);
        foreach ($data as $province) {
            DB::table('provinces')->insert([
                'province_code' => $province['province_code'],
                'name' => $province['name'],
                'short_name' => $province['short_name'],
                'code' => $province['code'],
                'place_type' => $province['place_type'],
            ]);
            foreach ($province['wards'] as $ward) {
                DB::table('wards')->insert([
                    'ward_code' => $ward['ward_code'],
                    'province_code' => $ward['province_code'],
                    'name' => $ward['name'],
                ]);
            }
        }
    }
}
