import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatPrice } from '@/lib';
import { show as bookingShow } from '@/routes/employee/booking';
import { show as orderShow } from '@/routes/employee/sales/orders';

export interface CustomerOrder {
    id: string;
    order_number: string;
    total_amount: string;
    status: string;
    created_at: string;
    can_view: boolean
}

export interface CustomerBooking {
    id: string;
    booking_number: string;
    total_price: string;
    status: string;
    start_at: string;
    can_view: boolean
}

export function getCustomerOrderColumns(): ColumnDef<CustomerOrder>[] {
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
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-xs text-muted-foreground tabular-nums' },
                row.original.created_at || '—',
            ),
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'px-2 py-0.5 rounded-full border text-[10px] font-medium bg-background' },
                row.original.status,
            ),
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
                formatPrice(Number(row.original.total_amount))
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
                if (!row.original.can_view) return null;
                return h(
                    Button,
                    {
                        variant: "outline",
                        class: 'text-xs hover:underline',
                        onClick: () => {
                            window.location.href = orderShow(row.original.id).url;
                        }
                    },
                    () => 'Xem',
                );
            },
        },
    ];
}

export function getCustomerBookingColumns(): ColumnDef<CustomerBooking>[] {
    return [
        {
            accessorKey: 'booking_number',
            header: 'Mã đặt lịch',
            size: 160,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs cursor-pointer' },
                () => row.original.booking_number,
            ),
        },
        {
            accessorKey: 'start_at',
            header: 'Ngày hẹn',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-xs text-muted-foreground tabular-nums' },
                row.original.start_at || '—',
            ),
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'px-2 py-0.5 rounded-full border text-[10px] font-medium bg-background' },
                row.original.status,
            ),
        },
        {
            accessorKey: 'total_price',
            header: 'Tổng tiền',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'right' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm tabular-nums font-medium' },
                formatPrice(Number(row.original.total_price))
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
                if (!row.original.can_view) return null;
                return h(
                    Button,
                    {
                        variant: "outline",
                        class: 'text-xs hover:underline',
                        onClick: () => {
                            window.location.href = bookingShow(row.original.id).url;
                        }
                    },
                    () => 'Xem',
                );
            },
        },
    ];
}
