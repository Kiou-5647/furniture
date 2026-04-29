<?php

namespace Database\Seeders;

use App\Models\Vendor\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Công ty TNHH Nội thất Gỗ Việt',
                'contact_name' => 'Nguyễn Minh Tuấn',
                'email' => 'tuan@gooviet.vn',
                'phone' => '0909123456',
                'website' => 'https://gooviet.vn',
                'province_code' => '01',
                'ward_code' => '00070',
                'street' => '123 Đường Láng',
                'bank_name' => 'Vietcombank',
                'bank_account_number' => '0123456789',
                'bank_account_holder' => 'NGUYEN MINH TUAN',
                'is_active' => true,
            ],
            [
                'name' => 'Công ty CP Sản xuất Đồ gỗ An Cường',
                'contact_name' => 'Trần Thị Hương',
                'email' => 'huong.tran@ancuong.com',
                'phone' => '0918765432',
                'website' => 'https://ancuong.com',
                'province_code' => '79',
                'ward_code' => '25747',
                'street' => '456 Nguyễn Văn Linh',
                'bank_name' => 'Techcombank',
                'bank_account_number' => '19028765432',
                'bank_account_holder' => 'TRAN THI HUONG',
                'is_active' => true,
            ],
            [
                'name' => 'Xưởng Mộc Mỹ Nghệ Đồng Kỵ',
                'contact_name' => 'Lê Văn Hùng',
                'email' => 'hung.dongky@gmail.com',
                'phone' => '0977654321',
                'website' => null,
                'province_code' => '01',
                'ward_code' => '00004',
                'street' => 'Làng Nghề Đồng Kỵ',
                'bank_name' => 'Agribank',
                'bank_account_number' => '2601205678901',
                'bank_account_holder' => 'LE VAN HUNG',
                'is_active' => true,
            ],
            [
                'name' => 'Công ty TNHH IKEA Vietnam Trading',
                'contact_name' => 'Phạm Thị Lan',
                'email' => 'lan.pham@ikeavn.com',
                'phone' => '0932111222',
                'website' => 'https://ikea.com.vn',
                'province_code' => '79',
                'ward_code' => '25750',
                'street' => 'Tầng 5, Tòa nhà Bitexco, 2 Hải Triều',
                'bank_name' => 'MB Bank',
                'bank_account_number' => '0912345678',
                'bank_account_holder' => 'PHAM THI LAN',
                'is_active' => true,
            ],
            [
                'name' => 'Nhà Máy Nội Thất Hoà Phát',
                'contact_name' => 'Đặng Quốc Bảo',
                'email' => 'bao.dang@noithathoaphat.vn',
                'phone' => '0966888999',
                'website' => 'https://noithathoaphat.vn',
                'province_code' => '01',
                'ward_code' => '00025',
                'street' => '392 Nguyễn Trãi',
                'bank_name' => 'BIDV',
                'bank_account_number' => '8888999900',
                'bank_account_holder' => 'DANG QUOC BAO',
                'is_active' => false,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }
}
