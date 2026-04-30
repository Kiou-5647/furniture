<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
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
import { index, destroy, cancel, complete, show, catalog as catalogRoute } from '@/routes/employee/sales/orders';
import type { BreadcrumbItem } from '@/types';
import type {
    Order,
    OrderFilterData,
    OrderPagination,
} from '@/types/order';
import { getColumns } from './types/columns';

const CreateOrderModal = createLazyComponent(
    () => import('./components/CreateOrderModal.vue'),
);

const props = defineProps<{
    statusOptions: { value: string; label: string; color: string }[];
    paymentMethodOptions: { value: string; label: string }[];
    customerOptions: { id: string; name: string; email: string }[];
    sourceOptions: { value: string; label: string }[];
    storeLocationOptions: { id: string; label: string }[];
    employeeLocationId?: string;
    employeeLocationName?: string;
    orders?: OrderPagination;
    filters: OrderFilterData;
}>();

const catalogItems = ref<any[]>([]);
const bundleContents = ref<Record<string, any[]>>({});
const catalogLoaded = ref(false);

const catalogCustomerOptions = ref<any[]>([]);
const shippingMethods = ref<any[]>([]);

async function loadCatalog() {
    if (catalogLoaded.value) return;
    try {
        const res = await fetch(catalogRoute().url);
        const json = await res.json();
        catalogCustomerOptions.value = json.customerOptions ?? [];
        catalogItems.value = json.catalogItems ?? [];
        bundleContents.value = json.bundleContents ?? {};
        shippingMethods.value = json.shippingMethods ?? [];
        catalogLoaded.value = true;
    } catch (e) {
        console.error('Failed to load catalog', e);
    }
}

function handleCreate() {
    selectedOrder.value = null;
    loadCatalog();
    showFormModal.value = true;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Đơn hàng', href: index().url },
];

const activeColumns = computed(() => getColumns(handleShow, handleCancel, handleComplete, confirmDelete));

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedOrder = ref<Order | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref<string | undefined>(props.filters.status ?? undefined);
const selectedCustomer = ref<string | undefined>(props.filters.customer_id ?? undefined);
const selectedSource = ref<string | undefined>(props.filters.source ?? undefined);
const selectedStoreLocation = ref<string | undefined>(props.filters.store_location_id ?? undefined);

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.status ||
        !!props.filters.customer_id ||
        !!props.filters.source ||
        !!props.filters.store_location_id
    );
});

const statusFilterOptions = computed(() =>
    props.statusOptions.map((s) => ({
        label: s.label,
        value: s.value,
    })),
);

const customerFilterOptions = computed(() =>
    props.customerOptions.map((c) => ({
        label: c.name,
        value: c.id,
        id: c.id,
    })),
);

const sourceFilterOptions = computed(() =>
    props.sourceOptions.map((s) => ({
        label: s.label,
        value: s.value,
    })),
);

const storeLocationFilterOptions = computed(() =>
    props.storeLocationOptions.map((s) => ({
        label: s.label,
        value: s.id,
    })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            status: selectedStatus.value ?? undefined,
            customer_id: selectedCustomer.value ?? undefined,
            source: selectedSource.value ?? undefined,
            store_location_id: selectedStoreLocation.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.orders,
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

function handleShow(order: Order) {
    router.visit(show(order).url);
}

function handleCancel(order: Order) {
    selectedOrder.value = order;
    router.post(cancel(selectedOrder.value).url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            selectedOrder.value = null;
        },
    });
}

function handleComplete(order: Order) {
    selectedOrder.value = order;
    router.post(complete(selectedOrder.value).url, {}, {
        preserveScroll: true,
        onSuccess: () => {
            selectedOrder.value = null;
        },
    });
}

function confirmDelete(order: Order) {
    selectedOrder.value = order;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedOrder.value) return;
    router.delete(destroy(selectedOrder.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedOrder.value = null;
        },
    });
}
</script>

<template>
    <Head title="Đơn hàng" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Đơn hàng"
                    description="Quản lý đơn hàng và trạng thái giao dịch"
                />
                <div class="flex items-center gap-2">
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tạo đơn hàng
                    </Button>
                </div>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="orders?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="orders?.meta.total ?? 0"
                :page-size="orders?.meta.per_page ?? 15"
                :current-page="orders?.meta.current_page ?? 1"
                :last-page="orders?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @row-click="handleShow"
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
                    <DataTableSingleFilter
                        v-model="selectedSource"
                        title="Nguồn"
                        :options="sourceFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                    <DataTableSingleFilter
                        v-model="selectedStoreLocation"
                        title="Cửa hàng"
                        :options="storeLocationFilterOptions"
                        icon_location="end"
                        :searchable="true"
                        @update:model-value="updateSearch"
                    />
                    <DataTableSingleFilter
                        v-model="selectedCustomer"
                        title="Khách hàng"
                        :options="customerFilterOptions"
                        icon_location="end"
                        :searchable="true"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <CreateOrderModal
            v-if="showFormModal"
            :open="showFormModal"
            :customer-options="catalogCustomerOptions"
            :payment-method-options="paymentMethodOptions"
            :store-location-id="employeeLocationId ?? null"
            :catalog-items="catalogItems"
            :bundle-contents="bundleContents"
            :shipping-methods="shippingMethods"
            @close="showFormModal = false"
            @refresh="router.reload({ only: ['orders'] })"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa đơn hàng"
            :item-name="selectedOrder?.order_number"
            :description="
                selectedOrder
                    ? `Bạn có chắc chắn muốn xóa đơn hàng &quot;${selectedOrder.order_number}&quot;?`
                    : ''
            "
            @confirm="performDelete"
        />
    </AppLayout>
</template>
