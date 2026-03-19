<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';

interface DataTableProps {
    columns: ColumnDef<any, any>[];
    data: any[];
    order_by?: string;
    order_direction?: string;
}

const props = defineProps<DataTableProps>();

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
});

const emit = defineEmits(['sort']);

function handleHeaderClick(columnId: string) {
    if (['actions', 'color'].includes(columnId)) return;
    emit('sort', columnId);
}
</script>
<template>
    <div class="overflow-x-auto rounded-md border bg-card">
        <Table class="table-fixed w-full">
            <TableHeader>
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id"
                        @click="handleHeaderClick(header.id)" :style="{
                            width: header.column.getSize() !== 0 ? `${header.column.getSize()}px` : 'auto'
                        }" :class="[
                            'font-semibold transition-colors truncate px-5',
                            header.column.columnDef.meta?.align === 'center' ? 'text-center' : 'text-left',
                            !['actions', 'color'].includes(header.id) ? 'cursor-pointer hover:bg-muted/50 hover:text-foreground' : ''
                        ]">
                        <div :class="[
                            'flex items-center gap-2',
                            header.column.columnDef.meta?.align === 'center' ? 'justify-center' : 'justify-start'
                        ]">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                :props="header.getContext()" />
                            <!-- Sorting Icons Logic -->
                            <template v-if="!['actions', 'color'].includes(header.id)">
                                <ArrowUp v-if="order_by === header.id && order_direction === 'asc'"
                                    class="h-4 w-4 text-primary" />
                                <ArrowDown v-else-if="order_by === header.id && order_direction === 'desc'"
                                    class="h-4 w-4 text-primary" />
                                <ArrowUpDown v-else class="h-3 w-3 text-muted-foreground/50" />
                            </template>
                        </div>
                    </TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                        class="transition-colors hover:bg-muted">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"
                            :style="{ width: cell.column.getSize() !== 150 ? `${cell.column.getSize()}px` : 'auto' }">
                            <div :class="[
                                'flex items-center w-full',
                                cell.column.columnDef.meta?.align === 'center' ? 'justify-center' : 'justify-start'
                            ]">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </div>
                        </TableCell>
                    </TableRow>
                </template>

                <template v-else>
                    <TableRow>
                        <TableCell :colspan="columns.length" class="h-32 text-center text-muted-foreground italic">
                            Không tìm thấy dữ liệu nào.
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
