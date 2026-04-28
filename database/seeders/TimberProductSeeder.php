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
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Black',
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'den', 'chat-lieu' => 'da'],
            'folder' => 'Sofa/Timber 90" Leather Sofa - Charme Black',
            'images' => [
                'primary' => 'primary_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Black', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Chocolat',
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'nau', 'chat-lieu' => 'da'],
            'folder' => 'Sofa/Timber 90" Leather Sofa - Charme Chocolat',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Chocolat', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Green',
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'xanh-la', 'chat-lieu' => 'da'],
            'folder' => 'Sofa/Timber 90" Leather Sofa - Charme Green',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                    'gallery_9.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Green', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 17990000,
            'profit_margin_value' => 7000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Tan',
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'nau', 'chat-lieu' => 'da'],
            'folder' => 'Sofa/Timber 90" Leather Sofa - Charme Tan',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Tan', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'xanh-la', 'chat-lieu' => 'vai'],
            'folder' => 'Sofa/Timber 90" Sofa - Olio Green',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                    'gallery_9.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Olio Green', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'xam', 'chat-lieu' => 'vai'],
            'folder' => 'Sofa/Timber 90" Sofa - Pebble Gray',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Pebble Gray', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'option_values' => ['danh-muc-phu' => 'ghe-sofa', 'mau-sac' => 'xam', 'chat-lieu' => 'vai'],
            'folder' => 'Sofa/Timber 90" Sofa - Rain Cloud Gray',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Rain Cloud Gray', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
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
            'sku' => 'TLJUPANT',
            'name' => 'đôi bọc da 66.5" - Charme Chocolat',
            'slug' => 'ghe-timber-doi-boc-da-665-charme-chocolat',
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 14990000,
            'profit_margin_value' => 6000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Chocolat',
            'option_values' => ['danh-muc-phu' => 'ghe-doi', 'mau-sac' => 'nau', 'chat-lieu' => 'da'],
            'folder' => 'Loveseat/Timber 66.5" Leather Loveseat - Charme Chocolat',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Chocolat', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '33"H x 36.25"W x 66.5"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => 'NAOFJJT3H',
            'name' => 'đôi bọc da 66.5" - Charme Green',
            'slug' => 'ghe-timber-doi-boc-da-665-charme-green',
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 14990000,
            'profit_margin_value' => 6000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Green',
            'option_values' => ['danh-muc-phu' => 'ghe-doi', 'mau-sac' => 'xanh-la', 'chat-lieu' => 'da'],
            'folder' => 'Loveseat/Timber 66.5" Leather Loveseat - Charme Green',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                    'gallery_9.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Green', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '33"H x 36.25"W x 66.5"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => 'ISJNAL35',
            'name' => 'đôi bọc da 66.5" - Charme Tan',
            'slug' => 'ghe-timber-doi-boc-da-665-charme-tan',
            'description' => 'Ghế Timber sẽ truyền cảm hứng cho bạn khám phá — ngay cả khi hành trình của bạn chỉ đến tận phòng đựng thức ăn. Với các chi tiết trang trí bằng gỗ sồi, đệm dày và quy trình nhuộm toàn anilin, Gỗ có thể chìm và sang trọng nhưng lại rất sạch sẽ. Các biến thể màu sắc tự nhiên, nếp nhăn và nếp gấp là một phần đặc điểm độc đáo của loại da này. Nó sẽ phát triển một cái nhìn cổ điển thoải mái khi sử dụng thường xuyên.',
            'price' => 14990000,
            'profit_margin_value' => 6000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Charme Tan',
            'option_values' => ['danh-muc-phu' => 'ghe-doi', 'mau-sac' => 'nau', 'chat-lieu' => 'da'],
            'folder' => 'Loveseat/Timber 66.5" Leather Loveseat - Charme Tan',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                    'gallery_8.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Charme Tan', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '33"H x 36.25"W x 66.5"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => ['Quần jean denim mới, chưa giặt có thể lem màu sang lớp da màu sáng hơn, gây ra vết bẩn vĩnh viễn.'],
        ],
        [
            'sku' => '4MANGOWK',
            'name' => 'đôi bọc vải 66.5" - Olio Green',
            'slug' => 'ghe-timber-doi-boc-vai-665-olio-green',
            'description' => 'Hãy tưởng tượng thế này: bạn vừa thức dậy. Người yêu của bạn vừa trở về từ quán cà phê với hai ly latte trên tay. Bạn di chuyển đến ghế sofa và thả mình vào sự êm ái rộng lớn của nó — không ai làm đổ đồ uống. Đó chính là giấc mơ cuối tuần lý tưởng (ngay cả vào thứ Hai). Đó cũng chính là điều chúng tôi gọi là một buổi sáng hoàn hảo cùng Timber.',
            'price' => 10990000,
            'profit_margin_value' => 5000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Olio Green',
            'option_values' => ['danh-muc-phu' => 'ghe-doi', 'mau-sac' => 'xanh-la', 'chat-lieu' => 'vai'],
            'folder' => 'Loveseat/Timber 66.5" Loveseat - Olio Green',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Olio Green', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '22"H x 37"W x 70"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => [],
        ],
        [
            'sku' => 'NAVJLW5O',
            'name' => 'đôi bọc vải 66.5" - Rain Cloud Gray',
            'slug' => 'ghe-timber-doi-boc-vai-665-rain-cloud-gray',
            'description' => 'Hãy tưởng tượng thế này: bạn vừa thức dậy. Người yêu của bạn vừa trở về từ quán cà phê với hai ly latte trên tay. Bạn di chuyển đến ghế sofa và thả mình vào sự êm ái rộng lớn của nó — không ai làm đổ đồ uống. Đó chính là giấc mơ cuối tuần lý tưởng (ngay cả vào thứ Hai). Đó cũng chính là điều chúng tôi gọi là một buổi sáng hoàn hảo cùng Timber.',
            'price' => 10990000,
            'profit_margin_value' => 5000000,
            'profit_margin_unit' => 'fixed',
            'swatch_label' => 'Rain Cloud Gray',
            'option_values' => ['danh-muc-phu' => 'ghe-doi', 'mau-sac' => 'xam', 'chat-lieu' => 'vai'],
            'folder' => 'Loveseat/Timber 66.5" Loveseat - Rain Cloud Gray',
            'images' => [
                'primary' => 'primary_image.jpg',
                'hover' => 'hover_image.jpg',
                'gallery' => [
                    'gallery_1.jpg',
                    'gallery_2.jpg',
                    'gallery_3.jpg',
                    'gallery_4.jpg',
                    'gallery_5.jpg',
                    'gallery_6.jpg',
                    'gallery_7.jpg',
                ],
                'dimension' => 'dimension_image.jpg',
                'swatch' => 'swatch_image.jpg',
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
                    'items' => [['display_name' => 'Rain Cloud Gray', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
                'Mức độ thoải mái' => [
                    'items' => [['display_name' => 'Cấp 3 - Vừa', 'lookup_slug' => 'cap-3', 'description' => null]],
                    'is_filterable' => true,
                    'lookup_namespace' => 'muc-do-thoai-mai',
                ],
                'Kích thước gói hàng' => [
                    'items' => [['display_name' => '22"H x 37"W x 70"L', 'lookup_slug' => null, 'description' => null]],
                    'is_filterable' => false,
                    'lookup_namespace' => null,
                ],
            ],
            'care_instructions' => [],
        ],
    ];

    public function run(): void
    {
        $categoryId = DB::table('categories')->where('slug', 'ghe-sofa-va-ghe-doi')->value('id');
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
                [
                    'lookup_slug' => 'tay-don',
                    'display_name' => 'Tay đòn',
                    'description' => 'Với kiểu dáng hiện đại, ghế sofa tay vịn thẳng giúp giữ cho phong cách của bạn luôn thanh lịch. Chúng cũng là giải pháp tiết kiệm không gian tuyệt vời, chiếm ít diện tích hơn và giúp bạn dễ dàng sắp xếp bố cục nội thất hơn.'
                ],
                [
                    'lookup_slug' => 'ghe-sau',
                    'display_name' => 'Ghế sâu',
                    'description' => 'Thiết kế ghế sâu này mang lại nhiều không gian hơn để bạn duỗi chân khi thư giãn. So với ghế nông, ghế sâu có đệm lớn hơn, cho phép người cao ngồi thoải mái – và người ở mọi chiều cao đều có thể ngả lưng hoặc cuộn tròn người theo ý muốn.'
                ],
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
                    'name' => 'Kiểu ghế',
                    'namespace' => 'danh-muc-phu',
                    'is_swatches' => false,
                    'options' => [
                        ['value' => 'ghe-sofa', 'label' => 'Ghế Sofa'],
                        ['value' => 'ghe-doi', 'label' => 'Ghế đôi'],
                    ],
                ],
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
                'danh-muc-phu' => ['ghe-sofa', 'ghe-doi'],
                'mau-sac' => ['den', 'nau', 'xanh-la', 'xam'],
                'chat-lieu' => ['da', 'vai'],
                'tinh-nang' => ['tay-don', 'ghe-sau'],
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
                'difficulty_level' => 'easy',
                'additional_information' => null
            ]),
            'warranty_months' => 12,
            'is_featured' => false,
            'is_new_arrival' => false,
            'published_date' => null,
            'new_arrival_until' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createVariant(string $productId, array $data): string
    {
        $variant = ProductVariant::create([
            'product_id' => $productId,
            'sku' => $data['sku'],
            'status' => 'active',
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'price' => $data['price'],
            'profit_margin_value' => $data['profit_margin_value'] ?? null,
            'profit_margin_unit' => $data['profit_margin_unit'] ?? 'fixed',
            'weight' => ['value' => 128, 'unit' => 'lb'],
            'dimensions' => ['length' => 90, 'width' => 35, 'height' => 32, 'unit' => 'inch'],
            'option_values' => $data['option_values'],
            'swatch_label' => $data['swatch_label'] ?? null,
            'features' => $data['features'],
            'specifications' => $data['specifications'],
            'care_instructions' => $data['care_instructions'],
        ]);

        return $variant->id;
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
