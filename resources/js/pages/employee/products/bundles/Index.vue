<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, CheckCircle2, CircleDashed } from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, create, edit, destroy } from '@/routes/employee/bundles';
import type { BreadcrumbItem } from '@/types';
import type { Bundle, BundleFilterData, BundlePagination } from '@/types/bundle';
import { getColumns } from './types/columns';

const props = defineProps<{
    bundles?: BundlePagination;
    filters: BundleFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Gói sản phẩm', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showDeleteDialog = ref(false);
const selectedBundle = ref<Bundle | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.is_active ?? undefined);
const dateFrom = ref(props.filters.created_from ?? '');
const dateTo = ref(props.filters.created_to ?? '');

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        props.filters.is_active !== null ||
        !!props.filters.created_from ||
        !!props.filters.created_to ||
        !!props.filters.order_by
    );
});

const statusOptions = [
    { label: 'Đang hiện', value: true, icon: CheckCircle2 },
    { label: 'Đang ẩn', value: false, icon: CircleDashed },
];

const updateSearch = debounce(() => {
    const rawQuery = {
        ...props.filters,
        search: search.value,
        is_active: selectedStatus.value ?? undefined,
        created_from: dateFrom.value ?? undefined,
        created_to: dateTo.value ?? undefined,
        page: 1,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());
watch(selectedStatus, () => updateSearch());
watch(dateFrom, () => updateSearch());
watch(dateTo, () => updateSearch());

watch(
    () => props.bundles,
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
    router.visit(create().url);
}

function handleEdit(bundle: Bundle) {
    router.visit(edit({ bundle: bundle.id }).url);
}

function confirmDelete(bundle: Bundle) {
    selectedBundle.value = bundle;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedBundle.value) return;
    router.delete(destroy({ bundle: selectedBundle.value.id }).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedBundle.value = null;
        },
    });
}
</script>

<template>
    <Head title="Quản lý gói sản phẩm" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Quản lý gói sản phẩm"
                    description="Tạo và quản lý các combo sản phẩm khuyến mãi"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm gói sản phẩm
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="bundles?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="bundles?.meta.total ?? 0"
                :page-size="bundles?.meta.per_page ?? 15"
                :current-page="bundles?.meta.current_page ?? 1"
                :last-page="bundles?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
            <template #filters>
                <div class="flex flex-wrap items-end gap-3">
                    <!-- Status Filter (Already exists) -->
                    <DataTableSingleFilter
                        title="Trạng thái"
                        v-model="selectedStatus"
                        :options="statusOptions"
                        icon_location="end"
                    />

                    <div class="flex items-center gap-2">
                        <div class="flex flex-col gap-1">
                            <Label class="text-xs text-muted-foreground">Từ ngày</Label>
                            <Input
                                type="date"
                                v-model="dateFrom"
                                class="h-8 w-36 text-xs"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <Label class="text-xs text-muted-foreground">Đến ngày</Label>
                            <Input
                                type="date"
                                v-model="dateTo"
                                class="h-8 w-36 text-xs"
                            />
                        </div>
                    </div>
                </div>
            </template>
            </DataTableGroup>
        </div>

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa gói sản phẩm"
            :item-name="selectedBundle?.name"
            description='Bạn có chắc chắn muốn xóa gói sản phẩm "{name}"?'
            @confirm="performDelete"
        />
    </AppLayout>
</template>
