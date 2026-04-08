import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { StockMovement } from '@/types/stock-movement';

const typeColorMap: Record<string, string> = {
    green: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900 dark:text-green-300 dark:border-green-800',
    red: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900 dark:text-red-300 dark:border-red-800',
    yellow: 'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-300 dark:border-yellow-800',
};

export function getColumns(): ColumnDef<StockMovement>[] {
    return [
        {
            accessorKey: 'created_at',
            header: 'Thời gian',
            size: 150,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.created_at,
                ),
        },
        {
            accessorKey: 'type',
            header: 'Loại',
            size: 140,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const colorClass =
                    typeColorMap[row.original.type_color] ??
                    typeColorMap.yellow;
                return h(
                    Badge,
                    { variant: 'outline', class: `${colorClass} text-xs` },
                    () => row.original.type_label,
                );
            },
        },
        {
            accessorKey: 'variant',
            header: 'Sản phẩm',
            size: 500,
            enableSorting: false,
            enableHiding: false,
            cell: ({ row }) =>
                h('div', { class: 'flex flex-col' }, [
                    h('div', { class: 'flex items-center gap-2' }, [
                        h(
                            'span',
                            { class: 'font-medium text-sm' },
                            row.original.variant?.name ?? '—',
                        ),
                        h(
                            Badge,
                            {
                                variant: 'outline',
                                class: 'font-mono text-xs shrink-0',
                            },
                            () => row.original.variant?.sku ?? '',
                        ),
                    ]),
                    row.original.variant?.product_name
                        ? h(
                              'span',
                              { class: 'text-xs text-muted-foreground' },
                              row.original.variant.product_name,
                          )
                        : null,
                ]),
        },
        {
            accessorKey: 'location',
            header: 'Vị trí',
            size: 160,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) =>
                h('div', { class: 'flex flex-col' }, [
                    h(
                        'span',
                        { class: 'text-sm' },
                        row.original.location?.name ?? '—',
                    ),
                    h(
                        'span',
                        { class: 'text-xs text-muted-foreground' },
                        row.original.location?.code ?? '',
                    ),
                ]),
        },
        {
            accessorKey: 'quantity',
            header: 'Số lượng',
            size: 100,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm font-medium tabular-nums' },
                    `${row.original.quantity}`,
                ),
        },
        {
            accessorKey: 'quantity_before',
            header: 'Trước',
            size: 80,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    `${row.original.quantity_before}`,
                ),
        },
        {
            accessorKey: 'quantity_after',
            header: 'Sau',
            size: 80,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs tabular-nums' },
                    `${row.original.quantity_after}`,
                ),
        },
        {
            accessorKey: 'performed_by',
            header: 'Người thực hiện',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm' },
                    row.original.performed_by?.full_name ?? '—',
                ),
        },
        {
            accessorKey: 'notes',
            header: 'Ghi chú',
            size: 180,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) =>
                h(
                    'span',
                    {
                        class: 'text-xs text-muted-foreground truncate max-w-[160px] block',
                    },
                    row.original.notes ?? '—',
                ),
        },
    ];
}
