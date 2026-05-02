<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ShoppingCart, Search, Menu, X, LogOut, LayoutGrid } from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';
import axios from 'axios';
import { debounce } from 'lodash';
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
import { edit } from '@/routes/customer/profile';
import { search } from '@/routes/discovery/variants';
import { dashboard } from '@/routes/employee';
import { show } from '@/routes/products';
import { useCartStore } from '@/stores/cart';

const page = usePage();
const auth = computed(() => page.props.auth);
const shopMenu = computed(() => (page.props.shopMenu ?? []) as ShopMenuRoom[]);
const showMobileMenu = ref(false);
const { openDrawer, itemCount } = useCartStore();

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

// Define the shape of a search result for TypeScript
interface SearchResult {
    id: string;
    sku: string;
    slug: string;
    full_name: string;
    image: string;
    price: number;
    effective_price: number;
    on_sale: boolean;
}

const searchQuery = ref('');
const searchResults = ref<SearchResult[]>([]);
const totalResults = ref(0);
const isLoading = ref(false);
const showResults = ref(false);
const searchContainer = ref(null);

onClickOutside(searchContainer, () => {
    showResults.value = false;
});

const performSearch = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        totalResults.value = 0;
        showResults.value = false;
        return;
    }

    isLoading.value = true;
    try {
        console.info(searchQuery.value);

        const { data } = await axios.get(search({} as any)?.url, {
            params: {
                q: searchQuery.value,
            },
        });

        searchResults.value = data.results;
        totalResults.value = data.total;
        showResults.value = true;
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
        showResults.value = true;
    } finally {
        isLoading.value = false;
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
    totalResults.value = 0;
    showResults.value = false;
    isLoading.value = false;
};

const debouncedSearch = debounce(performSearch, 1000);

