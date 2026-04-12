import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { Payment } from '@/types/payment';

export function getColumns(): ColumnDef<Payment>[] {
    return [
        {
            accessorKey: 'transaction_id',
            header: 'Mã giao dịch',
            size: 180,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'font-mono text-sm' },
                    row.original.transaction_id ?? '—',
                ),
        },
        {
            accessorKey: 'customer',
            header: 'Khách hàng',
            size: 200,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm' },
                    row.original.customer?.name ?? '—',
                ),
        },
        {
            accessorKey: 'amount',
            header: 'Số tiền',
            size: 140,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm font-medium tabular-nums' },
                    `${Number(row.original.amount).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'total_allocated',
            header: 'Đã phân bổ',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    `${Number(row.original.total_allocated).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'gateway',
            header: 'Cổng thanh toán',
            size: 150,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'outline' },
                    () => row.original.gateway ?? '—',
                ),
        },
        {
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 160,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    row.original.created_at,
                ),
        },
    ];
}
