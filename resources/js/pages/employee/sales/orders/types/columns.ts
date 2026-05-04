import { CheckCircle2, CircleDashed, MoreHorizontal, Pencil, Trash2, XCircle } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Order } from '@/types/order';

export function getColumns(
    onEdit: (order: Order) => void,
    onCancel: (order: Order) => void,
    onComplete: (order: Order) => void,
    onDelete: (order: Order) => void,
): ColumnDef<Order>[] {
    return [
        {
            accessorKey: 'order_number',
            header: 'Mã đơn hàng',
            size: 160,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs cursor-pointer' },
                () => row.original.order_number,
            ),
        },
        {
            accessorKey: 'customer',
            header: 'Khách hàng',
            size: 200,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const customer = row.original.customer;
                return h('div', { class: 'flex flex-col gap-0.5' }, [
                    h('span', { class: 'font-medium' }, row.original.guest_name ?? customer?.name ?? 'Khách vãng lai' ),
                    customer?.email
                        ? h('span', { class: 'text-xs text-muted-foreground truncate max-w-[180px]' }, customer.email)
                        : null,
                ]);
            },
        },
        {
            accessorKey: 'total_amount',
            header: 'Tổng tiền',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'right' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm tabular-nums font-medium' },
                `${Number(row.original.total_amount).toLocaleString('vi-VN')}đ`,
            ),
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const order = row.original;
                const icon = order.status === 'cancelled'
                    ? XCircle
                    : order.status === 'completed'
                        ? CheckCircle2
                        : CircleDashed;
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(icon, {
                        class: `h-3.5 w-3.5 ${order.status_color ?? 'text-muted-foreground'}`,
                    }),
                    h('span', {
                        class: `text-xs font-medium`,
                        style: { color: order.status_color || undefined },
                    }, order.status_label),
                ]);
            },
        },
        {
            accessorKey: 'total_items',
            header: 'SL',
            size: 60,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm tabular-nums' },
                row.original.total_items,
            ),
        },
        {
            accessorKey: 'accepted_by',
            header: 'Người nhận',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm text-muted-foreground' },
                row.original.accepted_by ?? '—',
            ),
        },
        {
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
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
                const order = row.original;
                const canCancel = !['completed', 'cancelled'].includes(order.status);
                const canComplete = order.status === 'processing';
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onEdit(order) }, () => [
                                h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                'Chi tiết',
                            ]),
                            h(DropdownMenuSeparator),
                            canCancel
                                ? h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onCancel(order) }, () => [
                                    h(XCircle, { class: 'mr-2 h-4 w-4' }),
                                    'Hủy đơn',
                                ])
                                : null,
                            canComplete
                                ? h(DropdownMenuItem, { class: 'text-green-600', onClick: () => onComplete(order) }, () => [
                                    h(CheckCircle2, { class: 'mr-2 h-4 w-4' }),
                                    'Hoàn thành',
                                ])
                                : null,
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(order) }, () => [
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
