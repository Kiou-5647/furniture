<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Trash2, Undo2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash';
import { computed, h, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index } from '@/routes/employee/sales/orders';
import { restore as restoreRoute } from '@/routes/employee/sales/orders/trash';
import type { BreadcrumbItem } from '@/types';
import type { Order, OrderFilterData, OrderPagination } from '@/types/order';

const props = defineProps<{
    orders?: OrderPagination;
    filters: OrderFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Đơn hàng', href: index().url },
    { title: 'Thùng rác', href: '#' },
];

const search = ref(props.filters.search ?? '');
const isActuallyLoading = ref(true);

const hasActiveFilters = computed(() => !!props.filters.search);

const updateSearch = debounce(() => {
    router.get(
        index().url + 'thung-rac',
        cleanQuery({
            search: search.value,
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
        index().url + 'thung-rac',
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
    router.get(index().url + 'thung-rac', cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);
    const { per_page: _, ...restFilters } = props.filters;
    router.get(index().url + 'thung-rac', cleanQuery({ ...restFilters, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handleRestore(order: Order) {
    router.post(restoreRoute(order).url, {}, {
        onSuccess: () => {},
    });
}

function handleForceDelete(order: Order) {
    router.delete(`/nhan-vien/ban-hang/don-hang/thung-rac/${order.id}/force`, {
        onSuccess: () => {},
    });
}

const columns: ColumnDef<Order>[] = [
    {
        accessorKey: 'order_number',
        header: 'Mã đơn hàng',
        size: 160,
        enableSorting: true,
        enableHiding: false,
        meta: { align: 'center' },
        cell: ({ row }) => h('span', { class: 'font-mono text-xs' }, row.original.order_number),
    },
    {
        accessorKey: 'customer',
        header: 'Khách hàng',
        size: 200,
        enableSorting: false,
        enableHiding: false,
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.customer?.name ?? '—'),
    },
    {
        accessorKey: 'total_amount',
        header: 'Tổng tiền',
        size: 140,
        enableSorting: false,
        enableHiding: true,
        meta: { align: 'right' },
        cell: ({ row }) => h('span', { class: 'text-sm tabular-nums' }, `${Number(row.original.total_amount).toLocaleString('vi-VN')}đ`),
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
    <Head title="Thùng rác - Đơn hàng" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <Heading
                title="Thùng rác - Đơn hàng"
                description="Các đơn hàng đã bị xóa"
            />

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="columns"
                :data="orders?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="orders?.meta.total ?? 0"
                :page-size="orders?.meta.per_page ?? 15"
                :current-page="orders?.meta.current_page ?? 1"
                :last-page="orders?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            />
        </div>
    </AppLayout>
</template>
