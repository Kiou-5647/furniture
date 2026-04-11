import {
    MoreHorizontal,
    Pencil,
    Star,
    Trash2,
    Package,
    Boxes,
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

function formatPrice(value: string | number): string {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
}

export function getColumns(
    onEdit: (product: Product) => void,
    onDelete: (product: Product) => void,
): ColumnDef<Product>[] {
    return [
        {
            id: 'actions',
            header: 'Thao tác',
            size: 60,
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
        {
            accessorKey: 'name',
            header: 'Sản phẩm',
            size: 300,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;

                return h('div', { class: 'flex flex-col gap-0.5' }, [
                    h('div', { class: 'flex items-center gap-1.5' }, [
                        h('span', { class: 'font-medium' }, item.name),
                        item.is_featured
                            ? h(Star, {
                                class: 'h-4 w-4 fill-yellow-400 text-yellow-400 shrink-0',
                            })
                            : null,
                        item.is_new_arrival
                            ? h(
                                Badge,
                                {
                                    variant: 'outline',
                                    class: 'h-5 w-9 items-center justify-center border-green-500 shrink-0',
                                },
                                () => [
                                    h(
                                        'span',
                                        {
                                            class: 'text-[9px] text-green-500',
                                        },
                                        'NEW',
                                    ),
                                    ,
                                ],
                            )
                            : null,
                        item.is_custom_made
                            ? h(
                                Badge,
                                {
                                    variant: 'secondary',
                                    class: 'text-xs h-5 px-1 shrink-0',
                                },
                                () => 'Custom',
                            )
                            : null,
                    ]),
                    item.collection
                        ? h(
                            'div',
                            {
                                class: 'flex items-center gap-1 text-xs text-muted-foreground',
                            },
                            [
                                h('span', {}, 'Bộ sưu tập:'),
                                h(
                                    'span',
                                    { class: 'font-medium' },
                                    item.collection.display_name,
                                ),
                            ],
                        )
                        : null,
                ]);
            },
        },
        {
            accessorKey: 'category',
            header: 'Danh mục',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm' },
                    row.original.category?.display_name ?? '—',
                ),
        },
        {
            id: 'price',
            header: 'Giá',
            size: 140,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'right' },
            cell: ({ row }) => {
                const { min_price, max_price } = row.original;
                const min =
                    typeof min_price === 'string'
                        ? parseFloat(min_price)
                        : min_price;
                const max =
                    typeof max_price === 'string'
                        ? parseFloat(max_price)
                        : max_price;

                return h('div', { class: 'flex flex-col items-end gap-0.5' }, [
                    min === max
                        ? h(
                            'span',
                            { class: 'font-semibold tabular-nums text-sm' },
                            formatPrice(min),
                        )
                        : [
                            h(
                                'div',
                                { class: 'flex items-center gap-2 text-xs' },
                                [
                                    h(
                                        'span',
                                        { class: 'text-muted-foreground' },
                                        'Từ:',
                                    ),
                                    h(
                                        'span',
                                        { class: 'font-medium tabular-nums' },
                                        formatPrice(min),
                                    ),
                                ],
                            ),
                            h(
                                'div',
                                { class: 'flex items-center gap-2 text-xs' },
                                [
                                    h(
                                        'span',
                                        { class: 'text-muted-foreground' },
                                        'Đến:',
                                    ),
                                    h(
                                        'span',
                                        { class: 'font-medium tabular-nums' },
                                        formatPrice(max),
                                    ),
                                ],
                            ),
                        ],
                ]);
            },
        },
        {
            id: 'variants',
            header: 'Biến thể',
            size: 80,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const item = row.original;
                const count = item.variants_count ?? item.variants?.length ?? 0;
                return h(
                    'div',
                    { class: 'flex items-center justify-center gap-1.5' },
                    [
                        h(Package, {
                            class: 'h-3.5 w-3.5 text-muted-foreground',
                        }),
                        h('span', { class: 'text-sm tabular-nums' }, count),
                    ],
                );
            },
        },
        {
            id: 'inventory',
            header: 'Tồn kho',
            size: 90,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const item = row.original;
                const totalStock =
                    item.variants?.reduce((sum: number, variant: any) => {
                        return (
                            sum +
                            (variant.stock?.reduce(
                                (s: number, st: any) => s + (st.quantity ?? 0),
                                0,
                            ) ?? 0)
                        );
                    }, 0) ?? 0;

                return h(
                    'div',
                    { class: 'flex items-center justify-center gap-1.5' },
                    [
                        h(Boxes, {
                            class: 'h-3.5 w-3.5 text-muted-foreground',
                        }),
                        h(
                            'span',
                            {
                                class: `text-sm tabular-nums ${totalStock === 0 ? 'text-destructive font-medium' : ''}`,
                            },
                            totalStock,
                        ),
                    ],
                );
            },
        },
        {
            id: 'status',
            header: 'Trạng thái',
            size: 130,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const { status, status_color, status_label, published_date } =
                    row.original;
                const colorMap: Record<string, string> = {
                    green: 'bg-green-500/10 text-green-700 border-green-200',
                    yellow: 'bg-yellow-500/10 text-yellow-700 border-yellow-200',
                    gray: 'bg-gray-500/10 text-gray-700 border-gray-200',
                    orange: 'bg-orange-500/10 text-orange-700 border-orange-200',
                    slate: 'bg-slate-500/10 text-slate-700 border-slate-200',
                };

                return h('div', { class: 'flex flex-col items-center gap-1' }, [
                    h(
                        Badge,
                        {
                            variant: 'outline',
                            class: colorMap[status_color] || '',
                        },
                        () => status_label,
                    ),
                    status === 'published' && published_date
                        ? h(
                            'span',
                            {
                                class: 'text-[10px] text-muted-foreground tabular-nums',
                            },
                            `Xuất bản ${new Date(published_date).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: '2-digit' })}`,
                        )
                        : null,
                ]);
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
    ];
}
