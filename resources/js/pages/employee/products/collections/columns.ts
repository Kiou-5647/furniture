import { Badge } from '@/components/ui/badge';
import type { Collection } from '@/types/collection';
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
import { CheckCircle2, CircleDashed, Eye, MoreHorizontal, Pencil, Star, Trash2 } from 'lucide-vue-next';

export const getColumns = (
    onEdit: (collection: Collection) => void,
    onDelete: (collection: Collection) => void,
    onViewDetails: (collection: Collection) => void,
    onPreviewImage: (url: string) => void,
): ColumnDef<Collection>[] => [
        {
            accessorKey: 'image_path',
            header: 'Hình ảnh',
            size: 100,
            enableSorting: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const collection = row.original;

                if (collection.image_path) {
                    return h(
                        'div',
                        {
                            class: 'max-w-32 max-h-32'
                        },
                        h('img', {
                            src: collection.image_path,
                            alt: collection.display_name,
                            class: 'rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105 transition-transform',
                            onClick: () => onPreviewImage(collection.image_path!)
                        })
                    );
                }

                return h('div', { class: 'w-24 h-12 rounded-md bg-muted flex items-center justify-center border text-[10px] text-muted-foreground' }, 'Không ảnh');
            }
        },
        {
            id: 'display_name',
            accessorKey: 'display_name',
            header: 'Tên bộ sưu tập',
            size: 400,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.display_name),
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
            id: 'is_featured',
            accessorKey: 'is_featured',
            header: 'Nổi bật',
            size: 50,
            enableSorting: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const isFeatured = row.getValue('is_featured') as boolean;
                return isFeatured
                    ? h('div', { class: 'flex items-center justify-center' }, h(Star, { class: 'h-4 w-4 fill-yellow-400 text-yellow-400' }))
                    : h('span', { class: 'text-muted-foreground' }, '-');
            }
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
            id: 'updated_at',
            accessorKey: 'updated_at',
            header: 'Ngày cập nhật',
            size: 180,
            enableSorting: true,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-muted-foreground tabular-nums' }, row.getValue('updated_at')),
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
            enableSorting: false,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const collection = row.original;
                return h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(DropdownMenuTrigger, { asChild: true }, () =>
                                h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0 hover:bg-white dark:hover:bg-black' }, () =>
                                    h(MoreHorizontal, { class: 'h-4 w-4' }),
                                ),
                            ),
                            h(DropdownMenuContent, { align: 'end', class: 'w-[180px]' }, () => [
                                h(DropdownMenuLabel, {}, () => 'Quản lý'),
                                h(
                                    DropdownMenuItem,
                                    { onClick: () => onViewDetails(collection) },
                                    () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'Xem chi tiết'],
                                ),
                                h(DropdownMenuSeparator),
                                h(
                                    DropdownMenuItem,
                                    { onClick: () => onEdit(collection) },
                                    () => [h(Pencil, { class: 'mr-2 h-4 w-4' }), 'Chỉnh sửa'],
                                ),
                                h(
                                    DropdownMenuItem,
                                    {
                                        class: 'text-destructive focus:text-destructive',
                                        onClick: () => onDelete(collection),
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
