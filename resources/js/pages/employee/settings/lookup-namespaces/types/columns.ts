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
            header: 'Tên hiển thị',
            size: 250,
            enableHiding: false,
        },
        {
            accessorKey: 'slug',
            header: 'Khóa',
            size: 150,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'code',
                    {
                        class: 'rounded bg-muted px-1.5 py-0.5 text-xs tabular-nums',
                    },
                    row.getValue('slug'),
                ),
        },
        {
            accessorKey: 'description',
            header: 'Mô tả',
            size: 300,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'p',
                    {
                        class: 'truncate text-xs text-muted-foreground tabular-nums',
                    },
                    row.original.description ?? '—',
                ),
        },
        {
            accessorKey: 'for_variants',
            header: 'Biến thể',
            size: 100,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const isVariant = row.original.for_variants;
                return h('div', { class: 'flex items-center justify-center' }, [
                    isVariant
                        ? h(
                              Badge,
                              { variant: 'secondary', class: 'text-xs' },
                              () => 'Có',
                          )
                        : h(
                              'span',
                              { class: 'text-xs text-muted-foreground' },
                              '—',
                          ),
                ]);
            },
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 120,
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
            size: 160,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.getValue('updated_at'),
                ),
        },
        {
            accessorKey: 'is_system',
            header: 'Hệ thống',
            size: 100,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const isSystem = row.original.is_system;
                return h('div', { class: 'flex items-center justify-center' }, [
                    isSystem
                        ? h(
                              Badge,
                              { variant: 'outline', class: 'text-xs' },
                              () => 'Hệ thống',
                          )
                        : h(
                              'span',
                              { class: 'text-xs text-muted-foreground' },
                              '—',
                          ),
                ]);
            },
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
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
