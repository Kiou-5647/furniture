<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/employee/fulfillment/shipping-methods';
import { restore, forceDestroy } from '@/routes/employee/fulfillment/shipping-methods';
import { index as trashIndex } from '@/routes/employee/fulfillment/shipping-methods/trash';
import type { BreadcrumbItem } from '@/types';
import type {
    ShippingMethod,
    ShippingMethodFilterData,
    ShippingMethodPagination,
} from '@/types/shipping-method';
import { getTrashColumns } from './types/trash-columns';

const props = defineProps<{
    shippingMethods?: ShippingMethodPagination;
    filters: ShippingMethodFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Phương thức vận chuyển', href: index().url },
    { title: 'Thùng rác', href: trashIndex().url },
];

const activeColumns = computed(() =>
    getTrashColumns(handleRestore, handleForceDelete),
);

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const hasActiveFilters = computed(() => !!props.filters.search);

const updateSearch = debounce(() => {
    router.get(
        trashIndex().url,
        cleanQuery({ search: search.value, page: 1 }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.shippingMethods,
    (newData) => {
        if (newData) {
            setTimeout(() => (isActuallyLoading.value = false), 200);
        }
    },
    { immediate: true },
);

function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';
    router.get(
        trashIndex().url,
        cleanQuery({ ...props.filters, order_by: column, order_direction: direction, page: 1 }),
        { preserveState: true },
    );
}

function handlePageChange(page: number) {
    router.get(trashIndex().url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);
    const { per_page: _, ...rest } = props.filters;
    router.get(trashIndex().url, cleanQuery({ ...rest, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(trashIndex().url, {}, { preserveState: false });
}

function handleRestore(method: ShippingMethod) {
    router.post(restore(method).url, {}, {
        preserveScroll: true,
        onSuccess: () => {},
    });
}

function handleForceDelete(method: ShippingMethod) {
    router.delete(forceDestroy(method).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Thùng rác - Phương thức vận chuyển" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <Heading
                title="Thùng rác"
                description="Phương thức vận chuyển đã xóa"
            />

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="shippingMethods?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="shippingMethods?.meta.total ?? 0"
                :page-size="shippingMethods?.meta.per_page ?? 15"
                :current-page="shippingMethods?.meta.current_page ?? 1"
                :last-page="shippingMethods?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            />
        </div>
    </AppLayout>
</template>
