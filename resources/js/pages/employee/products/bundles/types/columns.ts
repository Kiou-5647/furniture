import { MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Bundle } from '@/types/bundle';

export const getColumns = (
    handleEdit: (bundle: Bundle) => void,
    confirmDelete: (bundle: Bundle) => void
): ColumnDef<Bundle>[] => [
        {
            accessorKey: 'name',
            header: 'Tên gói',
            size: 200,
        },
        {
            accessorKey: 'slug',
            header: 'Slug',
            size: 150,
        },
        {
            accessorKey: 'discount_type',
            header: 'Loại giảm giá',
            size: 120,
            cell: ({ row }) => {
                const type = row.original.discount_type;
                const labels: Record<string, string> = {
                    percentage: 'Phần trăm',
                    fixed_amount: 'Số tiền cố định',
                    fixed_price: 'Giá cố định',
                };
                return labels[type] || type;
            },
        },
        {
            accessorKey: 'discount_value',
            header: 'Giá trị',
            size: 100,
            cell: ({ row }) => `${row.original.discount_value} ${row.original.discount_type === 'percentage' ? '%' : 'đ'
                }`,
        },
        {
            accessorKey: 'is_available',
            header: 'Kho',
            cell: ({row}) => {
                return row.original.is_available
                    ? h('span',{ class: "text-green-500 text-xs font-medium"}, 'Sẵn hàng')
                    : h('span',{ class: "text-destructive text-xs font-medium"}, 'Hết hàng')
            }
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 100,
            cell: ({ row }) => {
                const active = row.original.is_active;
                return active ? 'Đang hiện' : 'Đang ẩn';
            },
        },
        {
            accessorKey: 'created_at',
            header: 'Ngày tạo',
            size: 150,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-xs text-muted-foreground tabular-nums' },
                    row.original.created_at,
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
                                        { onClick: () => handleEdit(item) },
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
                                            onClick: () => confirmDelete(item),
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
