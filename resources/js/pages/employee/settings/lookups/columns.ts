import { Badge } from '@/components/ui/badge';
import type { Lookup } from '@/types/lookup';
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
    onEdit: (lookup: Lookup) => void,
    onDelete: (lookup: Lookup) => void,
    onViewDetails: (lookup: Lookup) => void,
): ColumnDef<Lookup>[] => [
        {
            id: 'display_name',
            accessorKey: 'display_name',
            header: 'Tên hiển thị',
            size: 250,
            enableSorting: true,
            enableHiding: false,
        },
        {
            id: 'slug',
            accessorKey: 'slug',
            header: 'Đường dẫn',
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
            id: 'description',
            accessorKey: 'description',
            header: 'Mô tả',
            size: 250,
            enableSorting: false,
            cell: ({ row }) => h('p', { class: 'truncate' }, row.getValue('description')),
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
            size: 165,
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
                const lookup = row.original;
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
                                    { onClick: () => onViewDetails(lookup) },
                                    () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'Xem chi tiết'],
                                ),
                                h(DropdownMenuSeparator),
                                h(
                                    DropdownMenuItem,
                                    { onClick: () => onEdit(lookup) },
                                    () => [h(Pencil, { class: 'mr-2 h-4 w-4' }), 'Chỉnh sửa'],
                                ),
                                h(
                                    DropdownMenuItem,
                                    {
                                        class: 'text-destructive focus:text-destructive',
                                        onClick: () => onDelete(lookup),
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

const colorColumn: ColumnDef<Lookup>[] = [
    {
        accessorKey: 'color',
        header: 'Màu',
        size: 50,
        meta: {
            align: 'center',
        },
        enableSorting: false,
        cell: ({ row }) => {
            const lookup = row.original;

            // Check for hex code
            if (lookup.metadata?.hex_code) {
                return h('div', {
                    class: 'w-5 h-5 rounded-full border shadow-sm',
                    style: { backgroundColor: lookup.metadata.hex_code },
                });
            }

            return h(
                'div',
                {
                    class: 'rounded-full bg-muted flex items-center justify-center border text-[10px] text-muted-foreground italic',
                },
                () => 'None',
            );
        },
    },
];

const imageColumn = (onPreviewImage: (url: string) => void): ColumnDef<Lookup>[] => [
    {
        accessorKey: 'image_path',
        header: 'Hình ảnh',
        size: 100,
        enableSorting: false,
        meta: { align: 'center' },
        cell: ({ row }) => {
            const lookup = row.original;

            if (lookup.image_path) {
                return h(
                    'div',
                    {
                        class: 'max-w-32 max-h-32'
                    },
                    h('img', {
                        src: lookup.image_path,
                        alt: lookup.display_name,
                        class: 'rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105 transition-transform',
                        onClick: () => onPreviewImage(lookup.image_path!)
                    })
                );
            }

            return h('div', { class: 'w-24 h-12 rounded-md bg-muted flex items-center justify-center border text-[10px] text-muted-foreground' }, 'Không ảnh');
        }
    }
]

export function getColumns(
    namespace: string,
    onEdit: (lookup: Lookup) => void,
    onDelete: (lookup: Lookup) => void,
    onViewDetails: (lookup: Lookup) => void,
    onPreviewImage: (url: string) => void
): ColumnDef<Lookup>[] {
    const base = baseColumns(onEdit, onDelete, onViewDetails);

    if (namespace === 'mau-sac') {
        return [...imageColumn(onPreviewImage), ...colorColumn, ...base];
    }

    return [...imageColumn(onPreviewImage), ...base];
}
