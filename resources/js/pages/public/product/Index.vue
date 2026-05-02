<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Filter } from '@lucide/vue';
import { ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import DataTablePagination from '@/components/custom/data-table/DataTablePagination.vue';
import BundleCard from '@/components/custom/product/BundleCard.vue';
import ProductCard from '@/components/custom/product/ProductCard.vue';
import ProductFilterSidebar from '@/components/public/product/ProductFilterSidebar.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/products';
import type { FilterNamespace, ProductFilters } from '@/types/public/filter';

type Props = {
    cards: {
        data: any[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: ProductFilters;
    filterSummary: FilterNamespace[];
    totalItems: number;
};

const props = defineProps<Props>();

const handleUpdateFilter = (newFilters: ProductFilters) => {
    const { type, limit, min_price, max_price, ...rest } = newFilters;

    const query: Record<string, any> = {
        type: type,
        limit: limit,
        min_price: min_price,
        max_price: max_price,
    };

    Object.assign(query, rest.filters);

    router.get(index().url, cleanQuery(query), {
        preserveScroll: true,
        replace: true,
    });
};

const changePage = (page: number) => {
    router.get(
        index().url,
        { ...props.filters, page },
        { preserveScroll: true, replace: true },
    );
};

const updateLimit = (limit: string | number) => {
    setCookie('per_page', limit, 30);
    router.get(
        index().url,
        { ...props.filters, limit, page: 1 },
        { preserveScroll: true, replace: true },
    );
};

const breadcrumbs = computed(() => {
    const trail: { label: string; slug: string | null; namespace?: string }[] =
        [{ label: 'Trang chủ', slug: '/', namespace: undefined }];

    trail.push({
        label: 'Tất cả sản phẩm',
        slug: '/san-pham',
        namespace: 'all',
    });

    // 1. Collection/Room (bo-suu-tap)
    const collectionSlug = props.filters.filters['phong'];
    if (collectionSlug) {
        const slug = Array.isArray(collectionSlug)
            ? collectionSlug[0]
            : collectionSlug;
        const label =
            props.filterSummary
                .find((ns) => ns.namespace === 'phong')
                ?.options.find((o) => o.slug === slug)?.label || 'Phòng';
        trail.push({ label, slug, namespace: 'phong' });
    }

    // 2. Category (danh-muc)
    const categorySlug = props.filters.filters['danh-muc'];
    if (categorySlug) {
        const slug = Array.isArray(categorySlug)
            ? categorySlug[0]
            : categorySlug;
        const label =
            props.filterSummary
                .find((ns) => ns.namespace === 'danh-muc')
                ?.options.find((o) => o.slug === slug)?.label || 'Danh mục';
        trail.push({ label, slug, namespace: 'danh-muc' });
    }

    return trail;
});

const handleBreadcrumbClick = (crumb: {
    label: string;
    slug: string | null;
    namespace?: string;
}) => {
    if (crumb.slug === '/') {
        router.visit('/');
        return;
    }

    if (crumb.namespace === 'all') {
        router.get(index().url, {}, { replace: true });
        return;
    }

    const query = {
        [crumb.namespace!]: crumb.slug,
    };

    router.get(index().url, query, { replace: true });
};
</script>

<template>
    <ShopLayout>
        <div class="mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex items-baseline justify-between">
                <nav
                    class="mb-1 flex items-center space-x-2 text-sm text-slate-500"
                >
                    <template
                        v-for="(crumb, index) in breadcrumbs"
                        :key="index"
                    >
                        <div class="flex items-center space-x-2">
                            <a
                                @click.prevent="handleBreadcrumbClick(crumb)"
                                class="transition-colors hover:text-slate-900"
                                :class="[
                                    index != breadcrumbs.length - 1
                                        ? 'cursor-pointer'
                                        : 'cursor-default',
                                ]"
                            >
                                {{ crumb.label }}
                            </a>
                            <ChevronRight
                                v-if="index < breadcrumbs.length - 1"
                                class="h-4 w-4 text-slate-300"
                            />
                        </div>
                    </template>
                </nav>
            </div>
            <h1 class="mb-6 text-2xl font-semibold text-slate-900">
                Tất cả sản phẩm
            </h1>

            <div class="mb-6 flex items-center justify-end">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-500">Hiển thị</span>
                    <select
                        :value="filters.limit"
                        @change="
                            updateLimit(
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                        class="cursor-pointer border-none bg-transparent p-0 text-xs font-medium text-slate-600 hover:text-slate-900 focus:ring-0"
                    >
                        <option :value="12">12</option>
                        <option :value="24">24</option>
                        <option :value="48">48</option>
                        <option :value="96">96</option>
                    </select>
                    <span class="text-xs text-slate-500">sản phẩm</span>
                </div>
            </div>

            <div class="flex gap-8">
                <!-- Sidebar -->
                <div class="hidden lg:block">
                    <ProductFilterSidebar
                        :filters="filters"
                        :filterSummary="filterSummary"
                        :totalItems="totalItems"
                        @update-filter="handleUpdateFilter"
                    />
                </div>

                <!-- Main Content (Product Grid) -->
                <div class="@container flex-1 gap-2">
                    <div
                        class="grid grid-cols-1 gap-x-6 gap-y-10 @xl:grid-cols-2 @3xl:grid-cols-3 @4xl:grid-cols-4"
                    >
                        <!-- Render logic for mixed types -->
                        <template v-for="item in cards.data" :key="item.id">
                            <!-- If type is bundle, use BundleCard -->
                            <BundleCard
                                v-if="item.type === 'bundle'"
                                :bundle="item"
                            />

                            <!-- If type is product, use ProductCard -->
                            <ProductCard v-else :product-card="item" />
                        </template>
                    </div>

                    <!-- Pagination UI: Using the project's standard DataTablePagination -->
                    <div v-if="cards.total > cards.per_page" class="mt-12">
                        <DataTablePagination
                            :total="cards.total"
                            :page-size="cards.per_page"
                            :current-page="cards.current_page"
                            :last-page="cards.last_page"
                            @update:page="changePage"
                            @update:pageSize="updateLimit"
                        />
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile Filter FAB and Drawer -->
        <div class="fixed right-6 bottom-6 z-50 lg:hidden">
            <Sheet>
                <SheetTrigger as-child>
                    <Button
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-400 p-0 text-white shadow-xl hover:bg-orange-300"
                    >
                        <Filter class="h-8 w-8" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="left" class="w-fit items-center p-0">
                    <SheetHeader class="w-full border-b p-6">
                        <SheetTitle
                            class="text-lg font-semibold text-slate-900"
                        >
                            Bộ lọc sản phẩm
                        </SheetTitle>
                    </SheetHeader>

                    <div class="h-full w-full overflow-y-auto p-6">
                        <ProductFilterSidebar
                            :filters="filters"
                            :filterSummary="filterSummary"
                            :totalItems="totalItems"
                            @update-filter="handleUpdateFilter"
                        />
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    </ShopLayout>
</template>
