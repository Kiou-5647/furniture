<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Plus,
    CheckCircle2,
    CircleDashed,
} from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, destroy } from '@/routes/employee/collections';
import type { BreadcrumbItem } from '@/types';
import type {
    Collection,
    CollectionFilterData,
    CollectionPagination,
} from '@/types/collection';
import { getColumns } from './types/columns';

// Lazy-load modal
const CollectionFormModal = createLazyComponent(
    () => import('./components/CollectionFormModal.vue'),
);

const props = defineProps<{
    collections?: CollectionPagination;
    filters: CollectionFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Bộ sưu tập', href: index().url },
];

// Column Logic
const activeColumns = computed(() =>
    getColumns(handleEdit, confirmDelete, handlePreviewImage),
);

// State
const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedCollection = ref<Collection | null>(null);
const previewImageUrl = ref<string | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.order_by ||
        (props.filters.is_active !== undefined &&
            props.filters.is_active !== null)
    );
});

const statusOptions = [
    { label: 'Đang hiện', value: 'true', icon: CheckCircle2 },
    { label: 'Đang ẩn', value: 'false', icon: CircleDashed },
];

// Status Filter
const selectedStatus = ref(props.filters.is_active ?? undefined);

// Filtering Logic (Debounced)
const updateSearch = debounce(() => {
    const rawQuery = {
        ...props.filters,
        search: search.value,
        is_active: selectedStatus.value ?? undefined,
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

watch(
    () => props.collections,
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
    router.get(index().url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
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
    router.get(index().url, {}, { preserveState: false });
}

function handleCreate() {
    selectedCollection.value = null;
    showFormModal.value = true;
}

function handleEdit(collection: Collection) {
    selectedCollection.value = collection;
    showFormModal.value = true;
}

function confirmDelete(collection: Collection) {
    selectedCollection.value = collection;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedCollection.value) return;
    router.delete(destroy(selectedCollection.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedCollection.value = null;
        },
    });
}

function handlePreviewImage(url: string) {
    previewImageUrl.value = url;
}
</script>

<template>
    <Head title="Bộ sưu tập sản phẩm" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Bộ sưu tập"
                    description="Quản lý các bộ sưu tập sản phẩm theo chủ đề hoặc mùa"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm bộ sưu tập
                </Button>
            </div>

            <div class="grid grid-cols-1">
                <div class="col-span-1 space-y-4">
                    <DataTableGroup
                        v-model:search="search"
                        :is-actually-loading="isActuallyLoading"
                        :columns="activeColumns"
                        :data="collections?.data ?? []"
                        :has-active-filters="hasActiveFilters"
                        :total="collections?.meta.total ?? 0"
                        :page-size="collections?.meta.per_page ?? 15"
                        :current-page="collections?.meta.current_page ?? 1"
                        :last-page="collections?.meta.last_page ?? 1"
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
                                :searchable="false"
                                icon_location="end"
                            />
                        </template>
                    </DataTableGroup>
                </div>
            </div>
        </div>

        <!-- Modals & Dialogs -->
        <CollectionFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :collection="selectedCollection"
            @close="showFormModal = false"
            @delete="confirmDelete"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa bộ sưu tập"
            :item-name="selectedCollection?.display_name"
            description='Bạn có chắc chắn muốn xóa bộ sưu tập "{name}"?'
            @confirm="performDelete"
        />

        <ImagePreviewDialog
            :open="!!previewImageUrl"
            :src="previewImageUrl"
            @update:open="previewImageUrl = $event ? previewImageUrl : null"
            @close="previewImageUrl = null"
        />
    </AppLayout>
</template>
emplate>
