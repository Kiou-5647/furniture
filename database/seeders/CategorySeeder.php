<?php

namespace Database\Seeders;

use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use MediaSeederTrait;

    private string $imageBase = 'images/categories';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategories();
    }

    protected function seedCategories(): void
    {
        // 1. Seatings
        $groupSeatings = Lookup::firstWhere('slug', 'ghe-ngoi');
        foreach (
            [
                ['display_name' => 'Ghế sofa & ghế đôi', 'slug' => 'ghe-sofa-va-ghe-doi', 'rooms' => ['phong-khach', 'van-phong']],
                ['display_name' => 'Ghế sofa góc', 'slug' => 'ghe-sofa-goc', 'rooms' => ['phong-khach', 'van-phong']],
                ['display_name' => 'Ghế đôi', 'slug' => 'ghe-doi', 'rooms' => ['phong-khach', 'phong-ngu']],
                ['display_name' => 'Ghế ăn', 'slug' => 'ghe-an', 'rooms' => ['phong-an']],
                ['display_name' => 'Ghế đẩu', 'slug' => 'ghe-dau', 'rooms' => ['phong-an']],
                ['display_name' => 'Ghế dài', 'slug' => 'ghe-dai', 'rooms' => ['phong-khach', 'phong-an']],
                ['display_name' => 'Ghế quầy bar', 'slug' => 'ghe-quay-bar', 'rooms' => ['phong-an']],
                ['display_name' => 'Băng ghế ăn', 'slug' => 'bang-ghe-an', 'rooms' => ['phong-an']],
                ['display_name' => 'Ghế mô đun', 'slug' => 'ghe-mo-dun', 'rooms' => ['phong-khach']],
                ['display_name' => 'Ghế ottoman', 'slug' => 'ghe-ottoman', 'rooms' => ['phong-khach']],
                ['display_name' => 'Ghế đệm', 'slug' => 'ghe-dem', 'rooms' => ['phong-khach']],
                ['display_name' => 'Ghế thư giãn', 'slug' => 'ghe-thu-gian', 'rooms' => ['phong-khach', 'phong-ngu']],
                ['display_name' => 'Ghế xoay', 'slug' => 'ghe-xoay', 'rooms' => ['phong-khach', 'van-phong']],
                ['display_name' => 'Ghế văn phòng', 'slug' => 'ghe-van-phong', 'rooms' => ['van-phong']],
                ['display_name' => 'Băng ghế', 'slug' => 'bang-ghe', 'rooms' => ['phong-khach']],
            ] as $d
        ) {
            $category = Category::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'display_name' => $d['display_name'],
                    'group_id' => $groupSeatings?->id,
                    'product_type' => 'noi-that',
                    'is_active' => true,
                ]
            );

            if (isset($d['rooms'])) {
                $roomIds = Lookup::whereIn('slug', $d['rooms'])->pluck('id')->toArray();
                $category->rooms()->sync($roomIds);
            }

            $this->attachMedia($category, "{$this->imageBase}/ghe-ngoi/{$d['slug']}.jpg");
        }
        $this->command->info('Seeded seating categories.');

        // 2. Tables
        $groupTables = Lookup::firstWhere('slug', 'ban');
        foreach (
            [
                ['display_name' => 'Bàn cà phê', 'slug' => 'ban-ca-phe', 'rooms' => ['phong-khach']],
                ['display_name' => 'Bàn phụ', 'slug' => 'ban-phu', 'rooms' => ['phong-khach', 'phong-ngu']],
                ['display_name' => 'Bàn điều khiển', 'slug' => 'ban-dieu-khien', 'rooms' => ['phong-khach']],
            ] as $d
        ) {
            $category = Category::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'display_name' => $d['display_name'],
                    'group_id' => $groupTables?->id,
                    'product_type' => 'noi-that',

                    'is_active' => true,
                ]
            );

            if (isset($d['rooms'])) {
                $roomIds = Lookup::whereIn('slug', $d['rooms'])->pluck('id')->toArray();
                $category->rooms()->sync($roomIds);
            }

            $this->attachMedia($category, "{$this->imageBase}/ban/{$d['slug']}.jpg");
        }
        $this->command->info('Seeded table categories.');

        // 3. Storages
        $groupStorage = Lookup::firstWhere('slug', 'luu-tru');
        foreach (
            [
                ['display_name' => 'Kệ TV', 'slug' => 'ke-tv', 'rooms' => ['phong-khach']],
                ['display_name' => 'Kệ sách', 'slug' => 'ke-sach', 'rooms' => ['phong-khach', 'phong-lam-viec']],
            ] as $d
        ) {
            $category = Category::updateOrCreate(
                ['slug' => $d['slug']],
                [
                    'display_name' => $d['display_name'],
                    'group_id' => $groupStorage?->id,
                    'product_type' => 'noi-that',
                    'is_active' => true,
                ]
            );

            if (isset($d['rooms'])) {
                $roomIds = Lookup::whereIn('slug', $d['rooms'])->pluck('id')->toArray();
                $category->rooms()->sync($roomIds);
            }

            $this->attachMedia($category, "{$this->imageBase}/luu-tru/{$d['slug']}.jpg");
        }
        $this->command->info('Seeded storage categories.');
    }
}
