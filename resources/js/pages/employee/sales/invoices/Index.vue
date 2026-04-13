<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, show } from '@/routes/employee/sales/invoices';
import { index as trashIndex } from '@/routes/employee/sales/invoices/trash';
import type { BreadcrumbItem } from '@/types';
import type {
    Invoice,
    InvoiceFilterData,
    InvoicePagination,
} from '@/types/invoice';
import CreateInvoiceDialog from './components/CreateInvoiceDialog.vue';
import { getColumns } from './types/columns';

const props = defineProps<{
    statusOptions: { value: string; label: string; color: string }[];
    typeOptions: { value: string; label: string; color: string }[];
    currentEmployeeId: string | null;
    orderOptions: {
        id: string;
        order_number: string;
        total_amount: string;
        status: string;
        customer_name: string;
    }[];
    // bookingOptions: { ... }[]; // TODO: uncomment when Booking model exists
    invoices?: InvoicePagination;
    filters: InvoiceFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Hóa đơn', href: index().url },
];

const activeColumns = computed(() => getColumns(handleShow));

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref<string | undefined>(props.filters.status ?? undefined);
const selectedType = ref<string | undefined>(props.filters.type ?? undefined);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || !!props.filters.status || !!props.filters.type;
});

const statusFilterOptions = computed(() =>
    props.statusOptions.map((s) => ({
        label: s.label,
        value: s.value,
    })),
);

const typeFilterOptions = computed(() =>
    props.typeOptions.map((t) => ({
        label: t.label,
        value: t.value,
    })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            status: selectedStatus.value ?? undefined,
            type: selectedType.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.invoices,
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

function handleShow(invoice: Invoice) {
    router.visit(show(invoice).url);
}

const showCreateDialog = ref(false);

function handleCreate() {
    showCreateDialog.value = true;
}

function handleDialogClose() {
    showCreateDialog.value = false;
}
</script>

<template>
    <Head title="Hóa đơn" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Hóa đơn"
                    description="Quản lý hóa đơn bán hàng"
                />
                <div class="flex items-center gap-2">
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tạo hóa đơn
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="trashIndex().url">
                            <Trash2 class="mr-2 h-4 w-4" /> Thùng rác
                        </Link>
                    </Button>
                </div>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="invoices?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="invoices?.meta.total ?? 0"
                :page-size="invoices?.meta.per_page ?? 15"
                :current-page="invoices?.meta.current_page ?? 1"
                :last-page="invoices?.meta.last_page ?? 1"
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
                        v-model="selectedType"
                        title="Loại hóa đơn"
                        :options="typeFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <CreateInvoiceDialog
            :open="showCreateDialog"
            :order-options="orderOptions"
            :booking-options="[]"
            :current-employee-id="currentEmployeeId"
            @close="handleDialogClose"
        />
    </AppLayout>
</template>
