import { CheckCircle2, CircleDashed, MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Department } from '@/types/department';

export function getColumns(
    onEdit: (department: Department) => void,
    onDelete: (department: Department) => void,
): ColumnDef<Department>[] {
    return [
        {
            accessorKey: 'code',
            header: 'Mã',
            size: 120,
            enableSorting: true,
            enableHiding: false,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                Badge,
                { variant: 'outline', class: 'font-mono text-xs' },
                () => row.original.code,
            ),
        },
        {
            accessorKey: 'name',
            header: 'Phòng ban',
            size: 260,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex flex-col gap-0.5' }, [
                    h('span', { class: 'font-medium' }, item.name),
                    item.description
                        ? h('span', { class: 'text-xs text-muted-foreground truncate max-w-[240px]' }, item.description)
                        : null,
                ]);
            },
        },
        {
            accessorKey: 'manager',
            header: 'Trưởng phòng',
            size: 160,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm' },
                row.original.manager?.full_name ?? '—',
            ),
        },
        {
            accessorKey: 'is_active',
            header: 'Trạng thái',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const active = row.original.is_active;
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(active ? CheckCircle2 : CircleDashed, {
                        class: `h-3.5 w-3.5 ${active ? 'text-green-500' : 'text-muted-foreground'}`,
                    }),
                    h('span', { class: `text-xs ${active ? 'text-green-600 font-medium' : 'text-muted-foreground'}` }, active ? 'Hoạt động' : 'Ngừng'),
                ]);
            },
        },
        {
            accessorKey: 'employees_count',
            header: 'Nhân viên',
            size: 100,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm tabular-nums' },
                row.original.employees_count,
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
                return h(DropdownMenu, {}, {
                    default: () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () => h(MoreHorizontal, { class: 'h-4 w-4' })),
                        ),
                        h(DropdownMenuContent, { align: 'end', class: 'w-45' }, () => [
                            h(DropdownMenuLabel, () => 'Thao tác'),
                            h(DropdownMenuItem, { onClick: () => onEdit(item) }, () => [
                                h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                'Sửa',
                            ]),
                            h(DropdownMenuSeparator),
                            h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onDelete(item) }, () => [
                                h(Trash2, { class: 'mr-2 h-4 w-4' }),
                                'Xóa',
                            ]),
                        ]),
                    ],
                });
            },
        },
    ];
}
