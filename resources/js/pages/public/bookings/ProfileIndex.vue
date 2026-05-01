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
import { bookings as bookingsRoute } from '@/routes/customer/profile';
import { show } from '@/routes/customer/profile/bookings';
import { initiate } from '@/routes/payment/vnpay';

type Props = {
    bookings: any;
    filters: any;
    deposit_percentage: number;
};

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');
const currentStatus = ref(props.filters.status || 'pending_deposit');
const isActuallyLoading = ref(false);

const columns: ColumnDef<any, any>[] = [
    {
        accessorKey: 'booking_number',
        header: 'Mã đặt lịch',
        size: 120,
        meta: { align: 'center' },
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('booking_number')),
    },
    {
        accessorKey: 'designer_name',
        header: 'Nhà thiết kế',
        size: 150,
        meta: { align: 'center' },
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('designer_name')),
    },
    {
        accessorKey: 'start_at',
        header: 'Thời gian',
        size: 150,
        meta: { align: 'center' },
        cell: ({ row }) => h('span', { class: 'font-medium' }, formatDateTime(row.getValue('start_at'))),
    },
    {
        accessorKey: 'total_price',
        header: 'Tổng giá trị',
        size: 120,
        meta: { align: 'center' },
        cell: ({ row }) => h(
            'span',
            { class: 'font-bold text-orange-500' },
            formatPrice(row.getValue('total_price'))
        ),
    },
    {
        accessorKey: 'status',
        header: 'Trạng thái',
        size: 250, // Increased width for the checkup text
        meta: { align: 'center' },
        cell: ({ row }) => {
            const status = row.getValue('status');
            const booking = row.original;

            // 1. Deposit Phase: Pending Deposit
            if (status === 'pending_deposit' && !booking.is_deposit_paid) {
                return h('div', { class: 'flex flex-col items-center gap-1' }, [
                    h('div', { class: 'flex items-center gap-2' }, [
                        h(Badge, { variant: 'outline' }, () => 'Chờ đặt cọc'),
                        h('a', {
                            href: initiate({ invoice: booking.deposit_invoice_id }).url,
                            class: 'inline-block'
                        }, [
                            h(Button, { variant: 'default', size: 'sm', class: 'h-7 px-2 text-xs' }, () => 'Thanh toán cọc')
                        ])
                    ]),
                    h('span', { class: 'text-[10px] text-muted-foreground' },
                        `Cần cọc: ${formatPrice(booking.total_price * props.deposit_percentage / 100)}`
                    )
                ]);
            }

            // 2. Final Phase: Confirmed but not fully paid
            if (status === 'confirmed' && !booking.is_final_paid) {
                return h('div', { class: 'flex flex-col items-center gap-1' }, [
                    h('div', { class: 'flex items-center gap-2' }, [
                        h(Badge, { variant: 'default', class: 'bg-blue-500' }, () => 'Đã xác nhận'),
                        h('a', {
                            href: initiate({ invoice: booking.final_invoice_id }).url,
                            class: 'inline-block'
                        }, [
                            h(Button, { variant: 'default', size: 'sm', class: 'h-7 px-2 text-[10px]' }, () => 'Thanh toán nốt')
                        ])
                    ]),
                    h('span', { class: 'text-[10px] text-muted-foreground' },
                        `Còn lại: ${formatPrice(booking.total_price)}`
                    )
                ]);
            }

            // 3. Default state (Paid, Completed, Cancelled, etc.)
            return h(Badge, { variant: 'outline' }, () => booking.status_label);
        },
    },
    {
        id: 'actions',
        header: 'Chi tiết',
        size: 50,
        meta: { align: 'center' },
        cell: ({ row }) => h(
            Button,
            {
                variant: 'outline',
                size: 'sm',
                onClick: () => router.visit(show(row.original.booking_number))
            },
            () => h(Eye, { class: 'h-4 w-4' })
        ),
    },
];

const statusOptions = [
    { value: 'pending_deposit', label: 'Chờ đặt cọc' },
    { value: 'pending_confirmation', label: 'Chờ xác nhận' },
    { value: 'confirmed', label: 'Đã xác nhận' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' },
];

const updateFilters = debounce(() => {
    router.get(
        bookingsRoute().url,
        cleanQuery({
            search: search.value,
            status: currentStatus.value,
            page: 1,
        }),
        { preserveState: true, replace: true }
    );
}, 500);

function updateTabs() {
    router.get(
        bookingsRoute().url,
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
    router.get(bookingsRoute().url, { status: currentStatus.value }, { preserveState: false });
}

watch(search, () => updateFilters());
watch(currentStatus, () => updateTabs());

function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';
    router.get(
        bookingsRoute().url,
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
    router.get(bookingsRoute().url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    router.get(
        bookingsRoute().url,
        cleanQuery({ ...props.filters, per_page, page: 1 }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

const tableData = computed(() => props.bookings?.data ?? []);
const totalRecords = computed(() => props.bookings?.meta?.total ?? 0);
const lastPage = computed(() => props.bookings?.meta?.last_page ?? 1);
const currentPage = computed(() => props.bookings?.meta?.current_page ?? 1);
const pageSize = computed(() => props.bookings?.meta?.per_page ?? 15);
</script>

<template>
    <ShopLayout>
        <CustomerLayout>
            <Head title="Lịch đặt thiết kế của tôi" />
            <div class="space-y-6 p-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Lịch đặt thiết kế của tôi
                    </h1>
                    <p class="text-muted-foreground">
                        Theo dõi và quản lý các buổi tư vấn thiết kế của bạn.
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
