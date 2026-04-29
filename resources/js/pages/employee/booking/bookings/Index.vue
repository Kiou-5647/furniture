<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery } from '@/lib/utils';
import { index, show, confirm, cancel } from '@/routes/employee/booking';
import type { BreadcrumbItem } from '@/types';
import type {
    Booking,
    BookingFilterData,
    BookingPagination,
} from '@/types/booking';
import { getColumns } from './types/columns';

const CreateBookingDialog = createLazyComponent(
    () => import('./components/CreateBookingDialog.vue'),
);

const props = defineProps<{
    deposit_percentage: number;
    statusOptions: { value: string; label: string; color: string }[];
    customerOptions: {
        id: string;
        name: string;
        email: string;
        phone?: string;
        address?: {
            province_code: string;
            province_name: string;
            ward_code: string;
            ward_name: string;
            street: string;
        };
    }[];
    designerOptions: {
        value: string;
        label: string;
        hourly_rate: number;
    }[];
    bookings?: BookingPagination;
    filters: BookingFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Đặt lịch', href: index().url },
];

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? undefined);

const hasActiveFilters = computed(
    () => !!props.filters.search || !!props.filters.status,
);

const statusFilterOptions = computed(() =>
    props.statusOptions.map((s) => ({ label: s.label, value: s.value })),
);

const activeColumns = computed(() => getColumns(handleConfirm, handleCancel));

watch(search, (val) => {
    if (val !== (props.filters.search ?? '')) {
        updateSearch();
    }
});

const showCreateDialog = ref(false);

watch(
    () => props.bookings?.data,
    (newData) => {
        if (newData) {
            setTimeout(() => (isActuallyLoading.value = false), 200);
        }
    },
    { immediate: true },
);

function handleCreate() {
    showCreateDialog.value = true;
}

function handleCreated() {
    showCreateDialog.value = false;
    router.reload({ only: ['bookings'] });
}

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
    const { per_page: _, ...restFilters } = props.filters;
    router.get(index().url, cleanQuery({ ...restFilters, page: 1, per_page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(index().url, {}, { preserveState: false });
}

function handleRowClick(booking: Booking) {
    router.get(show({ booking: booking.id }).url);
}

async function handleConfirm(booking: Booking) {
    if (!confirm(`Xác nhận đặt lịch của ${booking.customer.name}?`)) return;
    router.post(
        confirm({ booking: booking.id }).url,
        {},
        { preserveScroll: true },
    );
}

async function handleCancel(booking: Booking) {
    if (!confirm('Hủy đặt lịch này?')) return;
    router.post(
        cancel({ booking: booking.id }).url,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Đặt lịch" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Quản lý đặt lịch"
                    description="Danh sách đặt lịch thiết kế"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm mới
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="bookings?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="bookings?.meta.total ?? 0"
                :page-size="bookings?.meta.per_page ?? 15"
                :current-page="bookings?.meta.current_page ?? 1"
                :last-page="bookings?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @row-click="handleRowClick"
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

            <CreateBookingDialog
                :open="showCreateDialog"
                :deposit_percentage="deposit_percentage"
                :customer-options="customerOptions"
                :designer-options="designerOptions"
                @close="showCreateDialog = false"
                @created="handleCreated"
            />
        </div>
    </AppLayout>
</template>
