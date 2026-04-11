import { CheckCircle2, CircleDashed, MoreHorizontal, Pencil, Shield, Trash2, UserCheck, UserX } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Employee } from '@/types/employee';

export function getColumns(
    onEdit: (employee: Employee) => void,
    onViewRoles: (employee: Employee) => void,
    onTerminate: (employee: Employee) => void,
    onRestore: (employee: Employee) => void,
    onDelete: (employee: Employee) => void,
): ColumnDef<Employee>[] {
    return [
        {
            accessorKey: 'user.name',
            header: 'Nhân viên',
            size: 220,
            enableSorting: true,
            enableHiding: false,
            cell: ({ row }) => {
                const item = row.original;
                const avatarUrl = item.avatar_url;
                return h('div', { class: 'flex items-center gap-3' }, [
                    avatarUrl
                        ? h('img', {
                            src: avatarUrl,
                            class: 'h-9 w-9 shrink-0 rounded-full object-cover',
                        })
                        : h('div', {
                            class: 'flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted text-sm font-medium',
                        }, (item.user?.name ?? item.full_name ?? 'NV').charAt(0).toUpperCase()),
                    h('div', { class: 'flex flex-col gap-0.5 min-w-0' }, [
                        h('span', { class: 'font-medium truncate' }, item.user?.name ?? item.full_name),
                        item.department
                            ? h('span', { class: 'text-xs text-muted-foreground' }, item.department.name)
                            : null,
                    ]),
                ]);
            },
        },
        {
            accessorKey: 'user.email',
            header: 'Email',
            size: 200,
            enableSorting: false,
            enableHiding: true,
            cell: ({ row }) => h(
                'span',
                { class: 'text-sm' },
                row.original.user?.email ?? '—',
            ),
        },
        {
            id: 'roles',
            header: 'Vai trò',
            size: 140,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const roles = row.original.user?.roles ?? [];
                return h(
                    Button,
                    {
                        variant: 'outline',
                        size: 'sm',
                        class: 'gap-1 text-xs',
                        onClick: () => onViewRoles(row.original),
                    },
                    () => [
                        h(Shield, { class: 'h-3.5 w-3.5' }),
                        roles.length > 0 ? roles.join(', ') : 'Chưa có',
                    ],
                );
            },
        },
        {
            accessorKey: 'hire_date',
            header: 'Ngày vào làm',
            size: 120,
            enableSorting: true,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-xs text-muted-foreground tabular-nums' },
                row.original.hire_date ?? '—',
            ),
        },
        {
            accessorKey: 'termination_date',
            header: 'Ngày nghỉ',
            size: 120,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => h(
                'span',
                { class: 'text-xs tabular-nums' },
                row.original.termination_date ?? '—',
            ),
        },
        {
            id: 'is_active',
            header: 'Trạng thái',
            size: 100,
            enableSorting: false,
            enableHiding: true,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const terminated = row.original.termination_date;
                const active = row.original.user?.is_active;
                if (terminated) {
                    return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                        h(UserX, { class: 'h-3.5 w-3.5 text-red-500' }),
                        h('span', { class: 'text-xs text-red-600 font-medium' }, 'Đã nghỉ'),
                    ]);
                }
                return h('div', { class: 'flex items-center justify-center gap-1.5' }, [
                    h(active ? CheckCircle2 : CircleDashed, {
                        class: `h-3.5 w-3.5 ${active ? 'text-green-500' : 'text-muted-foreground'}`,
                    }),
                    h('span', { class: `text-xs ${active ? 'text-green-600 font-medium' : 'text-muted-foreground'}` }, active ? 'Hoạt động' : 'Vô hiệu'),
                ]);
            },
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
                const terminated = !!item.termination_date;
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
                            h(DropdownMenuItem, { onClick: () => onViewRoles(item) }, () => [
                                h(Shield, { class: 'mr-2 h-4 w-4' }),
                                'Quản lý quyền',
                            ]),
                            h(DropdownMenuSeparator),
                            terminated
                                ? h(DropdownMenuItem, { onClick: () => onRestore(item) }, () => [
                                    h(UserCheck, { class: 'mr-2 h-4 w-4' }),
                                    'Khôi phục',
                                ])
                                : h(DropdownMenuItem, { class: 'text-destructive', onClick: () => onTerminate(item) }, () => [
                                    h(UserX, { class: 'mr-2 h-4 w-4' }),
                                    'Chấm dứt',
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
