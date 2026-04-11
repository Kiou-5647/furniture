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
import { index, destroy } from '@/routes/employee/hr/departments';
import type { BreadcrumbItem } from '@/types';
import type {
    Department,
    DepartmentFilterData,
    DepartmentPagination,
} from '@/types/department';
import { getColumns } from './types/columns';

const DepartmentFormModal = createLazyComponent(
    () => import('./components/DepartmentFormModal.vue'),
);

const props = defineProps<{
    managerOptions: { id: string; label: string }[];
    departments?: DepartmentPagination;
    filters: DepartmentFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quản lý nhân sự', href: index().url },
    { title: 'Phòng ban', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedDepartment = ref<Department | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref<boolean | undefined>(
    props.filters.is_active ?? undefined,
);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || props.filters.is_active !== null;
});

const statusFilterOptions = computed(() => [
    { label: 'Hoạt động', value: true },
    { label: 'Ngừng', value: false },
]);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            is_active: selectedStatus.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.departments,
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
    selectedDepartment.value = null;
    showFormModal.value = true;
}

function handleEdit(department: Department) {
    selectedDepartment.value = department;
    showFormModal.value = true;
}

function confirmDelete(department: Department) {
    selectedDepartment.value = department;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedDepartment.value) return;
    router.delete(destroy(selectedDepartment.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedDepartment.value = null;
        },
    });
}
</script>

<template>
    <Head title="Phòng ban" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Phòng ban"
                    description="Quản lý các phòng ban trong công ty"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm phòng ban
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="departments?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="departments?.meta.total ?? 0"
                :page-size="departments?.meta.per_page ?? 15"
                :current-page="departments?.meta.current_page ?? 1"
                :last-page="departments?.meta.last_page ?? 1"
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
                        v-model="selectedStatus"
                        title="Trạng thái"
                        :options="statusFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <DepartmentFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :department="selectedDepartment"
            :manager-options="managerOptions"
            @close="showFormModal = false"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa phòng ban"
            :item-name="selectedDepartment?.name"
            :description="
                selectedDepartment
                    ? `Bạn có chắc chắn muốn xóa phòng ban &quot;${selectedDepartment.name}&quot;?`
                    : ''
            "
            @confirm="performDelete"
        />
    </AppLayout>
</template>
