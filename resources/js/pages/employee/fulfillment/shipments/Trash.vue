<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Trash2, Undo2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash';
import { computed, h, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/employee/fulfillment/shipments';
import { restore as restoreRoute, forceDestroy } from '@/routes/employee/fulfillment/shipments/trash';
import type { BreadcrumbItem } from '@/types';
import type { Shipment, ShipmentFilterData, ShipmentPagination } from '@/types/order';

const props = defineProps<{
    shipments?: ShipmentPagination;
    filters: ShipmentFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vận chuyển', href: index().url },
    { title: 'Thùng rác', href: '#' },
];

const search = ref(props.filters.search ?? '');
const isActuallyLoading = ref(true);

const hasActiveFilters = computed(() => !!props.filters.search);

const updateSearch = debounce(() => {
    router.get(
        index().url + 'thung-rac',
        cleanQuery({ search: search.value, page: 1 }),
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
        index().url + 'thung-rac',
        cleanQuery({ ...props.filters, order_by: column, order_direction: direction, page: 1 }),
        { preserveState: true },
    );
}

function handlePageChange(page: number) {
    router.get(index().url + 'thung-rac', cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);
    const { per_page: _, ...rest } = props.filters;
    router.get(index().url + 'thung-rac', cleanQuery({ ...rest, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handleRestore(shipment: Shipment) {
    router.post(restoreRoute(shipment).url, {}, {});
}

function handleForceDelete(shipment: Shipment) {
    router.delete(forceDestroy(shipment).url, {});
}

const columns: ColumnDef<Shipment>[] = [
    {
        accessorKey: 'shipment_number',
        header: 'Mã vận chuyển',
        size: 160,
        enableSorting: true,
        enableHiding: false,
        meta: { align: 'center' },
        cell: ({ row }) => h('span', { class: 'font-mono text-xs' }, row.original.shipment_number),
    },
    {
        accessorKey: 'order',
        header: 'Đơn hàng',
        size: 140,
        enableSorting: false,
        enableHiding: false,
        cell: ({ row }) => h('span', { class: 'text-xs font-mono' }, row.original.order?.order_number ?? '—'),
    },
    {
        accessorKey: 'origin_location',
        header: 'Nơi gửi',
        size: 160,
        enableSorting: false,
        enableHiding: true,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.origin_location?.name ?? '—'),
    },
    {
        accessorKey: 'deleted_at',
        header: 'Ngày xóa',
        size: 140,
        enableSorting: true,
        enableHiding: true,
        meta: { align: 'center' },
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground tabular-nums' }, row.original.deleted_at ?? '—'),
    },
    {
        id: 'actions',
        header: 'Thao tác',
        size: 160,
        enableSorting: false,
        enableHiding: false,
        meta: { align: 'center' },
        cell: ({ row }) => h('div', { class: 'flex items-center justify-center gap-2' }, [
            h(Button, { variant: 'outline', size: 'sm', onClick: () => handleRestore(row.original) }, () => [
                h(Undo2, { class: 'mr-1.5 h-3.5 w-3.5' }),
                'Khôi phục',
            ]),
            h(Button, { variant: 'ghost', size: 'sm', class: 'text-destructive', onClick: () => handleForceDelete(row.original) }, () => [
                h(Trash2, { class: 'mr-1.5 h-3.5 w-3.5' }),
                'Xóa vĩnh viễn',
            ]),
        ]),
    },
];
</script>

<template>
    <Head title="Thùng rác - Vận chuyển" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <Heading
                title="Thùng rác - Vận chuyển"
                description="Các đơn vận chuyển đã bị xóa"
            />

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="columns"
                :data="shipments?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="shipments?.meta.total ?? 0"
                :page-size="shipments?.meta.per_page ?? 15"
                :current-page="shipments?.meta.current_page ?? 1"
                :last-page="shipments?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            />
        </div>
    </AppLayout>
</template>
