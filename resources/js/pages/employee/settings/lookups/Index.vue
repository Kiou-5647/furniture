<script setup lang="ts">
import type {
    BreadcrumbItem,
    Lookup,
    LookupFilterData,
    LookupNamespace,
    LookupPagination,
} from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { getColumns } from './columns';
import { index } from '@/routes/employee/settings/lookups';
import { capitalize, debounce } from 'lodash';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Eye, EyeOff, Plus } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { destroy } from '@/routes/employee/settings/lookups';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { createLazyComponent } from '@/composables/createLazyComponent';
import LookupDetailsDialog from './LookupDetailsDialog.vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';

// Lazy-load modal (NO Suspense - safe for production)
const LookupFormModal = createLazyComponent(() => import('./LookupFormModal.vue'));

const props = defineProps<{
    namespaces: LookupNamespace[];
    lookups?: LookupPagination;
    filters: LookupFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tra cứu', href: index().url }];
const activeColumns = computed(() =>
    getColumns(
        props.filters.namespace!,
        handleEdit,
        confirmDelete,
        handleViewDetails,
        handlePreviewImage,
    ),
);
const showFormModal = ref(false);
const showDetailsDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedLookup = ref<Lookup | null>(null);
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

const selectedStatus = ref(props.filters.is_active ?? undefined);

const statusOptions = [
    { label: 'Đang hiện', value: true, icon: Eye },
    { label: 'Đang ẩn', value: false, icon: EyeOff },
];

const selectedNamespace = ref<LookupNamespace | null>(
    props.namespaces.find(n => n.namespace === props.filters.namespace) || null
);
const updateSearch = debounce(() => {
    const { namespace, ...restFilters } = props.filters;

    const isActiveValue = selectedStatus.value;

    const rawQuery = {
        ...restFilters,
        search: search.value,
        is_active: isActiveValue,
        page: 1,
    };

    router.get(index(namespace), cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

const namespaceMap = computed(() => {
    return props.namespaces.reduce((acc, item) => {
        acc[item.namespace] = item.label;
        return acc;
    }, {} as Record<string, string>)
})

const label = computed(() => namespaceMap.value[props.filters.namespace] ?? 'Không xác định');

let loadingTimeout: any = null;

watch(search, (newValue) => {
    if (newValue === (props.filters.search ?? '')) return;
    updateSearch();
});

watch(selectedStatus, () => {
    updateSearch();
});

watch(
    () => props.filters.search,
    (newSearch) => {
        search.value = newSearch ?? '';
    },
);

watch(
    () => [props.filters],
    () => {
        isActuallyLoading.value = true;
    },
);

watch(
    () => props.lookups,
    (newData) => {
        if (newData) {
            if (loadingTimeout) clearTimeout(loadingTimeout);
            loadingTimeout = setTimeout(() => {
                isActuallyLoading.value = false;
            }, 200);
        }
    },
    { immediate: true },
);

// Update selected namespace when filters or namespaces change
watch(
    () => [props.filters.namespace, props.namespaces],
    () => {
        selectedNamespace.value = props.namespaces.find(n => n.namespace === props.filters.namespace) || null;
    },
    { immediate: true }
);

function handleSort(column: string) {
    const { namespace, ...restFilters } = props.filters;
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';

    const rawQuery = {
        ...restFilters,
        order_by: column,
        order_direction: direction,
        page: 1,
    };

    router.get(index(namespace).url, cleanQuery(rawQuery), {
        preserveState: true,
    });
}

function resetFilters() {
    updateSearch.cancel();

    router.get(
        index(props.filters.namespace),
        {},
        {
            preserveState: false,
        },
    );
}
function handleCreate() {
    selectedLookup.value = null;
    showFormModal.value = true;
}

function handleViewDetails(lookup: Lookup) {
    selectedLookup.value = lookup;
    showDetailsDialog.value = true;
}

function handleEdit(lookup: Lookup) {
    selectedLookup.value = lookup;
    showFormModal.value = true;
}

function confirmDelete(lookup: Lookup) {
    selectedLookup.value = lookup;
    showDeleteDialog.value = true;
}

function performDelete() {
    if (!selectedLookup.value) return;

    router.delete(destroy(selectedLookup.value).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedLookup.value = null;
        },
    });
}

function handlePageChange(page: number) {
    const { namespace, ...restFilters } = props.filters;

    const isActiveValue = selectedStatus.value;

    const rawQuery = {
        ...restFilters,
        is_active: isActiveValue,
        page,
    };

    router.get(index(namespace).url, cleanQuery(rawQuery), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);

    const { namespace, per_page: _, ...restFilters } = props.filters;

    const isActiveValue = selectedStatus.value;

    const rawQuery = {
        ...restFilters,
        is_active: isActiveValue,
        page: 1,
    };
    router.get(index(namespace).url, cleanQuery(rawQuery), {
        preserveState: true,
        preserveScroll: true,
    });
}
function handlePreviewImage(url: string) {
    previewImageUrl.value = url;
}
</script>

<template>

    <Head title="Tra cứu" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading title="Quản lý tra cứu" description="Định nghĩa các kiểu dữ liệu và giá trị cho hệ thống" />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm Tra cứu
                </Button>
            </div>

            <div class="grid grid-cols-1 items-start sm:grid-cols-12 sm:gap-3">
                <!-- Desktop: Card Sidebar -->
                <Card class="hidden space-y-2 sm:block col-span-1 sm:col-span-4 md:col-span-3 xl:col-span-2">
                    <CardHeader>
                        <CardTitle class="text-lg font-medium">Danh mục</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-1">
                        <Link v-for="item in namespaces" :key="item.namespace" :href="index(item.namespace).url" :class="[
                            'group flex items-center justify-between rounded-md px-1 py-2 transition-all duration-200',
                            filters.namespace === item.namespace
                                ? 'bg-primary/10 text-primary shadow-sm'
                                : 'text-muted-foreground hover:bg-muted',
                        ]" preserve-state preserve-scroll>
                            <span class="font-medium text-sm">
                                {{ item.label }}</span>
                            <Badge variant="secondary" class="transition-colors group-hover:bg-background">
                                {{ item.count }}
                            </Badge>
                        </Link>
                    </CardContent>
                </Card>

                <div class="sm:hidden">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="w-full justify-between">
                                <span class="font-medium">
                                    Danh mục: {{ selectedNamespace?.label || 'Chọn danh mục' }}
                                </span>
                                <Badge variant="secondary">
                                    {{ selectedNamespace?.count || 0 }}
                                </Badge>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-75">
                            <DropdownMenuItem v-for="item in namespaces" :key="item.namespace" as-child>
                                <Link :href="index(item.namespace).url"
                                    class="w-full min-h-12 flex items-center justify-between cursor-pointer"
                                    preserve-state preserve-scroll>
                                    <span :class="[capitalize, 'font-medium']">
                                        {{ item.label }}
                                    </span>
                                    <Badge variant="secondary">
                                        {{ item.count }}
                                    </Badge>
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <div class="col-span-1 space-y-4 sm:col-span-8 md:col-span-9 xl:col-span-10">
                    <DataTableGroup v-model:search="search" :is-actually-loading="isActuallyLoading"
                        :columns="activeColumns" :data="lookups?.data ?? []" :has-active-filters="hasActiveFilters"
                        :total="lookups?.meta.total ?? 0" :page-size="lookups?.meta.per_page ?? 15"
                        :current-page="lookups?.meta.current_page ?? 1" :last-page="lookups?.meta.last_page ?? 1"
                        :order-by="filters.order_by" :order-direction="filters.order_direction" @reset="resetFilters"
                        @sort="handleSort" @row-click="handleViewDetails" @update:page="handlePageChange"
                        @update:pageSize="handlePageSizeChange">
                        <template #filters>
                            <DataTableSingleFilter title="Trạng thái" v-model="selectedStatus" :options="statusOptions"
                                :searchable="false" />
                        </template>
                    </DataTableGroup>
                </div>
            </div>
        </div>

        <!-- Lazy-loaded modal (handles loading state internally - NO Suspense) -->
        <LookupFormModal v-if="showFormModal" :open="showFormModal" :namespace="filters.namespace!"
            :display_namespace="label" :lookup="selectedLookup" @close="showFormModal = false" />

        <!-- Lookup Details Dialog -->
        <LookupDetailsDialog v-if="showDetailsDialog" :open="showDetailsDialog" :lookup="selectedLookup"
            @close="showDetailsDialog = false" @edit="handleEdit" @delete="confirmDelete"
            @preview-image="handlePreviewImage" />

        <DeleteConfirmation v-model:open="showDeleteDialog" title="Xác nhận xóa"
            :item-name="selectedLookup?.display_name"
            description="Bạn có chắc chắn muốn xóa tra cứu &quot;{name}&quot;? Hành động này không thể hoàn tác."
            @confirm="performDelete" />

        <!-- Universal Image Preview Dialog -->
        <ImagePreviewDialog :open="!!previewImageUrl" :src="previewImageUrl"
            @update:open="previewImageUrl = $event ? previewImageUrl : null" @close="previewImageUrl = null" />
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
