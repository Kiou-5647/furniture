<?php

namespace App\Services\Setting;

use App\Constants\Permission;
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
        if ($user->canAny([
            Permission::EMPLOYEE['SELECT'],
            Permission::EMPLOYEE['UPDATE'],
            Permission::DEPARTMENT['SELECT'],
            Permission::DEPARTMENT['UPDATE'],
            Permission::DESIGNER['SELECT'],
            Permission::DESIGNER['UPDATE'],
        ])) {
            $hrItems = [];

            if ($user->can(Permission::DEPARTMENT['UPDATE'])) {
                $hrItems[] = [
                    'title' => 'Phòng ban',
                    'href' => route('employee.hr.departments.index'),
                    'isActive' => Route::is('hr.departments.*'),
                ];
            }

            if ($user->can(Permission::EMPLOYEE['UPDATE'])) {
                $hrItems[] = [
                    'title' => 'Nhân viên',
                    'href' => route('employee.hr.employees.index'),
                    'isActive' => Route::is('hr.employees.*'),
                ];
            }

            if ($user->can(Permission::DESIGNER['SELECT'])) {
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
        if ($user->canAny([
            Permission::CATEGORY['SELECT'],
            Permission::COLLECTION['SELECT'],
            Permission::PRODUCT['SELECT'],
        ])) {
            $productItems = [];

            if ($user->can(Permission::PRODUCT['SELECT'])) {
                $productItems[] = [
                    'title' => 'Danh sách sản phẩm',
                    'href' => route('employee.products.index'),
                    'isActive' => Route::is('employee.products.*'),
                ];
            }

            if ($user->can(Permission::CATEGORY['SELECT'])) {
                $productItems[] = [
                    'title' => 'Danh mục',
                    'href' => route('employee.categories.index'),
                    'isActive' => Route::is('employee.categories.*'),
                ];
            }

            if ($user->can(Permission::COLLECTION['SELECT'])) {
                $productItems[] = [
                    'title' => 'Bộ sưu tập',
                    'href' => route('employee.collections.index'),
                    'isActive' => Route::is('employee.collections.*'),
                ];
            }

            if ($user->can(Permission::BUNDLE['SELECT'])) {
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
        if ($user->canAny([Permission::BOOKING['SELECT']])) {
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
        if ($user->canAny([
            Permission::DISCOUNT['SELECT'],
            Permission::ORDER['SELECT'],
            Permission::INVOICE['SELECT'],
            Permission::PAYMENT['SELECT'],
            Permission::REFUND['SELECT'],
        ])) {
            $salesItems = [];

            if ($user->can(Permission::ORDER['SELECT'])) {
                $salesItems[] = [
                    'title' => 'Đơn hàng',
                    'href' => route('employee.sales.orders.index'),
                    'isActive' => Route::is('sales.orders.*'),
                ];
            }

            if ($user->can(Permission::DISCOUNT['SELECT'])) {
                $salesItems[] = [
                    'title' => 'Khuyến mãi',
                    'href' => route('employee.sales.discounts.index'),
                    'isActive' => Route::is('sales.discounts.*'),
                ];
            }

            if ($user->can(Permission::INVOICE['SELECT'])) {
                $salesItems[] = [
                    'title' => 'Hóa đơn',
                    'href' => route('employee.sales.invoices.index'),
                    'isActive' => Route::is('sales.invoices.*'),
                ];
            }

            if ($user->can(Permission::PAYMENT['SELECT'])) {
                $salesItems[] = [
                    'title' => 'Thanh toán',
                    'href' => route('employee.sales.payments.index'),
                    'isActive' => Route::is('sales.payments.*'),
                ];
            }

            if ($user->can(Permission::REFUND['SELECT'])) {
                $salesItems[] = [
                    'title' => 'Hoàn tiền',
                    'href' => route('employee.sales.refunds.index'),
                    'isActive' => Route::is('sales.refunds.*'),
                ];
            }
            if ($user->can(Permission::CUSTOMER['SELECT'])) {
                $salesItems[] = [
                    'title' => 'Khách hàng',
                    'href' => route('employee.customers.index'),
                    'isActive' => Route::is('employee.customers.*'),
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
        if ($user->canAny([
            Permission::SHIPMENT['SELECT'],
            Permission::SHIPPING_METHOD['SELECT'],
        ])) {
            $fulfillmentItems = [];

            if ($user->can(Permission::SHIPMENT['SELECT'])) {
                $fulfillmentItems[] = [
                    'title' => 'Vận chuyển',
                    'href' => route('employee.fulfillment.shipments.index'),
                    'isActive' => Route::is('fulfillment.shipments.*'),
                ];
            }

            if ($user->can(Permission::SHIPPING_METHOD['SELECT'])) {
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
        if ($user->can(Permission::STOCK['SELECT'])) {
            $inventoryItems = [];

            if ($user->canAny([Permission::STOCK['SELECT'], Permission::STOCK['UPDATE']])) {
                if ($user->canAny([Permission::VENDOR['SELECT'], Permission::VENDOR['UPDATE']])) {
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
        if ($user->can(Permission::LOOKUP['SELECT'])) {
            $menu[] = [
                'title' => 'Cấu hình',
                'href' => '#',
                'icon' => 'Settings2',
                'isActive' => Route::is('employee.settings.*'),
                'items' => [
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
