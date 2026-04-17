<?php

namespace Database\Seeders;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TimberProductSeeder extends Seeder
{
    private string $imageBase = 'images/products/Timber';

    private array $variants = [
        [
            'sku' => 'V2WNL566',
            'name' => 'sofa bọc da 90" - Charme Black',
            'slug' => 'ghe-timber-sofa-boc-da-90-charme-black',
            'description' => 'The Timber will inspire you to explore — even if your journey only goes as far as the pantry. With its oak wood trim, plump cushions, and full-aniline dye process, the Timber is sinkable and plush but cleans up real nice. Natural color variations, wrinkles, and creases are part of the unique characteristics of this leather. It will develop a relaxed vintage look with regular use.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Black',
            'option_values' => ['mau-sac' => 'den', 'chat-lieu' => 'da'],
            'folder' => 'Charme Black Timber Leather Honey Oak 3 Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_37_image160798.jpg',
                'gallery' => [
                    'imgi_49_image208304.jpg',
                    'imgi_73_image208305.jpg',
                    'imgi_85_image160802.jpg',
                    'imgi_61_image170692.jpg',
                    'imgi_97_image160804.jpg',
                    'imgi_109_image160805.jpg',
                    'imgi_121_image160803.jpg',
                    'imgi_145_image160808.jpg',
                ],
                'dimension' => 'imgi_25_image160796.jpg',
                'swatch' => 'imgi_133_image160807.jpg',
            ],
            'features' => [[
                'display_name' => 'Da thuộc cao cấp',
                'lookup_slug' => null,
                'description' => 'Được bọc bằng da Charme, loại da aniline nguyên chất của chúng tôi, mềm mại khi chạm vào và không qua xử lý để giữ vẻ ngoài tự nhiên. Da tự nhiên sẽ có sự khác biệt về màu sắc, sắc thái và kết cấu — không có hai miếng nào giống nhau.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Da', 'lookup_slug' => null, 'description' => 'Da thuộc Ý cao cấp 100%, được xử lý aniline toàn phần.']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Charme Black', 'lookup_slug' => 'charme-black', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '22"H x 38"W x 91"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => '39Y6EMDB',
            'name' => 'sofa bọc da 90" - Charme Chocolat',
            'slug' => 'ghe-timber-sofa-boc-da-90-charme-chocolat',
            'description' => 'The Timber will inspire you to explore — even if your journey only goes as far as the pantry. With its oak wood trim, plump cushions, and full-aniline dye process, the Timber is sinkable and plush but cleans up real nice. Natural color variations, wrinkles, and creases are part of the unique characteristics of this leather. It will develop a relaxed vintage look with regular use.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Chocolat',
            'option_values' => ['mau-sac' => 'nau', 'chat-lieu' => 'da'],
            'folder' => 'Charme Chocolat Timber Leather Honey Oak 3 Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_50_image162347.jpg',
                'hover' => 'imgi_74_image206302.jpg',
                'gallery' => [
                    'imgi_62_image206303.jpg',
                    'imgi_86_image162348.jpg',
                    'imgi_110_image162349.jpg',
                    'imgi_122_image162346.jpg',
                    'imgi_98_image170693.jpg',
                    'imgi_134_image162351.jpg',
                    'imgi_146_image162352.jpg',
                    'imgi_170_image162354.jpg',
                ],
                'dimension' => 'imgi_38_image162345.jpg',
                'swatch' => 'imgi_158_image162353.jpg',
            ],
            'features' => [[
                'display_name' => 'Da thuộc cao cấp',
                'lookup_slug' => null,
                'description' => 'Được bọc bằng da Charme, loại da aniline nguyên chất của chúng tôi, mềm mại khi chạm vào và không qua xử lý để giữ vẻ ngoài tự nhiên. Da tự nhiên sẽ có sự khác biệt về màu sắc, sắc thái và kết cấu — không có hai miếng nào giống nhau.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Da', 'lookup_slug' => null, 'description' => 'Da thuộc Ý cao cấp 100%, được xử lý aniline toàn phần.']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Charme Chocolat', 'lookup_slug' => 'charme-chocolat', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '22"H x 38"W x 91"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => 'AHZ92DOW',
            'name' => 'sofa bọc da 90" - Charme Green',
            'slug' => 'ghe-timber-sofa-boc-da-90-charme-green',
            'description' => 'The Timber will inspire you to explore — even if your journey only goes as far as the pantry. With its oak wood trim, plump cushions, and full-aniline dye process, the Timber is sinkable and plush but cleans up real nice. Natural color variations, wrinkles, and creases are part of the unique characteristics of this leather. It will develop a relaxed vintage look with regular use.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Green',
            'option_values' => ['mau-sac' => 'xanh-la', 'chat-lieu' => 'da'],
            'folder' => 'Charme Green Timber Leather Honey Oak 3 Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_53_image165520.jpg',
                'hover' => 'imgi_77_image165522.jpg',
                'gallery' => [
                    'imgi_65_image165521.jpg',
                    'imgi_89_image159978.jpg',
                    'imgi_113_image159979.jpg',
                    'imgi_125_image158265.jpg',
                    'imgi_101_image177036.jpg',
                    'imgi_137_image158267.jpg',
                    'imgi_149_image158268.jpg',
                    'imgi_161_image158266.jpg',
                    'imgi_185_image201784.jpg',
                ],
                'dimension' => 'imgi_41_image159034.jpg',
                'swatch' => 'imgi_173_image201785.jpg',
            ],
            'features' => [[
                'display_name' => 'Da thuộc cao cấp',
                'lookup_slug' => null,
                'description' => 'Được bọc bằng da Charme, loại da aniline nguyên chất của chúng tôi, mềm mại khi chạm vào và không qua xử lý để giữ vẻ ngoài tự nhiên. Da tự nhiên sẽ có sự khác biệt về màu sắc, sắc thái và kết cấu — không có hai miếng nào giống nhau.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Da', 'lookup_slug' => null, 'description' => 'Da thuộc Ý cao cấp 100%, được xử lý aniline toàn phần.']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Charme Green', 'lookup_slug' => 'charme-green', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '22"H x 38"W x 91"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => 'P4QWZSL8',
            'name' => 'sofa bọc da 90" - Charme Tan',
            'slug' => 'ghe-timber-sofa-boc-da-90-charme-tan',
            'description' => 'The Timber will inspire you to explore — even if your journey only goes as far as the pantry. With its oak wood trim, plump cushions, and full-aniline dye process, the Timber is sinkable and plush but cleans up real nice. Natural color variations, wrinkles, and creases are part of the unique characteristics of this leather. It will develop a relaxed vintage look with regular use.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Tan',
            'option_values' => ['mau-sac' => 'nau', 'chat-lieu' => 'da'],
            'folder' => 'Charme Tan Timber Leather Honey Oak 3 Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_63_image162322.jpg',
                'hover' => 'imgi_51_image162323.jpg',
                'gallery' => [
                    'imgi_75_image162324.jpg',
                    'imgi_99_image162325.jpg',
                    'imgi_111_image162326.jpg',
                    'imgi_87_image170702.jpg',
                    'imgi_123_image162328.jpg',
                    'imgi_135_image162329.jpg',
                    'imgi_147_image162327.jpg',
                    'imgi_171_image162331.jpg',
                ],
                'dimension' => 'imgi_39_image162320.jpg',
                'swatch' => 'imgi_159_image162330.jpg',
            ],
            'features' => [[
                'display_name' => 'Da thuộc cao cấp',
                'lookup_slug' => null,
                'description' => 'Được bọc bằng da Charme, loại da aniline nguyên chất của chúng tôi, mềm mại khi chạm vào và không qua xử lý để giữ vẻ ngoài tự nhiên. Da tự nhiên sẽ có sự khác biệt về màu sắc, sắc thái và kết cấu — không có hai miếng nào giống nhau.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Da', 'lookup_slug' => null, 'description' => 'Da thuộc Ý cao cấp 100%, được xử lý aniline toàn phần.']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Charme Tan', 'lookup_slug' => 'charme-tan', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '22"H x 38"W x 91"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => 'BGA4AK6R',
            'name' => 'sofa bọc vải 90" - Olio Green',
            'slug' => 'ghe-timber-sofa-boc-vai-90-olio-green',
            'description' => 'Hãy tưởng tượng thế này: bạn vừa thức dậy. Người yêu của bạn vừa trở về từ quán cà phê với hai ly latte trên tay. Bạn di chuyển đến ghế sofa và thả mình vào sự êm ái rộng lớn của nó — không ai làm đổ đồ uống. Đó chính là giấc mơ cuối tuần lý tưởng (ngay cả vào thứ Hai). Đó cũng chính là điều chúng tôi gọi là một buổi sáng hoàn hảo cùng Timber.',
            'price' => 12990000,
            'profit_margin_value' => 5990000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Olio Green',
            'option_values' => ['mau-sac' => 'xanh-la', 'chat-lieu' => 'vai'],
            'folder' => 'Timber Olio Green Fabric & Solid Wood Legs 3-Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_45_image208409.jpg',
                'hover' => 'imgi_33_image119096.jpg',
                'gallery' => [
                    'imgi_57_image119093.jpg',
                    'imgi_69_image150188.jpg',
                    'imgi_93_image150189.jpg',
                    'imgi_105_image124429.jpg',
                    'imgi_81_image170695.jpg',
                    'imgi_117_image124431.jpg',
                    'imgi_129_image124432.jpg',
                    'imgi_141_image124430.jpg',
                    'imgi_165_90d9138d-c1d3-4807-9b88-2b0791ffa07f-video.mp4-00001.jpg',
                ],
                'dimension' => 'imgi_21_image124424.jpg',
                'swatch' => 'imgi_153_image142473.jpg',
            ],
            'features' => [[
                'display_name' => 'Vải bền bỉ',
                'lookup_slug' => null,
                'description' => 'Chúng tôi kiểm tra nghiêm ngặt khả năng chống mài mòn của vải, bằng cách cho chúng chịu tới 50.000 lần chà xát. Con số này vượt xa tiêu chuẩn ngành là 20.000 lần chà xát, đảm bảo vải của chúng tôi có độ bền vượt trội.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Vải', 'lookup_slug' => null, 'description' => '83% polyester, 10% viscose, 7% linen, thử nghiệm Martindale - 50.000 lần chà xát']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Olio Green', 'lookup_slug' => 'olio-green', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '23"H x 38"W x 94"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => [],
        ],
        [
            'sku' => '2P1XI34G',
            'name' => 'sofa bọc vải 90" - Pebble Gray',
            'slug' => 'ghe-timber-sofa-boc-vai-90-pebble-gray',
            'description' => 'Hãy tưởng tượng thế này: bạn vừa thức dậy. Người yêu của bạn vừa trở về từ quán cà phê với hai ly latte trên tay. Bạn di chuyển đến ghế sofa và thả mình vào sự êm ái rộng lớn của nó — không ai làm đổ đồ uống. Đó chính là giấc mơ cuối tuần lý tưởng (ngay cả vào thứ Hai). Đó cũng chính là điều chúng tôi gọi là một buổi sáng hoàn hảo cùng Timber.',
            'price' => 12990000,
            'profit_margin_value' => 5990000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Pebble Gray',
            'option_values' => ['mau-sac' => 'xam', 'chat-lieu' => 'vai'],
            'folder' => 'Timber Pebble Gray Fabric & Solid Wood Legs 3-Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_42_image175197.jpg',
                'hover' => 'imgi_30_image122275.jpg',
                'gallery' => [
                    'imgi_54_image150194.jpg',
                    'imgi_78_image150195.jpg',
                    'imgi_90_image122278.jpg',
                    'imgi_66_image170696.jpg',
                    'imgi_102_image172158.jpg',
                    'imgi_114_image172157.jpg',
                    'imgi_126_image142566.jpg',
                ],
                'dimension' => 'imgi_18_image122272.jpg',
                'swatch' => 'imgi_138_image142565.jpg',
            ],
            'features' => [[
                'display_name' => 'Vải bền bỉ',
                'lookup_slug' => null,
                'description' => 'Chúng tôi kiểm tra nghiêm ngặt khả năng chống mài mòn của vải, bằng cách cho chúng chịu tới 50.000 lần chà xát. Con số này vượt xa tiêu chuẩn ngành là 20.000 lần chà xát, đảm bảo vải của chúng tôi có độ bền vượt trội.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Vải', 'lookup_slug' => null, 'description' => '83% polyester, 10% viscose, 7% linen, thử nghiệm Martindale - 50.000 lần chà xát']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Pebble Gray', 'lookup_slug' => 'pebble-gray', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '23"H x 38"W x 94"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => [],
        ],
        [
            'sku' => 'TLJJY4C2',
            'name' => 'sofa bọc vải 90" - Rain Cloud Gray',
            'slug' => 'ghe-timber-sofa-boc-vai-90-rain-cloud-gray',
            'description' => 'Hãy tưởng tượng thế này: bạn vừa thức dậy. Người yêu của bạn vừa trở về từ quán cà phê với hai ly latte trên tay. Bạn di chuyển đến ghế sofa và thả mình vào sự êm ái rộng lớn của nó — không ai làm đổ đồ uống. Đó chính là giấc mơ cuối tuần lý tưởng (ngay cả vào thứ Hai). Đó cũng chính là điều chúng tôi gọi là một buổi sáng hoàn hảo cùng Timber.',
            'price' => 12990000,
            'profit_margin_value' => 5990000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Rain Cloud Gray',
            'option_values' => ['mau-sac' => 'xam', 'chat-lieu' => 'vai'],
            'folder' => 'Timber Rain Cloud Gray Fabric & Solid Wood Legs 3-Seater Sofa _ Article',
            'images' => [
                'primary' => 'imgi_69_image175188.jpg',
                'hover' => 'imgi_45_image134268.jpg',
                'gallery' => [
                    'imgi_81_image175190.jpg',
                    'imgi_93_image150192.jpg',
                    'imgi_117_image150193.jpg',
                    'imgi_129_image134249.jpg',
                    'imgi_105_image170697.jpg',
                    'imgi_141_image134252.jpg',
                    'imgi_153_image134251.jpg',
                    'imgi_165_image134250.jpg',
                ],
                'dimension' => 'imgi_33_image134245.jpg',
                'swatch' => 'imgi_177_image142612.jpg',
            ],
            'features' => [[
                'display_name' => 'Vải bền bỉ',
                'lookup_slug' => null,
                'description' => 'Chúng tôi kiểm tra nghiêm ngặt khả năng chống mài mòn của vải, bằng cách cho chúng chịu tới 50.000 lần chà xát. Con số này vượt xa tiêu chuẩn ngành là 20.000 lần chà xát, đảm bảo vải của chúng tôi có độ bền vượt trội.',
            ]],
            'specifications' => [
                'Chất liệu' => [
                    'items' => [['display_name' => 'Vải', 'lookup_slug' => null, 'description' => '83% polyester, 10% viscose, 7% linen, thử nghiệm Martindale - 50.000 lần chà xát']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Màu lớp bọc' => [
                    'items' => [['display_name' => 'Rain Cloud Gray', 'lookup_slug' => 'rain-cloud-gray', 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => 'mau-sac',
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '23"H x 38"W x 94"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => [],
        ],
    ];

    public function run(): void
    {
        $categoryId = DB::table('categories')->where('slug', 'sofas')->value('id');
        $collectionId = DB::table('collections')->where('slug', 'timber')->value('id');

        if (! $categoryId) {
            $this->command->error('Category "sofas" not found. Run LookupSeeder and CategorySeeder first.');

            return;
        }

        $productId = $this->createProduct($categoryId, $collectionId);

        foreach ($this->variants as $variantData) {
            $variantId = $this->createVariant($productId, $variantData);
            $this->attachImages($variantId, $variantData['folder'], $variantData['images']);
        }

        $this->command->info('Timber product seeded with 7 variants.');
    }

    private function createProduct(string $categoryId, ?string $collectionId): string
    {
        $id = fake()->uuid();

        DB::table('products')->insert([
            'id' => $id,
            'name' => 'Ghế Timber',
            'status' => 'draft',
            'category_id' => $categoryId,
            'collection_id' => $collectionId,
            'min_price' => 12990000,
            'max_price' => 17990000,
            'features' => json_encode([
                ['display_name' => 'Chống ẩm ướt', 'lookup_slug' => 'chong-uy-tinh', 'description' => null],
                ['display_name' => 'Cổ điển hóa', 'lookup_slug' => 'co-dien-hoa', 'description' => null],
                ['display_name' => 'Dễ tháo lắp', 'lookup_slug' => 'de-thao', 'description' => null],
            ]),
            'specifications' => json_encode([
                'Tay ghế' => [
                    'items' => [['display_name' => 'Cao', 'lookup_slug' => null, 'description' => '26"']],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Vết gỗ' => [
                    'items' => [['display_name' => 'Honey Oak', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Phong cách' => [
                    'items' => [['display_name' => 'Cổ điển', 'lookup_slug' => 'co-dien', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'phong-cach',
                ],
                'Ghế ngồi' => [
                    'items' => [
                        ['display_name' => 'Cao', 'lookup_slug' => null, 'description' => '19"'],
                        ['display_name' => 'Sâu', 'lookup_slug' => null, 'description' => '24"'],
                    ],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Chất liệu' => [
                    'items' => [
                        ['display_name' => 'Khung ghế', 'lookup_slug' => null, 'description' => 'Gỗ thông nguyên khối sấy khô, chân gỗ cao su, ván ép, MDF, phụ kiện thép'],
                        ['display_name' => 'Đệm lót', 'lookup_slug' => null, 'description' => 'Bọt mút xốp mật độ cao, sợi polyester'],
                    ],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ]),
            'option_groups' => json_encode([
                [
                    'name' => 'Chất liệu',
                    'namespace' => 'chat-lieu',
                    'is_swatches' => false,
                    'options' => [
                        ['value' => 'da', 'label' => 'Da'],
                        ['value' => 'vai', 'label' => 'Vải'],
                    ],
                ],
                [
                    'name' => 'Màu sắc',
                    'namespace' => 'mau-sac',
                    'is_swatches' => true,
                    'options' => [
                        ['value' => 'den', 'label' => 'Đen'],
                        ['value' => 'nau', 'label' => 'Nâu'],
                        ['value' => 'xanh-la', 'label' => 'Xanh lá'],
                        ['value' => 'xam', 'label' => 'Xám'],
                    ],
                ],
            ]),
            'filterable_options' => json_encode([
                'mau-sac' => ['den', 'nau', 'xanh-la', 'xam'],
                'chat-lieu' => ['da', 'vai'],
                'tinh-nang' => ['chong-uy-tinh', 'co-dien-hoa', 'de-thao'],
                'phong-cach' => ['co-dien'],
                'muc-do-thoai-mai' => ['cap-3'],
            ]),
            'care_instructions' => json_encode([
                'Lau bằng khăn ẩm sạch',
                'Thường xuyên vỗ nhẹ đệm để giữ dáng',
                'Không nên sử dụng chất tẩy rửa hóa học',
                'Nên giặt khô chuyên nghiệp đối với các vết bẩn cứng đầu hơn',
            ]),
            'assembly_info' => json_encode([
                'required' => true,
                'estimated_minutes' => 15,
                'price' => null,
                'difficulty_level' => 'easy',
                'instructions_url' => null,
            ]),
            'is_custom_made' => false,
            'warranty_months' => 12,
            'is_featured' => false,
            'is_new_arrival' => true,
            'published_date' => null,
            'new_arrival_until' => null,
            'views_count' => 0,
            'reviews_count' => 0,
            'average_rating' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createVariant(string $productId, array $data): string
    {
        $id = fake()->uuid();

        DB::table('product_variants')->insert([
            'id' => $id,
            'product_id' => $productId,
            'sku' => $data['sku'],
            'status' => 'active',
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'price' => $data['price'],
            'profit_margin_value' => $data['profit_margin_value'] ?? null,
            'profit_margin_unit' => $data['profit_margin_unit'] ?? 'fixed',
            'weight' => json_encode(['value' => 128, 'unit' => 'lb']),
            'dimensions' => json_encode(['length' => 90, 'width' => 35, 'height' => 32, 'unit' => 'inch']),
            'option_values' => json_encode($data['option_values']),
            'swatch_label' => $data['swatch_label'] ?? null, // <--- ADD THIS LINE
            'features' => json_encode($data['features']),
            'specifications' => json_encode($data['specifications']),
            'care_instructions' => json_encode($data['care_instructions']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function attachImages(string $variantId, string $folder, array $images): void
    {
        $disk = Storage::disk('local');
        $basePath = "{$this->imageBase}/{$folder}";

        /** @var ProductVariant $variant */
        $variant = ProductVariant::find($variantId);
        if (! $variant) {
            $this->command->warn("Variant {$variantId} not found");

            return;
        }

        // Primary
        if (isset($images['primary'])) {
            $this->addMediaToVariant($variant, $disk, $basePath . '/' . $images['primary'], 'primary_image');
        }

        // Hover
        if (isset($images['hover'])) {
            $this->addMediaToVariant($variant, $disk, $basePath . '/' . $images['hover'], 'hover_image');
        }

        // Gallery
        foreach ($images['gallery'] as $file) {
            $this->addMediaToVariant($variant, $disk, $basePath . '/' . $file, 'gallery');
        }

        // Dimension
        if (isset($images['dimension'])) {
            $this->addMediaToVariant($variant, $disk, $basePath . '/' . $images['dimension'], 'dimension_image');
        }

        // Swatch
        if (isset($images['swatch'])) {
            $this->addMediaToVariant($variant, $disk, $basePath . '/' . $images['swatch'], 'swatch_image');
        }
    }

    private function addMediaToVariant(ProductVariant $variant, $disk, string $path, string $collection): void
    {
        if (! $disk->exists($path)) {
            $this->command->warn("Image not found: {$path}");

            return;
        }

        $fullPath = $disk->path($path);

        $variant->addMedia($fullPath)
            ->preservingOriginal()
            ->toMediaCollection($collection, 'public');
    }
}
