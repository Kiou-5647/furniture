<?php

namespace App\Http\Middleware;

use App\Services\Public\ShopMenuService;
use App\Services\Setting\MenuService;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        protected GeneralSettings $settings,
        protected MenuService $menuService,
        protected ShopMenuService $shopMenuService,
    ) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => $this->settings->site_name ?? config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type->value,
                    'avatar_url' => $user->avatar_url,
                    'email_verified_at' => $user->email_verified_at,
                    'permissions' => $user->hasRole('Quản trị viên')
                        ? ['*']
                        : $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'flash' => [
                'success' => fn() => session('success'),
                'error' => fn() => session('error'),
                'info' => fn() => session('info'),
                'warning' => fn() => session('warning'),
            ],
            'settings' => [
                'freeship_threshold' => $this->settings->freeship_threshold,
                'default_warranty' => $this->settings->default_warranty,
            ],
            'menu' => $this->menuService->getMenu($user),
            'shopMenu' => $this->shopMenuService->getMenu(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'query' => (object) $request->query(),
        ];
    }
}
