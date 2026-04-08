<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, create, show } from '@/routes/employee/inventory/transfers';
import type { BreadcrumbItem } from '@/types';
import type {
    StockTransfer,
    StockTransferFilterData,
    StockTransferPagination,
} from '@/types/stock-transfer';
import { getColumns } from './types/columns';

const props = defineProps<{
    statusOptions: { value: string; label: string; color: string }[];
    locationOptions: { id: string; label: string; type: string }[];
    transfers?: StockTransferPagination;
    filters: StockTransferFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: index().url },
    { title: 'Chuyển kho', href: index().url },
];

function handleView(transfer: StockTransfer) {
    router.get(show(transfer).url);
}

const activeColumns = computed(() => getColumns(handleView));

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref<string | undefined>(
    props.filters.status ?? undefined,
);
const selectedFromLocation = ref<string | undefined>(
    props.filters.from_location_id ?? undefined,
);

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.status ||
        !!props.filters.from_location_id
    );
});

const statusFilterOptions = computed(() =>
    props.statusOptions.map((s) => ({
        label: s.label,
        value: s.value,
    })),
);

const locationFilterOptions = computed(() =>
    props.locationOptions.map((l) => ({
        label: l.label,
        value: l.id,
    })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            status: selectedStatus.value ?? undefined,
            from_location_id: selectedFromLocation.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.transfers,
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
        index().url,
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
    router.get(index().url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);
    const { per_page: _, ...restFilters } = props.filters;
    router.get(index().url, cleanQuery({ ...restFilters, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(index().url, {}, { preserveState: false });
}
</script>

<template>
    <Head title="Chuyển kho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Chuyển kho"
                    description="Quản lý phiếu chuyển kho giữa các vị trí"
                />
                <Button as="a" :href="create().url">
                    <Plus class="mr-2 h-4 w-4" /> Tạo phiếu chuyển kho
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="transfers?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="transfers?.meta.total ?? 0"
                :page-size="transfers?.meta.per_page ?? 15"
                :current-page="transfers?.meta.current_page ?? 1"
                :last-page="transfers?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @row-click="handleView"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        v-model="selectedStatus"
                        title="Trạng thái"
                        :options="statusFilterOptions"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                    <DataTableSingleFilter
                        v-model="selectedFromLocation"
                        title="Từ vị trí"
                        :options="locationFilterOptions"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>
    </AppLayout>
</template>
