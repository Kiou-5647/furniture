import {
    CheckCircle2,
    CircleDashed,
    MoreHorizontal,
    Pencil,
    Phone,
    Trash2,
} from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Vendor } from '@/types/vendor';

export function getColumns(
    onEdit: (vendor: Vendor) => void,
    onDelete: (vendor: Vendor) => void,
): ColumnDef<Vendor>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Nhà cung cấp',
            size: 260,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex flex-col gap-0.5' }, [
                    h('div', { class: 'flex items-center gap-2' }, [
                        h('span', { class: 'font-medium' }, item.name),
                    ]),
                    h(
                        'div',
                        {
                            class: 'flex items-center gap-3 text-xs text-muted-foreground',
                        },
                        [
                            item.email
                                ? h(
                                      'span',
                                      { class: 'truncate max-w-[180px]' },
                                      item.email,
                                  )
                                : null,
                            item.phone
                                ? h(
                                      'span',
                                      {
                                          class: 'flex items-center gap-1 shrink-0',
                                      },
                                      [
                                          h(Phone, { class: 'h-3 w-3' }),
                                          item.phone,
                                      ],
                                  )
                                : null,
                        ],
                    ),
                ]);
            },
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
                return h(
                    'div',
                    { class: 'flex items-center justify-center gap-1.5' },
                    [
                        h(active ? CheckCircle2 : CircleDashed, {
                            class: `h-3.5 w-3.5 ${active ? 'text-green-500' : 'text-muted-foreground'}`,
                        }),
                        h(
                            'span',
                            {
                                class: `text-xs ${active ? 'text-green-600 font-medium' : 'text-muted-foreground'}`,
                            },
                            active ? 'Hiện' : 'Ẩn',
                        ),
                    ],
                );
            },
        },
        {
            accessorKey: 'updated_at',
            header: 'Cập nhật',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.updated_at,
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
                const item = row.original;
                return h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(DropdownMenuTrigger, { asChild: true }, () =>
                                h(
                                    Button,
                                    { variant: 'ghost', class: 'h-8 w-8 p-0' },
                                    () =>
                                        h(MoreHorizontal, { class: 'h-4 w-4' }),
                                ),
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end', class: 'w-45' },
                                () => [
                                    h(DropdownMenuLabel, () => 'Thao tác'),
                                    h(
                                        DropdownMenuItem,
                                        { onClick: () => onEdit(item) },
                                        () => [
                                            h(Pencil, {
                                                class: 'mr-2 h-4 w-4',
                                            }),
                                            'Sửa',
                                        ],
                                    ),
                                    h(DropdownMenuSeparator),
                                    h(
                                        DropdownMenuItem,
                                        {
                                            class: 'text-destructive',
                                            onClick: () => onDelete(item),
                                        },
                                        () => [
                                            h(Trash2, {
                                                class: 'mr-2 h-4 w-4',
                                            }),
                                            'Xóa',
                                        ],
                                    ),
                                ],
                            ),
                        ],
                    },
                );
            },
        },
    ];
}