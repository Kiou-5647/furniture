import { CheckCircle2, CircleDashed, MoreHorizontal, Eye, UserX } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Customer } from '@/types/customer';

export function getColumns(
    onView: (customer: Customer) => void,
    onDeactivate: (customer: Customer) => void,
): ColumnDef<Customer>[] {
    return [
        {
            accessorKey: 'full_name',
            header: 'Khách hàng',
            size: 240,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                const avatarUrl = item.avatar_url;
                return h('div', { class: 'flex items-center gap-3' }, [
                    avatarUrl
                        ? h('img', {
                            src: avatarUrl,
                            class: 'h-9 w-9 shrink-0 rounded-full object-cover',
                        })
                        : h('div', {
                            class: 'flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted text-sm font-medium',
                        }, (item.full_name ?? 'K').charAt(0).toUpperCase()),
                    h('div', { class: 'flex flex-col gap-0.5 min-w-0' }, [
                        h('span', { class: 'font-medium truncate' }, item.full_name),
                        h('span', { class: 'text-xs text-muted-foreground truncate' }, item.user?.email),
                    ]),
                ]);
            },
        },
        {
            accessorKey: 'phone',
            header: 'Số điện thoại',
            size: 120,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-sm tabular-nums' }, row.original.phone),
        },
        {
            accessorKey: 'street',
            header: 'Địa chỉ',
            size: 200,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.street || '—'),
        },
        {
            accessorKey: 'total_spent',
            header: 'Tổng chi tiêu',
            size: 120,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm tabular-nums font-medium text-green-600' },
                `${Number(row.original.total_spent).toLocaleString('vi-VN')}đ`,
            ),
        },
        {
            accessorKey: 'user.is_active',
            header: 'Trạng thái',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const active = row.original.user?.is_active;
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(active ? CheckCircle2 : CircleDashed, {
                        class: `h-3.5 w-3.5 ${active ? 'text-green-500' : 'text-muted-foreground'}`,
                    }),
                    h('span', { class: `text-xs ${active ? 'text-green-600 font-medium' : 'text-muted-foreground'}` }, active ? 'Hoạt động' : 'Ngừng'),
                ]);
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
                const item = row.original;
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onView(item) }, () => [
                                h(Eye, { class: 'mr-2 h-4 w-4' }),
                                'Chi tiết',
                            ]),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { 
                                class: 'text-destructive', 
                                onClick: () => onDeactivate(item) 
                            }, () => [
                                h(UserX, { class: 'mr-2 h-4 w-4' }),
                                'Vô hiệu hóa',
                            ]),
                        ]),
                    ],
                });
            },
        },
    ];
}
