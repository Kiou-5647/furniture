<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Models\Inventory\Location;
use App\Models\Setting\Province;
use App\Models\Setting\Ward;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedLocations();
        Location::factory()->warehouse()->count(20)->create();
    }

    protected function seedLocations(): void
    {
        $hn = Province::firstWhere('province_code', '01');
        $hcm = Province::firstWhere('province_code', '79');
        $hnWard = Ward::firstWhere('province_code', '01');
        $hcmWard = Ward::firstWhere('province_code', '79');

        $data = [
            ['code' => 'WH-001', 'name' => 'Kho chính Hà Nội', 'type' => LocationType::Warehouse, 'province' => $hn, 'ward' => $hnWard, 'is_active' => true],
            ['code' => 'WH-002', 'name' => 'Kho phụ HCM', 'type' => LocationType::Warehouse, 'province' => $hcm, 'ward' => $hcmWard, 'is_active' => true],
            ['code' => 'RT-001', 'name' => 'Cửa hàng Cầu Giấy', 'type' => LocationType::Retail, 'province' => $hn, 'ward' => $hnWard, 'is_active' => true],
            ['code' => 'RT-002', 'name' => 'Cửa hàng Đống Đa', 'type' => LocationType::Retail, 'province' => $hn, 'ward' => $hnWard, 'is_active' => true],
        ];

        foreach ($data as $d) {
            Location::firstOrCreate(
                ['code' => $d['code']],
                [
                    'name' => $d['name'],
                    'type' => $d['type'],
                    'province_code' => $d['province']->province_code ?? null,
                    'province_name' => $d['province']->name ?? null,
                    'ward_code' => $d['ward']->ward_code ?? null,
                    'ward_name' => $d['ward']->name ?? null,
                    'is_active' => $d['is_active'],
                ]
            );
        }
    }
}
