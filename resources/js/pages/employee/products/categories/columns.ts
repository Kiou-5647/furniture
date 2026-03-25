import { Badge } from '@/components/ui/badge';
import type { Category } from '@/types/category';
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
import { CheckCircle2, CircleDashed, Eye, MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';

export const baseColumns = (
    onEdit: (category: Category) => void,
    onDelete: (category: Category) => void,
    onViewDetails: (category: Category) => void,
): ColumnDef<Category>[] => [
        {
            id: 'display_name',
            accessorKey: 'display_name',
            header: 'Tên danh mục',
            size: 500,
            enableSorting: true,
            enableHiding: false
        },
        {
            id: 'slug',
            accessorKey: 'slug',
            header: 'Slug',
            size: 150,
            enableSorting: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                Badge,
                { variant: 'outline', class: '' },
                () => row.getValue('slug'),
            ),
        },
        {
            id: 'product_type',
            accessorKey: 'product_type_label',
            header: 'Loại sản phẩm',
            size: 150,
            enableSorting: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                Badge,
                { variant: 'secondary', class: 'font-medium' },
                () => row.getValue('product_type')
            ),
        },
        {
            id: 'is_active',
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 100,
            enableSorting: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const isActive = row.getValue('is_active') as boolean;
                return h(
                    'div',
                    { class: 'flex items-center gap-1.5' },
                    [
                        isActive
                            ? h(CheckCircle2, { class: 'h-3.5 w-3.5 text-green-500' })
                            : h(CircleDashed, { class: 'h-3.5 w-3.5 text-muted-foreground' }),
                        h('span', { class: isActive ? 'text-green-600 font-medium' : 'text-muted-foreground' }, isActive ? 'Hiện' : 'Ẩn')
                    ]
                );
            }
        },
        {
            id: 'created_at',
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 180,
            enableSorting: true,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-muted-foreground tabular-nums' }, row.getValue('created_at')),
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const category = row.original;
                return h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(DropdownMenuTrigger, { asChild: true }, () =>
                                h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () =>
                                    h(MoreHorizontal, { class: 'h-4 w-4' }),
                                ),
                            ),
                            h(DropdownMenuContent, { align: 'end', class: 'w-[180px]' }, () => [
                                h(DropdownMenuLabel, {}, () => 'Quản lý'),
                                h(
                                    DropdownMenuItem,
                                    { onClick: () => onViewDetails(category) },
                                    () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'Xem chi tiết'],
                                ),
                                h(DropdownMenuSeparator),
                                h(
                                    DropdownMenuItem,
                                    { onClick: () => onEdit(category) },
                                    () => [h(Pencil, { class: 'mr-2 h-4 w-4' }), 'Chỉnh sửa'],
                                ),
                                h(
                                    DropdownMenuItem,
                                    {
                                        class: 'text-destructive focus:text-destructive',
                                        onClick: () => onDelete(category),
                                    },
                                    () => [h(Trash2, { class: 'mr-2 h-4 w-4' }), 'Xóa mục này'],
                                ),
                            ]),
                        ],
                    },
                );
            },
        },
    ];

const imageColumn = (onPreviewImage: (url: string) => void): ColumnDef<Category>[] => [
    {
        accessorKey: 'image_path',
        header: 'Hình ảnh',
        size: 100,
        enableSorting: false,
        meta: { align: 'center' },
        cell: ({ row }) => {
            const category = row.original;

            if (category.image_path) {
                return h(
                    'div',
                    {
                        class: 'max-w-32 max-h-32'
                    },
                    h('img', {
                        src: category.image_path,
                        alt: category.display_name,
                        class: 'rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105 transition-transform',
                        onClick: () => onPreviewImage(category.image_path!)
                    })
                );
            }

            return h('div', { class: 'w-24 h-12 rounded-md bg-muted flex items-center justify-center border text-[10px] text-muted-foreground' }, 'Không ảnh');
        }
    }
]

const groupColumn = (): ColumnDef<Category>[] => [
    {
        id: 'group',
        accessorKey: 'group.display_name',
        header: 'Nhóm danh mục',
        size: 150,
        enableSorting: false,
        cell: ({ row }) => {
            const groupName = row.original.group?.display_name;
            return h(
                Badge,
                { variant: 'outline', class: 'bg-primary/5 text-primary border-primary/20' },
                () => groupName ?? 'Không nhóm'
            );
        }
    }
]

export function getColumns(
    onEdit: (category: Category) => void,
    onDelete: (category: Category) => void,
    onViewDetails: (category: Category) => void,
    onPreviewImage: (url: string) => void,
    showGroupColumn: boolean
): ColumnDef<Category>[] {
    const base = baseColumns(onEdit, onDelete, onViewDetails);
    const columns = [...imageColumn(onPreviewImage), ...base];

    if (showGroupColumn) {
        columns.splice(1, 0, ...groupColumn());
    }

    return columns;
}
