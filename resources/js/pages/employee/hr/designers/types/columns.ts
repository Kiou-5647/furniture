import { CheckCircle2, CircleDashed, MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Designer } from '@/types/designer';

export function getColumns(
    onEdit: (designer: Designer) => void,
    onDelete: (designer: Designer) => void,
): ColumnDef<Designer>[] {
    return [
        {
            accessorKey: 'display_name',
            header: 'Nhà thiết kế',
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
                        }, (item.full_name ?? item.display_name).charAt(0).toUpperCase()),
                    h('div', { class: 'flex flex-col gap-0.5 min-w-0' },
                        h('span', { class: 'font-medium truncate' }, item.full_name ?? item.display_name),
                    ),
                ]);
            },
        },
        {
            accessorKey: 'hourly_rate',
            header: 'Giá/giờ',
            size: 120,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm tabular-nums' },
                `${Number(row.original.hourly_rate).toLocaleString('vi-VN')}đ`,
            ),
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const active = row.original.is_active;
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
                            h(DropdownMenuItem, { onClick: () => onEdit(item) }, () => [
                                h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                'Sửa',
                            ]),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(item) }, () => [
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
