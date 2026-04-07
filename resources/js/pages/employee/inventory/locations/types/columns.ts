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
import type { Location } from '@/types/location';

export function getColumns(
    onEdit: (location: Location) => void,
    onDelete: (location: Location) => void,
): ColumnDef<Location>[] {
    return [
        {
            accessorKey: 'code',
            header: 'Mã',
            size: 120,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'outline', class: 'font-mono text-xs' },
                    () => row.original.code,
                ),
        },
        {
            accessorKey: 'name',
            header: 'Vị trí',
            size: 260,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                const typeColorClass =
                    {
                        warehouse:
                            'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-800',
                        retail: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900 dark:text-green-300 dark:border-green-800',
                        vendor: 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900 dark:text-orange-300 dark:border-orange-800',
                    }[item.type] || 'bg-gray-100 text-gray-700 border-gray-200';

                return h('div', { class: 'flex flex-col gap-0.5' }, [
                    h('div', { class: 'flex items-center gap-2' }, [
                        h('span', { class: 'font-medium' }, item.name),
                        h(
                            Badge,
                            {
                                variant: 'outline',
                                class: `${typeColorClass} text-xs shrink-0`,
                            },
                            () => item.type_label,
                        ),
                    ]),
                    h(
                        'div',
                        {
                            class: 'flex items-center gap-3 text-xs text-muted-foreground',
                        },
                        [
                            item.full_address
                                ? h(
                                      'span',
                                      { class: 'truncate max-w-[180px]' },
                                      item.full_address,
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
            accessorKey: 'manager',
            header: 'Người quản lý',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm' },
                    row.original.manager?.full_name ?? '—',
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
