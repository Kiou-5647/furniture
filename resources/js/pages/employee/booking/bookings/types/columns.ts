import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { formatDateTime, formatSessionDate } from '@/lib/date-utils';
import type { Booking } from '@/types/booking';

export function getColumns(
    onConfirm: (booking: Booking) => void,
    onCancel: (booking: Booking) => void,
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
                        row.original.customer.name,
                    ),
                    h(
                        'div',
                        { class: 'text-xs text-muted-foreground' },
                        row.original.customer.email,
                    ),
                ]),
        },
        {
            accessorKey: 'service',
            header: 'Dịch vụ',
            size: 180,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) =>
                h('div', {}, [
                    h(
                        'div',
                        { class: 'font-medium text-sm' },
                        row.original.service.name,
                    ),
                    h(
                        'div',
                        { class: 'text-xs text-muted-foreground' },
                        `${Number(row.original.service.base_price).toLocaleString('vi-VN')}đ`,
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
                    row.original.designer.name,
                ),
        },
        {
            accessorKey: 'sessions',
            header: 'Ngày/Giờ',
            size: 200,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'left' },
            cell: ({ row }) => {
                const sessions = row.original.sessions;
                if (sessions && sessions.length > 0) {
                    const first = sessions[0];
                    const formatted = formatSessionDate(
                        first.date,
                        first.start_hour,
                        first.end_hour,
                    );
                    const extra = sessions.length > 1
                        ? ` +${sessions.length - 1}`
                        : '';
                    return h(
                        'span',
                        { class: 'text-sm' },
                        `${formatted}${extra}`,
                    );
                }
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
            accessorKey: 'actions',
            header: 'Thao tác',
            size: 180,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const booking = row.original;
                const children = [];

                if (booking.can_confirm) {
                    children.push(
                        h(
                            'button',
                            {
                                class: 'text-sm text-green-600 hover:underline mx-1',
                                onClick: (e: Event) => {
                                    e.stopPropagation();
                                    onConfirm(booking);
                                },
                            },
                            'Xác nhận',
                        ),
                    );
                }

                if (booking.can_cancel) {
                    children.push(
                        h(
                            'button',
                            {
                                class: 'text-sm text-red-600 hover:underline mx-1',
                                onClick: (e: Event) => {
                                    e.stopPropagation();
                                    onCancel(booking);
                                },
                            },
                            'Hủy',
                        ),
                    );
                }

                return h(
                    'div',
                    { class: 'flex justify-center' },
                    children.length > 0 ? children : '—',
                );
            },
        },
    ];
}
