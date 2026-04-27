<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, CheckCircle2, CircleDashed, Percent, Banknote, Tag } from '@lucide/vue';
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
import { index, destroy } from '@/routes/employee/sales/discounts';
import type { BreadcrumbItem } from '@/types';
import { getColumns } from './types/columns';
import type {
    Discount,
    DiscountFilterData,
    DiscountPagination,
} from '@/types/discount';

const DiscountFormModal = createLazyComponent(
    () => import('./components/DiscountFormModal.vue'),
);

const props = defineProps<{
    discountableTypes: Record<string, string>;
    discounts?: DiscountPagination;
    filters: DiscountFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quản lý giảm giá', href: index().url },
];

const activeColumns = computed(() =>
    getColumns(
        handleEdit,
        confirmDelete,
    ),
);

// State
const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedDiscount = ref<Discount | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.type ||
        !!props.filters.discountable_type ||
        !!props.filters.discountable_id ||
        (props.filters.is_active !== undefined &&
            props.filters.is_active !== null) ||
        !!props.filters.start_after ||
        !!props.filters.end_before ||
        !!props.filters.order_by ||
        !!props.filters.order_direction
    );
});

// --- Filter Options ---

const statusOptions = [
    { label: 'Đang hiện', value: true, icon: CheckCircle2 },
    { label: 'Đang ẩn', value: false, icon: CircleDashed },
];
const selectedStatus = ref(props.filters.is_active ?? undefined);

const typeOptions = [
    { label: 'Phần trăm', value: 'percentage', icon: Percent },
    { label: 'Số tiền', value: 'fixed_amount', icon: Banknote },
];
const selectedType = ref(props.filters.type ?? null);

const targetTypeOptions = Object.entries(props.discountableTypes).map(([value, label]) => ({
    label,
    value,
    icon: Tag,
}));
const selectedTargetType = ref(props.filters.discountable_type ?? null);

// Date Filters
const startAfter = ref(props.filters.start_after ?? '');
const endBefore = ref(props.filters.end_before ?? '');

// Filtering Logic (Debounced)
const updateSearch = debounce(() => {
    const { ...restFilters } = props.filters;

    const rawQuery = {
        ...restFilters,
        search: search.value,
        is_active: selectedStatus.value ?? undefined,
        type: selectedType.value ?? undefined,
        discountable_type: selectedTargetType.value ?? undefined,
        start_after: startAfter.value ?? undefined,
        end_before: endBefore.value ?? undefined,
        page: 1,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

// Watchers
watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());
watch(selectedStatus, () => updateSearch());
watch(selectedType, () => updateSearch());
watch(selectedTargetType, () => updateSearch());
watch(startAfter, () => updateSearch());
watch(endBefore, () => updateSearch());

watch(
    () => props.discounts,
    (newData) => {
        if (newData) {
            setTimeout(() => (isActuallyLoading.value = false), 200);
        }
    },
    { immediate: true },
);

// Actions
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
    router.get(
        index().url,
        cleanQuery({ ...props.filters, page }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);
    const { per_page: _, ...restFilters } = props.filters;
    router.get(
        index().url,
        cleanQuery({
            ...restFilters,
            page: 1,
        }),
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function resetFilters() {
    search.value = '';
    selectedStatus.value = undefined;
    selectedType.value = null;
    selectedTargetType.value = null;
    startAfter.value = '';
    endBefore.value = '';

    router.get(
        index().url,
        {},
        { preserveState: false },
    );
}

function handleCreate() {
    selectedDiscount.value = null;
    showFormModal.value = true;
}

function handleEdit(discount: Discount) {
    selectedDiscount.value = discount;
    showFormModal.value = true;
}

function confirmDelete(discount: Discount) {
    selectedDiscount.value = discount;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedDiscount.value) return;
    router.delete(destroy(selectedDiscount.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedDiscount.value = null;
        },
    });
}
</script>

<template>
    <Head title="Quản lý giảm giá" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Quản lý giảm giá"
                    description="Thiết lập chương trình giảm giá tự động cho danh mục, bộ sưu tập hoặc nhà cung cấp"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm giảm giá
                </Button>
            </div>

            <div class="@container">
                <div class="flex flex-col gap-2 @lg:flex-row @lg:items-start">
                    <div class="w-full min-w-0 flex-1">
                        <DataTableGroup
                            v-model:search="search"
                            :is-actually-loading="isActuallyLoading"
                            :columns="activeColumns"
                            :data="discounts?.data ?? []"
                            :has-active-filters="hasActiveFilters"
                            :total="discounts?.meta.total ?? 0"
                            :page-size="discounts?.meta.per_page ?? 15"
                            :current-page="discounts?.meta.current_page ?? 1"
                            :last-page="discounts?.meta.last_page ?? 1"
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
                                    title="Trạng thái"
                                    v-model="selectedStatus"
                                    :options="statusOptions"
                                    icon_location="end"
                                />
                                <DataTableSingleFilter
                                    title="Loại giảm giá"
                                    v-model="selectedType"
                                    :options="typeOptions"
                                    icon_location="end"
                                />
                                <DataTableSingleFilter
                                    title="Đối tượng"
                                    v-model="selectedTargetType"
                                    :options="targetTypeOptions"
                                    icon_location="end"
                                />
                                <!-- Date Filters -->
                                <div class="flex items-center gap-2">
                                    <input
                                        type="date"
                                        v-model="startAfter"
                                        class="h-8 rounded-md border px-2 text-xs"
                                        title="Bắt đầu từ"
                                    />
                                    <input
                                        type="date"
                                        v-model="endBefore"
                                        class="h-8 rounded-md border px-2 text-xs"
                                        title="Kết thúc trước"
                                    />
                                </div>
                            </template>
                        </DataTableGroup>
                    </div>
                </div>
            </div>
        </div>

        <DiscountFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :discountable-types="discountableTypes"
            :discount="selectedDiscount"
            @close="showFormModal = false"
            @delete="confirmDelete"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa giảm giá"
            :item-name="selectedDiscount?.name"
            description='Bạn có chắc chắn muốn xóa giảm giá "{name}"?'
            @confirm="performDelete"
        />
    </AppLayout>
</template>
