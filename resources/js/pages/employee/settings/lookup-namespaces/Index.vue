<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff, Plus } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/employee/settings/lookupNamespaces';
import { index as lookupsIndex } from '@/routes/employee/settings/lookups';
import type {
    BreadcrumbItem,
    LookupNamespaceFull,
    LookupNamespacePagination,
    LookupNamespaceFilterData,
} from '@/types';
import { getColumns } from './types/columns';

const LookupNamespaceFormModal = createLazyComponent(
    () => import('./components/LookupNamespaceFormModal.vue'),
);

const props = defineProps<{
    namespaces?: LookupNamespacePagination;
    filters: LookupNamespaceFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tra cứu', href: lookupsIndex().url },
    { title: 'Danh mục Tra cứu', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedNamespace = ref<LookupNamespaceFull | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.order_by ||
        (props.filters.is_active !== undefined &&
            props.filters.is_active !== null) ||
        !!props.filters.for_variants
    );
});

const selectedStatus = ref(props.filters.is_active ?? undefined);
const selectedVariantFilter = ref(props.filters.for_variants ?? undefined);

const statusOptions = [
    { label: 'Đang hiện', value: true, icon: Eye },
    { label: 'Đang ẩn', value: false, icon: EyeOff },
];

const variantOptions = [
    { label: 'Có biến thể', value: true },
    { label: 'Không biến thể', value: false },
];

const updateSearch = debounce(() => {
    const rawQuery = {
        search: search.value,
        is_active: selectedStatus.value,
        for_variants: selectedVariantFilter.value,
        order_by: props.filters.order_by,
        order_direction: props.filters.order_direction,
        page: 1,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

let loadingTimeout: any = null;

watch(search, (newValue) => {
    if (newValue === (props.filters.search ?? '')) return;
    updateSearch();
});

watch(selectedStatus, () => {
    updateSearch();
});

watch(selectedVariantFilter, () => {
    updateSearch();
});

watch(
    () => props.filters.search,
    (newSearch) => {
        search.value = newSearch ?? '';
    },
);

watch(
    () => [props.filters],
    () => {
        isActuallyLoading.value = true;
    },
);

watch(
    () => props.namespaces,
    (newData) => {
        if (newData) {
            if (loadingTimeout) clearTimeout(loadingTimeout);
            loadingTimeout = setTimeout(() => {
                isActuallyLoading.value = false;
            }, 200);
        }
    },
    { immediate: true },
);

function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';

    const rawQuery = {
        search: props.filters.search,
        is_active: selectedStatus.value,
        for_variants: selectedVariantFilter.value,
        order_by: column,
        order_direction: direction,
        page: 1,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
    });
}

function resetFilters() {
    updateSearch.cancel();

    router.get(
        index().url,
        {},
        {
            preserveState: false,
        },
    );
}

function handleCreate() {
    selectedNamespace.value = null;
    showFormModal.value = true;
}

function handleEdit(ns: LookupNamespaceFull) {
    selectedNamespace.value = ns;
    showFormModal.value = true;
}

function confirmDelete(ns: LookupNamespaceFull) {
    selectedNamespace.value = ns;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedNamespace.value) return;

    router.delete(
        `/nhan-vien/cau-hinh/danh-muc-tra-cuu/${selectedNamespace.value.id}`,
        {
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedNamespace.value = null;
            },
        },
    );
}

function handlePageChange(page: number) {
    const rawQuery = {
        search: props.filters.search,
        is_active: selectedStatus.value,
        for_variants: selectedVariantFilter.value,
        page,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);

    const rawQuery = {
        search: props.filters.search,
        is_active: selectedStatus.value,
        for_variants: selectedVariantFilter.value,
        page: 1,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Danh mục Tra cứu" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div
                class="space-y-4 md:flex md:items-center md:justify-between md:space-y-0"
            >
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-8 w-8 hidden sm:flex"
                        @click="router.visit(lookupsIndex().url)"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <Heading
                        title="Quản lý danh mục tra cứu"
                        description="Định nghĩa các nhóm dữ liệu dùng cho tra cứu và biến thể sản phẩm"
                    />
                </div>
                <div class="flex items-center justify-between gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-8 flex sm:hidden"
                        @click="router.visit(lookupsIndex().url)"
                    >
                        <ArrowLeft class="h-4 mr-1" /> Quay lại
                    </Button>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" /> Thêm danh mục
                    </Button>
                </div>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="namespaces?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="namespaces?.meta.total ?? 0"
                :page-size="namespaces?.meta.per_page ?? 15"
                :current-page="namespaces?.meta.current_page ?? 1"
                :last-page="namespaces?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @row-click="handleEdit"
                @update:page="handlePageChange"
                @update:pageSize="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        title="Trạng thái"
                        v-model="selectedStatus"
                        :options="statusOptions"
                        :searchable="false"
                    />
                    <DataTableSingleFilter
                        title="Biến thể"
                        v-model="selectedVariantFilter"
                        :options="variantOptions"
                        :searchable="false"
                    />
                </template>
            </DataTableGroup>
        </div>

        <LookupNamespaceFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :namespace="selectedNamespace"
            @close="showFormModal = false"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa"
            :item-name="selectedNamespace?.display_name"
            description='Bạn có chắc chắn muốn xóa danh mục "{name}"? Các tra cứu thuộc danh mục này sẽ trở thành không có danh mục.'
            @confirm="performDelete"
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
