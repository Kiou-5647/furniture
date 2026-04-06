<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'id' => '019d533e-6636-73c6-b933-1b3d536335f7',
                'display_name' => 'Timber',
                'slug' => 'timber',
                'is_active' => true,
                'is_featured' => true,
                'description' => 'Với những chiếc đệm mềm mại, chất liệu đẹp mắt và nhiều kiểu dáng khác nhau, việc thư giãn trở nên dễ dàng. Ghế ngồi thoải mái và vẻ ngoài cổ điển của Timber mang đến sự ấm áp và dễ chịu cho không gian sống của bạn.',
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($collections as $collection) {
            DB::table('collections')->updateOrInsert(
                ['slug' => $collection['slug']],
                $collection,
            );
        }
    }
}
