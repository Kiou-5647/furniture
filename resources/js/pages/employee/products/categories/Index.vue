<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    LayoutGrid,
    Package,
    Sparkles,
    Lamp,
    CheckCircle2,
    CircleDashed,
} from '@lucide/vue';
import { capitalize, debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, destroy } from '@/routes/employee/products/categories';
import type { BreadcrumbItem } from '@/types';
import type {
    Category,
    CategoryFilterData,
    CategoryPagination,
} from '@/types/category';
import { getColumns } from './types/columns';

const CategoryFormModal = createLazyComponent(
    () => import('./components/CategoryFormModal.vue'),
);

const props = defineProps<{
    categoryGroups: any[];
    roomOptions: any[];
    categories?: CategoryPagination;
    filters: CategoryFilterData;
    currentGroup?: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Danh mục', href: index().url },
];

// Column Logic
const activeColumns = computed(() =>
    getColumns(
        handleEdit,
        confirmDelete,
        handlePreviewImage,
        !props.currentGroup,
    ),
);

// State
const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedCategory = ref<Category | null>(null);
const previewImageUrl = ref<string | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const hasActiveFilters = computed(() => {
    return (
        !!props.filters.product_type ||
        !!props.filters.search ||
        !!props.filters.order_by ||
        (props.filters.is_active !== undefined &&
            props.filters.is_active !== null)
    );
});

const selectedGroup = ref(
    props.categoryGroups.find((n) => n.group_id === props.currentGroup),
);

// Faceted Filter: Product Types
const selectedType = ref(props.filters.product_type ?? null);

const typeOptions = [
    { label: 'Nội thất', value: 'noi-that', icon: LayoutGrid },
    { label: 'Phụ kiện', value: 'phu-kien', icon: Package },
    { label: 'Trang trí', value: 'trang-tri', icon: Sparkles },
    { label: 'Thắp sáng', value: 'thap-sang', icon: Lamp },
];

const statusOptions = [
    { label: 'Đang hiện', value: 'true', icon: CheckCircle2 },
    { label: 'Đang ẩn', value: 'false', icon: CircleDashed },
];

const selectedStatus = ref(props.filters.is_active ?? undefined);

