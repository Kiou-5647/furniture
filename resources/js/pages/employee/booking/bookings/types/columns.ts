import { CheckCircle2, MoreHorizontal, Pencil, Trash2, XCircle } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { formatDateTime, formatPrice } from '@/lib';
import type { Booking } from '@/types';

export function getColumns(
    onEdit: (booking: Booking) => void,
    onConfirm: (booking: Booking) => void,
    onCancel: (booking: Booking) => void,
    onDelete: (booking: Booking) => void,
): ColumnDef<Booking>[] {
    return [
        {
            accessorKey: 'customer',
            header: 'Khách hàng',
            size: 200,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) =>
                h('div', {}, [
                    h(
                        'div',
                        { class: 'font-medium text-sm' },
                        row.original.customer?.name,
                    ),
                    h(
                        'div',
                        { class: 'text-xs text-muted-foreground' },
                        row.original.customer?.email,
                    ),
                ]),
        },
        {
            accessorKey: 'designer',
            header: 'Nhà thiết kế',
            size: 160,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm font-medium' },
                    row.original.designer?.name ?? 'Không tìm thấy',
                ),
        },
        {
            accessorKey: 'total_price', // Replaced 'service'
            header: 'Tổng tiền',
            size: 120,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm font-medium' },
                    formatPrice(row.original.total_price),
                ),
        },
        {
            accessorKey: 'start_at',
            header: 'Ngày/Giờ',
            size: 200,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) => {
                if (row.original.start_at) {
                    const formatted = formatDateTime(row.original.start_at);
                    return h(
                        'span',
                        { class: 'text-sm' },
                        formatted,
                    );
                }
                return h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    '—',
                );
            },
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 160,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const status = row.original.status;
                const colors: Record<string, string> = {
                    pending_deposit: 'bg-yellow-100 text-yellow-800',
                    pending_confirmation: 'bg-orange-100 text-orange-800',
                    confirmed: 'bg-blue-100 text-blue-800',
                    completed: 'bg-green-100 text-green-800',
                    cancelled: 'bg-gray-100 text-gray-800',
                };
                return h(
                    'span',
                    {
                        class: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${colors[status] || 'bg-gray-100'}`,
                    },
                    row.original.status_label,
                );
            },
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const booking = row.original;

                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onEdit(booking) }, () => [
                                h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                'Chi tiết',
                            ]),
                            h(DropdownMenuSeparator),
                            booking.can_confirm
                                ? h(DropdownMenuItem, { class: 'text-blue-600', onClick: () => onConfirm(booking) }, () => [
                                    h(CheckCircle2, { class: 'mr-2 h-4 w-4' }),
                                    'Xác nhận',
                                ])
                                : null,
                            booking.can_cancel
                                ? h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onCancel(booking) }, () => [
                                    h(XCircle, { class: 'mr-2 h-4 w-4' }),
                                    'Hủy lịch',
                                ])
                                : null,
                            h(DropdownMenuSeparator),
                            booking.can_delete ?
                                h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(booking) }, () => [
                                    h(Trash2, { class: 'mr-2 h-4 w-4' }),
                                    'Xóa',
                                ])
                                : null,
                        ]),
                    ],
                });
            },
        },
    ];
}
