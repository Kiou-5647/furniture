import { MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { DesignService } from '@/types/design-service';

export function getColumns(
    onEdit: (service: DesignService) => void,
    onDelete: (service: DesignService) => void,
): ColumnDef<DesignService>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Tên dịch vụ',
            size: 240,
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
            accessorKey: 'type',
            header: 'Loại',
            size: 160,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: row.original.type === 'consultation' ? 'default' : 'secondary',
                        class: 'text-xs',
                    },
                    () => row.original.type_label,
                ),
        },
        {
            accessorKey: 'base_price',
            header: 'Giá cơ bản',
            size: 150,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums' },
                    `${Number(row.original.base_price).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'deposit_percentage',
            header: 'Đặt cọc',
            size: 100,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    row.original.deposit_percentage > 0
                        ? `${row.original.deposit_percentage}%`
                        : '—',
                ),
        },
        {
            accessorKey: 'estimated_minutes',
            header: 'Thời gian dự kiến',
            size: 160,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const mins = row.original.estimated_minutes;
                return h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    mins ? (mins >= 60 ? `${Math.floor(mins / 60)}h ${mins % 60 > 0 ? mins % 60 + 'p' : ''}` : `${mins} phút`) : '—',
                );
            },
        },
        {
            accessorKey: 'is_schedule_blocking',
            header: 'Chặn lịch',
            size: 120,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: row.original.is_schedule_blocking ? 'default' : 'outline',
                        class: 'text-xs',
                    },
                    () => row.original.is_schedule_blocking ? 'Có' : 'Không',
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
                const service = row.original;
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-40' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onEdit(service) }, () => [
                                h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                'Sửa',
                            ]),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(service) }, () => [
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
