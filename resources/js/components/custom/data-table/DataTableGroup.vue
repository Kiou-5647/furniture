<template>
    <div class="@container space-y-2">
        <!-- Toolbar: Search + Filters + Actions -->
        <div class="flex flex-col gap-2 @lg:flex-row">
            <div class="flex w-full min-w-0 gap-2">
                <DataTableSearchBar :search="search" @update:search="emit('update:search', $event)" />
                <!-- Mobile: Sheet for filters -->
                <Sheet>
                    <SheetTrigger as-child>
                        <Button variant="outline" size="icon" class="shrink-0 @lg:hidden">
                            <ListFilter class="h-4 w-4" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[300px] @md:w-[400px]">
                        <SheetHeader>
                            <SheetTitle>Bộ lọc</SheetTitle>
                            <SheetDescription>Chọn các bộ lọc để áp dụng cho bảng dữ liệu.</SheetDescription>
                        </SheetHeader>
                        <div class="grid px-4 gap-4 py-4">
                            <slot name="filters" />
                        </div>
                    </SheetContent>
                </Sheet>
            </div>

            <div class="flex gap-2 justify-between @lg:justify-end">
                <DataTableClearFilter :has-active-filters="hasActiveFilters" @reset="emit('reset')" />
                <DataTableVisibility :table="table" />
            </div>
        </div>

        <!-- Desktop: Inline filters -->
        <div class="hidden gap-2 overflow-auto @lg:flex">
            <slot name="filters" class="flex"/>
        </div>

        <!-- Table with loading state -->
        <Transition name="fade" mode="out-in">
            <div v-if="isActuallyLoading" class="space-y-4">
                <Skeleton v-for="i in 5" :key="i" class="h-8 w-full" />
            </div>
            <div v-else class="space-y-4">
                <DataTable :table="table" :order-by="order_by" :order-direction="order_direction"
                    @sort="emit('sort', $event)" @row-click="emit('row-click', $event)" />
                <DataTablePagination :total="total" :page-size="pageSize" :current-page="currentPage"
                    :last-page="lastPage" @update:page="emit('update:page', $event)"
                    @update:pageSize="emit('update:pageSize', $event)" />
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ListFilter } from '@lucide/vue';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type {
    ColumnDef,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import DataTable from './DataTable.vue';
import DataTableClearFilter from './DataTableClearFilter.vue';
import DataTablePagination from './DataTablePagination.vue';
import DataTableSearchBar from './DataTableSearchBar.vue';
import DataTableVisibility from './DataTableVisibility.vue';

const props = defineProps<{
    columns: ColumnDef<any, any>[];
    data: any[];

    search: string;
    hasActiveFilters: boolean;
    isActuallyLoading: boolean;

    total: number;
    pageSize: number;
    currentPage: number;
    lastPage: number;

    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
}>();

const emit = defineEmits([
    'update:search',
    'reset',
    'sort',
    'row-click',
    'update:page',
    'update:pageSize',
]);

const columnVisibility = ref<VisibilityState>({});

const sorting = computed<SortingState>(() => {
    if (!props.order_by) return [];
    return [{ id: props.order_by, desc: props.order_direction === 'desc' }];
});

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
    },
    getCoreRowModel: getCoreRowModel(),
    onColumnVisibilityChange: (updater) => {
        if (typeof updater === 'function') {
            columnVisibility.value = updater(columnVisibility.value);
        } else {
            columnVisibility.value = updater;
        }
    },
});
</script>

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
