import {
    MoreHorizontal,
    Pencil,
    Trash2,
    CheckCircle2,
    CircleDashed,
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
import { formatDateOnly, formatDateTime } from '@/lib/date-utils';
import { formatPrice } from '@/lib/utils';
import type { Discount } from '@/types/discount';

export function getColumns(
    onEdit: (discount: Discount) => void,
    onDelete: (discount: Discount) => void,
): ColumnDef<Discount>[] {
    const columns: ColumnDef<Discount>[] = [
        {
            accessorKey: 'name',
            header: 'Tên giảm giá',
            size: 250,
            enableSorting: true, // DB Column: name
            enableHiding: false,
            cell: ({ row }) => h('span', { class: 'font-medium truncate block' }, row.original.name),
        },
        {
            accessorKey: 'discountable_type_label',
            header: 'Đối tượng',
            size: 120,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'secondary', class: 'text-xs' },
                    () => row.original.discountable_type_label,
                ),
        },
        {
            accessorKey: 'discountable_name',
            header: 'Tên mục tiêu',
            size: 200,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h('span', { class: 'text-muted-foreground truncate block' }, row.original.discountable_name),
        },
        {
            accessorKey: 'type',
            header: 'Loại',
            size: 120,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const type = row.original.type;
                return h(
                    'span',
                    { class: 'text-xs' },
                    type === 'percentage' ? 'Phần trăm (%)' : 'Số tiền cố định',
                );
            },
        },
        {
            accessorKey: 'value',
            header: 'Giá trị',
            size: 120,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const item = row.original;
                return h(
                    'span',
                    { class: 'font-semibold tabular-nums' },
                    item.type === 'percentage' ? `${item.value}%` : formatPrice(item.value),
                );
            },
        },
        {
            accessorKey: 'start_at',
            header: 'Thời hạn',
            size: 200,
            enableSorting: true, // DB Column: start_at
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const start = row.original.start_at;
                const end = row.original.end_at;

                if (!start && !end) {
                    return h('span', { class: 'text-xs text-muted-foreground' }, 'Không thời hạn');
                }

                const startDate = start ? formatDateOnly(start) : '...';
                const endDate = end ? formatDateOnly(end) : '...';

                return h('div', { class: 'flex flex-col items-center text-xs' }, [
                    h('span', { class: 'font-medium' }, `${startDate} → ${endDate}`),
                    h('span', { class: 'text-[10px] text-muted-foreground' }, 'Bắt đầu → Kết thúc'),
                ]);
            },
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 100,
            enableSorting: true, // DB Column: is_active
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
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 140,
            enableSorting: true, // DB Column: created_at
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    formatDateTime(row.original.created_at),
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
    ];

    return columns;
}
