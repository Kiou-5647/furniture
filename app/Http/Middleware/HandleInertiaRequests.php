<?php

namespace App\Http\Middleware;

use App\Services\Public\ShopMenuService;
use App\Services\Setting\MenuService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
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
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type->value,
                    'avatar_url' => $user->avatar_url,
                    'permissions' => $user->hasRole('super_admin')
                        ? ['*']
                        : $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'menu' => $this->menuService->getMenu($user),
            'shopMenu' => $this->shopMenuService->getMenu(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'query' => (object) $request->query(),
        ];
    }
}
