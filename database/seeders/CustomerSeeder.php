<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Models\Customer\Customer;
use App\Models\Setting\Province;
use App\Models\Setting\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCustomers();
    }

    protected function seedCustomers(): void
    {
        Customer::factory()->count(1000)->create();

        $hn = Province::firstWhere('province_code', '01');
        $hnWard = Ward::firstWhere('province_code', '01');

        $data = [
            ['full_name' => 'Nguyễn Văn A', 'email' => 'nguyenvana@gmail.com', 'phone' => '0911111111', 'address' => '123 Nguyễn Trãi'],
            ['full_name' => 'Trần Thị B', 'email' => 'tranthib@gmail.com', 'phone' => '0922222222', 'address' => '456 Lê Lợi'],
            ['full_name' => 'Lê Văn C', 'email' => 'levanc@gmail.com', 'phone' => '0933333333', 'address' => '789 Trần Hưng Đạo'],
            ['full_name' => 'Phạm Thị D', 'email' => 'phamthid@gmail.com', 'phone' => '0944444444', 'address' => '321 Hai Bà Trưng'],
            ['full_name' => 'Hoàng Văn E', 'email' => 'hoangvane@gmail.com', 'phone' => '0955555555', 'address' => '654 Nguyễn Huệ'],
        ];

        foreach ($data as $i => $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'type' => 'customer',
                    'name' => $d['full_name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]
            );

            Customer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $d['full_name'],
                    'phone' => $d['phone'],
                    'province_code' => $hn?->province_code,
                    'province_name' => $hn?->name,
                    'ward_code' => $hnWard?->ward_code,
                    'ward_name' => $hnWard?->name,
                    'address_data' => ['street' => $d['address'], 'full_address' => $d['address'] . ', ' . $hnWard?->name . ', ' . $hn?->name]
                ]
            );
        }

        $this->command->info('Created ' . count($data) . ' customers.');
    }
}
