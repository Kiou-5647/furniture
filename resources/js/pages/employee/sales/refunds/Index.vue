<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Eye } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, show, approve, reject } from '@/routes/employee/sales/refunds';
import type { BreadcrumbItem } from '@/types';
import type {
    Refund,
    RefundFilterData,
    RefundPagination,
} from '@/types/refund';
import { getColumns } from './types/columns';

const props = defineProps<{
    refunds?: RefundPagination;
    filters: RefundFilterData;
    statusOptions: { value: string; label: string; color: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Hoàn tiền', href: index().url },
];

const activeColumns = computed(() => getColumns(handleView, handleApprove, handleReject));

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? undefined);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || !!props.filters.status;
});

const statusFilterOptions = computed(() =>
    props.statusOptions.map((s) => ({ label: s.label, value: s.value })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            status: selectedStatus.value,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.refunds,
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
        cleanQuery({ ...props.filters, order_by: column, order_direction: direction, page: 1 }),
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
    const { per_page: _, ...rest } = props.filters;
    router.get(index().url, cleanQuery({ ...rest, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(index().url, {}, { preserveState: false });
}

function handleView(refund: Refund) {
    router.get(show({ refund: refund.id }).url);
}

function handleApprove(refund: Refund) {
    router.post(approve({ refund: refund.id }).url, {}, {
        preserveScroll: true,
    });
}

function handleReject(refund: Refund) {
    router.post(reject({ refund: refund.id }).url, {
        notes: '',
    }, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Hoàn tiền" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Yêu cầu hoàn tiền"
                    description="Quản lý các yêu cầu hoàn tiền cho đơn hàng và đặt lịch"
                />
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="refunds?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="refunds?.meta.total ?? 0"
                :page-size="refunds?.meta.per_page ?? 15"
                :current-page="refunds?.meta.current_page ?? 1"
                :last-page="refunds?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @row-click="handleView"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        v-model="selectedStatus"
                        title="Trạng thái"
                        :options="statusFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>
    </AppLayout>
</template>
