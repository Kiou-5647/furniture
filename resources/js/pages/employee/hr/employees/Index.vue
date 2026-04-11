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
import { index, terminate, restore, destroy } from '@/routes/employee/hr/employees';
import type { BreadcrumbItem } from '@/types';
import type {
    Employee,
    EmployeeFilterData,
    EmployeePagination,
} from '@/types/employee';
import { getColumns } from './types/columns';

const EmployeeFormModal = createLazyComponent(
    () => import('./components/EmployeeFormModal.vue'),
);

const EmployeeRolesModal = createLazyComponent(
    () => import('./components/EmployeeRolesModal.vue'),
);

const props = defineProps<{
    departmentOptions: { id: string; label: string }[];
    roleOptions: { id: string; label: string }[];
    permissionOptions: { id: string; label: string }[];
    locationOptions: { id: string; label: string }[];
    rolePermissions: Record<string, string[]>;
    employees?: EmployeePagination;
    filters: EmployeeFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quản lý nhân sự', href: index().url },
    { title: 'Nhân viên', href: index().url },
];

const activeColumns = computed(() =>
    getColumns(handleEdit, viewRoles, confirmTerminate, confirmRestore, confirmDelete),
);

const showFormModal = ref(false);
const showRolesModal = ref(false);
const showDeleteDialog = ref(false);
const showTerminateDialog = ref(false);
const selectedEmployee = ref<Employee | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedDepartment = ref<string | undefined>(props.filters.department_id ?? undefined);

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.department_id
    );
});

const departmentFilterOptions = computed(() =>
    props.departmentOptions.map((d) => ({
        label: d.label,
        value: d.id,
    })),
);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            department_id: selectedDepartment.value ?? undefined,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.employees,
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
    selectedEmployee.value = null;
    showFormModal.value = true;
}

function handleEdit(employee: Employee) {
    selectedEmployee.value = employee;
    showFormModal.value = true;
}

function viewRoles(employee: Employee) {
    selectedEmployee.value = employee;
    showRolesModal.value = true;
}

function confirmTerminate(employee: Employee) {
    selectedEmployee.value = employee;
    showTerminateDialog.value = true;
}

function confirmRestore(employee: Employee) {
    selectedEmployee.value = employee;
    router.post(restore(employee).url, {}, {
        onSuccess: () => {
            selectedEmployee.value = null;
        }
    });
}

function confirmDelete(employee: Employee) {
    selectedEmployee.value = employee;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedEmployee.value) return;
    router.delete(destroy(selectedEmployee.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedEmployee.value = null;
        },
    });
}

function performTerminate() {
    if (!selectedEmployee.value) return;
    router.post(terminate(selectedEmployee.value).url, {}, {
        onSuccess: () => {
            showTerminateDialog.value = false;
            selectedEmployee.value = null;
        },
    });
}
</script>

<template>
    <Head title="Nhân viên" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Nhân viên"
                    description="Quản lý thông tin nhân viên và quyền hạn"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm nhân viên
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="employees?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="employees?.meta.total ?? 0"
                :page-size="employees?.meta.per_page ?? 15"
                :current-page="employees?.meta.current_page ?? 1"
                :last-page="employees?.meta.last_page ?? 1"
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
                        v-model="selectedDepartment"
                        title="Phòng ban"
                        :options="departmentFilterOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <EmployeeFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :employee="selectedEmployee"
            :department-options="departmentOptions"
            :location-options="locationOptions"
            :role-options="roleOptions"
            :permission-options="permissionOptions"
            :role-permissions="rolePermissions"
            @close="showFormModal = false"
            @refresh="router.reload({ only: ['employees'] })"
        />

        <EmployeeRolesModal
            v-if="showRolesModal"
            :open="showRolesModal"
            :employee="selectedEmployee"
            :role-options="roleOptions"
            :permission-options="permissionOptions"
            @close="showRolesModal = false"
            @refresh="router.reload({ only: ['employees'] })"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa nhân viên"
            :item-name="selectedEmployee?.full_name"
            :description="
                selectedEmployee
                    ? `Bạn có chắc chắn muốn xóa nhân viên &quot;${selectedEmployee.full_name}&quot;?`
                    : ''
            "
            @confirm="performDelete"
        />

        <DeleteConfirmation
            v-model:open="showTerminateDialog"
            title="Xác nhận chấm dứt nhân viên"
            :item-name="selectedEmployee?.full_name"
            description="Nhân viên sẽ bị vô hiệu hóa tài khoản. Bạn có thể khôi phục sau."
            @confirm="performTerminate"
        />
    </AppLayout>
</template>