// Filtering Logic (Debounced)
const updateSearch = debounce(() => {
    const { group_id, ...restFilters } = props.filters;

    const rawQuery = {
        ...restFilters,
        search: search.value,
        product_type: selectedType.value ?? undefined,
        is_active: selectedStatus.value ?? undefined,
        page: 1,
    };

    const groupSlug = props.currentGroup?.slug;
    router.get(index(groupSlug).url, cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

// Watchers
watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());
watch(selectedType, () => updateSearch());

watch(
    () => props.categories,
    (newData) => {
        if (newData) {
            setTimeout(() => (isActuallyLoading.value = false), 200);
        }
    },
    { immediate: true },
);

// Actions
function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';
    router.get(
        index(props.currentGroup?.slug).url,
        cleanQuery({
            ...props.filters,
            order_by: column,
            order_direction: direction,
            page: 1,
        }),
        { preserveState: true },
    );
}

function handlePageChange(page: number) {
    router.get(
        index(props.currentGroup?.slug).url,
        cleanQuery({ ...props.filters, page }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);

    const { per_page: _, ...restFilters } = props.filters;

    router.get(
        index(props.currentGroup?.slug).url,
        cleanQuery({
            ...restFilters,
            page: 1,
        }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function resetFilters() {
    router.get(
        index(props.currentGroup?.slug).url,
        {},
        { preserveState: false },
    );
}

function handleCreate() {
    selectedCategory.value = null;
    showFormModal.value = true;
}

function handleEdit(category: Category) {
    selectedCategory.value = category;
    showFormModal.value = true;
}

function confirmDelete(category: Category) {
    selectedCategory.value = category;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedCategory.value) return;
    router.delete(destroy(selectedCategory.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedCategory.value = null;
        },
    });
}

function handlePreviewImage(url: string) {
    previewImageUrl.value = url;
}
</script>

<template>
    <Head title="Danh mục sản phẩm" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    :title="
                        currentGroup
                            ? `Danh mục: ${currentGroup.display_name}`
                            : 'Tất cả danh mục'
                    "
                    description="Quản lý cấu trúc phân loại sản phẩm và Landing Pages"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm danh mục
                </Button>
            </div>

            <div class="grid grid-cols-1 items-start sm:grid-cols-12 sm:gap-3">
                <Card
                    class="col-span-1 hidden space-y-2 sm:col-span-4 sm:block md:col-span-3 xl:col-span-2"
                >
                    <CardHeader>
                        <CardTitle class="text-lg font-medium text-primary"
                            >Nhóm danh mục</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="grid gap-1">
                        <Link
                            :href="index().url"
                            :class="[
                                'group flex items-center justify-between rounded-md px-3 py-2 transition-all',
                                !currentGroup
                                    ? 'bg-primary/10 text-primary shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted',
                            ]"
                        >
                            <span class="text-sm font-medium">Tất cả</span>
                        </Link>

                        <Link
                            v-for="group in categoryGroups"
                            :key="group.id"
                            :href="index(group.slug).url"
                            :class="[
                                'group flex items-center justify-between rounded-md px-3 py-2 transition-all',
                                currentGroup?.id === group.id
                                    ? 'bg-primary/10 text-primary shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted',
                            ]"
                        >
                            <span class="text-sm font-medium">{{
                                group.label
                            }}</span>
                            <Badge variant="secondary" class="text-xs">{{
                                group.count
                            }}</Badge>
                        </Link>
                    </CardContent>
                </Card>

                <div class="sm:hidden">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                class="w-full justify-between"
                            >
                                <span class="font-medium">
                                    Nhóm danh mục:
                                    {{
                                        selectedGroup?.display_name || 'Tất cả'
                                    }}
                                </span>
                                <Badge variant="secondary">
                                    {{ selectedGroup?.count || 0 }}
                                </Badge>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-75">
                            <DropdownMenuItem
                                v-for="group in categoryGroups"
                                :key="group.id"
                                as-child
                            >
                                <Link
                                    :href="index(group.slug).url"
                                    class="flex min-h-12 w-full cursor-pointer items-center justify-between"
                                    preserve-state
                                    preserve-scroll
                                >
                                    <span :class="[capitalize, 'font-medium']">
                                        {{ group.label }}
                                    </span>
                                    <Badge variant="secondary">
                                        {{ group.count }}
                                    </Badge>
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- MAIN TABLE -->
                <div
                    class="col-span-1 space-y-4 sm:col-span-8 md:col-span-9 xl:col-span-10"
                >
                    <DataTableGroup
                        v-model:search="search"
                        :is-actually-loading="isActuallyLoading"
                        :columns="activeColumns"
                        :data="categories?.data ?? []"
                        :has-active-filters="hasActiveFilters"
                        :total="categories?.meta.total ?? 0"
                        :page-size="categories?.meta.per_page ?? 15"
                        :current-page="categories?.meta.current_page ?? 1"
                        :last-page="categories?.meta.last_page ?? 1"
                        :order-by="filters.order_by"
                        :order-direction="filters.order_direction"
                        @reset="resetFilters"
                        @sort="handleSort"
                        @row-click="handleEdit"
                        @update:page="handlePageChange"
                        @update:page-size="handlePageSizeChange"
                    >
                        <template #filters>
                            <DataTableSingleFilter
                                title="Trạng thái"
                                v-model="selectedStatus"
                                :options="statusOptions"
                                icon_location="end"
                            />
                            <DataTableSingleFilter
                                title="Loại sản phẩm"
                                v-model="selectedType"
                                :options="typeOptions"
                                icon_location="end"
                            />
                        </template>
                    </DataTableGroup>
                </div>
            </div>
        </div>

        <!-- Modals & Dialogs -->
        <CategoryFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :category-groups="categoryGroups"
            :room-options="roomOptions"
            :category="selectedCategory"
            @close="showFormModal = false"
            @delete="confirmDelete"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa danh mục"
            :item-name="selectedCategory?.display_name"
            description='Bạn có chắc chắn muốn xóa danh mục "{name}"?'
            @confirm="performDelete"
        />

        <!-- Universal Image Preview Dialog -->
        <ImagePreviewDialog
            :open="!!previewImageUrl"
            :src="previewImageUrl"
            @update:open="previewImageUrl = $event ? previewImageUrl : null"
            @close="previewImageUrl = null"
        />
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.1s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
