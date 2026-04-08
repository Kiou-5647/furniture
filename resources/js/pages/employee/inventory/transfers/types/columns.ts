import { Eye, MoreHorizontal } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { StockTransfer } from '@/types/stock-transfer';

const statusColorMap: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
    blue: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-800',
    green: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900 dark:text-green-300 dark:border-green-800',
    red: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900 dark:text-red-300 dark:border-red-800',
};

export function getColumns(
    onView: (transfer: StockTransfer) => void,
): ColumnDef<StockTransfer>[] {
    return [
        {
            accessorKey: 'transfer_number',
            header: 'Mã phiếu',
            size: 160,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'outline', class: 'font-mono text-xs' },
                    () => row.original.transfer_number,
                ),
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 140,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const colorClass =
                    statusColorMap[row.original.status_color] ??
                    statusColorMap.gray;
                return h(
                    Badge,
                    { variant: 'outline', class: `${colorClass} text-xs` },
                    () => row.original.status_label,
                );
            },
        },
        {
            accessorKey: 'from_location',
            header: 'Từ vị trí',
            size: 180,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) =>
                h('div', { class: 'flex flex-col' }, [
                    h(
                        'span',
                        { class: 'font-medium text-sm' },
                        row.original.from_location?.name ?? '—',
                    ),
                    h(
                        'span',
                        { class: 'text-xs text-muted-foreground' },
                        row.original.from_location?.code ?? '',
                    ),
                ]),
        },
        {
            accessorKey: 'to_location',
            header: 'Đến vị trí',
            size: 180,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) =>
                h('div', { class: 'flex flex-col' }, [
                    h(
                        'span',
                        { class: 'font-medium text-sm' },
                        row.original.to_location?.name ?? '—',
                    ),
                    h(
                        'span',
                        { class: 'text-xs text-muted-foreground' },
                        row.original.to_location?.code ?? '',
                    ),
                ]),
        },
        {
            accessorKey: 'items_count',
            header: 'Sản phẩm',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums' },
                    `${row.original.items_count ?? 0}`,
                ),
        },
        {
            accessorKey: 'requested_by',
            header: 'Người tạo',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm' },
                    row.original.requested_by?.full_name ?? '—',
                ),
        },
        {
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.created_at,
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
                const item = row.original;
                return h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(DropdownMenuTrigger, { asChild: true }, () =>
                                h(
                                    Button,
                                    {
                                        variant: 'ghost',
                                        class: 'h-8 w-8 p-0',
                                    },
                                    () =>
                                        h(MoreHorizontal, {
                                            class: 'h-4 w-4',
                                        }),
                                ),
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end', class: 'w-45' },
                                () => [
                                    h(DropdownMenuLabel, () => 'Thao tác'),
                                    h(
                                        DropdownMenuItem,
                                        { onClick: () => onView(item) },
                                        () => [
                                            h(Eye, {
                                                class: 'mr-2 h-4 w-4',
                                            }),
                                            'Xem chi tiết',
                                        ],
                                    ),
                                ],
                            ),
                        ],
                    },
                );
            },
        },
    ];
}
