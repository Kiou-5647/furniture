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
import { index, destroy } from '@/routes/employee/hr/designers';
import type { BreadcrumbItem } from '@/types';
import type {
    Designer,
    DesignerFilterData,
    DesignerPagination,
} from '@/types/designer';
import { getColumns } from './types/columns';

const DesignerFormModal = createLazyComponent(
    () => import('./components/DesignerFormModal.vue'),
);

const props = defineProps<{
    workHours: any[],
    employeeOptions: { id: string; full_name: string; phone: string | null; email: string | null }[];
    designers?: DesignerPagination;
    filters: DesignerFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quản lý nhân sự', href: index().url },
    { title: 'Nhà thiết kế', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedDesigner = ref<Designer | null>(null);
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
    () => props.designers,
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
    selectedDesigner.value = null;
    showFormModal.value = true;
}

function handleEdit(designer: Designer) {
    selectedDesigner.value = designer;
    showFormModal.value = true;
}

function confirmDelete(designer: Designer) {
    selectedDesigner.value = designer;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedDesigner.value) return;
    router.delete(destroy({ designer: selectedDesigner.value.id }).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedDesigner.value = null;
        },
    });
}
</script>

<template>
    <Head title="Nhà thiết kế" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Nhà thiết kế"
                    description="Quản lý nhà thiết kế nội bộ, freelancer và đối tác"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm nhà thiết kế
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="designers?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="designers?.meta.total ?? 0"
                :page-size="designers?.meta.per_page ?? 15"
                :current-page="designers?.meta.current_page ?? 1"
                :last-page="designers?.meta.last_page ?? 1"
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

        <DesignerFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :workHours="workHours"
            :designer="selectedDesigner"
            :employee-options="employeeOptions"
            @close="showFormModal = false"
            @refresh="router.reload({ only: ['designers'] })"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa nhà thiết kế"
            :item-name="selectedDesigner?.display_name"
            :description="
                selectedDesigner
                    ? `Bạn có chắc chắn muốn xóa nhà thiết kế &quot;${selectedDesigner.display_name}&quot;?`
                    : ''
            "
            @confirm="performDelete"
        />
    </AppLayout>
</template>