const handleInput = () => {
    debouncedSearch();
};
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Top Bar -->
        <header class="sticky top-0 z-50 border-b bg-white">
            <div
                class="mx-auto flex h-14 max-w-full items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <!-- Logo -->
                <Link :href="home()" class="flex shrink-0 items-center gap-2">
                    <AppLogo />
                </Link>

                <!-- Search Bar (center) -->
                <div
                    ref="searchContainer"
                    class="relative mx-8 hidden max-w-2xl flex-1 md:flex"
                >
                    <div class="relative w-full">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-zinc-400"
                        />
                        <input
                            v-model="searchQuery"
                            @input="handleInput"
                            type="text"
                            placeholder="Tìm sản phẩm..."
                            class="h-9 w-full rounded-lg border border-zinc-200 bg-zinc-50 pr-4 pl-9 text-sm transition-all placeholder:text-zinc-400 focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900/10 focus:outline-none"
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            type="button"
                            class="absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-zinc-400 transition-colors hover:text-zinc-600"
                        >
                            <X class="h-3 w-3" />
                        </button>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div
                        v-if="showResults || isLoading"
                        class="absolute top-full right-0 left-0 z-50 mt-2 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg"
                    >
                        <!-- Loading State -->
                        <div
                            v-if="isLoading"
                            class="p-4 text-center text-sm text-zinc-500"
                        >
                            Đang tìm kiếm...
                        </div>

                        <!-- Results Content -->
                        <div v-else class="p-2">
                            <!-- Case: No Results found -->
                            <div
                                v-if="searchResults.length === 0"
                                class="p-4 text-center text-sm text-zinc-500"
                            >
                                Không có sản phẩm nào phù hợp
                            </div>

                            <!-- Case: Results found -->
                            <template v-else>
                                <Link
                                    v-for="variant in searchResults"
                                    :key="variant.id"
                                    :href="
                                        show({
                                            sku: variant.sku,
                                            variant_slug: variant.slug,
                                        })
                                    "
                                    class="group flex cursor-pointer items-center gap-3 rounded-lg p-2 transition-colors hover:bg-zinc-50"
                                >
                                    <img
                                        :src="variant.image"
                                        class="h-16 w-16 rounded bg-zinc-100 object-cover"
                                    />

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm font-medium text-zinc-900"
                                        >
                                            {{ variant.full_name }}
                                        </p>

                                        <div
                                            class="mt-0.5 flex items-center gap-2"
                                        >
                                            <span
                                                :class="[
                                                    variant.on_sale
                                                        ? 'font-bold text-orange-400'
                                                        : 'text-zinc-900',
                                                    'text-xs',
                                                ]"
                                            >
                                                {{
                                                    new Intl.NumberFormat(
                                                        'vi-VN',
                                                    ).format(
                                                        variant.effective_price,
                                                    )
                                                }}đ
                                            </span>
                                            <span
                                                v-if="variant.on_sale"
                                                class="text-[10px] text-zinc-400 line-through"
                                            >
                                                {{
                                                    new Intl.NumberFormat(
                                                        'vi-VN',
                                                    ).format(variant.price)
                                                }}đ
                                            </span>
                                        </div>
                                    </div>
                                </Link>

                                <Link
                                    v-if="totalResults > 5"
                                    href="/san-pham"
                                    class="mt-1 block border-t border-zinc-100 p-2 text-center text-xs font-medium text-primary hover:underline"
                                >
                                    Xem toàn bộ {{ totalResults }} sản phẩm
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-1">
                    <!-- Search (mobile) -->
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-9 w-9 md:hidden"
                    >
                        <Search class="h-4 w-4 text-zinc-500" />
                    </Button>

                    <!-- Cart -->
                    <Button
                        v-if="auth"
                        variant="ghost"
                        @click="openDrawer"
                        size="icon"
                        class="relative h-9 w-9"
                    >
                        <ShoppingCart class="h-4 w-4 text-zinc-500" />
                        <span
                            v-if="itemCount > 0"
                            class="absolute -top-1 -right-1 rounded-full bg-primary px-1.5 py-0.5 text-[10px] font-bold text-primary-foreground"
                        >
                            {{ itemCount }}
                        </span>
                    </Button>

                    <!-- User Menu -->
                    <DropdownMenu v-if="auth?.user">
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 rounded-full"
                            >
                                <Avatar class="h-7 w-7">
                                    <AvatarImage
                                        v-if="auth.user.avatar_url"
                                        :src="auth.user.avatar_url"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="bg-zinc-200 text-xs font-medium text-zinc-700"
                                    >
                                        {{ getInitials(auth.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <div class="px-3 py-2">
                                <p class="text-sm font-medium">
                                    {{ auth.user.name }}
                                </p>
                                <p class="truncate text-xs text-zinc-500">
                                    {{ auth.user.email }}
                                </p>
                            </div>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                v-if="auth.user.type == 'employee'"
                                as-child
                            >
                                <Link
                                    :href="dashboard()"
                                    class="flex w-full cursor-pointer items-center gap-2"
                                >
                                    <LayoutGrid class="h-4 w-4" />
                                    Bảng điều khiển
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator
                                v-if="auth.user.type == 'employee'"
                            />
                            <DropdownMenuItem
                                v-if="auth.user.type == 'customer'"
                                as-child
                            >
                                <Link
                                    :href="edit()"
                                    class="flex w-full cursor-pointer items-center gap-2"
                                >
                                    <LayoutGrid class="h-4 w-4" />
                                    Hồ sơ cá nhân
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator
                                v-if="auth.user.type == 'customer'"
                            />
                            <DropdownMenuItem as-child>
                                <Link
                                    :href="logout().url"
                                    method="post"
                                    as="button"
                                    class="flex w-full cursor-pointer items-center gap-2 text-red-600"
                                >
                                    <LogOut class="h-4 w-4" />
                                    Đăng xuất
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- Guest Login -->
                    <Button
                        v-else
                        variant="ghost"
                        size="sm"
                        as-child
                        class="hidden h-9 sm:inline-flex"
                    >
                        <Link :href="login()" class="text-xs">Đăng nhập</Link>
                    </Button>
                </div>
            </div>
        </header>

        <!-- Category Navigation -->
        <nav class="hidden border-b bg-white lg:block">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <NavigationMenu>
                    <NavigationMenuList class="gap-0">
                        <!-- Products (all) -->
                        <NavigationMenuItem>
                            <Link
                                :href="'/san-pham'"
                                class="group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 hover:text-zinc-900"
                            >
                                Tất cả sản phẩm
                            </Link>
                        </NavigationMenuItem>

                        <!-- Rooms with dropdown -->
                        <NavigationMenuItem
                            v-for="room in shopMenu"
                            :key="room.id"
                        >
                            <NavigationMenuTrigger
                                class="px-4 text-sm font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 data-[state=open]:bg-zinc-50 data-[state=open]:text-zinc-900"
                            >
                                {{ room.label }}
                            </NavigationMenuTrigger>
                            <NavigationMenuContent>
                                <div class="flex max-h-[50vh] w-fit gap-6 p-6">
                                    <!-- Room Image (Left) -->
                                    <div
                                        v-if="room.image_url"
                                        class="aspect-square h-[40vh] shrink-0 overflow-hidden rounded-lg bg-zinc-100"
                                    >
                                        <img
                                            :src="room.image_url"
                                            :alt="room.label"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>

                                    <!-- Groups (Right) -->
                                    <div class="flex flex-1 gap-8">
                                        <div
                                            v-for="group in room.groups"
                                            :key="group.id ?? 'other'"
                                            class="min-w-0 flex-1"
                                        >
                                            <h4
                                                class="mb-3 text-sm font-semibold text-zinc-900"
                                            >
                                                {{ group.label }}
                                            </h4>
                                            <ul class="space-y-1.5">
                                                <li
                                                    v-for="cat in group.categories"
                                                    :key="cat.id"
                                                >
                                                    <NavigationMenuLink
                                                        as-child
                                                    >
                                                        <Link
                                                            :href="`/san-pham?phong=${room.slug}&danh-muc=${cat.slug}`"
                                                            class="block w-36 rounded-md px-2 py-1 text-sm text-zinc-600 transition-colors hover:bg-zinc-50 hover:text-zinc-900"
                                                        >
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
        <div v-if="showMobileMenu" class="border-b bg-white lg:hidden">
            <div class="max-h-[70vh] space-y-1 overflow-y-auto px-4 py-4">
                <Link
                    href="/san-pham"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50"
                    @click="showMobileMenu = false"
                >
                    Tất cả sản phẩm
                </Link>
                <div v-for="room in shopMenu" :key="room.id" class="pt-2">
                    <p
                        class="px-3 text-xs font-semibold tracking-wider text-zinc-400 uppercase"
                    >
                        {{ room.label }}
                    </p>
                    <div
                        v-for="group in room.groups"
                        :key="group.id ?? 'other'"
                        class="mt-2"
                    >
                        <p class="px-3 text-sm font-medium text-zinc-700">
                            {{ group.label }}
                        </p>
                        <Link
                            v-for="cat in group.categories"
                            :key="cat.id"
                            :href="`/san-pham?danh-muc=${cat.slug}`"
                            class="block rounded-lg px-6 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900"
                            @click="showMobileMenu = false"
                        >
                            {{ cat.label }}
                        </Link>
                    </div>
                </div>
                <div class="border-t pt-4">
                    <Link
                        v-if="auth?.user.type == 'employee'"
                        :href="dashboard()"
                        class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                        @click="showMobileMenu = false"
                    >
                        Bảng điều khiển
                    </Link>
                    <Link
                        v-if="auth?.user.type == 'customer'"
                        :href="edit()"
                        class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                        @click="showMobileMenu = false"
                    >
                        Hồ sơ cá nhân
                    </Link>
                    <Link
                        v-if="auth?.user"
                        :href="logout().url"
                        method="post"
                        class="block rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                        @click="showMobileMenu = false"
                    >
                        Đăng xuất
                    </Link>
                    <Link
                        v-else
                        :href="'/login'"
                        class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-50"
                        @click="showMobileMenu = false"
                    >
                        Đăng nhập
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile Category Bar -->
        <div class="overflow-x-auto border-b bg-white lg:hidden">
            <div class="flex min-w-max items-center gap-1 px-4 py-2">
                <Link
                    href="/san-pham"
                    class="shrink-0 rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700"
                    >Tất cả
                </Link>
                <template v-for="room in shopMenu" :key="room.id">
                    <button
                        @click="showMobileMenu = !showMobileMenu"
                        class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-50 hover:text-zinc-900"
                    >
                        {{ room.label }}
                    </button>
                </template>
                <button
                    class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center"
                    @click="showMobileMenu = !showMobileMenu"
                >
                    <Menu
                        v-if="!showMobileMenu"
                        class="h-4 w-4 text-zinc-500"
                    />
                    <X v-else class="h-4 w-4 text-zinc-500" />
                </button>
            </div>
        </div>

        <!-- Page Content -->
        <main class="@container px-6">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-20 border-t bg-zinc-50">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">
                            Sản phẩm
                        </h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li v-for="room in shopMenu" :key="room.id">
                                <Link
                                    :href="`/san-pham?phong=${room.slug}`"
                                    class="hover:text-zinc-900"
                                    >{{ room.label }}</Link
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">
                            Hỗ trợ
                        </h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Liên hệ</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Vận chuyển</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Đổi trả</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-zinc-900">FAQ</a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">
                            Về chúng tôi
                        </h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Giới thiệu</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Tuyển dụng</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-zinc-900">Blog</a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900">
                            Pháp lý
                        </h3>
                        <ul class="mt-4 space-y-2 text-sm text-zinc-500">
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Điều khoản</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-zinc-900"
                                    >Bảo mật</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 border-t border-zinc-200 pt-8">
                    <p class="text-center text-sm text-zinc-400">
                        &copy; {{ new Date().getFullYear() }} Nội Thất. Mọi
                        quyền được bảo lưu.
                    </p>
                </div>
            </div>
        </footer>
    </div>
    <CartDrawer />
</template>
