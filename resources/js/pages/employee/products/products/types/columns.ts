import {
    MoreHorizontal,
    Pencil,
    Star,
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
import type { Product } from '@/types/product';

export function getColumns(
    onEdit: (product: Product) => void,
    onDelete: (product: Product) => void,
): ColumnDef<Product>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Tên sản phẩm',
            size: 250,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'space-x-2' }, [
                    h('span', {}, item.name),
                    item.is_featured
                        ? h(
                            Badge,
                            {
                                variant: 'default',
                                class: 'bg-yellow-300 w-fit text-xs h-4 px-1',
                            },
                            () =>
                                h(Star, {
                                    class: 'text-black dark:text-white h-3 w-3',
                                }),
                        )
                        : null,
                ]);
            },
        },
        {
            id: 'vendor',
            header: 'Nhà cung cấp',
            size: 150,
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground' },
                    row.original.vendor?.name ?? '—',
                ),
        },
        {
            id: 'category',
            header: 'Danh mục',
            size: 150,
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground' },
                    row.original.category?.display_name ?? '—',
                ),
        },
        {
            id: 'price',
            header: 'Giá',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'right' },
            cell: ({ row }) => {
                const { min_price, max_price } = row.original;
                const priceText =
                    min_price === max_price
                        ? `${min_price}₫`
                        : `${min_price}₫ — ${max_price}₫`;
                return h(
                    'span',
                    { class: 'text-xs font-medium tabular-nums' },
                    priceText,
                );
            },
        },
        {
            accessorKey: 'status_label',
            header: 'Trạng thái',
            size: 130,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const { status_color, status_label } = row.original;
                const colorMap: Record<string, string> = {
                    green: 'bg-green-500/10 text-green-700 border-green-200',
                    yellow: 'bg-yellow-500/10 text-yellow-700 border-yellow-200',
                    gray: 'bg-gray-500/10 text-gray-700 border-gray-200',
                    orange: 'bg-orange-500/10 text-orange-700 border-orange-200',
                    slate: 'bg-slate-500/10 text-slate-700 border-slate-200',
                };
                return h(
                    'div',
                    { class: 'flex items-center justify-center gap-1.5' },
                    [
                        h(
                            Badge,
                            {
                                variant: 'outline',
                                class: colorMap[status_color] || '',
                            },
                            () => status_label,
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
                    row.original.updated_at,
                ),
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
