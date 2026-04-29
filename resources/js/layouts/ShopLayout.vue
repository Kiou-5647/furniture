<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ShoppingCart, Search, Menu, X, LogOut, LayoutGrid } from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import CartDrawer from '@/components/custom/public/CartDrawer.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
import { getInitials } from '@/composables/useInitials';
import { home, login, logout } from '@/routes';
import { dashboard } from '@/routes/employee';
import { useCartStore } from '@/stores/cart';
import { edit } from '@/routes/customer/profile';

const page = usePage();
const auth = computed(() => page.props.auth);
const shopMenu = computed(() => (page.props.shopMenu ?? []) as ShopMenuRoom[]);
const showMobileMenu = ref(false);
const { openDrawer, itemCount } = useCartStore()

interface ShopMenuCategory {
    id: string;
    label: string;
    slug: string;
}

interface ShopMenuGroup {
    id: string | null;
    label: string;
    slug: string;
    categories: ShopMenuCategory[];
}

interface ShopMenuRoom {
    id: string;
    label: string;
    slug: string;
    count: number;
    groups: ShopMenuGroup[];
    image_url: string | null;
}
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Top Bar -->
        <header class="sticky top-0 z-50 border-b bg-white">
            <div class="mx-auto flex h-14 max-w-full items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Logo -->
                <Link :href="home()" class="flex items-center gap-2 shrink-0">
                    <AppLogo />
                </Link>

                <!-- Search Bar (center) -->
                <div class="hidden md:flex flex-1 max-w-md mx-8">
                    <div class="relative w-full">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                        <input type="text" placeholder="Tìm sản phẩm..."
                            class="w-full h-9 pl-9 pr-4 rounded-lg border border-zinc-200 bg-zinc-50 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-900/10 focus:border-zinc-900 transition-all" />
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-1">
                    <!-- Search (mobile) -->
                    <Button variant="ghost" size="icon" class="md:hidden h-9 w-9">
                        <Search class="h-4 w-4 text-zinc-500" />
                    </Button>

                    <!-- Cart -->
                    <Button v-if="auth" variant="ghost" @click="openDrawer" size="icon" class="relative h-9 w-9">
                        <ShoppingCart class="h-4 w-4 text-zinc-500" />
                        <span v-if="itemCount > 0" class="absolute -top-1 -right-1 bg-primary text-primary-foreground text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            {{ itemCount }}
                        </span>
                    </Button>

                    <!-- User Menu -->
                    <DropdownMenu v-if="auth?.user">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-9 w-9 rounded-full">
                                <Avatar class="h-7 w-7">
                                    <AvatarImage v-if="auth.user.avatar_url" :src="auth.user.avatar_url"
                                        :alt="auth.user.name" />
                                    <AvatarFallback class="bg-zinc-200 text-xs font-medium text-zinc-700">
                                        {{ getInitials(auth.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <div class="px-3 py-2">
                                <p class="text-sm font-medium">{{ auth.user.name }}</p>
                                <p class="text-xs text-zinc-500 truncate">{{ auth.user.email }}</p>
                            </div>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem v-if="auth.user.type == 'employee'" as-child>
                                <Link :href="dashboard()" class="cursor-pointer flex items-center gap-2">
                                    <LayoutGrid class="h-4 w-4" />
                                    Bảng điều khiển
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator v-if="auth.user.type == 'employee'" />
                            <DropdownMenuItem v-if="auth.user.type == 'customer'" as-child>
                                <Link :href="edit()" class="cursor-pointer flex items-center gap-2">
                                    <LayoutGrid class="h-4 w-4" />
                                    Hồ sơ cá nhân
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator v-if="auth.user.type == 'customer'" />
                            <DropdownMenuItem as-child>
                                <Link :href="logout().url" method="post" as="button"
                                    class="cursor-pointer flex items-center gap-2 text-red-600">
                                    <LogOut class="h-4 w-4" />
                                    Đăng xuất
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- Guest Login -->
                    <Button v-else variant="ghost" size="sm" as-child class="hidden sm:inline-flex h-9">
                        <Link :href="login()" class="text-xs">Đăng nhập</Link>
                    </Button>
                </div>
            </div>
        </header>

        <!-- Category Navigation -->
        <nav class="hidden lg:block border-b bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <NavigationMenu>
                    <NavigationMenuList class="gap-0">
                        <!-- Products (all) -->
                        <NavigationMenuItem>
                            <Link :href="'/san-pham'"
                                class="group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">
                                Tất cả sản phẩm
                            </Link>
                        </NavigationMenuItem>

                        <!-- Rooms with dropdown -->
                        <NavigationMenuItem v-for="room in shopMenu" :key="room.id">
                            <NavigationMenuTrigger
                                class="text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 data-[state=open]:bg-zinc-50 data-[state=open]:text-zinc-900 px-4">
                                {{ room.label }}
                            </NavigationMenuTrigger>
                            <NavigationMenuContent>
                                <div class="flex w-fit gap-6 p-6 max-h-[50vh]">
                                    <!-- Room Image (Left) -->
                                    <div v-if="room.image_url"
                                        class="h-[40vh] aspect-square shrink-0 overflow-hidden rounded-lg bg-zinc-100">
                                        <img :src="room.image_url" :alt="room.label"
                                            class="h-full w-full object-cover" />
                                    </div>

                                    <!-- Groups (Right) -->
                                    <div class="flex flex-1 gap-8">
                                        <div v-for="group in room.groups" :key="group.id ?? 'other'"
                                            class="min-w-0 flex-1">
                                            <h4 class="text-sm font-semibold text-zinc-900 mb-3">{{ group.label }}</h4>
                                            <ul class="space-y-1.5">
                                                <li v-for="cat in group.categories" :key="cat.id">
                                                    <NavigationMenuLink as-child>
                                                        <Link :href="`/san-pham?danh-muc=${cat.slug}`"
                                                            class="block w-36 rounded-md px-2 py-1 text-sm text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">
                                                            {{ cat.label }}
                                                        </Link>
                                                    </NavigationMenuLink>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </NavigationMenuContent>
                        </NavigationMenuItem>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div v-if="showMobileMenu" class="lg:hidden border-b bg-white">
            <div class="px-4 py-4 space-y-1 max-h-[70vh] overflow-y-auto">
                <Link href="/san-pham"
                    class="block px-3 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50 rounded-lg"
                    @click="showMobileMenu = false">
                    Tất cả sản phẩm
                </Link>
                <div v-for="room in shopMenu" :key="room.id" class="pt-2">
                    <p class="px-3 text-xs font-semibold text-zinc-400 uppercase tracking-wider">{{ room.label }}</p>
                    <div v-for="group in room.groups" :key="group.id ?? 'other'" class="mt-2">
                        <p class="px-3 text-sm font-medium text-zinc-700">{{ group.label }}</p>
                        <Link v-for="cat in group.categories" :key="cat.id" :href="`/san-pham?danh-muc=${cat.slug}`"
                            class="block px-6 py-1.5 text-sm text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 rounded-lg"
                            @click="showMobileMenu = false">
                            {{ cat.label }}
                        </Link>
                    </div>
                </div>
                <div class="pt-4 border-t">
                    <Link v-if="auth?.user.type == 'employee'" :href="dashboard()"
                        class="block px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 rounded-lg"
                        @click="showMobileMenu = false">
                        Bảng điều khiển
                    </Link>
                    <Link v-if="auth?.user.type == 'customer'" :href="edit()"
                        class="block px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 rounded-lg"
                        @click="showMobileMenu = false">
                        Hồ sơ cá nhân
                    </Link>
                    <Link v-if="auth?.user" :href="logout().url" method="post"
                        class="block px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg"
                        @click="showMobileMenu = false">
                        Đăng xuất
                    </Link>
                    <Link v-else :href="'/login'"
                        class="block px-3 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50 rounded-lg"
                        @click="showMobileMenu = false">
                        Đăng nhập
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile Category Bar -->
        <div class="lg:hidden border-b bg-white overflow-x-auto">
            <div class="flex items-center gap-1 px-4 py-2 min-w-max">
                <Link href="/san-pham"
                    class="shrink-0 px-3 py-1.5 text-xs font-medium text-zinc-700 bg-zinc-100 rounded-full">Tất cả
                </Link>
                <template v-for="room in shopMenu" :key="room.id">
                    <button @click="showMobileMenu = !showMobileMenu"
                        class="shrink-0 px-3 py-1.5 text-xs font-medium text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 rounded-full transition-colors">
                        {{ room.label }}
                    </button>
                </template>
                <button class="ml-auto shrink-0 h-8 w-8 flex items-center justify-center"
                    @click="showMobileMenu = !showMobileMenu">
                    <Menu v-if="!showMobileMenu" class="h-4 w-4 text-zinc-500" />
                    <X v-else class="h-4 w-4 text-zinc-500" />
                </button>
            </div>
        </div>

        <!-- Page Content -->
        <main class="@container px-6">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t bg-zinc-50 mt-20">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">Sản phẩm</h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li v-for="room in shopMenu" :key="room.id">
                                <Link :href="`/san-pham?phong=${room.slug}`" class="hover:text-zinc-900">{{ room.label
                                    }}</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">Hỗ trợ</h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li><a href="#" class="hover:text-zinc-900">Liên hệ</a></li>
                            <li><a href="#" class="hover:text-zinc-900">Vận chuyển</a></li>
                            <li><a href="#" class="hover:text-zinc-900">Đổi trả</a></li>
                            <li><a href="#" class="hover:text-zinc-900">FAQ</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">Về chúng tôi</h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li><a href="#" class="hover:text-zinc-900">Giới thiệu</a></li>
                            <li><a href="#" class="hover:text-zinc-900">Tuyển dụng</a></li>
                            <li><a href="#" class="hover:text-zinc-900">Blog</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">Pháp lý</h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li><a href="#" class="hover:text-zinc-900">Điều khoản</a></li>
                            <li><a href="#" class="hover:text-zinc-900">Bảo mật</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 pt-8 border-t border-zinc-200">
                    <p class="text-center text-sm text-zinc-400">
                        &copy; {{ new Date().getFullYear() }} Nội Thất. Mọi quyền được bảo lưu.
                    </p>
                </div>
            </div>
        </footer>
    </div>
    <CartDrawer />
</template>
