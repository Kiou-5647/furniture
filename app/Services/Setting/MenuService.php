<?php

namespace App\Services\Setting;

use App\Enums\UserType;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Route;

class MenuService
{
    public function getMenu(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return match ($user->type) {
            UserType::Employee => $this->getEmployeeMenu($user),
            default => [],
        };
    }

    private function getEmployeeMenu(User $user): array
    {
        $menu = [
            [
                'title' => 'Bảng điều khiển',
                'href' => route('employee.dashboard'),
                'icon' => 'LayoutGrid',
                'isActive' => Route::is('employee.dashboard'),
            ],
        ];

        // HR Group
        if ($user->canAny(['Xem nhân viên', 'Quản lý nhân viên', 'Xem phòng ban', 'Quản lý phòng ban', 'Xem nhà thiết kế', 'Quản lý nhà thiết kế'])) {
            $hrItems = [];

            if ($user->can('Quản lý phòng ban')) {
                $hrItems[] = [
                    'title' => 'Phòng ban',
                    'href' => route('employee.hr.departments.index'),
                    'isActive' => Route::is('hr.departments.*'),
                ];
            }

            if ($user->can('Quản lý nhân viên')) {
                $hrItems[] = [
                    'title' => 'Nhân viên',
                    'href' => route('employee.hr.employees.index'),
                    'isActive' => Route::is('hr.employees.*'),
                ];
            }

            if ($user->can('Xem nhà thiết kế')) {
                $hrItems[] = [
                    'title' => 'Nhà thiết kế',
                    'href' => route('employee.hr.designers.index'),
                    'isActive' => Route::is('hr.designers.*'),
                ];
            }

            $menu[] = [
                'title' => 'Quản lý nhân sự',
                'href' => '#',
                'icon' => 'Users',
                'isActive' => Route::is('hr.*'),
                'items' => $hrItems,
            ];
        }

        // Product Management Group
        if ($user->canAny(['Xem danh mục', 'Xem bộ sưu tập', 'Xem sản phẩm'])) {
            $productItems = [];

            if ($user->can('Xem sản phẩm')) {
                $productItems[] = [
                    'title' => 'Danh sách sản phẩm',
                    'href' => route('employee.products.index'),
                    'isActive' => Route::is('employee.products.*'),
                ];
            }

            if ($user->can('Xem danh mục')) {
                $productItems[] = [
                    'title' => 'Danh mục',
                    'href' => route('employee.categories.index'),
                    'isActive' => Route::is('employee.categories.*'),
                ];
            }

            if ($user->can('Xem bộ sưu tập')) {
                $productItems[] = [
                    'title' => 'Bộ sưu tập',
                    'href' => route('employee.collections.index'),
                    'isActive' => Route::is('employee.collections.*'),
                ];
            }

            if ($user->can('Xem gói sản phẩm')) {
                $productItems[] = [
                    'title' => 'Gói sản phẩm',
                    'href' => route('employee.bundles.index'),
                    'isActive' => Route::is('employee.bundles.*'),
                ];
            }

            $menu[] = [
                'title' => 'Sản phẩm',
                'href' => '#',
                'icon' => 'Package',
                'isActive' => Route::is('employee.products.*'),
                'items' => $productItems,
            ];
        }

        // Booking Group
        if ($user->canAny(['Xem lịch thiết kế'])) {
            $bookingItems = [];

            $menu[] = [
                'title' => 'Đặt lịch thiết kế',
                'href' => route('employee.booking.index'),
                'icon' => 'CalendarDays',
                'isActive' => Route::is('booking.*'),
                'items' => $bookingItems,
            ];
        }

        // Sales Group
        if ($user->canAny(['Xem khuyến mãi', 'Xem đơn hàng', 'Xem hóa đơn', 'Xem thanh toán', 'Quản lý thanh toán'])) {
            $salesItems = [];

            if ($user->can('Xem đơn hàng')) {
                $salesItems[] = [
                    'title' => 'Đơn hàng',
                    'href' => route('employee.sales.orders.index'),
                    'isActive' => Route::is('sales.orders.*'),
                ];
            }

            if ($user->can('Xem khuyến mãi')) {
                $salesItems[] = [
                    'title' => 'Giảm giá',
                    'href' => route('employee.sales.discounts.index'),
                    'isActive' => Route::is('sales.discounts.*'),
                ];
            }

            if ($user->can('Xem hóa đơn')) {
                $salesItems[] = [
                    'title' => 'Hóa đơn',
                    'href' => route('employee.sales.invoices.index'),
                    'isActive' => Route::is('sales.invoices.*'),
                ];
            }

            if ($user->can('Xem thanh toán')) {
                $salesItems[] = [
                    'title' => 'Thanh toán',
                    'href' => route('employee.sales.payments.index'),
                    'isActive' => Route::is('sales.payments.*'),
                ];
            }

            if ($user->can('Quản lý thanh toán')) {
                $salesItems[] = [
                    'title' => 'Hoàn tiền',
                    'href' => route('employee.sales.refunds.index'),
                    'isActive' => Route::is('sales.refunds.*'),
                ];
            }

            $menu[] = [
                'title' => 'Bán hàng',
                'href' => '#',
                'icon' => 'ShoppingCart',
                'isActive' => Route::is('sales.*'),
                'items' => $salesItems,
            ];
        }

        // Fulfillment Group
        if ($user->canAny(['Xem vận chuyển', 'Xem phương thức vận chuyển'])) {
            $fulfillmentItems = [];

            if ($user->can('Xem vận chuyển')) {
                $fulfillmentItems[] = [
                    'title' => 'Vận chuyển',
                    'href' => route('employee.fulfillment.shipments.index'),
                    'isActive' => Route::is('fulfillment.shipments.*'),
                ];
            }

            if ($user->can('Xem phương thức vận chuyển')) {
                $fulfillmentItems[] = [
                    'title' => 'Phương thức vận chuyển',
                    'href' => route('employee.fulfillment.shipping-methods.index'),
                    'isActive' => Route::is('fulfillment.shipping-methods.*'),
                ];
            }

            $menu[] = [
                'title' => 'Vận hành',
                'href' => '#',
                'icon' => 'Truck',
                'isActive' => Route::is('fulfillment.*'),
                'items' => $fulfillmentItems,
            ];
        }

        // Inventory Management Group
        if ($user->can('Xem kho hàng')) {
            $inventoryItems = [];

            if ($user->canAny(['Xem kho hàng', 'Quản lý kho hàng'])) {
                if ($user->canAny(['Xem nhà cung cấp', 'Quản lý nhà cung cấp'])) {
                    $inventoryItems[] = [
                        'title' => 'Nhà cung cấp',
                        'href' => route('employee.inventory.vendor.index'),
                        'isActive' => Route::is('employee.vendor.*'),
                    ];
                }

                $inventoryItems[] = [
                    'title' => 'Vị trí kho hàng',
                    'href' => route('employee.inventory.locations.index'),
                    'isActive' => Route::is('employee.inventory.locations.*'),
                ];

                $inventoryItems[] = [
                    'title' => 'Chuyển kho',
                    'href' => route('employee.inventory.transfers.index'),
                    'isActive' => Route::is('employee.inventory.transfers.*'),
                ];

                $inventoryItems[] = [
                    'title' => 'Lịch sử tồn kho',
                    'href' => route('employee.inventory.movements.index'),
                    'isActive' => Route::is('employee.inventory.movements.*'),
                ];
            }

            $menu[] = [
                'title' => 'Kho hàng',
                'href' => '#',
                'icon' => 'Warehouse',
                'isActive' => Route::is('employee.inventory.*') || Route::is('employee.vendor.*'),
                'items' => $inventoryItems,
            ];
        }

        // Settings Group
        if ($user->can('Xem tra cứu')) {
            $menu[] = [
                'title' => 'Cấu hình',
                'href' => '#',
                'icon' => 'Settings2',
                'isActive' => Route::is('employee.settings.*'),
                'items' => [
                    [
                        'title' => 'Cấu hình chung',
                        'href' => route('employee.settings.general.index'),
                        'isActive' => Route::is('employee.settings.general.*'),
                    ],
                    [
                        'title' => 'Tra cứu',
                        'href' => route('employee.settings.lookups.index'),
                        'isActive' => Route::is('employee.settings.lookups.*'),
                    ],
                ],
            ];
        }

        return $menu;
    }
}
