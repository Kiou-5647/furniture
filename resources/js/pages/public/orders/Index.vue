<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Eye } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import { h } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import CustomerLayout from '@/layouts/settings/CustomerLayout.vue';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatDateTime } from '@/lib/date-utils';
import { cleanQuery, formatPrice } from '@/lib/utils';
import { orders as orderRoute } from '@/routes/customer/profile';
import { show } from '@/routes/customer/profile/orders';
import { initiate } from '@/routes/payment/vnpay';

type Props = {
    orders: any;
    filters: any;
};

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');
const currentStatus = ref(props.filters.status || 'pending');
const isActuallyLoading = ref(false);

const columns: ColumnDef<any, any>[] = [
    {
        accessorKey: 'order_number',
        header: 'Mã đơn hàng',
        size: 100,
        enableSorting: true,
        enableHiding: false,
        meta: {
            align: 'center',
        },
        cell: ({ row }) =>
            h('span', { class: 'font-medium' }, row.getValue('order_number')),
    },
    {
        accessorKey: 'created_at',
        header: 'Ngày đặt',
        size: 100,
        meta: {
            align: 'center',
        },
        cell: ({ row }) =>
            h(
                'span',
                { class: 'font-medium' },
                formatDateTime(row.getValue('created_at')),
            ),
    },
    {
        accessorKey: 'total_amount',
        header: 'Tổng tiền',
        size: 100,
        meta: {
            align: 'center',
        },
        cell: ({ row }) =>
            h(
                'span',
                { class: 'font-bold text-orange-500' },
                formatPrice(row.getValue('total_amount')),
            ),
    },
    {
        accessorKey: 'payment_method',
        header: 'Thanh toán',
        size: 100,
        meta: {
            align: 'center',
        },
        cell: ({ row }) => {
            const method = row.getValue('payment_method') as string;
            const labels: Record<string, string> = {
                cod: 'Thanh toán khi nhận hàng',
                bank_transfer: 'Chuyển khoản',
            };
            return h(
                'span',
                { class: 'text-sm' },
                labels[method] || method || '—',
            );
        },
    },
    {
        accessorKey: 'paid_at',
        header: 'Trạng thái',
        size: 80,
        meta: {
            align: 'center',
        },
        cell: ({ row }) => {
            const paidAt = row.getValue('paid_at');
            const method = row.original.payment_method;
            const invoiceId = row.original.invoices?.[0]?.id;

            // Case 1: Order is already paid
            if (paidAt) {
                return h(
                    Badge,
                    {
                        variant: 'default',
                        class: 'bg-green-500',
                    },
                    () => 'Đã thanh toán',
                );
            }

            // Case 2: Order is unpaid and can be paid via VNPay
            if (method === 'bank_transfer' && invoiceId) {
                return h(
                    'div',
                    { class: 'flex items-center justify-center gap-2' },
                    [
                        h(
                            Badge,
                            {
                                variant: 'outline',
                                class: 'text-muted-foreground',
                            },
                            () => 'Chưa thanh toán',
                        ),
                        h(
                            'a',
                            {
                                href: initiate({ invoice: invoiceId }).url,
                                class: 'inline-block',
                            },
                            [
                                h(
                                    Button,
                                    {
                                        variant: 'default',
                                        size: 'sm',
                                        class: 'h-7 px-2 text-[10px]',
                                    },
                                    () => 'Thanh toán',
                                ),
                            ],
                        ),
                    ],
                );
            }

            // Case 3: Order is unpaid but not via bank transfer (e.g., COD)
            return h(
                Badge,
                {
                    variant: 'outline',
                    class: 'text-muted-foreground',
                },
                () => 'Chưa thanh toán',
            );
        },
    },
    {
        id: 'actions',
        header: 'Chi tiết',
        size: 50,
        meta: {
            align: 'center',
        },
        cell: ({ row }) =>
            h(
                Button,
                {
                    variant: 'outline',
                    size: 'sm',
                    onClick: () =>
                        router.visit(show(row.original.order_number).url),
                },
                () => h(Eye, { class: 'h-4 w-4' }),
            ),
    },
];

const statusOptions = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang xử lý' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' },
];

const updateFilters = debounce(() => {
    router.get(
        orderRoute().url,
        cleanQuery({
            search: search.value,
            status: currentStatus.value,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

function updateTabs() {
    router.get(
        orderRoute().url,
        cleanQuery({
            search: search.value,
            status: currentStatus.value,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}

function resetFilters() {
    search.value = '';
    router.get(
        orderRoute().url,
        { status: currentStatus.value },
        { preserveState: false },
    );
}

watch(search, () => updateFilters());
watch(currentStatus, () => updateTabs());

function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';
    router.get(
        orderRoute().url,
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
    router.get(orderRoute().url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    router.get(
        orderRoute().url,
        cleanQuery({ ...props.filters, per_page, page: 1 }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

const tableData = computed(() => props.orders?.data ?? []);
const totalRecords = computed(() => props.orders?.meta?.total ?? 0);
const lastPage = computed(() => props.orders?.meta?.last_page ?? 1);
const currentPage = computed(() => props.orders?.meta?.current_page ?? 1);
const pageSize = computed(() => props.orders?.meta?.per_page ?? 15);
</script>

<template>
    <ShopLayout>
        <CustomerLayout>
            <Head title="Đơn hàng của tôi" />
            <div class="space-y-6 p-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Đơn hàng của tôi
                    </h1>
                    <p class="text-muted-foreground">
                        Quản lý và theo dõi lịch sử mua hàng của bạn.
                    </p>
                </div>

                <Tabs v-model="currentStatus" class="w-full">
                    <TabsList class="mb-4">
                        <TabsTrigger
                            v-for="opt in statusOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent :value="currentStatus">
                        <DataTableGroup
                            v-model:search="search"
                            :is-actually-loading="isActuallyLoading"
                            :columns="columns"
                            :data="tableData"
                            :has-active-filters="!!props.filters.search"
                            :total="totalRecords"
                            :page-size="pageSize"
                            :current-page="currentPage"
                            :last-page="lastPage"
                            :order-by="props.filters.order_by"
                            :order-direction="props.filters.order_direction"
                            @sort="handleSort"
                            @update:page="handlePageChange"
                            @update:page-size="handlePageSizeChange"
                            @reset="resetFilters"
                        />
                    </TabsContent>
                </Tabs>
            </div>
        </CustomerLayout>
    </ShopLayout>
</template>
