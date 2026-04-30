import { Package, MoreHorizontal, Truck, CheckCircle2, Repeat } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Shipment } from '@/types/order';

export function getColumns(
    onShow: (shipment: Shipment) => void,
    onShip: (shipment: Shipment) => void,
    onDeliver: (shipment: Shipment) => void,
    onResend: (shipment: Shipment) => void,
): ColumnDef<Shipment>[] {
    return [
        {
            accessorKey: 'shipment_number',
            header: 'Mã vận chuyển',
            size: 160,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs cursor-pointer' },
                () => row.original.shipment_number,
            ),
        },
        {
            accessorKey: 'order',
            header: 'Đơn hàng',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-xs font-mono' },
                row.original.order?.order_number ?? '—',
            ),
        },
        {
            accessorKey: 'origin_location',
            header: 'Nơi gửi',
            size: 160,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm text-muted-foreground' },
                row.original.origin_location?.name ?? '—',
            ),
        },
        {
            accessorKey: 'status',
            header: 'Trạng thái',
            size: 130,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const s = row.original;
                const icon = s.status === 'shipped' ? Truck : s.status === 'delivered' ? CheckCircle2 : Package;
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(icon, { class: `h-3.5 w-3.5 text-${s.status_color}-600` }),
                    h('span', { class: 'text-xs font-medium', style: { color: `var(--color-${s.status_color}-600)` } }, s.status_label),
                ]);
            },
        },
        {
            accessorKey: 'handled_by',
            header: 'Phụ trách',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm text-muted-foreground' },
                row.original.handled_by?.full_name ?? '—',
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
                const s = row.original;
                const canShip = s.status === 'pending';
                const canDeliver = s.status === 'shipped';
                const canResend = s.status === 'cancelled' && s.order?.status !== 'cancelled';
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onShow(s) }, () => 'Chi tiết'),
                            h(DropdownMenuSeparator),
                            canShip
                                ? h(DropdownMenuItem, { class: 'text-blue-600', onClick: () => onShip(s) }, () => [
                                    h(Truck, { class: 'mr-2 h-4 w-4' }),
                                    'Xuất kho',
                                ])
                                : null,
                            canDeliver
                                ? h(DropdownMenuItem, { class: 'text-green-600', onClick: () => onDeliver(s) }, () => [
                                    h(CheckCircle2, { class: 'mr-2 h-4 w-4' }),
                                    'Đã giao',
                                ])
                                : null,
                            canResend
                                ? h(DropdownMenuItem, { class: 'text-blue-600', onClick: () => onResend(s) }, () => [
                                    h(Repeat, { class: 'mr-2 h-4 w-4' }),
                                    'Gửi lại',
                                ])
                                : null,
                        ]),
                    ],
                });
            },
        },
    ];
}
