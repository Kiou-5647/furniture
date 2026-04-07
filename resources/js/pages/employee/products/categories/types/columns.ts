import {
    CheckCircle2,
    CircleDashed,
    MoreHorizontal,
    Pencil,
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
import type { Category } from '@/types/category';

export function getColumns(
    onEdit: (category: Category) => void,
    onDelete: (category: Category) => void,
    onPreviewImage: (url: string) => void,
    showGroupColumn: boolean,
): ColumnDef<Category>[] {
    const columns: ColumnDef<Category>[] = [
        {
            accessorKey: 'display_name',
            header: 'Danh mục',
            size: 320,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex items-center gap-3' }, [
                    item.image_thumb_url
                        ? h('img', {
                              src: item.image_thumb_url,
                              class: 'w-12 h-8 rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105 transition-all shrink-0',
                              onClick: (event: MouseEvent) => {
                                  event.stopPropagation();
                                  onPreviewImage(item.image_url!);
                              },
                          })
                        : h(
                              'div',
                              {
                                  class: 'w-12 h-8 rounded-md border border-dashed flex items-center justify-center bg-muted/50 shrink-0',
                              },
                              [
                                  h(
                                      'span',
                                      {
                                          class: 'text-[8px] text-muted-foreground',
                                      },
                                      'N/A',
                                  ),
                              ],
                          ),
                    h('div', { class: 'min-w-0 flex-1' }, [
                        h(
                            'span',
                            { class: 'font-medium truncate block' },
                            item.display_name,
                        ),
                        h(
                            'span',
                            {
                                class: 'text-xs text-muted-foreground tabular-nums truncate block',
                            },
                            item.slug,
                        ),
                    ]),
                ]);
            },
        },
    ];

    if (showGroupColumn) {
        columns.push({
            id: 'group',
            header: 'Nhóm',
            size: 120,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: 'outline',
                        class: 'bg-primary/5 border-primary/20 text-primary text-xs',
                    },
                    () => row.original.group?.display_name ?? 'N/A',
                ),
        });
    }

    columns.push(
        {
            accessorKey: 'product_type_label',
            header: 'Loại',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'secondary', class: 'text-xs' },
                    () => row.original.product_type_label,
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
    );

    return columns;
}
