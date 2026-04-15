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
import { index, destroy } from '@/routes/employee/inventory/vendor';
import type { BreadcrumbItem } from '@/types';
import type {
    Vendor,
    VendorFilterData,
    VendorPagination,
} from '@/types/vendor';
import { getColumns } from './types/columns';

const VendorFormModal = createLazyComponent(
    () => import('./components/VendorFormModal.vue'),
);

const props = defineProps<{
    vendors?: VendorPagination;
    filters: VendorFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: '#' },
    { title: 'Nhà cung cấp', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedVendor = ref<Vendor | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const hasActiveFilters = computed(() => {
    return !!search.value;
});

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.vendors,
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

function handleCreate() {
    selectedVendor.value = null;
    showFormModal.value = true;
}

function handleEdit(vendor: Vendor) {
    selectedVendor.value = vendor;
    showFormModal.value = true;
}

function confirmDelete(vendor: Vendor) {
    selectedVendor.value = vendor;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedVendor.value) return;
    router.delete(destroy(selectedVendor.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedVendor.value = null;
        },
    });
}
</script>

<template>
    <Head title="Nhà cung cấp" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Nhà cung cấp"
                    description="Quản lý thông tin các nhà cung cấp vật tư, thiết bị"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm nhà cung cấp
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="vendors?.data ?? []"
                :total="vendors?.meta.total ?? 0"
                :page-size="vendors?.meta.per_page ?? 15"
                :current-page="vendors?.meta.current_page ?? 1"
                :last-page="vendors?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                :has-active-filters="hasActiveFilters"
                @reset="resetFilters"
                @sort="handleSort"
                @row-click="handleEdit"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <!-- No specific filters needed here besides search -->
                </template>
            </DataTableGroup>
        </div>

        <VendorFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :vendor="selectedVendor"
            @close="showFormModal = false"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa nhà cung cấp"
            :item-name="selectedVendor?.name"
            :description="
                selectedVendor
                    ? `Bạn có chắc chắn muốn xóa nhà cung cấp &quot;${selectedVendor.name}&quot;?`
                    : ''
            "
            @confirm="performDelete"
        />
    </AppLayout>
</template>
