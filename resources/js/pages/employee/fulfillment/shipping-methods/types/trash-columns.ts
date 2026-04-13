import { RotateCcw, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { ShippingMethod } from '@/types/shipping-method';

export function getTrashColumns(
    onRestore: (method: ShippingMethod) => void,
    onForceDelete: (method: ShippingMethod) => void,
): ColumnDef<ShippingMethod>[] {
    return [
        {
            accessorKey: 'code',
            header: 'Mã',
            size: 120,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'font-mono text-sm text-muted-foreground' },
                    row.original.code,
                ),
        },
        {
            accessorKey: 'name',
            header: 'Tên phương thức',
            size: 200,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    row.original.name,
                ),
        },
        {
            accessorKey: 'price',
            header: 'Giá',
            size: 130,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    `${Number(row.original.price).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'deleted_at',
            header: 'Ngày xóa',
            size: 160,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.deleted_at,
                ),
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 120,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const method = row.original;
                return h('div', { class: 'flex items-center justify-center gap-1' }, [
                    h(
                        Button,
                        { variant: 'ghost', size: 'sm', class: 'h-7 text-xs text-green-600', onClick: () => onRestore(method) },
                        () => [h(RotateCcw, { class: 'mr-1 h-3.5 w-3.5' }), 'Khôi phục'],
                    ),
                    h(
                        Button,
                        { variant: 'ghost', size: 'sm', class: 'h-7 text-xs text-destructive', onClick: () => onForceDelete(method) },
                        () => [h(Trash2, { class: 'mr-1 h-3.5 w-3.5' }), 'Xóa vĩnh viễn'],
                    ),
                ]);
            },
        },
    ];
}
