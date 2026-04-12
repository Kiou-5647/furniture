<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Trash2, Plus } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import Heading from '@/components/Heading.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, store, update, destroy } from '@/routes/employee/fulfillment/shipping-methods';
import { index as trashIndex } from '@/routes/employee/fulfillment/shipping-methods/trash';
import type { BreadcrumbItem } from '@/types';
import type {
    ShippingMethod,
    ShippingMethodFilterData,
    ShippingMethodPagination,
} from '@/types/shipping-method';
import ShippingMethodDialog from './components/ShippingMethodDialog.vue';
import { getColumns } from './types/columns';

const props = defineProps<{
    shippingMethods?: ShippingMethodPagination;
    filters: ShippingMethodFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Phương thức vận chuyển', href: index().url },
];

const activeColumns = computed(() =>
    getColumns(handleEdit, handleDelete),
);

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedActive = ref<string | undefined>(
    props.filters.is_active !== null ? String(props.filters.is_active) : undefined,
);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || props.filters.is_active !== null;
});

const activeFilterOptions = computed(() => [
    { label: 'Hoạt động', value: 'true' },
    { label: 'Không hoạt động', value: 'false' },
]);

const showDialog = ref(false);
const editingMethod = ref<ShippingMethod | null>(null);
const showDeleteDialog = ref(false);
const selectedMethod = ref<ShippingMethod | null>(null);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            is_active: selectedActive.value !== undefined
                ? (selectedActive.value === 'true' ? true : false)
                : undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.shippingMethods,
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

function handleCreate() {
    editingMethod.value = null;
    showDialog.value = true;
}

function handleEdit(method: ShippingMethod) {
    editingMethod.value = method;
    showDialog.value = true;
}

function handleDelete(method: ShippingMethod) {
    selectedMethod.value = method;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!selectedMethod.value) return;
    router.delete(destroy(selectedMethod.value).url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedMethod.value = null;
        },
    });
}

function handleDialogClose() {
    showDialog.value = false;
    editingMethod.value = null;
}
</script>

<template>
    <Head title="Phương thức vận chuyển" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Phương thức vận chuyển"
                    description="Quản lý phương thức và giá vận chuyển"
                />
                <div class="flex items-center gap-2">
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" /> Thêm phương thức
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
                :data="shippingMethods?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="shippingMethods?.meta.total ?? 0"
                :page-size="shippingMethods?.meta.per_page ?? 15"
                :current-page="shippingMethods?.meta.current_page ?? 1"
                :last-page="shippingMethods?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @row-click="handleEdit"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        v-model="selectedActive"
                        title="Trạng thái"
                        :options="activeFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <ShippingMethodDialog
            :open="showDialog"
            :method="editingMethod"
            @close="handleDialogClose"
        />

        <AlertDialog :open="showDeleteDialog" @update:open="(val) => !val && (showDeleteDialog = false)">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Xóa phương thức vận chuyển?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Bạn có chắc muốn xóa "{{ selectedMethod?.name }}"? Phương thức đã xóa có thể khôi phục từ thùng rác.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Hủy</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete">Xóa</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
