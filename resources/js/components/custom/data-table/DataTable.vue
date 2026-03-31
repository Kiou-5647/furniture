<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { FlexRender } from '@tanstack/vue-table';
import type { Table as VueTable } from '@tanstack/vue-table';
import { ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';

const props = defineProps<{
    table: VueTable<any>;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
}>();
const emit = defineEmits(['sort', 'row-click']);

function handleHeaderClick(header: any) {
    const columnId = header.column.id;
    if (!header.column.getCanSort()) return;

    emit('sort', columnId);
}

function handleRowClick(row: any, event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (target.closest('button, a, input, [role="menuitem"], [data-radix-collection-item]')) {
        return;
    }
    emit('row-click', row.original);
}
</script>

<template>
    <div class="w-full space-y-4">
        <slot name="toolbar" :table="table" />
        <!-- Table Container -->
        <div class="rounded-md border bg-card">
            <Table class="w-full table-fixed">
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" :style="{
                            width: `${header.column.getSize()}px`,
                        }" :class="[
                            'h-11 px-4 font-bold transition-colors',
                            header.column.columnDef.meta?.align === 'center'
                                ? 'text-center'
                                : 'text-left',
                            header.column.getCanSort()
                                ? 'cursor-pointer hover:bg-muted/50 hover:text-foreground'
                                : '',
                        ]" @click="handleHeaderClick(header)">
                            <div :class="[
                                'flex items-center gap-2',
                                header.column.columnDef.meta?.align ===
                                    'center'
                                    ? 'justify-center'
                                    : 'justify-start',
                            ]">
                                <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                    :props="header.getContext()" />

                                <!-- Icons Sắp xếp -->
                                <template v-if="header.column.getCanSort()">
                                    <ArrowUp v-if="
                                        order_by === header.id &&
                                        order_direction === 'asc'
                                    " class="h-3.5 w-3.5 text-primary" />
                                    <ArrowDown v-else-if="
                                        order_by === header.id &&
                                        order_direction === 'desc'
                                    " class="h-3.5 w-3.5 text-primary" />
                                    <ArrowUpDown v-else class="h-3 w-3 text-muted-foreground/30" />
                                </template>
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                            class="cursor-pointer transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
                            @click="handleRowClick(row, $event)">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" :style="{
                                width: `${cell.column.getSize()}px`,
                            }" :class="[
                                'p-3 overflow-hidden',
                                cell.column.columnDef.meta?.align ===
                                    'center'
                                    ? 'text-center'
                                    : 'text-left',
                            ]">
                                <div :class="[
                                    'flex w-full items-center',
                                    cell.column.columnDef.meta?.align ===
                                        'center'
                                        ? 'justify-center'
                                        : 'justify-start',
                                ]">
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>

                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="table.getAllColumns().length"
                                class="h-32 text-center text-muted-foreground italic">
                                Không tìm thấy kết quả nào phù hợp.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
