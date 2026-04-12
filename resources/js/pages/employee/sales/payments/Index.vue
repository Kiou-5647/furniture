<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/employee/sales/payments';
import type { BreadcrumbItem } from '@/types';
import type {
    PaymentFilterData,
    PaymentPagination,
} from '@/types/payment';
import { getColumns } from './types/columns';

const props = defineProps<{
    gatewayOptions: { id: string; label: string }[];
    customerOptions?: { id: string; label: string }[];
    payments?: PaymentPagination;
    filters: PaymentFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Thanh toán', href: index().url },
];

const activeColumns = computed(() => getColumns());

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedCustomer = ref<string | undefined>(props.filters.customer_id ?? undefined);
const selectedGateway = ref<string | undefined>(props.filters.gateway ?? undefined);

const customerFilterOptions = computed(() =>
    (props.customerOptions ?? []).map((c) => ({
        label: c.label,
        value: c.id,
        id: c.id,
    })),
);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || !!props.filters.customer_id || !!props.filters.gateway;
});

const gatewayFilterOptions = computed(() =>
    props.gatewayOptions.map((g) => ({
        label: g.label,
        value: g.id,
        id: g.id,
    })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            customer_id: selectedCustomer.value,
            gateway: selectedGateway.value,
            page: 1,
            order_by: props.filters.order_by,
            order_direction: props.filters.order_direction,
        }),
        { preserveState: true },
    );
}, 300);

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
    search.value = '';
    selectedCustomer.value = undefined;
    selectedGateway.value = undefined;
    router.get(index().url, {}, { preserveState: false });
}

watch(
    () => props.payments,
    (newData) => {
        if (newData) {
            setTimeout(() => (isActuallyLoading.value = false), 200);
        }
    },
    { immediate: true },
);

watch(search, () => {
    updateSearch();
});
</script>

<template>
    <Head title="Thanh toán" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <Heading
                title="Thanh toán"
                description="Nhật ký thanh toán"
            />

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="payments?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="payments?.meta.total ?? 0"
                :page-size="payments?.meta.per_page ?? 15"
                :current-page="payments?.meta.current_page ?? 1"
                :last-page="payments?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        v-model="selectedCustomer"
                        title="Khách hàng"
                        :options="customerFilterOptions"
                        icon_location="end"
                        :searchable="true"
                        @update:model-value="updateSearch"
                    />
                    <DataTableSingleFilter
                        v-model="selectedGateway"
                        title="Cổng thanh toán"
                        :options="gatewayFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>
    </AppLayout>
</template>
