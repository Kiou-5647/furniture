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
        $this->seedRooms();
        $this->seedStyles();
        $this->seedColors();
    }

    protected function seedRooms(): void
    {
        $rooms = [
            ['key' => 'phong-khach', 'display_name' => 'Phòng khách', 'namespace' => 'phong'],
            ['key' => 'phong-ngu', 'display_name' => 'Phòng ngủ', 'namespace' => 'phong'],
            ['key' => 'phong-an', 'display_name' => 'Phòng ăn', 'namespace' => 'phong'],
            ['key' => 'nha-bep', 'display_name' => 'Nhà bếp', 'namespace' => 'phong'],
            ['key' => 'phong-tam', 'display_name' => 'Phòng tắm', 'namespace' => 'phong'],
            ['key' => 'van-phong', 'display_name' => 'Văn phòng', 'namespace' => 'phong'],
            ['key' => 'hanh-lang', 'display_name' => 'Hành lang', 'namespace' => 'phong'],
            ['key' => 'san-vuon', 'display_name' => 'Sân vườn', 'namespace' => 'phong'],
        ];
        foreach ($rooms as $room) {
            Lookup::updateOrCreate(
                ['namespace' => $room['namespace'], 'key' => $room['key']],
                $room
            );
        }
        $this->command->info('Seeded rooms');
    }

    protected function seedStyles(): void
    {
        $styles = [
            ['key' => 'hien-dai', 'display_name' => 'Hiện đại', 'namespace' => 'phong-cach'],
            ['key' => 'co-dien', 'display_name' => 'Cổ điển', 'namespace' => 'phong-cach'],
            ['key' => 'toi-gian', 'display_name' => 'Tối giản', 'namespace' => 'phong-cach'],
            ['key' => 'cong-nghiep', 'display_name' => 'Công nghiệp', 'namespace' => 'phong-cach'],
            ['key' => 'scandinavian', 'display_name' => 'Scandinavian', 'namespace' => 'phong-cach'],
            ['key' => 'vintage', 'display_name' => 'Vintage', 'namespace' => 'phong-cach'],
            ['key' => 'boho', 'display_name' => 'Boho', 'namespace' => 'phong-cach'],
            ['key' => 'nordic', 'display_name' => 'Nordic', 'namespace' => 'phong-cach'],
        ];
        foreach ($styles as $style) {
            Lookup::updateOrCreate(
                ['namespace' => $style['namespace'], 'key' => $style['key']],
                $style
            );
        }
        $this->command->info('Seeded styles');
    }

    protected function seedColors(): void
    {
        $colors = [
            ['key' => 'trang', 'display_name' => 'Trắng', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFFFFF']],
            ['key' => 'den', 'display_name' => 'Đen', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#000000']],
            ['key' => 'xam', 'display_name' => 'Xám', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#808080']],
            ['key' => 'nau', 'display_name' => 'Nâu', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#8B4513']],
            ['key' => 'xanh-duong', 'display_name' => 'Xanh dương', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#0000FF']],
            ['key' => 'xanh-la', 'display_name' => 'Xanh lá', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#008000']],
            ['key' => 'do', 'display_name' => 'Đỏ', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FF0000']],
            ['key' => 'vang', 'display_name' => 'Vàng', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFD700']],
            ['key' => 'cam', 'display_name' => 'Cam', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFA500']],
            ['key' => 'tim', 'display_name' => 'Tím', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#800080']],
            ['key' => 'hong', 'display_name' => 'Hồng', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#FFC0CB']],
            ['key' => 'xanh-ngoc', 'display_name' => 'Xanh ngọc', 'namespace' => 'mau-sac', 'metadata' => ['hex_code' => '#40E0D0']],
        ];
        foreach ($colors as $color) {
            Lookup::updateOrCreate(
                ['namespace' => $color['namespace'], 'key' => $color['key']],
                $color
            );
        }
        $this->command->info('Seeded colors');
    }
}
