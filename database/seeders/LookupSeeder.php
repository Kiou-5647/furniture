<?php

namespace Database\Seeders;

use App\Models\Setting\Lookup;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategoryGroups();
        $this->seedRooms();
        $this->seedStyles();
        $this->seedColors();
    }

    protected function seedCategoryGroups(): void
    {
        $groups = [
            // Seating - Ghế ngồi
            ['slug' => 'ghe-ngoi', 'display_name' => 'Ghế ngồi', 'namespace' => 'nhom-danh-muc', 'description' => 'Các loại ghế ngồi trong nhà'],

            // Tables - Bàn
            ['slug' => 'ban', 'display_name' => 'Bàn', 'namespace' => 'nhom-danh-muc', 'description' => 'Các loại bàn trong nhà'],

            // Storage - Lưu trữ
            ['slug' => 'luu-tru', 'display_name' => 'Lưu trữ', 'namespace' => 'nhom-danh-muc', 'description' => 'Tủ, kệ, ngăn lưu trữ'],

            // Sleeping - Ngủ nghỉ
            ['slug' => 'ngu-nghi', 'display_name' => 'Ngủ nghỉ', 'namespace' => 'nhom-danh-muc', 'description' => 'Giường, đệm, gối'],

            // Lighting - Chiếu sáng
            ['slug' => 'chieu-sang', 'display_name' => 'Chiếu sáng', 'namespace' => 'nhom-danh-muc', 'description' => 'Đèn và thiết bị chiếu sáng'],

            // Decoration - Trang trí
            ['slug' => 'trang-tri', 'display_name' => 'Trang trí', 'namespace' => 'nhom-danh-muc', 'description' => 'Đồ trang trí nội thất'],

            // Textiles - Dệt may
            ['slug' => 'det-may', 'display_name' => 'Dệt may', 'namespace' => 'nhom-danh-muc', 'description' => 'Rèm, thảm, vải'],

            // Outdoor - Ngoài trời
            ['slug' => 'ngoai-troi', 'display_name' => 'Ngoài trời', 'namespace' => 'nhom-danh-muc', 'description' => 'Nội thất sân vườn, ngoài trời'],
        ];

        foreach ($groups as $group) {
            Lookup::updateOrCreate(
                ['namespace' => $group['namespace'], 'slug' => $group['slug']],
                $group
            );
        }
        $this->command->info('Seeded category groups');
    }

    protected function seedRooms(): void
    {
        $rooms = [
            ['slug' => 'phong-khach', 'display_name' => 'Phòng khách', 'namespace' => 'phong'],
            ['slug' => 'phong-ngu', 'display_name' => 'Phòng ngủ', 'namespace' => 'phong'],
            ['slug' => 'phong-an', 'display_name' => 'Phòng ăn', 'namespace' => 'phong'],
            ['slug' => 'nha-bep', 'display_name' => 'Nhà bếp', 'namespace' => 'phong'],
            ['slug' => 'phong-tam', 'display_name' => 'Phòng tắm', 'namespace' => 'phong'],
            ['slug' => 'van-phong', 'display_name' => 'Văn phòng', 'namespace' => 'phong'],
            ['slug' => 'hanh-lang', 'display_name' => 'Hành lang', 'namespace' => 'phong'],
            ['slug' => 'san-vuon', 'display_name' => 'Sân vườn', 'namespace' => 'phong'],
        ];
        foreach ($rooms as $room) {
            Lookup::updateOrCreate(
                ['namespace' => $room['namespace'], 'slug' => $room['slug']],
                $room
            );
        }
        $this->command->info('Seeded rooms');
    }

    protected function seedStyles(): void
    {
        $styles = [
            ['slug' => 'hien-dai', 'display_name' => 'Hiện đại', 'namespace' => 'phong-cach'],
            ['slug' => 'co-dien', 'display_name' => 'Cổ điển', 'namespace' => 'phong-cach'],
            ['slug' => 'toi-gian', 'display_name' => 'Tối giản', 'namespace' => 'phong-cach'],
            ['slug' => 'cong-nghiep', 'display_name' => 'Công nghiệp', 'namespace' => 'phong-cach'],
            ['slug' => 'scandinavian', 'display_name' => 'Scandinavian', 'namespace' => 'phong-cach'],
            ['slug' => 'vintage', 'display_name' => 'Vintage', 'namespace' => 'phong-cach'],
            ['slug' => 'boho', 'display_name' => 'Boho', 'namespace' => 'phong-cach'],
            ['slug' => 'nordic', 'display_name' => 'Nordic', 'namespace' => 'phong-cach'],
        ];
        foreach ($styles as $style) {
            Lookup::updateOrCreate(
                ['namespace' => $style['namespace'], 'slug' => $style['slug']],
                $style
            );
        }
        $this->command->info('Seeded styles');
    }

    protected function seedColors(): void
    {
        $colors = [
            ['slug' => 'trang', 'display_name' => 'Trắng', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFFFFF']],
            ['slug' => 'den', 'display_name' => 'Đen', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#000000']],
            ['slug' => 'xam', 'display_name' => 'Xám', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#808080']],
            ['slug' => 'nau', 'display_name' => 'Nâu', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#8B4513']],
            ['slug' => 'xanh-duong', 'display_name' => 'Xanh dương', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#0000FF']],
            ['slug' => 'xanh-la', 'display_name' => 'Xanh lá', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#008000']],
            ['slug' => 'do', 'display_name' => 'Đỏ', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FF0000']],
            ['slug' => 'vang', 'display_name' => 'Vàng', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFD700']],
            ['slug' => 'cam', 'display_name' => 'Cam', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFA500']],
            ['slug' => 'tim', 'display_name' => 'Tím', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#800080']],
            ['slug' => 'hong', 'display_name' => 'Hồng', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFC0CB']],
            ['slug' => 'xanh-ngoc', 'display_name' => 'Xanh ngọc', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#40E0D0']],
        ];
        foreach ($colors as $color) {
            Lookup::updateOrCreate(
                ['namespace' => $color['namespace'], 'slug' => $color['slug']],
                $color
            );
        }
        $this->command->info('Seeded colors');
    }
}
