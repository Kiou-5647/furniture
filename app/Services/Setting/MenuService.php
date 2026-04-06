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
            UserType::Vendor => $this->getVendorMenu($user),
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

        // Product Management Group
        if ($user->canAny(['categories.view', 'collections.view'])) {
            $productItems = [];

            if ($user->can('products.view')) {
                $productItems[] = [
                    'title' => 'Danh sách sản phẩm',
                    'href' => route('employee.products.items.index'),
                    'isActive' => Route::is('employee.products.items.*'),
                ];
            }

            if ($user->can('categories.view')) {
                $productItems[] = [
                    'title' => 'Danh mục',
                    'href' => route('employee.products.categories.index'),
                    'isActive' => Route::is('employee.products.categories.*'),
                ];
            }

            if ($user->can('collections.view')) {
                $productItems[] = [
                    'title' => 'Bộ sưu tập',
                    'href' => route('employee.products.collections.index'),
                    'isActive' => Route::is('employee.products.collections.*'),
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

        // Settings Group
        if ($user->can('lookups.view')) {
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

    private function getVendorMenu(User $user): array
    {
        return [
            [
                'title' => 'Bảng điều khiển',
                'url' => route('dashboard'), // Placeholder
                'icon' => 'LayoutGrid',
                'isActive' => Route::is('dashboard'),
            ],
        ];
    }

    private function getCustomerMenu(User $user): array
    {
        return [
            [
                'title' => 'Bảng điều khiển',
                'url' => route('dashboard'),
                'icon' => 'LayoutGrid',
                'isActive' => Route::is('dashboard'),
            ],
        ];
    }
}
