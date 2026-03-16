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
        $this->seedSecret();
        $this->seedDatatypes();
        $this->seedRooms();
        $this->seedStyles();
        $this->seedColors();
    }

    protected function seedSecret(): void
    {
        $secrets = [
            [
                'key' => 'input',
                'display_name' => 'Nhập liệu',
                'namespace' => 'secret',
                'metadata' => [
                    'fields', 'unit',
                ],
                'is_system' => true,
            ],
            [
                'key' => 'validation',
                'display_name' => 'Ràng buộc',
                'namespace' => 'secret',
                'metadata' => [
                    'datatype', 'min', 'max', 'regex',
                ],
                'is_system' => true,
            ],
            [
                'key' => 'datatype',
                'display_name' => 'Kiểu dữ liệu',
                'namespace' => 'secret',
                'metadata' => [
                    'input', 'validation',
                ],
                'is_system' => true,
            ],
            [
                'key' => 'rooms',
                'display_name' => 'Phòng',
                'namespace' => 'secret',
                'metadata' => [
                    'images',
                ],
                'is_system' => true,
            ],
            [
                'key' => 'styles',
                'display_name' => 'Phong cách',
                'namespace' => 'secret',
                'metadata' => [
                    'images',
                ],
                'is_system' => true,
            ],
            [
                'key' => 'colors',
                'display_name' => 'Màu sắc',
                'namespace' => 'secret',
                'metadata' => [
                    'hex_code',
                ],
                'is_system' => true,
            ],
        ];
        foreach ($secrets as $secret) {
            Lookup::updateOrCreate(
                ['namespace' => $secret['namespace'], 'key' => $secret['key']],
                $secret
            );
        }
        $this->command->info('Seeded secrets');
    }

    protected function seedDatatypes(): void
    {
        $datatypes = [
            [
                'key' => 'text',
                'display_name' => 'Văn bản',
                'namespace' => 'datatype',
                'metadata' => ['input' => ['fields' => ['value']], 'validation' => []],
                'is_system' => true,
            ],
            [
                'key' => 'number',
                'display_name' => 'Số',
                'namespace' => 'datatype',
                'metadata' => ['input' => ['fields' => ['value']], 'validation' => []],
                'is_system' => true,
            ],
            [
                'key' => 'boolean',
                'display_name' => 'Boolean',
                'namespace' => 'datatype',
                'metadata' => ['input' => ['fields' => ['value']], 'validation' => []],
                'is_system' => true,
            ],
            [
                'key' => 'color',
                'display_name' => 'Màu sắc',
                'namespace' => 'datatype',
                'metadata' => [
                    'input' => ['fields' => ['value']],
                    'validation' => ['lookup' => 'colors'],
                ],
                'is_system' => true,
            ],
            [
                'key' => 'dimensions',
                'display_name' => 'Kích thước',
                'namespace' => 'datatype',
                'metadata' => [
                    'input' => [
                        'fields' => ['height', 'width', 'depth'],
                        'unit' => 'cm',
                    ],
                    'validation' => [
                        'datatype' => 'number',
                        'min' => [
                            'required' => true,
                            'default' => 0,
                        ],
                        'max' => [
                            'required' => false,
                            'default' => 0,
                        ],
                    ],
                ],
                'is_system' => true,
            ],
            [
                'key' => 'weight',
                'display_name' => 'Cân nặng',
                'namespace' => 'datatype',
                'metadata' => [
                    'input' => [
                        'fields' => ['value'],
                        'unit' => 'kg',
                    ],
                    'validation' => [
                        'datatype' => 'number',
                        'min' => [
                            'required' => true,
                            'default' => 0,
                        ],
                        'max' => [
                            'required' => false,
                            'default' => 0,
                        ],
                    ],
                ],
                'is_system' => true,
            ],
        ];
        foreach ($datatypes as $datatype) {
            Lookup::updateOrCreate(
                ['namespace' => $datatype['namespace'], 'key' => $datatype['key']],
                $datatype
            );
        }
        $this->command->info('Seeded datatypes');
    }

    protected function seedRooms(): void
    {
        $rooms = [
            ['key' => 'phong-khach', 'display_name' => 'Phòng khách', 'namespace' => 'rooms'],
            ['key' => 'phong-ngu', 'display_name' => 'Phòng ngủ', 'namespace' => 'rooms'],
            ['key' => 'phong-an', 'display_name' => 'Phòng ăn', 'namespace' => 'rooms'],
            ['key' => 'nha-bep', 'display_name' => 'Nhà bếp', 'namespace' => 'rooms'],
            ['key' => 'phong-tam', 'display_name' => 'Phòng tắm', 'namespace' => 'rooms'],
            ['key' => 'van-phong', 'display_name' => 'Văn phòng', 'namespace' => 'rooms'],
            ['key' => 'hanh-lang', 'display_name' => 'Hành lang', 'namespace' => 'rooms'],
            ['key' => 'san-vuon', 'display_name' => 'Sân vườn', 'namespace' => 'rooms'],
        ];
        foreach ($rooms as $room) {
            Lookup::updateOrCreate(
                ['namespace' => $room['namespace'], 'key' => $room['key']],
                [...$room, 'is_system' => true]
            );
        }
        $this->command->info('Seeded rooms');
    }

    protected function seedStyles(): void
    {
        $styles = [
            ['key' => 'hien-dai', 'display_name' => 'Hiện đại', 'namespace' => 'styles'],
            ['key' => 'co-dien', 'display_name' => 'Cổ điển', 'namespace' => 'styles'],
            ['key' => 'toi-gian', 'display_name' => 'Tối giản', 'namespace' => 'styles'],
            ['key' => 'cong-nghiep', 'display_name' => 'Công nghiệp', 'namespace' => 'styles'],
            ['key' => 'scandinavian', 'display_name' => 'Scandinavian', 'namespace' => 'styles'],
            ['key' => 'vintage', 'display_name' => 'Vintage', 'namespace' => 'styles'],
            ['key' => 'boho', 'display_name' => 'Boho', 'namespace' => 'styles'],
            ['key' => 'nordic', 'display_name' => 'Nordic', 'namespace' => 'styles'],
        ];
        foreach ($styles as $style) {
            Lookup::updateOrCreate(
                ['namespace' => $style['namespace'], 'key' => $style['key']],
                [...$style, 'is_system' => true]
            );
        }
        $this->command->info('Seeded styles');
    }

    protected function seedColors(): void
    {
        $colors = [
            ['key' => 'trang', 'display_name' => 'Trắng', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#FFFFFF']],
            ['key' => 'den', 'display_name' => 'Đen', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#000000']],
            ['key' => 'xam', 'display_name' => 'Xám', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#808080']],
            ['key' => 'nau', 'display_name' => 'Nâu', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#8B4513']],
            ['key' => 'xanh-duong', 'display_name' => 'Xanh dương', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#0000FF']],
            ['key' => 'xanh-la', 'display_name' => 'Xanh lá', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#008000']],
            ['key' => 'do', 'display_name' => 'Đỏ', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#FF0000']],
            ['key' => 'vang', 'display_name' => 'Vàng', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#FFD700']],
            ['key' => 'cam', 'display_name' => 'Cam', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#FFA500']],
            ['key' => 'tim', 'display_name' => 'Tím', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#800080']],
            ['key' => 'hong', 'display_name' => 'Hồng', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#FFC0CB']],
            ['key' => 'xanh-ngoc', 'display_name' => 'Xanh ngọc', 'namespace' => 'colors', 'metadata' => ['hex_code' => '#40E0D0']],
        ];
        foreach ($colors as $color) {
            Lookup::updateOrCreate(
                ['namespace' => $color['namespace'], 'key' => $color['key']],
                [...$color, 'is_system' => true]
            );
        }
        $this->command->info('Seeded colors');
    }
}
