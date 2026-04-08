<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/employee/inventory/movements';
import type { BreadcrumbItem } from '@/types';
import type {
    StockMovementFilterData,
    StockMovementPagination,
} from '@/types/stock-movement';
import { getColumns } from './types/columns';

const props = defineProps<{
    typeOptions: {
        value: string;
        label: string;
        color: string;
        isIncoming: boolean;
        isOutgoing: boolean;
    }[];
    locationOptions: { id: string; label: string }[];
    variantOptions?: {
        id: string;
        label: string;
        product_name: string | null;
    }[];
    movements?: StockMovementPagination;
    filters: StockMovementFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: index().url },
    { title: 'Lịch sử tồn kho', href: index().url },
];

const activeColumns = computed(() => getColumns());

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedType = ref<string | undefined>(props.filters.type ?? undefined);
const selectedLocation = ref<string | undefined>(
    props.filters.location_id ?? undefined,
);

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.type ||
        !!props.filters.location_id
    );
});

const typeFilterOptions = computed(() =>
    props.typeOptions.map((t) => ({
        label: t.label,
        value: t.value,
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
            type: selectedType.value ?? undefined,
            location_id: selectedLocation.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.movements,
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
    <Head title="Lịch sử tồn kho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Lịch sử tồn kho"
                    description="Xem toàn bộ lịch sử thay đổi tồn kho"
                />
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="movements?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="movements?.meta.total ?? 0"
                :page-size="movements?.meta.per_page ?? 15"
                :current-page="movements?.meta.current_page ?? 1"
                :last-page="movements?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        v-model="selectedType"
                        title="Loại"
                        :options="typeFilterOptions"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                    <DataTableSingleFilter
                        v-model="selectedLocation"
                        title="Vị trí"
                        :options="locationFilterOptions"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>
    </AppLayout>
</template>
