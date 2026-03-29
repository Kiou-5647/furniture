<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';
import type { Collection, CollectionFilterData, CollectionPagination } from '@/types/collection';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { getColumns } from './columns';
import { index, destroy } from '@/routes/employee/products/collections';
import { debounce } from 'lodash';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Plus, Star, StarOff, CheckCircle2, CircleDashed } from 'lucide-vue-next';
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
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { createLazyComponent } from '@/composables/createLazyComponent';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';

// Lazy-load modal
const CollectionFormModal = createLazyComponent(() => import('./CollectionFormModal.vue'));
const CollectionDetailsDialog = createLazyComponent(() => import('./CollectionDetailsDialog.vue'));

const props = defineProps<{
    collections?: CollectionPagination;
    filters: CollectionFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sản phẩm', href: '#' },
    { title: 'Bộ sưu tập', href: index().url }
];

// Column Logic
const activeColumns = computed(() =>
    getColumns(handleEdit, confirmDelete, handleViewDetails, handlePreviewImage)
);

// State
const showFormModal = ref(false);
const showDetailsDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedCollection = ref<Collection | null>(null);
const previewImageUrl = ref<string | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.search ||
        !!props.filters.order_by ||
        (props.filters.is_active !== undefined && props.filters.is_active !== null) ||
        (props.filters.is_featured !== undefined && props.filters.is_featured !== null)
    );
});

const featuredOptions = [
    { label: 'Nổi bật', value: 'true', icon: Star },
    { label: 'Thường', value: 'false', icon: StarOff },
];

const statusOptions = [
    { label: 'Đang hiện', value: 'true', icon: CheckCircle2 },
    { label: 'Đang ẩn', value: 'false', icon: CircleDashed },
];

// Featured Filter
const selectedFeatured = ref(props.filters.is_featured ?? undefined);

// Status Filter
const selectedStatus = ref(props.filters.is_active ?? undefined);

// Filtering Logic (Debounced)
const updateSearch = debounce(() => {
    const rawQuery = {
        ...props.filters,
        search: search.value,
        is_featured: selectedFeatured.value ?? undefined,
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
watch(selectedFeatured, () => updateSearch());
watch(selectedStatus, () => updateSearch());

watch(() => props.collections, (newData) => {
    if (newData) {
        setTimeout(() => isActuallyLoading.value = false, 200);
    }
}, { immediate: true });

// Actions
function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';
    router.get(index().url, cleanQuery({
        ...props.filters,
        order_by: column,
        order_direction: direction,
        page: 1
    }), { preserveState: true });
}

function handlePageChange(page: number) {
    router.get(index().url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);

    const { per_page: _, ...restFilters } = props.filters;

    router.get(index().url, cleanQuery({
        ...restFilters,
        page: 1,
    }), {
        preserveState: true,
        preserveScroll: true,
    });
}


function resetFilters() {
    router.get(index().url, {}, { preserveState: false });
}

function handleCreate() {
    selectedCollection.value = null;
    showFormModal.value = true;
}

function handleViewDetails(collection: Collection) {
    selectedCollection.value = collection;
    showDetailsDialog.value = true;
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
    router.delete(destroy(selectedCollection.value.id).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedCollection.value = null;
        }
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
                <Heading title="Bộ sưu tập" description="Quản lý các bộ sưu tập sản phẩm theo chủ đề hoặc mùa" />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm bộ sưu tập
                </Button>
            </div>

            <div class="grid grid-cols-1">
                <div class="col-span-1 space-y-4">
                    <DataTableGroup v-model:search="search" :is-actually-loading="isActuallyLoading"
                        :columns="activeColumns" :data="collections?.data ?? []" :has-active-filters="hasActiveFilters"
                        :total="collections?.meta.total ?? 0" :page-size="collections?.meta.per_page ?? 15"
                        :current-page="collections?.meta.current_page ?? 1"
                        :last-page="collections?.meta.last_page ?? 1" :order-by="filters.order_by"
                        :order-direction="filters.order_direction" @reset="resetFilters" @sort="handleSort"
                        @update:page="handlePageChange" @update:page-size="handlePageSizeChange">
                        <template #filters>
                            <DataTableSingleFilter title="Trạng thái" v-model="selectedStatus" :options="statusOptions"
                                :searchable="false" icon_location="end" />
                            <DataTableSingleFilter title="Nổi bật" v-model="selectedFeatured" :options="featuredOptions"
                                :searchable="false" icon_location="end" />
                        </template>
                    </DataTableGroup>
                </div>
            </div>
        </div>

        <!-- Modals & Dialogs -->
        <CollectionFormModal v-if="showFormModal" :open="showFormModal" :collection="selectedCollection"
            @close="showFormModal = false" />

        <CollectionDetailsDialog v-if="showDetailsDialog" :open="showDetailsDialog" :collection="selectedCollection"
            @close="showDetailsDialog = false" @edit="handleEdit" @preview-image="handlePreviewImage" />

        <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Xác nhận xóa bộ sưu tập</AlertDialogTitle>
                    <AlertDialogDescription>
                        Bạn có chắc chắn muốn xóa bộ sưu tập "{{ selectedCollection?.display_name }}"?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="selectedCollection = null">Hủy</AlertDialogCancel>
                    <AlertDialogAction @click="performDelete" class="bg-destructive hover:bg-destructive/90 text-white">
                        Xóa
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <ImagePreviewDialog :open="!!previewImageUrl" :src="previewImageUrl"
            @update:open="previewImageUrl = $event ? previewImageUrl : null" @close="previewImageUrl = null" />
    </AppLayout>
</template>
emplate>
