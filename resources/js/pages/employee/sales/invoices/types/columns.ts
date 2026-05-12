import { Eye, MoreHorizontal, Trash2, XCircle } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Invoice } from '@/types';

export function getColumns(
    onShow: (invoice: Invoice) => void,
    onCancel: (invoice: Invoice) => void,
    onDelete: (invoice: Invoice) => void,
): ColumnDef<Invoice>[] {
    return [
        {
            accessorKey: 'invoice_number',
            header: 'Mã hóa đơn',
            size: 180,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'outline', class: 'font-mono text-xs cursor-pointer hover:bg-accent' },
                    () => row.original.invoice_number,
                ),
        },
        {
            accessorKey: 'invoiceable',
            header: 'Liên kết',
            size: 160,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const inv = row.original.invoiceable;
                if (!inv) return h('span', { class: 'text-sm text-muted-foreground' }, '—');
                return h(
                    'div',
                    { class: 'flex flex-col items-center gap-0.5' },
                    [
                        h(
                            Badge,
                            { variant: inv.type === 'Order' ? 'default' : 'secondary', class: 'text-xs' },
                            () => inv.type === 'Order' ? 'Đơn hàng' : 'Đặt lịch',
                        ),
                        h(
                            'span',
                            { class: 'text-xs font-mono text-muted-foreground' },
                            inv.number,
                        ),
                    ],
                );
            },
        },
        {
            accessorKey: 'type',
            header: 'Loại',
            size: 160,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const s = row.original;
                const colorMap: Record<string, string> = {
                    deposit: 'orange',
                    final_balance: 'blue',
                    full: 'green',
                };
                const color = colorMap[s.type] ?? 'gray';
                return h(
                    Badge,
                    { variant: 'outline', class: 'text-xs', style: { borderColor: `var(--color-${color}-300)`, color: `var(--color-${color}-700)` } },
                    () => s.type_label,
                );
            },
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
                return h(
                    Badge,
                    {
                        variant: 'outline',
                        class: 'text-xs',
                        style: { borderColor: `var(--color-${s.status_color}-300)`, color: `var(--color-${s.status_color}-700)` },
                    },
                    () => s.status_label,
                );
            },
        },
        {
            accessorKey: 'amount_due',
            header: 'Phải thu',
            size: 140,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm font-medium tabular-nums' },
                    `${Number(row.original.amount_due).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'amount_paid',
            header: 'Đã thu',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm tabular-nums text-muted-foreground' },
                    `${Number(row.original.amount_paid).toLocaleString('vi-VN')}đ`,
                ),
        },
        {
            accessorKey: 'remaining_balance',
            header: 'Còn lại',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const balance = Number(row.original.remaining_balance);
                const color = balance > 0 ? 'text-red-600' : 'text-green-600';
                return h(
                    'span',
                    { class: `text-sm font-medium tabular-nums ${color}` },
                    `${balance.toLocaleString('vi-VN')}đ`,
                );
            },
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
                const invoice = row.original;

                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            invoice.can_view
                                ? h(DropdownMenuItem, { onClick: () => onShow(invoice) }, () => [
                                    h(Eye, { class: 'mr-2 h-4 w-4' }),
                                    'Chi tiết',
                                ])
                                : null,
                            h(DropdownMenuSeparator),
                            invoice.can_cancel
                                ? h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onCancel(invoice) }, () => [
                                    h(XCircle, { class: 'mr-2 h-4 w-4' }),
                                    'Hủy hóa đơn',
                                ])
                                : null,
                            h(DropdownMenuSeparator),
                            invoice.can_delete
                                ? h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(invoice) }, () => [
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
