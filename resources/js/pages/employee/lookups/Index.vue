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
import { index } from '@/routes/employee/lookups';
import { capitalize, debounce } from 'lodash';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { X, Plus, Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import DataTable from '@/components/custom/data-table/DataTable.vue';
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
import { destroy } from '@/routes/employee/lookups';
import DataTablePagination from '@/components/custom/data-table/DataTablePagination.vue';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { VisuallyHidden } from 'reka-ui';

const props = defineProps<{
    namespaces: LookupNamespace[];
    lookups?: LookupPagination;
    filters: LookupFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tra cứu', href: index() }];
const activeColumns = computed(() => getColumns(props.filters.namespace!, handleEdit, confirmDelete, handlePreviewImage));
const search = ref(props.filters.search ?? '');

const isActuallyLoading = ref(true);
let loadingTimeout: any = null;

const showFormModal = ref(false);
const showDeleteDialog = ref(false);
const selectedLookup = ref<Lookup | null>(null);

const updateSearch = debounce((value: string) => {
    router.get(
        index(),
        {
            ...props.filters,
            search: value,
            page: 1,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}, 300);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || !!props.filters.order_by;
});

const previewImageUrl = ref<string | null>(null);


watch(search, (newValue) => {
    if (newValue === (props.filters.search ?? '')) return;
    updateSearch(newValue);
});

watch(() => props.filters.search, (newSearch) => {
    search.value = newSearch ?? '';
});

watch(
    () => [props.filters],
    () => { isActuallyLoading.value = true; }
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

function getDisplayNamespace(namespace: string) {
    const labels: Record<string, string> = {
        'mau-sac': 'Màu sắc',
        'phong': 'Phòng',
        'phong-cach': 'Phong cách',
        'tinh-nang': 'Tính năng'
    };
    return labels[namespace] || capitalize(namespace);
}

function handleSort(column: string) {
    let nextDirection: 'asc' | 'desc' | null = 'asc';

    if (props.filters.order_by === column) {
        if (props.filters.order_direction === 'asc') nextDirection = 'desc';
        else if (props.filters.order_direction === 'desc') nextDirection = null;
    }

    router.get(
        index().url,
        {
            ...props.filters,
            order_by: nextDirection ? column : undefined,
            order_direction: nextDirection ?? undefined,
            page: 1,
        },
        { preserveState: true }
    );
}

function resetFilters() {
    updateSearch.cancel();
    router.get(
        index().url,
        { namespace: props.filters.namespace },
        { preserveState: true }
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
    router.get(index().url, {
        ...props.filters,
        page
    }, { preserveState: true, preserveScroll: true });
}

function handlePageSizeChange(per_page: number) {
    router.get(index().url, {
        ...props.filters,
        per_page,
        page: 1 // Reset to page 1 when changing size
    }, { preserveState: true, preserveScroll: true });
}

function handlePreviewImage(url: string) {
    previewImageUrl.value = url;
}



</script>
<template>

    <Head title="Tra cứu" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Quản lý tra cứu" description="Định nghĩa các kiểu dữ liệu và giá trị cho hệ thống" />
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" /> Thêm Tra cứu
                </Button>
            </div>

            <div class="grid items-start grid-cols-1 lg:grid-cols-12 lg:gap-3">
                <Card class="col-span-1 lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="text-lg font-medium">Danh mục</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-1">
                        <Link v-for="item in namespaces" :key="item.namespace" :href="index({ query: { namespace: item.namespace } })
                            .url
                            " :class="[
                                'group flex items-center justify-between rounded-md px-3 py-2 transition-all duration-200',
                                filters.namespace === item.namespace
                                    ? 'bg-primary/10 text-primary shadow-sm'
                                    : 'text-muted-foreground hover:bg-muted',
                            ]" preserve-state preserve-scroll>
                            <span :class="[capitalize, 'font-medium']">
                                {{ getDisplayNamespace(item.namespace) }}</span>
                            <Badge variant="secondary" class="transition-colors group-hover:bg-background">{{ item.count
                            }}</Badge>
                        </Link>
                    </CardContent>
                </Card>

                <div class="col-span-1 lg:col-span-9 space-y-4">
                    <!-- Filters Bar -->
                    <div class="flex items-center gap-4">
                        <div class="relative mt-3 w-full max-w-sm lg:mt-0">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Tìm kiếm theo tên hoặc khóa..." class="pl-8" />
                        </div>
                        <Transition name="fade">
                            <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="resetFilters"
                                class="h-9 mt-3 px-2 lg:mt-0 lg:px-3 text-muted-foreground hover:text-foreground">
                                Xóa bộ lọc
                                <X class="ml-2 h-4 w-4" />
                            </Button>
                        </Transition>
                    </div>

                    <!-- Datatable -->
                    <Transition name="fade" mode="out-in">
                        <div v-if="isActuallyLoading" class="space-y-4">
                            <Skeleton v-for="i in 5" :key="i" class="h-8 w-full" />
                        </div>
                        <DataTable v-else :columns="activeColumns" :data="lookups!.data" :order-by="filters.order_by"
                            :order-direction="filters.order_direction" @sort="handleSort" />
                    </Transition>
                    <DataTablePagination v-if="lookups" :total="lookups.total" :page-size="lookups.per_page"
                        :current-page="lookups.current_page" :last-page="lookups.last_page"
                        @update:page="handlePageChange" @update:pageSize="handlePageSizeChange" />
                </div>
            </div>
        </div>
        <LookupFormModal :open="showFormModal" :namespace="filters.namespace!"
            :display_namespace="getDisplayNamespace(filters.namespace!)" :lookup="selectedLookup"
            @close="showFormModal = false" />

        <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Xác nhận xóa</AlertDialogTitle>
                    <AlertDialogDescription>
                        Bạn có chắc chắn muốn xóa tra cứu "{{ selectedLookup?.display_name }}"?
                        Hành động này không thể hoàn tác.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="selectedLookup = null">Hủy</AlertDialogCancel>
                    <AlertDialogAction @click="performDelete" class="bg-destructive hover:bg-destructive/90">
                        Xóa ngay
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
        <Dialog :open="!!previewImageUrl" @update:open="previewImageUrl = null">
            <DialogContent class="max-w-[90vw] max-h-[90vh] border-none bg-transparent p-0 shadow-none sm:rounded-none">
                <VisuallyHidden>
                    <DialogTitle />
                    <DialogDescription />
                </VisuallyHidden>
                <div class="relative flex items-center justify-center">
                    <img
                        :src="previewImageUrl!"
                        class="rounded-lg w-full h-full shadow-2xl"
                    />

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
