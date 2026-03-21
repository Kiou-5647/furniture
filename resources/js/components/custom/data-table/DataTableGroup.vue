<template>
    <div class="space-y-2">
        <div class="grid grid-cols-1 lg:grid-cols-12 items-center justify-between">
            <div class="col-span-1 lg:col-span-4 flex gap-4 mt-2 lg:mt-0" >
                <DataTableSearchBar :search="search" @update:search="emit('update:search', $event)" />

                <Sheet>
                    <SheetTrigger as-child>
                        <Button variant="outline" size="icon" class="lg:hidden shrink-0">
                            <ListFilter class="h-4 w-4" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[300px] sm:w-[400px]">
                        <SheetHeader >
                            <SheetTitle>Bộ lọc nâng cao</SheetTitle>
                        </SheetHeader>
                        <div class="grid px-6 gap-4 py-6">
                            <slot name="filters" />
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
            <div class="col-span-1 lg:col-span-8 flex gap-4 ml-auto mt-2 lg:mt-0">
                <DataTableClearFilter :has-active-filters="hasActiveFilters" @reset="emit('reset')" />

                <DataTableVisibility :table="table" />
            </div>


        </div>
        <div class="hidden lg:flex items-center justify-between gap-4">
            <slot name="filters" />
        </div>

        <Transition name="fade" mode="out-in">
            <div v-if="isActuallyLoading" class="space-y-4">
                <Skeleton v-for="i in 5" :key="i" class="h-8 w-full" />
            </div>
            <div v-else class="space-y-4">
                <DataTable :table="table" :order-by="order_by" :order-direction="order_direction"
                    @sort="emit('sort', $event)" />
                <DataTablePagination :total="total" :page-size="pageSize" :current-page="currentPage"
                    :last-page="lastPage" @update:page="emit('update:page', $event)"
                    @update:pageSize="emit('update:pageSize', $event)" />
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type {
    ColumnDef,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table';
import { computed, ref } from 'vue';
import DataTable from './DataTable.vue';
import DataTableSearchBar from './DataTableSearchBar.vue';
import DataTableVisibility from './DataTableVisibility.vue';
import DataTablePagination from './DataTablePagination.vue';
import DataTableClearFilter from './DataTableClearFilter.vue';
import { ListFilter } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';

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
