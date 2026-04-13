<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
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
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { destroy, index } from '@/routes/employee/services';
import type { BreadcrumbItem } from '@/types';
import type {
    DesignService,
    DesignServiceFilterData,
    DesignServicePagination,
} from '@/types/design-service';
import { getColumns } from './types/columns';

const DesignServiceDialog = createLazyComponent(
    () => import('./components/DesignServiceDialog.vue'),
);

const props = defineProps<{
    services?: DesignServicePagination;
    filters: DesignServiceFilterData;
    typeOptions: { value: string; label: string; color: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dịch vụ thiết kế', href: index().url },
];

const activeColumns = computed(() =>
    getColumns(handleEdit, handleDelete),
);

const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedType = ref(props.filters.type ?? undefined);

const scheduleBlockingOptions = computed(() => [
    { label: 'Có chặn lịch', value: 'true' },
    { label: 'Không chặn lịch', value: 'false' },
]);

const selectedScheduleBlocking = ref(
    props.filters.is_schedule_blocking !== null
        ? String(props.filters.is_schedule_blocking)
        : undefined,
);

const hasActiveFilters = computed(() => {
    return !!props.filters.search
        || !!props.filters.type
        || props.filters.is_schedule_blocking !== null;
});

const showDialog = ref(false);
const editingService = ref<DesignService | null>(null);
const showDeleteDialog = ref(false);
const selectedService = ref<DesignService | null>(null);

const updateSearch = debounce(() => {
    router.get(
        index().url,
        cleanQuery({
            search: search.value,
            type: selectedType.value,
            is_schedule_blocking: selectedScheduleBlocking.value !== undefined
                ? (selectedScheduleBlocking.value === 'true')
                : null,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.services,
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
    editingService.value = null;
    showDialog.value = true;
}

function handleEdit(service: DesignService) {
    editingService.value = service;
    showDialog.value = true;
}

function handleDelete(service: DesignService) {
    selectedService.value = service;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!selectedService.value) return;
    router.delete(destroy(selectedService.value).url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedService.value = null;
        },
    });
}

function handleDialogClose() {
    showDialog.value = false;
    editingService.value = null;
}
</script>

<template>
    <Head title="Dịch vụ thiết kế" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Dịch vụ thiết kế"
                    description="Quản lý các loại dịch vụ thiết kế và tư vấn nội thất"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm dịch vụ
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="services?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="services?.meta.total ?? 0"
                :page-size="services?.meta.per_page ?? 15"
                :current-page="services?.meta.current_page ?? 1"
                :last-page="services?.meta.last_page ?? 1"
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
                        v-model="selectedType"
                        title="Loại dịch vụ"
                        :options="typeOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                    <DataTableSingleFilter
                        v-model="selectedScheduleBlocking"
                        title="Chặn lịch"
                        :options="scheduleBlockingOptions"
                        icon_location="end"
                        :searchable="false"
                        @update:model-value="updateSearch"
                    />
                </template>
            </DataTableGroup>
        </div>

        <DesignServiceDialog
            :open="showDialog"
            :service="editingService"
            @close="handleDialogClose"
        />

        <AlertDialog :open="showDeleteDialog" @update:open="(val) => !val && (showDeleteDialog = false)">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Xóa dịch vụ thiết kế?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Bạn có chắc muốn xóa "{{ selectedService?.name }}"? Dịch vụ đã xóa có thể khôi phục.
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
