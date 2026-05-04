<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, show, deactivate } from '@/routes/employee/customers';
import type { BreadcrumbItem } from '@/types';
import type {
    Customer,
    CustomerFilterData,
    CustomerPagination,
} from '@/types/customer';
import { getColumns } from './types/columns';

const props = defineProps<{
    customers?: CustomerPagination;
    filters: CustomerFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quản lý khách hàng', href: index().url },
    { title: 'Danh sách khách hàng', href: index().url },
];

const activeColumns = computed(() => getColumns(handleView, confirmDeactivate));

const showDeactivateDialog = ref(false);
const selectedCustomer = ref<Customer | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref<boolean | undefined>(
    props.filters.is_active ?? undefined,
);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || props.filters.is_active !== null;
});

const statusFilterOptions = computed(() => [
    { label: 'Hoạt động', value: true },
    { label: 'Ngừng', value: false },
]);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            is_active: selectedStatus.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.customers,
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

function handleView(customer: Customer) {
    router.get(show({ customer: customer.id }).url);
}

function confirmDeactivate(customer: Customer) {
    selectedCustomer.value = customer;
    showDeactivateDialog.value = true;
}

function performDeactivate() {
    if (!selectedCustomer.value) return;
    router.post(deactivate({ customer: selectedCustomer.value.id }).url, {}, {
        onSuccess: () => {
            showDeactivateDialog.value = false;
            selectedCustomer.value = null;
        },
    });
}
</script>

<template>
    <Head title="Khách hàng" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Khách hàng"
                    description="Quản lý thông tin khách hàng và chi tiêu."
                />
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="customers?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="customers?.meta.total ?? 0"
                :page-size="customers?.meta.per_page ?? 15"
                :current-page="customers?.meta.current_page ?? 1"
                :last-page="customers?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
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

        <DeleteConfirmation
            v-model:open="showDeactivateDialog"
            title="Xác nhận vô hiệu hóa tài khoản"
            :item-name="selectedCustomer?.full_name"
            :description="
                selectedCustomer
                    ? `Bạn có chắc chắn muốn vô hiệu hóa tài khoản của khách hàng &quot;${selectedCustomer.full_name}&quot;? Người dùng này sẽ không thể đăng nhập.`
                    : ''
            "
            @confirm="performDeactivate"
        />
    </AppLayout>
</template>
