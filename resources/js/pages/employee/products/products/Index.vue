<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Plus,
    Package,
    CheckCircle2,
    CircleDashed,
    NotepadTextDashed,
    ClipboardClock,
    Sparkles,
    Star,
} from '@lucide/vue';
import { debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index, destroy, show, create, edit } from '@/routes/employee/products';
import type {
    BreadcrumbItem,
    ProductStatus,
    SpecNamespace,
    SpecLookupOption,
} from '@/types';
import type {
    Product,
    ProductFilterData,
    ProductPagination,
} from '@/types/product';
import { getColumns } from './types/columns';

const props = defineProps<{
    statusOptions: { value: string; label: string; color: string }[];
    vendorOptions: { id: string; label: string }[];
    categoryOptions: { id: string; label: string }[];
    collectionOptions: { id: string; label: string }[];
    locationOptions: any[];
    variantOptions: any[];
    featureOptions: any[];
    specNamespaces: SpecNamespace[];
    allSpecLookupOptions: Record<string, SpecLookupOption[]>;
    lookupNamespaces: { id: string | null; slug: string; label: string }[];
    products?: ProductPagination;
    filters: ProductFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sản phẩm', href: index().url },
];

const activeColumns = computed(() => getColumns(handleEdit, confirmDelete));

const showDeleteDialog = ref(false);
const selectedProduct = ref<Product | null>(null);
const isActuallyLoading = ref(true);
const search = ref(props.filters.search ?? '');

const selectedStatus = ref<ProductStatus | undefined>(
    props.filters.status ?? undefined,
);
const selectedVendor = ref<string | undefined>(
    props.filters.vendor_id ?? undefined,
);
const selectedCategory = ref<string | undefined>(
    props.filters.category_id ?? undefined,
);
const selectedCollection = ref<string | undefined>(
    props.filters.collection_id ?? undefined,
);
const selectedNewArrival = ref<boolean | undefined>(
    props.filters.is_new_arrival ?? undefined,
);
const selectedFeatured = ref<boolean | undefined>(
    props.filters.is_featured ?? undefined,
);

const hasActiveFilters = computed(() => {
    return (
        !!props.filters.status ||
        !!props.filters.vendor_id ||
        !!props.filters.category_id ||
        !!props.filters.collection_id ||
        props.filters.is_new_arrival !== null ||
        props.filters.is_featured !== null ||
        !!props.filters.search ||
        !!props.filters.order_by
    );
});

const statusIconMap: Record<string, any> = {
    published: CheckCircle2,
    pending_review: ClipboardClock,
    hidden: CircleDashed,
    draft: NotepadTextDashed,
    archived: Package,
};

const statusOptions = computed(() =>
    props.statusOptions.map((s) => ({
        ...s,
        icon: statusIconMap[s.value] ?? Package,
    })),
);

const vendorOptions = computed(() =>
    props.vendorOptions.map((v) => ({
        ...v,
        value: v.id,
    })),
);

const categoryOptions = computed(() =>
    props.categoryOptions.map((c) => ({
        ...c,
        value: c.id,
    })),
);

const collectionOptions = computed(() =>
    props.collectionOptions.map((c) => ({
        ...c,
        value: c.id,
    })),
);

const newArrivalOptions = [
    { label: 'Mới', value: true, icon: Sparkles },
    { label: 'Không', value: false },
];

const featuredOptions = [
    { label: 'Nổi bật', value: true, icon: Star },
    { label: 'Không', value: false },
];

const updateSearch = debounce(() => {
    const rawQuery = {
        ...props.filters,
        search: search.value,
        status: selectedStatus.value ?? undefined,
        vendor_id: selectedVendor.value ?? undefined,
        category_id: selectedCategory.value ?? undefined,
        collection_id: selectedCollection.value ?? undefined,
        is_new_arrival: selectedNewArrival.value ?? undefined,
        is_featured: selectedFeatured.value ?? undefined,
        page: 1,
    };

    router.get(index().url, cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());
watch(selectedStatus, () => updateSearch());
watch(selectedVendor, () => updateSearch());
watch(selectedCategory, () => updateSearch());
watch(selectedCollection, () => updateSearch());
watch(selectedNewArrival, () => updateSearch());
watch(selectedFeatured, () => updateSearch());

watch(
    () => props.products,
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
    router.get(create())
}

function handleShow(product: Product) {
    router.visit(show({ product: product.id }).url);
}

function handleEdit(product: Product) {
    router.visit(edit(product).url)
}

function confirmDelete(product: Product) {
    selectedProduct.value = product;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedProduct.value) return;
    router.delete(destroy(selectedProduct.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedProduct.value = null;
        },
    });
}
</script>

<template>
    <Head title="Quản lý sản phẩm" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Quản lý sản phẩm"
                    description="Quản lý thông tin, biến thể và hình ảnh sản phẩm"
                />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm sản phẩm
                </Button>
            </div>

            <DataTableGroup
                v-model:search="search"
                :is-actually-loading="isActuallyLoading"
                :columns="activeColumns"
                :data="products?.data ?? []"
                :has-active-filters="hasActiveFilters"
                :total="products?.meta.total ?? 0"
                :page-size="products?.meta.per_page ?? 15"
                :current-page="products?.meta.current_page ?? 1"
                :last-page="products?.meta.last_page ?? 1"
                :order-by="filters.order_by"
                :order-direction="filters.order_direction"
                @reset="resetFilters"
                @sort="handleSort"
                @row-click="handleShow"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            >
                <template #filters>
                    <DataTableSingleFilter
                        title="Nhà cung cấp"
                        v-model="selectedVendor"
                        :options="vendorOptions"
                        :searchable="true"
                    />
                    <DataTableSingleFilter
                        title="Danh mục"
                        v-model="selectedCategory"
                        :options="categoryOptions"
                        :searchable="true"
                    />
                    <DataTableSingleFilter
                        title="Bộ sưu tập"
                        v-model="selectedCollection"
                        :options="collectionOptions"
                        :searchable="true"
                    />
                    <DataTableSingleFilter
                        title="Mới"
                        v-model="selectedNewArrival"
                        :options="newArrivalOptions"
                    />
                    <DataTableSingleFilter
                        title="Nổi bật"
                        v-model="selectedFeatured"
                        :options="featuredOptions"
                    />
                    <DataTableSingleFilter
                        title="Trạng thái"
                        v-model="selectedStatus"
                        :options="statusOptions"
                    />
                </template>
            </DataTableGroup>
        </div>

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa sản phẩm"
            :item-name="selectedProduct?.name"
            description='Bạn có chắc chắn muốn xóa sản phẩm "{name}"?'
            @confirm="performDelete"
        />
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.1s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
