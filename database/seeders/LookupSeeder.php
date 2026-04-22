<?php

namespace Database\Seeders;

use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    use MediaSeederTrait;

    private string $roomImageBase = 'images/lookups';

    public function run(): void
    {
        $this->seedNamespaces();
        $this->seedCategoryGroups();
        $this->seedSubCategories();
        $this->seedRooms();
        $this->seedStyles();
        $this->seedColors();
        $this->seedFeatures();
        $this->seedMaterials();
        $this->seedShapes();
        $this->seedSizes();
        $this->seedComfortLevels();
    }

    protected function seedNamespaces(): void
    {
        $namespaces = [
            ['slug' => 'nhom-danh-muc', 'display_name' => 'Nhóm danh mục', 'for_variants' => false, 'is_system' => true],
            ['slug' => 'danh-muc-phu', 'display_name' => 'Danh mục phụ', 'for_variants' => true, 'is_system' => true],
            ['slug' => 'phong', 'display_name' => 'Phòng', 'for_variants' => false, 'is_system' => true],
            ['slug' => 'phong-cach', 'display_name' => 'Phong cách', 'for_variants' => false, 'is_system' => true],
            ['slug' => 'mau-sac', 'display_name' => 'Màu sắc', 'for_variants' => true, 'is_system' => true],
            ['slug' => 'tinh-nang', 'display_name' => 'Tính năng', 'for_variants' => false, 'is_system' => true],
            ['slug' => 'muc-do-thoai-mai', 'display_name' => 'Mức độ thoải mái', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'loi-song', 'display_name' => 'Lối sống', 'for_variants' => false, 'is_system' => false],
            ['slug' => 'hinh-dang', 'display_name' => 'Hình dáng', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'kich-co', 'display_name' => 'Kích cỡ', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'hoa-van', 'display_name' => 'Hoa văn', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'chat-lieu', 'display_name' => 'Chất liệu', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'loai-thiet-ke', 'display_name' => 'Loại thiết kế', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'hoan-thien', 'display_name' => 'Hoàn thiện', 'for_variants' => true, 'is_system' => false],
            ['slug' => 'loai-de', 'display_name' => 'Loại đế', 'for_variants' => true, 'is_system' => false],
        ];

        foreach ($namespaces as $ns) {
            LookupNamespace::updateOrCreate(['slug' => $ns['slug']], $ns);
        }
        $this->command->info('Seeded lookup namespaces');
    }

    protected function getNamespaceId(string $slug): ?string
    {
        return LookupNamespace::where('slug', $slug)->first()?->id;
    }

    protected function seedCategoryGroups(): void
    {
        $nsId = $this->getNamespaceId('nhom-danh-muc');
        if (! $nsId) {
            return;
        }

        $groups = [
            ['slug' => 'ghe-ngoi', 'display_name' => 'Ghế ngồi', 'description' => 'Các loại ghế ngồi trong nhà'],
            ['slug' => 'ban', 'display_name' => 'Bàn', 'description' => 'Các loại bàn trong nhà'],
            ['slug' => 'luu-tru', 'display_name' => 'Lưu trữ', 'description' => 'Tủ, kệ, ngăn lưu trữ'],
            ['slug' => 'ngu-nghi', 'display_name' => 'Ngủ nghỉ', 'description' => 'Giường, đệm, gối'],
            ['slug' => 'chieu-sang', 'display_name' => 'Chiếu sáng', 'description' => 'Đèn và thiết bị chiếu sáng'],
            ['slug' => 'trang-tri', 'display_name' => 'Trang trí', 'description' => 'Đồ trang trí nội thất'],
            ['slug' => 'det-may', 'display_name' => 'Dệt may', 'description' => 'Rèm, thảm, vải'],
            ['slug' => 'ngoai-troi', 'display_name' => 'Ngoài trời', 'description' => 'Nội thất sân vườn, ngoài trời'],
        ];

        foreach ($groups as $group) {
            $lookup = Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $group['slug']],
                ['namespace_id' => $nsId, ...$group]
            );

            $this->attachMedia(
                $lookup,
                "{$this->roomImageBase}/nhom-danh-muc/{$group['slug']}.jpg",
                'image'
            );
        }
        $this->command->info('Seeded category groups');
    }

    protected function seedSubCategories(): void
    {
        $nsId = $this->getNamespaceId('danh-muc-phu');
        if (! $nsId) {
            return;
        }

        $subCats = [
            ['slug' => 'ghe-sofa', 'display_name' => 'Ghế Sofa', 'description' => 'Các loại ghế sofa'],
            ['slug' => 'ghe-doi', 'display_name' => 'Ghế đôi', 'description' => 'Các loại ghế đôi'],
        ];

        foreach ($subCats as $sub) {
            $lookup = Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $sub['slug']],
                ['namespace_id' => $nsId, ...$sub]
            );

            $this->attachMedia(
                $lookup,
                "{$this->roomImageBase}/danh-muc-phu/{$sub['slug']}.jpg",
                'image'
            );
        }
        $this->command->info('Seeded category groups');
    }

    protected function seedRooms(): void
    {
        $nsId = $this->getNamespaceId('phong');
        if (! $nsId) {
            return;
        }

        $rooms = [
            ['slug' => 'phong-khach', 'display_name' => 'Phòng khách'],
            ['slug' => 'phong-ngu', 'display_name' => 'Phòng ngủ'],
            ['slug' => 'phong-an', 'display_name' => 'Phòng ăn'],
            ['slug' => 'van-phong', 'display_name' => 'Văn phòng'],
            ['slug' => 'ngoai-troi', 'display_name' => 'Ngoài trời'],
            ['slug' => 'trang-tri', 'display_name' => 'Trang trí'],
        ];

        foreach ($rooms as $room) {
            $lookup = Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $room['slug']],
                ['namespace_id' => $nsId, ...$room]
            );

            $this->attachMedia(
                $lookup,
                "{$this->roomImageBase}/phong/{$room['slug']}.jpg",
                'image'
            );
        }
        $this->command->info('Seeded rooms');
    }

    protected function seedStyles(): void
    {
        $nsId = $this->getNamespaceId('phong-cach');
        if (! $nsId) {
            return;
        }

        $styles = [
            ['slug' => 'hien-dai', 'display_name' => 'Hiện đại'],
            ['slug' => 'co-dien', 'display_name' => 'Cổ điển'],
            ['slug' => 'toi-gian', 'display_name' => 'Tối giản'],
            ['slug' => 'cong-nghiep', 'display_name' => 'Công nghiệp'],
            ['slug' => 'scandinavian', 'display_name' => 'Scandinavian'],
            ['slug' => 'vintage', 'display_name' => 'Vintage'],
            ['slug' => 'boho', 'display_name' => 'Boho'],
            ['slug' => 'nordic', 'display_name' => 'Nordic'],
        ];

        foreach ($styles as $style) {
            Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $style['slug']],
                ['namespace_id' => $nsId, ...$style]
            );
        }
        $this->command->info('Seeded styles');
    }

    protected function seedColors(): void
    {
        $nsId = $this->getNamespaceId('mau-sac');
        if (! $nsId) {
            return;
        }

        $colors = [
            ['slug' => 'trang', 'display_name' => 'Trắng', 'metadata' => ['hex_code' => '#FFFFFF']],
            ['slug' => 'den', 'display_name' => 'Đen', 'metadata' => ['hex_code' => '#000000']],
            ['slug' => 'xam', 'display_name' => 'Xám', 'metadata' => ['hex_code' => '#808080']],
            ['slug' => 'nau', 'display_name' => 'Nâu', 'metadata' => ['hex_code' => '#8B4513']],
            ['slug' => 'xanh-duong', 'display_name' => 'Xanh dương', 'metadata' => ['hex_code' => '#0000FF']],
            ['slug' => 'xanh-la', 'display_name' => 'Xanh lá', 'metadata' => ['hex_code' => '#008000']],
            ['slug' => 'do', 'display_name' => 'Đỏ', 'metadata' => ['hex_code' => '#FF0000']],
            ['slug' => 'vang', 'display_name' => 'Vàng', 'metadata' => ['hex_code' => '#FFD700']],
            ['slug' => 'cam', 'display_name' => 'Cam', 'metadata' => ['hex_code' => '#FFA500']],
            ['slug' => 'tim', 'display_name' => 'Tím', 'metadata' => ['hex_code' => '#800080']],
            ['slug' => 'hong', 'display_name' => 'Hồng', 'metadata' => ['hex_code' => '#FFC0CB']],
        ];

        foreach ($colors as $color) {
            $lookup = Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $color['slug']],
                ['namespace_id' => $nsId, ...$color]
            );

            $this->attachMedia(
                $lookup,
                "{$this->roomImageBase}/mau-sac/{$color['slug']}.png",
                'image'
            );
        }
        $this->command->info('Seeded colors');
    }

    protected function seedFeatures(): void
    {
        $nsId = $this->getNamespaceId('tinh-nang');
        if (! $nsId) {
            return;
        }

        $features = [
            ['slug' => 'dao-chieu', 'display_name' => 'Đảo chiều', 'description' => 'Bộ sofa này có thêm phần ghế dài ở bên phải – hoặc bên trái! Bạn có thể lựa chọn bất cứ lúc nào: di chuyển phần ghế dài có thể đảo chiều sang một bên tháng này và bên kia tháng sau, tùy thuộc vào nhu cầu bố trí và tần suất bạn muốn thay đổi không gian. Với tùy chọn ghế dài có thể đảo chiều, bạn sẽ có nhiều tự do hơn trong việc trang trí phòng khách.'],
            ['slug' => 'tay-don', 'display_name' => 'Tay đòn', 'description' => 'Với kiểu dáng hiện đại, ghế sofa tay vịn thẳng giúp giữ cho phong cách của bạn luôn thanh lịch. Chúng cũng là giải pháp tiết kiệm không gian tuyệt vời, chiếm ít diện tích hơn và giúp bạn dễ dàng sắp xếp bố cục nội thất hơn.'],
            ['slug' => 'khong-gian-hep', 'display_name' => 'Không gian hẹp', 'description' => 'Với kiểu dáng thanh lịch, thiết kế này hòa hợp hoàn hảo với những không gian nhỏ. Kích thước nhỏ gọn đồng nghĩa với nhiều khả năng phối hợp hơn – nhiều cách hơn để sắp xếp, đặt và trang trí mà không cần lo lắng về kích thước hay sự phù hợp.'],
            ['slug' => 'ghe-sau', 'display_name' => 'Ghế sâu', 'description' => 'Thiết kế ghế sâu này mang lại nhiều không gian hơn để bạn duỗi chân khi thư giãn. So với ghế nông, ghế sâu có đệm lớn hơn, cho phép người cao ngồi thoải mái – và người ở mọi chiều cao đều có thể ngả lưng hoặc cuộn tròn người theo ý muốn.'],
            ['slug' => 'hang-qua-kho', 'display_name' => 'Hàng quá khổ', 'description' => 'Do kích thước lớn của mặt hàng này, chúng tôi đặc biệt khuyến nghị bạn xem lại Hướng dẫn Giao hàng Thành công của chúng tôi để tránh các vấn đề có thể xảy ra khi giao hàng.'],
        ];

        foreach ($features as $feature) {
            $lookup = Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $feature['slug']],
                ['namespace_id' => $nsId, ...$feature]
            );

            $this->attachMedia(
                $lookup,
                "{$this->roomImageBase}/tinh-nang/{$feature['slug']}.jpg",
                'image'
            );
        }
        $this->command->info('Seeded features');
    }

    protected function seedMaterials(): void
    {
        $nsId = $this->getNamespaceId('chat-lieu');
        if (! $nsId) {
            return;
        }

        $materials = [
            ['slug' => 'vai', 'display_name' => 'Vải'],
            ['slug' => 'da', 'display_name' => 'Da'],
            ['slug' => 'go', 'display_name' => 'Gỗ'],
            ['slug' => 'go-cong-nghiep', 'display_name' => 'Gỗ công nghiệp'],
            ['slug' => 'go-tu-nhien', 'display_name' => 'Gỗ tự nhiên'],
            ['slug' => 'go-oai', 'display_name' => 'Gỗ óc chó'],
            ['slug' => 'go-soi', 'display_name' => 'Gỗ sồi'],
            ['slug' => 'go-huong', 'display_name' => 'Gỗ hương'],
            ['slug' => 'mdf', 'display_name' => 'MDF'],
            ['slug' => 'nhom', 'display_name' => 'Nhôm'],
            ['slug' => 'thep', 'display_name' => 'Thép'],
            ['slug' => 'inox', 'display_name' => 'Inox'],
            ['slug' => 'kinh', 'display_name' => 'Kính'],
            ['slug' => 'nhua', 'display_name' => 'Nhựa'],
        ];

        foreach ($materials as $material) {
            Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $material['slug']],
                ['namespace_id' => $nsId, ...$material]
            );
        }
        $this->command->info('Seeded materials');
    }

    protected function seedShapes(): void
    {
        $nsId = $this->getNamespaceId('hinh-dang');
        if (! $nsId) {
            return;
        }

        $shapes = [
            ['slug' => 'tron', 'display_name' => 'Tròn'],
            ['slug' => 'vuong', 'display_name' => 'Vuông'],
            ['slug' => 'chunhat', 'display_name' => 'Chữ nhật'],
            ['slug' => 'oval', 'display_name' => 'Oval'],
            ['slug' => 'tam-giac', 'display_name' => 'Tam giác'],
            ['slug' => 'luc-giac', 'display_name' => 'Lục giác'],
            ['slug' => 'thang', 'display_name' => 'Thang'],
            ['slug' => 'ban-ngan', 'display_name' => 'Bán nguyệt'],
        ];

        foreach ($shapes as $shape) {
            Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $shape['slug']],
                ['namespace_id' => $nsId, ...$shape]
            );
        }
        $this->command->info('Seeded shapes');
    }

    protected function seedSizes(): void
    {
        $nsId = $this->getNamespaceId('kich-co');
        if (! $nsId) {
            return;
        }

        $sizes = [
            ['slug' => 'nho', 'display_name' => 'Nhỏ'],
            ['slug' => 'trung-binh', 'display_name' => 'Trung bình'],
            ['slug' => 'lon', 'display_name' => 'Lớn'],
            ['slug' => 'extra-large', 'display_name' => 'Extra Large'],
            ['slug' => '1-cho', 'display_name' => '1 chỗ'],
            ['slug' => '2-cho', 'display_name' => '2 chỗ'],
            ['slug' => '3-cho', 'display_name' => '3 chỗ'],
            ['slug' => '4-cho', 'display_name' => '4 chỗ'],
        ];

        foreach ($sizes as $size) {
            Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $size['slug']],
                ['namespace_id' => $nsId, ...$size]
            );
        }
        $this->command->info('Seeded sizes');
    }

    protected function seedComfortLevels(): void
    {
        $nsId = $this->getNamespaceId('muc-do-thoai-mai');
        if (! $nsId) {
            return;
        }

        $levels = [
            ['slug' => 'cap-1', 'display_name' => 'Cấp 1 - Cứng'],
            ['slug' => 'cap-2', 'display_name' => 'Cấp 2 - Hơi cứng'],
            ['slug' => 'cap-3', 'display_name' => 'Cấp 3 - Vừa'],
            ['slug' => 'cap-4', 'display_name' => 'Cấp 4 - Khá mềm'],
            ['slug' => 'cap-5', 'display_name' => 'Cấp 5 - Mềm'],
        ];

        foreach ($levels as $level) {
            Lookup::updateOrCreate(
                ['namespace_id' => $nsId, 'slug' => $level['slug']],
                ['namespace_id' => $nsId, ...$level]
            );
        }
        $this->command->info('Seeded comfort levels');
    }
}
