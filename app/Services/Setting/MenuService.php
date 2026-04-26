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
            UserType::Customer => $this->getCustomerMenu($user),
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
        if ($user->canAny(['employees.view', 'departments.view', 'designers.view'])) {
            $hrItems = [];

            if ($user->can('departments.view')) {
                $hrItems[] = [
                    'title' => 'Phòng ban',
                    'href' => route('employee.hr.departments.index'),
                    'isActive' => Route::is('hr.departments.*'),
                ];
            }

            if ($user->can('employees.view')) {
                $hrItems[] = [
                    'title' => 'Nhân viên',
                    'href' => route('employee.hr.employees.index'),
                    'isActive' => Route::is('hr.employees.*'),
                ];
            }

            if ($user->can('designers.view')) {
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
        if ($user->canAny(['categories.view', 'collections.view', 'products.view'])) {
            $productItems = [];

            if ($user->can('products.view')) {
                $productItems[] = [
                    'title' => 'Danh sách sản phẩm',
                    'href' => route('employee.products.index'),
                    'isActive' => Route::is('employee.products.*'),
                ];
            }

            if ($user->can('categories.view')) {
                $productItems[] = [
                    'title' => 'Danh mục',
                    'href' => route('employee.categories.index'),
                    'isActive' => Route::is('employee.categories.*'),
                ];
            }

            if ($user->can('collections.view')) {
                $productItems[] = [
                    'title' => 'Bộ sưu tập',
                    'href' => route('employee.collections.index'),
                    'isActive' => Route::is('employee.collections.*'),
                ];
            }

            if ($user->can('bundles.view')) {
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

        // Sales Group
        if ($user->canAny(['orders.view', 'invoices.view', 'payments.view', 'payments.manage'])) {
            $salesItems = [];

            if ($user->can('orders.view')) {
                $salesItems[] = [
                    'title' => 'Đơn hàng',
                    'href' => route('employee.sales.orders.index'),
                    'isActive' => Route::is('sales.orders.*'),
                ];
            }

            if ($user->can('invoices.view')) {
                $salesItems[] = [
                    'title' => 'Hóa đơn',
                    'href' => route('employee.sales.invoices.index'),
                    'isActive' => Route::is('sales.invoices.*'),
                ];
            }

            if ($user->can('payments.view')) {
                $salesItems[] = [
                    'title' => 'Thanh toán',
                    'href' => route('employee.sales.payments.index'),
                    'isActive' => Route::is('sales.payments.*'),
                ];
            }

            if ($user->can('payments.manage')) {
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
        if ($user->canAny(['shipments.view', 'shipping_methods.view'])) {
            $fulfillmentItems = [];

            if ($user->can('shipments.view')) {
                $fulfillmentItems[] = [
                    'title' => 'Vận chuyển',
                    'href' => route('employee.fulfillment.shipments.index'),
                    'isActive' => Route::is('fulfillment.shipments.*'),
                ];
            }

            if ($user->can('shipping_methods.view')) {
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

        // Booking Group
        if ($user->canAny(['bookings.view', 'design_services.view'])) {
            $bookingItems = [];

            if ($user->can('bookings.view')) {
                $bookingItems[] = [
                    'title' => 'Đặt lịch',
                    'href' => route('employee.booking.index'),
                    'isActive' => Route::is('booking.*') && ! Route::is('booking.services.*'),
                ];
            }

            $menu[] = [
                'title' => 'Đặt lịch thiết kế',
                'href' => '#',
                'icon' => 'CalendarDays',
                'isActive' => Route::is('booking.*'),
                'items' => $bookingItems,
            ];
        }

        // Inventory Management Group
        if ($user->can('inventory.view')) {
            $inventoryItems = [];

            if ($user->can('inventory.view')) {
                $inventoryItems[] = [
                    'title' => 'Nhà cung cấp',
                    'href' => route('employee.inventory.vendor.index'),
                    'isActive' => Route::is('employee.vendor.*'),
                ];

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
        if ($user->can('lookups.view')) {
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

    private function getCustomerMenu(User $user): array
    {
        return [
            [
                'title' => 'Bảng điều khiển',
                'url' => route('customer.dashboard'),
                'icon' => 'LayoutGrid',
                'isActive' => Route::is('customer.dashboard'),
            ],
        ];
    }
}
