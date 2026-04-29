<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, EyeOff, Plus, Settings2 } from '@lucide/vue';
import { capitalize, debounce } from 'lodash';
import { computed, ref, watch } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import DataTableSingleFilter from '@/components/custom/data-table/DataTableSingleFilter.vue';
import DeleteConfirmation from '@/components/custom/DeleteConfirmation.vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { cleanQuery, setCookie } from '@/lib/utils';
import { index as namespacesIndex } from '@/routes/employee/settings/lookupNamespaces';
import { index } from '@/routes/employee/settings/lookups';
import { destroy } from '@/routes/employee/settings/lookups';
import type {
    BreadcrumbItem,
    Lookup,
    LookupFilterData,
    LookupNamespace,
    LookupPagination,
} from '@/types';
import { getColumns } from './types/columns';

// Lazy-load modal (NO Suspense - safe for production)
const LookupFormModal = createLazyComponent(
    () => import('./components/LookupFormModal.vue'),
);

const props = defineProps<{
    namespaces: LookupNamespace[];
    categories: {id: string, display_name: string}[];
    lookups?: LookupPagination;
    filters: LookupFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tra cứu', href: index().url }];
const activeColumns = computed(() =>
    getColumns(
        props.filters.namespace!,
        handleEdit,
        confirmDelete,
        handlePreviewImage,
    ),
);
const showFormModal = ref(false);
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
    { label: 'Đang hiển thị', value: true, icon: Eye },
    { label: 'Đang ẩn', value: false, icon: EyeOff },
];

const selectedNamespace = ref<LookupNamespace | null>(
    props.namespaces.find((n) => n.slug === props.filters.namespace) ||
        props.namespaces.find((n) => n.slug === '_all') ||
        null,
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

    router.get(index(namespace ?? undefined), cleanQuery(rawQuery), {
        preserveState: true,
        replace: true,
    });
}, 500);

const namespaceMap = computed(() => {
    return props.namespaces.reduce(
        (acc, item) => {
            acc[item.slug] = item.label;
            return acc;
        },
        {} as Record<string, string>,
    );
});

const label = computed(() => {
    const ns = props.filters.namespace;
    return (ns && namespaceMap.value[ns]) ?? 'Không xác định';
});

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
        selectedNamespace.value =
            props.namespaces.find((n) => n.slug === props.filters.namespace) ||
            null;
    },
    { immediate: true },
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

    router.get(index(namespace ?? undefined).url, cleanQuery(rawQuery), {
        preserveState: true,
    });
}

function resetFilters() {
    updateSearch.cancel();

    const ns = props.filters.namespace;
    router.get(
        index(ns ?? undefined),
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

    router.get(index(namespace ?? undefined).url, cleanQuery(rawQuery), {
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
    router.get(index(namespace ?? undefined).url, cleanQuery(rawQuery), {
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
            <div class="space-y-2 md:flex md:items-center md:justify-between">
                <Heading
                    title="Quản lý tra cứu"
                    description="Định nghĩa các kiểu dữ liệu và giá trị cho hệ thống"
                />
                <div class="flex items-center gap-2 justify-end">
                    <Button
                        variant="outline"
                        @click="router.visit(namespacesIndex().url)"
                    >
                        <Settings2 class="mr-2 h-4 w-4" /> Quản lý danh mục
                    </Button>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" /> Thêm Tra cứu
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 items-start sm:grid-cols-12 sm:gap-3">
                <!-- Desktop: Card Sidebar -->
                <Card
                    class="col-span-1 hidden space-y-2 sm:col-span-5 sm:block md:col-span-4 lg:col-span-3 xl:col-span-2"
                >
                    <CardHeader>
                        <CardTitle class="text-lg font-medium"
                            >Danh mục</CardTitle
                        >
                    </CardHeader>
                    <CardContent class="grid gap-1">
                        <Link
                            v-for="item in namespaces"
                            :key="item.slug"
                            :href="
                                item.slug === '_all'
                                    ? index().url
                                    : index(item.slug).url
                            "
                            :class="[
                                'group flex items-center justify-between rounded-md px-1 py-2 transition-all duration-200',
                                (item.slug === '_all' && !filters.namespace) ||
                                filters.namespace === item.slug
                                    ? 'bg-primary/10 text-primary shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted',
                            ]"
                            preserve-state
                            preserve-scroll
                        >
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-medium">
                                    {{ item.label }}</span
                                >
                            </div>
                            <Badge
                                variant="secondary"
                                class="transition-colors group-hover:bg-background"
                            >
                                {{ item.count }}
                            </Badge>
                        </Link>
                    </CardContent>
                </Card>

                <div class="sm:hidden">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                class="w-full justify-between"
                            >
                                <span class="font-medium">
                                    Danh mục:
                                    {{
                                        selectedNamespace?.label ||
                                        'Chọn danh mục'
                                    }}
                                </span>
                                <Badge variant="secondary">
                                    {{ selectedNamespace?.count || 0 }}
                                </Badge>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-75">
                            <DropdownMenuItem
                                v-for="item in namespaces"
                                :key="item.slug"
                                as-child
                            >
                                <Link
                                    :href="
                                        item.slug === '_all'
                                            ? index().url
                                            : index(item.slug).url
                                    "
                                    class="flex min-h-12 w-full cursor-pointer items-center justify-between"
                                    preserve-state
                                    preserve-scroll
                                >
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

                <div
                    class="col-span-1 space-y-4 sm:col-span-7 md:col-span-8 lg:col-span-9 xl:col-span-10"
                >
                    <DataTableGroup
                        v-model:search="search"
                        :is-actually-loading="isActuallyLoading"
                        :columns="activeColumns"
                        :data="lookups?.data ?? []"
                        :has-active-filters="hasActiveFilters"
                        :total="lookups?.meta.total ?? 0"
                        :page-size="lookups?.meta.per_page ?? 15"
                        :current-page="lookups?.meta.current_page ?? 1"
                        :last-page="lookups?.meta.last_page ?? 1"
                        :order-by="filters.order_by"
                        :order-direction="filters.order_direction"
                        @reset="resetFilters"
                        @sort="handleSort"
                        @row-click="handleEdit"
                        @update:page="handlePageChange"
                        @update:pageSize="handlePageSizeChange"
                    >
                        <template #filters>
                            <DataTableSingleFilter
                                title="Trạng thái"
                                v-model="selectedStatus"
                                :options="statusOptions"
                                icon_location="end"
                                :searchable="false"
                            />
                        </template>
                    </DataTableGroup>
                </div>
            </div>
        </div>

        <!-- Lazy-loaded modal (handles loading state internally - NO Suspense) -->
        <LookupFormModal
            v-if="showFormModal"
            :open="showFormModal"
            :namespace_id="selectedNamespace?.id ?? ''"
            :display_namespace="label"
            :lookup="selectedLookup"
            :namespaces="namespaces"
            :categories="categories"
            @close="showFormModal = false"
            @delete="confirmDelete"
        />

        <DeleteConfirmation
            v-model:open="showDeleteDialog"
            title="Xác nhận xóa"
            :item-name="selectedLookup?.display_name"
            description='Bạn có chắc chắn muốn xóa tra cứu "{name}"? Hành động này không thể hoàn tác.'
            @confirm="performDelete"
        />

        <!-- Universal Image Preview Dialog -->
        <ImagePreviewDialog
            :open="!!previewImageUrl"
            :src="previewImageUrl"
            @update:open="previewImageUrl = $event ? previewImageUrl : null"
            @close="previewImageUrl = null"
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
