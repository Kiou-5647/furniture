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
import type { Lookup } from '@/types/lookup';
import type { ColumnDef } from '@tanstack/vue-table';
import { CheckCircle2, CircleDashed, Eye, MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

export function getColumns(
    namespace: string, // Giữ lại để xử lý cột màu sắc riêng
    onEdit: (lookup: Lookup) => void,
    onDelete: (lookup: Lookup) => void,
    onViewDetails: (lookup: Lookup) => void,
    onPreviewImage: (url: string) => void
): ColumnDef<Lookup>[] {
    const columns: ColumnDef<Lookup>[] = [
        {
            id: 'image',
            header: 'Ảnh',
            size: 100,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const item = row.original;
                if (!item.image_thumb_url) return h('div', { class: 'text-[10px] text-muted-foreground italic' }, 'N/A');
                return h('div', { class: 'flex justify-center' },
                    h('img', {
                        src: item.image_thumb_url,
                        class: 'w-16 h-10 rounded-md object-cover border shadow-sm cursor-zoom-in hover:scale-105 transition-all',
                        onClick: (event: MouseEvent) => {
                            event.stopPropagation();
                            onPreviewImage(item.image_url!);
                        }
                    })
                );
            }
        },
        {
            accessorKey: 'display_name',
            header: 'Tên hiển thị',
            size: 300,
            enableHiding: false,
        }
    ];

    // Cột màu sắc đặc biệt cho namespace 'mau-sac'
    if (namespace === 'mau-sac') {
        columns.push({
            id: 'color',
            header: 'Màu',
            size: 60,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const hex = row.original.metadata?.hex_code;
                if (!hex) return null;
                return h('div', {
                    class: 'w-5 h-5 rounded-full border shadow-sm mx-auto',
                    style: { backgroundColor: hex }
                });
            }
        });
    }

    columns.push(
        {
            accessorKey: 'slug',
            header: 'Khóa',
            size: 150,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground tabular-nums' }, row.getValue('slug'))
        },
        {
            accessorKey: 'description',
            header: 'Mô tả',
            size: 300,
            meta: { align: 'center' },
            cell: ({ row }) => h('p', { class: 'truncate text-xs text-muted-foreground tabular-nums' }, row.getValue('description'))
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 120,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const active = row.original.is_active;
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(active ? CheckCircle2 : CircleDashed, { class: `h-3.5 w-3.5 ${active ? 'text-green-500' : 'text-muted-foreground'}` }),
                    h('span', { class: `text-xs ${active ? 'text-green-600 font-medium' : 'text-muted-foreground'}` }, active ? 'Hiện' : 'Ẩn')
                ]);
            }
        },
        {
            accessorKey: 'updated_at',
            header: 'Cập nhật',
            size: 160,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground tabular-nums' }, row.getValue('updated_at'))
        },
        {
            id: 'actions',
            header: 'Thao tác',
            size: 80,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const item = row.original;
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' }))
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onViewDetails(item) }, () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'Chi tiết']),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { onClick: () => onEdit(item) }, () => [h(Pencil, { class: 'mr-2 h-4 w-4' }), 'Sửa']),
                            h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(item) }, () => [h(Trash2, { class: 'mr-2 h-4 w-4' }), 'Xóa']),
                        ])
                    ]
                });
            }
        }
    );

    return columns;
}
