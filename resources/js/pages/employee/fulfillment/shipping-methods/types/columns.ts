import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { ShippingMethod } from '@/types/shipping-method';

export function getColumns(
    onEdit: (method: ShippingMethod) => void,
    onDelete: (method: ShippingMethod) => void,
): ColumnDef<ShippingMethod>[] {
    return [
        {
            accessorKey: 'code',
            header: 'Mã',
            size: 120,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'font-mono text-sm' },
                    row.original.code,
                ),
        },
        {
            accessorKey: 'name',
            header: 'Tên phương thức',
            size: 200,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm font-medium' },
                    row.original.name,
                ),
        },
        {
            accessorKey: 'price',
            header: 'Giá',
            size: 130,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums' },
                    `${Number(row.original.price).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'estimated_delivery_days',
            header: 'Dự kiến giao',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const days = row.original.estimated_delivery_days;
                return h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    days ? `${days} ngày` : '—',
                );
            },
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 130,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: row.original.is_active ? 'default' : 'secondary',
                        class: 'text-xs',
                    },
                    () => row.original.is_active ? 'Hoạt động' : 'Không hoạt động',
                ),
        },
        {
            accessorKey: 'shipments_count',
            header: 'Đơn vận chuyển',
            size: 130,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    row.original.shipments_count ?? 0,
                ),
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const method = row.original;
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-40' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onEdit(method) }, () => [
                                h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                'Sửa',
                            ]),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(method) }, () => [
                                h(Trash2, { class: 'mr-2 h-4 w-4' }),
                                'Xóa',
                            ]),
                        ]),
                    ],
                });
            },
        },
    ];
}
