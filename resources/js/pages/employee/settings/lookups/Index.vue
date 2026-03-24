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
import LookupFormModal from './LookupFormModal.vue';
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
import { destroy } from '@/routes/employee/settings/lookups';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { VisuallyHidden } from 'reka-ui';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import { cleanQuery } from '@/lib/utils';
import DataTableFacetedFilter from '@/components/custom/data-table/DataTableFacetedFilter.vue';

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

const selectedStatuses = ref<boolean[]>(
    props.filters.is_active === undefined || props.filters.is_active === null
        ? []
        : [props.filters.is_active],
);

const statusOptions = [
    { label: 'Đang hiện', value: true, icon: Eye },
    { label: 'Đang ẩn', value: false, icon: EyeOff },
];
const updateSearch = debounce(() => {
    const { namespace, ...restFilters } = props.filters;

    let isActiveValue: boolean | undefined = undefined;
    if (selectedStatuses.value.length === 1) {
        isActiveValue = selectedStatuses.value[0];
    }
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

watch(selectedStatuses, () => {
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

    router.delete(destroy(selectedLookup.value.id).url, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedLookup.value = null;
        },
    });
}

function handlePageChange(page: number) {
    const { namespace, ...restFilters } = props.filters;

    let isActiveValue: boolean | undefined = undefined;
    if (selectedStatuses.value.length === 1) {
        isActiveValue = selectedStatuses.value[0];
    }

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
    const { namespace, ...restFilters } = props.filters;

    let isActiveValue: boolean | undefined = undefined;
    if (selectedStatuses.value.length === 1) {
        isActiveValue = selectedStatuses.value[0];
    }

    const rawQuery = {
        ...restFilters,
        is_active: isActiveValue,
        per_page,
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

            <div class="grid grid-cols-1 items-start lg:grid-cols-12 lg:gap-3">
                <Card class="col-span-1 lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="text-lg font-medium">Danh mục</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-1">
                        <Link v-for="item in namespaces" :key="item.namespace" :href="index(item.namespace).url" :class="[
                            'group flex items-center justify-between rounded-md px-3 py-2 transition-all duration-200',
                            filters.namespace === item.namespace
                                ? 'bg-primary/10 text-primary shadow-sm'
                                : 'text-muted-foreground hover:bg-muted',
                        ]" preserve-state preserve-scroll>
                            <span :class="[capitalize, 'font-medium']">
                                {{ item.label }}</span>
                            <Badge variant="secondary" class="transition-colors group-hover:bg-background">
                                {{ item.count }}
                            </Badge>
                        </Link>
                    </CardContent>
                </Card>

                <div class="col-span-1 space-y-4 lg:col-span-9">
                    <DataTableGroup v-model:search="search" :is-actually-loading="isActuallyLoading"
                        :columns="activeColumns" :data="lookups?.data ?? []" :has-active-filters="hasActiveFilters"
                        :total="lookups?.meta.total ?? 0" :page-size="lookups?.meta.per_page ?? 15"
                        :current-page="lookups?.meta.current_page ?? 1" :last-page="lookups?.meta.last_page ?? 1"
                        :order-by="filters.order_by" :order-direction="filters.order_direction" @reset="resetFilters"
                        @sort="handleSort" @update:page="handlePageChange" @update:pageSize="handlePageSizeChange">
                        <template #filters>
                            <DataTableFacetedFilter title="Trạng thái" v-model="selectedStatuses"
                                :options="statusOptions" />
                        </template>
                    </DataTableGroup>
                </div>
            </div>
        </div>
        <LookupFormModal :open="showFormModal" :namespace="filters.namespace!" :display_namespace="label"
            :lookup="selectedLookup" @close="showFormModal = false" />

        <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Xác nhận xóa</AlertDialogTitle>
                    <AlertDialogDescription>
                        Bạn có chắc chắn muốn xóa tra cứu "{{
                            selectedLookup?.display_name
                        }}"? Hành động này không thể hoàn tác.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="selectedLookup = null">Hủy</AlertDialogCancel>
                    <AlertDialogAction @click="performDelete" class="bg-destructive hover:bg-destructive/90">
                        Xóa
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
        <Dialog :open="!!previewImageUrl" @update:open="previewImageUrl = null">
            <DialogContent class="w-[90vw] h-[90vh] border-none bg-transparent p-0 shadow-none sm:rounded-none">
                <VisuallyHidden>
                    <DialogTitle />
                    <DialogDescription />
                </VisuallyHidden>
                <div class="relative flex items-center justify-center">
                    <img :src="previewImageUrl!" class="h-auto w-auto rounded-lg shadow-2xl" />
                </div>
            </DialogContent>
        </Dialog>
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
