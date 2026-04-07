import {
    CheckCircle2,
    CircleDashed,
    Pencil,
    Trash2,
    MoreHorizontal,
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
import type { LookupNamespaceFull } from '@/types';

export function getColumns(
    onEdit: (ns: LookupNamespaceFull) => void,
    onDelete: (ns: LookupNamespaceFull) => void,
): ColumnDef<LookupNamespaceFull>[] {
    return [
        {
            accessorKey: 'display_name',
            header: 'Danh mục',
            size: 340,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex flex-col gap-0.5' }, [
                    h('div', { class: 'flex items-center gap-2' }, [
                        h('span', { class: 'font-medium' }, item.display_name),
                        item.is_system
                            ? h(
                                  Badge,
                                  {
                                      variant: 'outline',
                                      class: 'text-xs shrink-0',
                                  },
                                  () => 'Hệ thống',
                              )
                            : null,
                        item.for_variants
                            ? h(
                                  Badge,
                                  {
                                      variant: 'secondary',
                                      class: 'text-xs shrink-0',
                                  },
                                  () => 'Biến thể',
                              )
                            : null,
                    ]),
                    h(
                        'code',
                        {
                            class: 'rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground tabular-nums w-fit',
                        },
                        item.slug,
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
                                    item.is_system
                                        ? h(
                                              DropdownMenuItem,
                                              {
                                                  disabled: true,
                                                  class: 'text-muted-foreground opacity-50',
                                              },
                                              () => [
                                                  h(Trash2, {
                                                      class: 'mr-2 h-4 w-4',
                                                  }),
                                                  'Xóa',
                                              ],
                                          )
                                        : h(
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
