<?php

namespace Database\Seeders;

use App\Models\Setting\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceRegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            // REGION 2: MIỀN BẮC (North)
            'North' => [
                'Thành phố Hà Nội',
                'Thành phố Hải Phòng',
                'Tuyên Quang',
                'Lào Cai',
                'Thái Nguyên',
                'Phú Thọ',
                'Bắc Ninh',
                'Hưng Yên',
                'Ninh Bình',
                'Cao Bằng',
                'Điện Biên',
                'Lai Châu',
                'Lạng Sơn',
                'Sơn La',
                'Quảng Ninh',
            ],
            // REGION 1: MIỀN TRUNG (Central)
            'Central' => [
                'Thành phố Đà Nẵng',
                'Thành phố Huế',
                'Quảng Trị',
                'Quảng Ngãi',
                'Khánh Hòa',
                'Gia Lai',
                'Lâm Đồng',
                'Thanh Hóa',
                'Nghệ An',
                'Hà Tĩnh',
                'Đắk Lắk',
            ],
            // REGION 0: MIỀN NAM (South)
            'South' => [
                'Thành phố Hồ Chí Minh',
                'Thành phố Cần Thơ',
                'Tây Ninh',
                'Đồng Nai',
                'Vĩnh Long',
                'Đồng Tháp',
                'Cà Mau',
                'An Giang',

            ],
        ];

        $regionMap = [
            'South' => 0,
            'Central' => 1,
            'North' => 2,
        ];

        foreach ($regions as $regionName => $provinces) {
            $regionId = $regionMap[$regionName];

            foreach ($provinces as $provinceName) {
                // We match by name. If your table uses codes, you should map codes instead.
                Province::where('name', 'like', "%{$provinceName}%")
                    ->update(['region_id' => $regionId]);
            }
        }
    }
}
