<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Booking\Designer;
use App\Models\Booking\DesignService;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerAddress;
use App\Models\Employee\Department;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\ShippingMethod;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Product\Bundle;
use App\Models\Product\BundleContent;
use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Setting\Lookup;
use App\Models\Setting\Province;
use App\Models\Setting\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission as SpatiePermission;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedLocations();
        $this->seedDepartments();
        $this->seedEmployees();
        $this->seedCustomers();
        $this->seedCategories();
        $this->seedCollections();

        // Seed Ghế Timber product (with images, specs, features)
        $this->call([TimberProductSeeder::class]);

        $this->seedProducts();
        $this->seedBundles();
        $this->seedTimberInventory();
        $this->seedShippingMethods();
        $this->seedDesigners();
    }

    // ── Roles & Permissions ────────────────────────────────────────────────

    protected function seedRolesAndPermissions(): void
    {
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.manage',
            'roles.view',
            'roles.manage',
            'permissions.view',
            'permissions.assign',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'products.manage',
            'products.publish',
            'categories.view',
            'categories.manage',
            'bundles.view',
            'bundles.manage',
            'customers.view',
            'customers.update',
            'customers.delete',
            'employees.view',
            'employees.manage',
            'inventory.view',
            'inventory.manage',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.manage',
            'invoices.view',
            'invoices.manage',
            'payments.view',
            'payments.manage',
            'shipments.view',
            'shipments.manage',
            'bookings.view',
            'bookings.manage',
            'bookings.approve',
            'designers.view',
            'designers.manage',
            'design_services.view',
            'design_services.manage',
            'hr.employees.view',
            'hr.employees.manage',
            'settings.view',
            'settings.manage',
            'lookups.view',
            'lookups.manage',
            'shipping_methods.view',
            'shipping_methods.manage',
            'horizon.view',
        ];

        foreach ($permissions as $perm) {
            SpatiePermission::firstOrCreate(['name' => $perm]);
        }

        $rolePermissions = [
            'super_admin' => null,
            'admin' => [
                'users.manage',
                'roles.manage',
                'permissions.assign',
                'products.manage',
                'products.publish',
                'categories.manage',
                'bundles.manage',
                'customers.view',
                'customers.update',
                'customers.delete',
                'employees.manage',
                'inventory.manage',
                'orders.view',
                'orders.create',
                'orders.update',
                'orders.manage',
                'shipments.view',
                'shipments.manage',
                'invoices.manage',
                'payments.manage',
                'bookings.manage',
                'bookings.approve',
                'designers.view',
                'design_services.view',
                'design_services.manage',
                'hr.employees.manage',
                'settings.manage',
                'lookups.manage',
                'shipping_methods.view',
                'shipping_methods.manage',
                'horizon.view',
            ],
            'store_manager' => [
                'products.view',
                'categories.view',
                'bundles.view',
                'customers.view',
                'customers.update',
                'inventory.view',
                'inventory.manage',
                'orders.view',
                'orders.create',
                'orders.update',
                'orders.manage',
                'shipments.view',
                'shipments.manage',
                'bookings.view',
                'bookings.manage',
                'designers.view',
                'design_services.view',
            ],
            'warehouse_staff' => [
                'products.view',
                'inventory.view',
                'inventory.manage',
                'orders.view',
                'shipments.view',
                'shipments.manage',
            ],
            'support' => [
                'customers.view',
                'customers.update',
                'products.view',
                'orders.view',
                'bookings.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($perms === null) {
                $role->givePermissionTo(SpatiePermission::pluck('name'));
            } else {
                $role->givePermissionTo($perms);
            }
        }

        $this->command->info('Created '.count($rolePermissions).' roles.');
    }

    // ── Locations ──────────────────────────────────────────────────────────

    protected function seedLocations(): void
    {
        $hn = Province::firstWhere('province_code', '01');
        $hcm = Province::firstWhere('province_code', '79');
        $hnWard = Ward::firstWhere('province_code', '01');
        $hcmWard = Ward::firstWhere('province_code', '79');

        $data = [
            ['code' => 'WH-001', 'name' => 'Kho chính Hà Nội', 'type' => LocationType::Warehouse, 'province' => $hn, 'ward' => $hnWard, 'is_active' => true],
            ['code' => 'WH-002', 'name' => 'Kho phụ HCM', 'type' => LocationType::Warehouse, 'province' => $hcm, 'ward' => $hcmWard, 'is_active' => true],
            ['code' => 'RT-001', 'name' => 'Cửa hàng Cầu Giấy', 'type' => LocationType::Retail, 'province' => $hn, 'ward' => $hnWard, 'is_active' => true],
            ['code' => 'RT-002', 'name' => 'Cửa hàng Đống Đa', 'type' => LocationType::Retail, 'province' => $hn, 'ward' => $hnWard, 'is_active' => true],
        ];

        foreach ($data as $d) {
            Location::firstOrCreate(
                ['code' => $d['code']],
                [
                    'name' => $d['name'],
                    'type' => $d['type'],
                    'province_code' => $d['province']->province_code ?? null,
                    'province_name' => $d['province']->name ?? null,
                    'ward_code' => $d['ward']->ward_code ?? null,
                    'ward_name' => $d['ward']->name ?? null,
                    'is_active' => $d['is_active'],
                ]
            );
        }

        $this->command->info('Created '.count($data).' locations.');
    }

    // ── Departments ────────────────────────────────────────────────────────

    protected function seedDepartments(): void
    {
        foreach (
            [
                ['name' => 'Phòng Kinh doanh', 'code' => 'SALES', 'is_active' => true],
                ['name' => 'Phòng Kho vận', 'code' => 'WAREHOUSE', 'is_active' => true],
                ['name' => 'Phòng Thiết kế', 'code' => 'DESIGN', 'is_active' => true],
                ['name' => 'Phòng Hỗ trợ', 'code' => 'SUPPORT', 'is_active' => true],
            ] as $d
        ) {
            Department::firstOrCreate(['code' => $d['code']], $d);
        }

        $this->command->info('Created 4 departments.');
    }

    // ── Employees ──────────────────────────────────────────────────────────

    protected function seedEmployees(): void
    {
        $store = Location::where('code', 'RT-001')->first();
        $salesDept = Department::where('code', 'SALES')->first();

        $data = [
            ['email' => 'admin@furniture.com', 'name' => 'Quản trị viên', 'full_name' => 'Quản trị viên', 'role' => 'super_admin', 'dept' => $salesDept?->id, 'loc' => $store?->id],
            ['email' => 'manager@furniture.com', 'name' => 'Lan', 'full_name' => 'Nguyễn Thị Lan', 'role' => 'store_manager', 'dept' => $salesDept?->id, 'loc' => $store?->id],
            ['email' => 'warehouse1@furniture.com', 'name' => 'Hùng', 'full_name' => 'Trần Văn Hùng', 'role' => 'warehouse_staff', 'dept' => null, 'loc' => null],
            ['email' => 'support1@furniture.com', 'name' => 'Mai', 'full_name' => 'Lê Thị Mai', 'role' => 'support', 'dept' => null, 'loc' => null],
        ];

        foreach ($data as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'type' => 'employee',
                    'name' => $d['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::firstWhere('name', $d['role']);
            if ($role && ! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $d['full_name'],
                    'phone' => '090'.rand(1000000, 9999999),
                    'department_id' => $d['dept'],
                    'location_id' => $d['loc'],
                    'hire_date' => now()->subMonths(rand(3, 24)),
                ]
            );
        }

        $this->command->info('Created '.count($data).' employees.');
    }

    // ── Customers ──────────────────────────────────────────────────────────

    protected function seedCustomers(): void
    {
        $hn = Province::firstWhere('province_code', '01');
        $hnWard = Ward::firstWhere('province_code', '01');

        $data = [
            ['full_name' => 'Nguyễn Văn A', 'email' => 'nguyen.van.a@gmail.com', 'phone' => '0911111111', 'address' => '123 Nguyễn Trãi'],
            ['full_name' => 'Trần Thị B', 'email' => 'tran.thi.b@gmail.com', 'phone' => '0922222222', 'address' => '456 Lê Lợi'],
            ['full_name' => 'Lê Văn C', 'email' => 'le.van.c@gmail.com', 'phone' => '0933333333', 'address' => '789 Trần Hưng Đạo'],
            ['full_name' => 'Phạm Thị D', 'email' => 'pham.thi.d@gmail.com', 'phone' => '0944444444', 'address' => '321 Hai Bà Trưng'],
            ['full_name' => 'Hoàng Văn E', 'email' => 'hoang.van.e@gmail.com', 'phone' => '0955555555', 'address' => '654 Nguyễn Huệ'],
        ];

        foreach ($data as $i => $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'type' => 'customer',
                    'name' => Str::slug($d['full_name'], '_'),
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]
            );

            $customer = Customer::firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $d['full_name'], 'phone' => $d['phone']]
            );

            CustomerAddress::firstOrCreate(
                ['customer_id' => $customer->id, 'type' => 'shipping'],
                [
                    'customer_id' => $customer->id,
                    'type' => 'shipping',
                    'province_code' => $hn?->province_code,
                    'province_name' => $hn?->name,
                    'ward_code' => $hnWard?->ward_code,
                    'ward_name' => $hnWard?->name,
                    'address_data' => ['street' => $d['address']],
                    'is_default' => true,
                ]
            );
        }

        $this->command->info('Created '.count($data).' customers.');
    }

    // ── Categories ─────────────────────────────────────────────────────────

    protected function seedCategories(): void
    {
        $group = Lookup::first(); // Ghế ngồi
        $room = Lookup::firstWhere('display_name', 'Phòng khách');

        foreach (
            [
                ['display_name' => 'Ghế', 'slug' => 'ghe'],
                ['display_name' => 'Bàn', 'slug' => 'ban'],
                ['display_name' => 'Giường', 'slug' => 'giuong'],
                ['display_name' => 'Tủ', 'slug' => 'tu'],
                ['display_name' => 'Kệ', 'slug' => 'ke'],
                ['display_name' => 'Sofa', 'slug' => 'sofas'],
                ['display_name' => 'Bàn trang điểm', 'slug' => 'ban-trang-diem'],
                ['display_name' => 'Ghế thư giãn', 'slug' => 'ghe-thu-gian'],
            ] as $d
        ) {
            Category::firstOrCreate(
                ['slug' => $d['slug']],
                [
                    'display_name' => $d['display_name'],
                    'group_id' => $group?->id,
                    'room_id' => $room?->id,
                    'product_type' => 'noi-that',
                    'is_active' => true,
                    'filterable_specs' => [],
                ]
            );
        }

        $this->command->info('Created 8 categories.');
    }

    // ── Collections ────────────────────────────────────────────────────────

    protected function seedCollections(): void
    {
        Collection::firstOrCreate(
            ['slug' => 'timber'],
            ['display_name' => 'Timber', 'description' => 'Bộ sưu tập Ghế Timber', 'is_active' => true, 'metadata' => []]
        );

        $this->command->info('Created Timber collection.');
    }

    // ── Products & Variants ────────────────────────────────────────────────

    protected function seedProducts(): void
    {
        $categories = Category::all();

        $products = [
            ['name' => 'Ghế sofa', 'variants' => [
                ['name' => 'Xanh nhạt', 'sku' => 'GS-XN', 'price' => 5500000],
                ['name' => 'Xám đậm', 'sku' => 'GS-XD', 'price' => 5800000],
                ['name' => 'Be', 'sku' => 'GS-BE', 'price' => 5200000],
            ]],
            ['name' => 'Ghế ăn', 'variants' => [
                ['name' => 'Gỗ tự nhiên', 'sku' => 'GA-GTN', 'price' => 2800000],
                ['name' => 'Gỗ cao su', 'sku' => 'GA-GCS', 'price' => 2200000],
            ]],
            ['name' => 'Bàn làm việc', 'variants' => [
                ['name' => 'Trắng 120cm', 'sku' => 'BLV-T120', 'price' => 3500000],
                ['name' => 'Đen 120cm', 'sku' => 'BLV-D120', 'price' => 3500000],
                ['name' => 'Trắng 140cm', 'sku' => 'BLV-T140', 'price' => 4200000],
            ]],
            ['name' => 'Bàn trà', 'variants' => [
                ['name' => 'Kính tròn', 'sku' => 'BT-KT', 'price' => 4800000],
                ['name' => 'Gỗ vuông', 'sku' => 'BT-GV', 'price' => 3900000],
            ]],
            ['name' => 'Giường ngủ', 'variants' => [
                ['name' => '160x200', 'sku' => 'GN-160', 'price' => 12500000],
                ['name' => '180x200', 'sku' => 'GN-180', 'price' => 14800000],
            ]],
            ['name' => 'Tủ quần áo', 'variants' => [
                ['name' => '2 cánh', 'sku' => 'TQA-2C', 'price' => 8500000],
                ['name' => '3 cánh', 'sku' => 'TQA-3C', 'price' => 11200000],
                ['name' => '4 cánh', 'sku' => 'TQA-4C', 'price' => 14500000],
            ]],
            ['name' => 'Kệ sách', 'variants' => [
                ['name' => '5 tầng gỗ', 'sku' => 'KS-5T', 'price' => 3200000],
                ['name' => '3 tầng kim loại', 'sku' => 'KS-3TK', 'price' => 2800000],
            ]],
            ['name' => 'Tủ TV', 'variants' => [
                ['name' => 'Trắng 160cm', 'sku' => 'TTV-T160', 'price' => 6800000],
                ['name' => 'Gỗ 160cm', 'sku' => 'TTV-G160', 'price' => 7500000],
            ]],
            ['name' => 'Bàn trang điểm', 'variants' => [
                ['name' => 'Có gương LED', 'sku' => 'BTD-GL', 'price' => 5500000],
                ['name' => 'Không gương', 'sku' => 'BTD-KG', 'price' => 4200000],
            ]],
            ['name' => 'Ghế thư giãn', 'variants' => [
                ['name' => 'Da nâu', 'sku' => 'GTL-DC', 'price' => 8900000],
                ['name' => 'Vải xám', 'sku' => 'GTL-VX', 'price' => 6500000],
            ]],
        ];

        // Stock per product: [RT-001, RT-002, WH-001, WH-002]
        $stock = [
            [15, 10, 50, 30],   // Ghế sofa — everywhere
            [8, 6, 20, 0],      // Ghế ăn — no WH-002
            [5, 4, 10, 15],     // Bàn làm việc
            [0, 0, 25, 10],     // Bàn trà — NO stores
            [3, 2, 8, 0],       // Giường ngủ
            [0, 0, 0, 12],      // Tủ quần áo — ONLY WH-002
            [10, 8, 30, 20],    // Kệ sách
            [0, 0, 15, 0],      // Tủ TV — ONLY WH-001
            [6, 5, 12, 8],      // Bàn trang điểm
            [0, 0, 0, 0],       // Ghế thư giãn — NOWHERE
        ];

        $locations = Location::where('is_active', true)->orderBy('code')->get();

        foreach ($products as $idx => $prod) {
            $product = Product::firstOrCreate(
                ['name' => $prod['name']],
                [
                    'status' => 'published',
                    'category_id' => $categories->random()?->id,
                    'min_price' => collect($prod['variants'])->min('price'),
                    'max_price' => collect($prod['variants'])->max('price'),
                    'is_featured' => $idx % 3 === 0,
                    'is_new_arrival' => $idx % 4 === 0,
                    'is_custom_made' => false,
                    'warranty_months' => 12,
                ]
            );

            foreach ($prod['variants'] as $v) {
                $variant = ProductVariant::firstOrCreate(
                    ['sku' => $v['sku']],
                    [
                        'product_id' => $product->id,
                        'name' => $v['name'],
                        'slug' => Str::slug($v['sku']),
                        'sku' => $v['sku'],
                        'price' => $v['price'],
                        'status' => 'active',
                        'profit_margin_value' => 20,
                        'profit_margin_unit' => 'percentage',
                        'option_values' => [],
                    ]
                );

                // Distribute inventory across locations
                $s = $stock[$idx] ?? [0, 0, 0, 0];
                foreach ($locations as $lIdx => $loc) {
                    $qty = $s[$lIdx] ?? 0;
                    if ($qty > 0) {
                        Inventory::firstOrCreate(
                            ['variant_id' => $variant->id, 'location_id' => $loc->id],
                            [
                                'variant_id' => $variant->id,
                                'location_id' => $loc->id,
                                'quantity' => $qty,
                                'cost_per_unit' => $v['price'] * 0.6,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Created '.count($products).' products with variants & inventory.');
    }

    // ── Ghế Timber inventory ──────────────────────────────────────────────

    protected function seedTimberInventory(): void
    {
        $timber = Product::where('name', 'Ghế Timber')->first();
        if (! $timber) {
            return;
        }

        // [RT-001, RT-002, WH-001, WH-002] per variant
        $stock = [
            [10, 8, 25, 15],   // Pebble Gray
            [5, 3, 12, 0],     // Charme Chocolat
            [7, 5, 20, 10],    // Charme Green
            [0, 0, 30, 18],    // Olio Green — NO stores
            [6, 4, 15, 8],     // Charme Tan
            [12, 10, 35, 22],  // Rain Cloud Gray
            [0, 0, 0, 0],      // Charme Black — OOS
        ];

        $locations = Location::where('is_active', true)->orderBy('code')->get();

        foreach ($timber->variants as $vIdx => $variant) {
            $s = $stock[$vIdx] ?? [0, 0, 0, 0];
            foreach ($locations as $lIdx => $loc) {
                $qty = $s[$lIdx] ?? 0;
                if ($qty > 0) {
                    Inventory::firstOrCreate(
                        ['variant_id' => $variant->id, 'location_id' => $loc->id],
                        [
                            'variant_id' => $variant->id,
                            'location_id' => $loc->id,
                            'quantity' => $qty,
                            'cost_per_unit' => $variant->price * 0.55,
                        ]
                    );
                }
            }
        }

        $this->command->info('Added inventory for Ghế Timber.');
    }

    // ── Bundles ────────────────────────────────────────────────────────────

    protected function seedBundles(): void
    {
        $products = Product::with('variants')->get();

        foreach (
            [
                ['name' => 'Gói Phòng Khách', 'discount_type' => 'percentage', 'discount_value' => 10, 'product_names' => ['Ghế sofa', 'Bàn trà']],
                ['name' => 'Gói Phòng Ngủ', 'discount_type' => 'percentage', 'discount_value' => 12, 'product_names' => ['Giường ngủ', 'Ghế thư giãn']],
                ['name' => 'Gói Làm Việc', 'discount_type' => 'fixed_amount', 'discount_value' => 5000000, 'product_names' => ['Bàn làm việc', 'Bàn trang điểm']],
            ] as $b
        ) {
            $bundle = Bundle::firstOrCreate(
                ['slug' => Str::slug($b['name'])],
                [
                    'name' => $b['name'],
                    'slug' => Str::slug($b['name']),
                    'description' => 'Gói sản phẩm '.$b['name'],
                    'discount_type' => $b['discount_type'],
                    'discount_value' => $b['discount_value'],
                    'is_active' => true,
                ]
            );

            foreach ($b['product_names'] as $pName) {
                $product = $products->firstWhere('name', $pName);
                if ($product) {
                    BundleContent::firstOrCreate(
                        ['bundle_id' => $bundle->id, 'product_id' => $product->id],
                        ['bundle_id' => $bundle->id, 'product_id' => $product->id, 'quantity' => 1]
                    );
                }
            }
        }

        $this->command->info('Created 3 bundles.');
    }

    // ── Shipping Methods ───────────────────────────────────────────────────

    protected function seedShippingMethods(): void
    {
        ShippingMethod::firstOrCreate(
            ['code' => 'standard'],
            [
                'name' => 'Tiêu chuẩn',
                'code' => 'standard',
                'price' => 30000,
                'estimated_delivery_days' => 3,
                'is_active' => true,
            ]
        );

        ShippingMethod::firstOrCreate(
            ['code' => 'express'],
            [
                'name' => 'Nhanh',
                'code' => 'express',
                'price' => 60000,
                'estimated_delivery_days' => 1,
                'is_active' => true,
            ]
        );

        $this->command->info('Created 2 shipping methods.');
    }

    // ── Designers ──────────────────────────────────────────────────────────

    protected function seedDesigners(): void
    {
        $emp = Employee::first();
        if ($emp) {
            Designer::firstOrCreate(
                ['user_id' => $emp->user_id],
                [
                    'user_id' => $emp->user_id,
                    'employee_id' => $emp->id,
                    'full_name' => $emp->full_name,
                    'phone' => '090'.rand(1000000, 9999999),
                    'hourly_rate' => 500000,
                    'auto_confirm_bookings' => false,
                    'is_active' => true,
                ]
            );
        }

        DesignService::firstOrCreate(
            ['name' => 'Tư vấn thiết kế nội thất'],
            [
                'name' => 'Tư vấn thiết kế nội thất',
                'type' => 'consultation',
                'is_schedule_blocking' => true,
                'base_price' => 1000000,
                'deposit_percentage' => 30,
                'estimated_minutes' => 90,
            ]
        );

        $this->command->info('Created 1 designer + 1 design service.');
    }
}
