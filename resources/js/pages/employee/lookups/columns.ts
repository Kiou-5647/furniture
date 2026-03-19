import { Badge } from '@/components/ui/badge';
import type { Lookup, LookupType } from '@/types/lookup';
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
import { MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';

export const baseColumns = (
    onEdit: (lookup: Lookup) => void,
    onDelete: (lookup: Lookup) => void,
): ColumnDef<Lookup>[] => [
        {
            accessorKey: 'display_name',
            header: 'Tên hiển thị',
        },
        {
            accessorKey: 'key',
            header: 'Khóa',
            size: 100,
            meta: {
                align: 'center'
            },
            cell: ({ row }) => {
                return h(
                    Badge,
                    {
                        variant: 'default',
                        class: 'flex justify-self-center ',
                    },
                    () => row.getValue('key'),
                );
            },
        },
        {
            accessorKey: 'actions',
            header: 'Thao tác',
            size: 50,
            meta: {
                align: 'center'
            },
            cell: ({ row }) => {
                const lookup = row.original;

                return h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(DropdownMenuTrigger, { asChild: true }, () =>
                                h(Button, { variant: 'secondary', class: 'h-8 w-8 p-0 hover:bg-white dark:hover:bg-black ' }, () =>
                                    h(MoreHorizontal, { class: 'h-4 w-4' }),
                                ),
                            ),
                            h(DropdownMenuContent, { align: 'end' }, () => [
                                h(DropdownMenuLabel, {}, () => 'Thao tác'),
                                h(DropdownMenuSeparator),
                                h(
                                    DropdownMenuItem,
                                    { onClick: () => onEdit(lookup) },
                                    () => [
                                        h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                        'Chỉnh sửa',
                                    ],
                                ),
                                h(
                                    DropdownMenuItem,
                                    {
                                        class: 'text-destructive focus:text-destructive',
                                        onClick: () => onDelete(lookup),
                                    },
                                    () => [
                                        h(Trash2, { class: 'mr-2 h-4 w-4' }),
                                        'Xóa',
                                    ],
                                ),
                            ]),
                        ],
                    },
                );
            },
        },
    ];

const colorColumn = (onPreviewImage: (url: string) => void): ColumnDef<Lookup>[] => [
    {
        accessorKey: 'color',
        header: 'Màu sắc',
        size: 50,
        meta: {
            align: 'center',
        },
        cell: ({ row }) => {
            const metadata = row.original.metadata;

            if (metadata?.image) {
                return h('img', {
                    src: metadata.image,
                    alt: row.original.display_name,
                    class: 'w-5 h-5 flex justify-self-center rounded-full border shadow-sm cursor-zoom-in hover:scale-105 object-contain',
                    onClick: () => onPreviewImage(metadata.image)
                });
            }

            // Check for hex code
            if (metadata?.hex_code) {
                return h('div', {
                    class: 'w-5 h-5 flex justify-self-center rounded-full border shadow-sm',
                    style: { backgroundColor: metadata.hex_code ?? '#ffffff' },
                });
            }

            // Fallback (Empty state)
            return h(
                'div',
                {
                    class: 'w-10 h-10 rounded-full bg-muted flex items-center justify-center border text-[10px] text-muted-foreground italic',
                },
                () => 'None',
            );
        },
    },
];

const imageColumn = (onPreviewImage: (url: string) => void): ColumnDef<Lookup>[] => [
    {
        accessorKey: 'image',
        header: 'Hình ảnh',
        size: 50,
        meta: {
            align: 'center',
        },
        cell: ({ row }) => {
            const metadata = row.original.metadata;

            // Check for image
            if (metadata?.image) {
                return h('img', {
                    src: metadata.image,
                    alt: row.original.display_name,
                    class: 'min-w-20 max-w-30 min-h-10 max-h-20  flex justify-self-center rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105',
                    onClick: () => onPreviewImage(metadata.image)
                });
            }

            return h(
                'div',
                {
                    class: 'min-w-20 min-h-20 rounded-md bg-muted flex items-center justify-center border text-[10px] text-muted-foreground italic',
                },
                () => 'None',
            );
        }
    }
]

export function getColumns(
    namespace: string,
    onEdit: (lookup: Lookup) => void,
    onDelete: (lookup: Lookup) => void,
    onPreviewImage: (url: string) => void
): ColumnDef<Lookup>[] {
    const base = baseColumns(onEdit, onDelete);

    if (namespace === 'mau-sac') {
        return [...colorColumn(onPreviewImage), ...base];
    }

    return [...imageColumn(onPreviewImage), ...base];
}
