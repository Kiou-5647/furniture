<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib';
import {
    index,
    ship,
    deliver,
    resend,
    show,
    cancel,
    destroy,
} from '@/routes/employee/shipments';
import type { BreadcrumbItem } from '@/types';
import type {
    Shipment,
    ShipmentFilterData,
    ShipmentPagination,
} from '@/types/order';
import { getColumns } from './types/columns';

const props = defineProps<{
    statusOptions: { value: string; label: string; color: string }[];
    shipments?: ShipmentPagination;
    filters: ShipmentFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vận chuyển', href: index().url },
];

const activeColumns = computed(() =>
    getColumns(
        handleShow,
        handleShip,
        handleDeliver,
        handleResend,
        handleCancel,
        confirmDelete,
    ),
);

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref<string | undefined>(
    props.filters.status ?? undefined,
);

const showDeleteDialog = ref(false);
const selectedShipment = ref<Shipment | null>(null);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || !!props.filters.status;
});

const statusFilterOptions = computed(() =>
    props.statusOptions.map((s) => ({
        label: s.label,
        value: s.value,
    })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            status: selectedStatus.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.shipments,
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
    const { per_page: _, ...rest } = props.filters;
    router.get(index().url, cleanQuery({ ...rest, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(index().url, {}, { preserveState: false });
}

function handleShow(shipment: Shipment) {
    router.visit(show(shipment).url);
}

function handleShip(shipment: Shipment) {
    router.post(ship(shipment).url, {}, { preserveScroll: true });
}

function handleDeliver(shipment: Shipment) {
    router.post(deliver(shipment).url, {}, { preserveScroll: true });
}

function handleResend(shipment: Shipment) {
    router.post(resend(shipment).url, {}, { preserveScroll: true });
}

async function handleCancel(shipment: Shipment) {
    if (!confirm('Hủy đặt lịch này?')) return;
    router.post(
        cancel({ shipment: shipment.id }).url,
        {},
        { preserveScroll: true },
    );
}

function confirmDelete(shipment: Shipment) {
    selectedShipment.value = shipment;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedShipment.value) return;
    router.delete(destroy(selectedShipment.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedShipment.value = null;
        },
    });
}
</script>

<template>
    <Head title="Vận chuyển" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Vận chuyển"
                    description="Quản lý đơn vận chuyển hàng hóa"
                />
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="shipments?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="shipments?.meta.total ?? 0"
                :page-size="shipments?.meta.per_page ?? 15"
                :current-page="shipments?.meta.current_page ?? 1"
                :last-page="shipments?.meta.last_page ?? 1"
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
                </template>
            </DataTableGroup>

            <DeleteConfirmation
                v-model:open="showDeleteDialog"
                title="Xác nhận xóa đơn vận chuyển"
                :item-name="selectedShipment?.shipment_number"
                :description="
                    selectedShipment
                        ? `Bạn có chắc chắn muốn xóa đơn vận chuyển &quot;${selectedShipment.shipment_number}&quot;?`
                        : ''
                "
                @confirm="performDelete"
            />
        </div>
    </AppLayout>
</template>
