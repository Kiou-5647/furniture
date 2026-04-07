<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Warehouse, Store, Truck } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import type { Component } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, destroy } from '@/routes/employee/inventory/locations';
import type { BreadcrumbItem } from '@/types';
import type {
    Location,
    LocationFilterData,
    LocationPagination,
} from '@/types/location';
import { getColumns } from './types/columns';

const LocationFormModal = createLazyComponent(
    () => import('./components/LocationFormModal.vue'),
);

const props = defineProps<{
    typeOptions: { value: string; label: string }[];
    managerOptions: { id: string; label: string }[];
    locations?: LocationPagination;
    filters: LocationFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: index().url },
    { title: 'Vị trí', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedLocation = ref<Location | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const selectedType = ref<string | undefined>(props.filters.type ?? undefined);
const selectedStatus = ref<boolean | undefined>(
    props.filters.is_active ?? undefined,
);

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.type ||
        props.filters.is_active !== null
    );
});

function getTypeIcon (value: string) {
    switch (value) {
        case 'warehouse': return Warehouse as Component
        case 'retail': return Store as Component
        case 'vendor': return Truck as Component
    }
};

const typeFilterOptions = computed(() => [
    ...props.typeOptions.map((t) => ({
        label: t.label,
        value: t.value,
        icon: getTypeIcon(t.value),
    })),
]);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            type: selectedType.value ?? undefined,
            is_active: selectedStatus.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.locations,
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
    router.get(index().url, cleanQuery({ ...props.filters, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(index().url, {}, { preserveState: false });
}

function handleCreate() {
    selectedLocation.value = null;
    showFormModal.value = true;
}

function handleEdit(location: Location) {
    selectedLocation.value = location;
    showFormModal.value = true;
}

function confirmDelete(location: Location) {
    selectedLocation.value = location;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedLocation.value) return;
    router.delete(destroy(selectedLocation.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedLocation.value = null;
        },
    });
}
</script>

<template>
    <Head title="Vị trí kho hàng" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Vị trí kho hàng"
                    description="Quản lý các kho hàng, cửa hàng và nhà cung cấp"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm vị trí
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="locations?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="locations?.meta.total ?? 0"
                :page-size="locations?.meta.per_page ?? 15"
                :current-page="locations?.meta.current_page ?? 1"
                :last-page="locations?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @row-click="handleEdit"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        v-model="selectedType"
                        title="Loại"
                        :options="typeFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <LocationFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :location="selectedLocation"
            :type-options="typeOptions"
            :manager-options="managerOptions"
            @close="showFormModal = false"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa vị trí"
            :item-name="selectedLocation?.name"
            :description="
                selectedLocation
                    ? `Bạn có chắc chắn muốn xóa vị trí &quot;${selectedLocation.name}&quot;?`
                    : ''
            "
            @confirm="performDelete"
        />
    </AppLayout>
</template>
