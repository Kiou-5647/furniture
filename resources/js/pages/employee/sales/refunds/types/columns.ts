import { CheckCircle2, Clock, XCircle, MoreHorizontal } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Refund } from '@/types/refund';

export function getColumns(
    onShow: (refund: Refund) => void,
    onApprove: (refund: Refund) => void,
    onReject: (refund: Refund) => void,
): ColumnDef<Refund>[] {
    return [
        {
            accessorKey: 'order_number',
            header: 'Mã đơn hàng',
            size: 140,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'font-mono text-xs' }, row.original.order_number),
        },
        {
            accessorKey: 'amount',
            header: 'Số tiền',
            size: 140,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'right' },
            cell: ({ row }) => h('span', { class: 'text-sm font-medium tabular-nums' }, `${Number(row.original.amount).toLocaleString('vi-VN')}đ`),
        },
        {
            accessorKey: 'reason',
            header: 'Lý do',
            size: 200,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) => h('span', { class: 'text-sm truncate block max-w-[200px]' }, row.original.reason || '—'),
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 140,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const s = row.original;
                const icon = s.status === 'completed' ? CheckCircle2 : s.status === 'rejected' ? XCircle : Clock;
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(icon, { class: `h-3.5 w-3.5 text-${s.status_color}-600` }),
                    h('span', { class: 'text-xs font-medium' }, s.status_label),
                ]);
            },
        },
        {
            accessorKey: 'requested_by',
            header: 'Người tạo',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.requested_by?.full_name ?? '—'),
        },
        {
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground tabular-nums' }, row.original.created_at),
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const r = row.original;
                const canApprove = r.status === 'pending';
                const canReject = r.status === 'pending';
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () => h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' }))),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onShow(r) }, () => 'Chi tiết'),
                            canApprove || canReject ? h(DropdownMenuSeparator) : null,
                            canApprove ? h(DropdownMenuItem, { class: 'text-green-600', onClick: () => onApprove(r) }, () => [h(CheckCircle2, { class: 'mr-2 h-4 w-4' }), 'Duyệt']) : null,
                            canReject ? h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onReject(r) }, () => [h(XCircle, { class: 'mr-2 h-4 w-4' }), 'Từ chối']) : null,
                        ]),
                    ],
                });
            },
        },
    ];
}
