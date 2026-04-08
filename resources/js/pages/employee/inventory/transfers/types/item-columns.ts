import { ImageIcon } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Input } from '@/components/ui/input';
import { formatPrice } from '@/lib/utils';
import type {
    StockTransferItem,
    StockTransferStatus,
} from '@/types/stock-transfer';

export function getItemColumns(
    status: StockTransferStatus,
    openImagePreview: (url: string | null | undefined) => void,
    isReceiving: boolean = false,
    receiveForm?: any,
): ColumnDef<StockTransferItem>[] {
    const columns: ColumnDef<StockTransferItem>[] = [
        {
            id: 'image',
            header: '',
            size: 64,
            enableSorting: false,
            meta: { align: 'center' },
            cell: ({ row }) => {
                const imageUrl = row.original.variant.image_url;
                if (imageUrl) {
                    return h(
                        'div',
                        {
                            class: 'relative w-12 shrink-0 overflow-hidden rounded-md border bg-muted',
                        },
                        [
                            h('img', {
                                src: imageUrl,
                                alt: row.original.variant.name,
                                class: 'h-full w-full cursor-zoom-in object-cover transition-all hover:scale-105',
                                onClick: () =>
                                    openImagePreview(
                                        row.original.variant.full_image_url,
                                    ),
                            }),
                        ],
                    );
                }
                return h(
                    'div',
                    {
                        class: 'flex h-12 w-12 shrink-0 items-center justify-center rounded-md border bg-muted',
                    },
                    [h(ImageIcon, { class: 'h-5 w-5 text-muted-foreground' })],
                );
            },
        },
        {
            accessorKey: 'name',
            header: 'Sản phẩm',
            size: 300,
            enableSorting: false,
            cell: ({ row }) => {
                const variant = row.original.variant;

                return h('div', { class: 'flex flex-col truncate' }, [
                    h('span', { class: 'text-sm font-medium truncate' }, variant.name),
                    variant.product_name
                        ? h(
                              'span',
                              { class: 'text-xs text-muted-foreground' },
                              variant.product_name,
                          )
                        : null,
                    h(
                        'div',
                        { class: 'mt-1 flex flex-wrap items-center gap-2' },
                        [
                            variant.price
                                ? h(
                                      'span',
                                      {
                                          class: 'text-xs font-medium text-emerald-600 dark:text-emerald-500',
                                      },
                                      formatPrice(variant.price),
                                  )
                                : null,
                        ],
                    ),
                ]);
            },
        },
        {
            accessorKey: 'sku',
            header: 'SKU',
            size: 80,
            enableSorting: false,
            meta: {align: 'center'},
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'font-mono text-xs' },
                    row.original.variant.sku,
                ),
        },
        {
            accessorKey: 'quantity_shipped',
            header: 'SL xuất',
            size: 80,
            meta: { align: 'center' },
            enableSorting: false,
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'tabular-nums' },
                    row.original.quantity_shipped,
                ),
        },
    ];

    if (isReceiving && receiveForm) {
        columns.push({
            id: 'quantity_received_input',
            header: 'SL nhận',
            size: 80,
            meta: { align: 'center' },
            enableSorting: false,
            cell: ({ row }) => {
                const idx = row.index;
                const error =
                    receiveForm.errors[`items.${idx}.quantity_received`];
                return h('div', { class: 'flex flex-col items-center gap-1' }, [
                    h(Input, {
                        type: 'number',
                        min: 0,
                        max: row.original.quantity_shipped,
                        class: 'w-20 text-center',
                        modelValue: receiveForm.items[idx].quantity_received,
                        'onUpdate:modelValue': (val: any) => {
                            receiveForm.items[idx].quantity_received =
                                typeof val === 'string'
                                    ? parseInt(val, 10)
                                    : val;
                        },
                    }),
                    error
                        ? h('p', { class: 'text-xs text-destructive' }, error)
                        : null,
                ]);
            },
        });
    } else if (status === 'completed' || status === 'in_transit') {
        columns.push({
            accessorKey: 'quantity_received',
            header: 'SL nhận',
            size: 120,
            meta: { align: 'center' },
            enableSorting: false,
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'tabular-nums' },
                    row.original.quantity_received,
                ),
        });
    }

    return columns;
}
