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
        // Cột Ảnh
        {
            id: 'image',
            header: 'Hình ảnh',
            size: 100,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const item = row.original;
                if (!item.image_thumb_url)
                    return h(
                        'div',
                        { class: 'text-[10px] text-muted-foreground italic' },
                        'Không ảnh',
                    );
                return h(
                    'div',
                    { class: 'flex justify-center' },
                    h('img', {
                        src: item.image_thumb_url,
                        class: 'w-16 h-10 rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105 transition-all',
                        onClick: (event: MouseEvent) => {
                            event.stopPropagation();
                            onPreviewImage(item.image_url!);
                        },
                    }),
                );
            },
        },
        // Cột Tên
        {
            accessorKey: 'display_name',
            header: 'Tên danh mục',
            size: 250,
            enableSorting: true,
            enableHiding: false,
        },
    ];

    // Cột Nhóm (Tùy chọn)
    if (showGroupColumn) {
        columns.push({
            id: 'group',
            header: 'Nhóm',
            size: 150,
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: 'outline',
                        class: 'bg-primary/5 border-primary/20 text-primary',
                    },
                    () => row.original.group?.display_name ?? 'N/A',
                ),
        });
    }

    columns.push(
        {
            accessorKey: 'product_type_label',
            header: 'Loại',
            size: 120,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'secondary' },
                    () => row.original.product_type_label,
                ),
        },
        {
            accessorKey: 'slug',
            header: 'Khóa',
            size: 150,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.slug,
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
                    row.getValue('description'),
                ),
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
        // Cột Thời gian
        {
            accessorKey: 'updated_at',
            header: 'Cập nhật',
            size: 160,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.updated_at,
                ),
        },
        // Cột Thao tác
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
