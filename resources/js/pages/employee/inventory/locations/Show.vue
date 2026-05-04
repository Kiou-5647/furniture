<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Package,
    AlertTriangle,
    MapPin,
    ArrowLeft,
    DollarSign,
    Box,
    Settings2,
    Plus,
    Minus,
    Coins,
    Trash2,
    PlusCircle,
} from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { debounce } from 'lodash';
import { ref, computed, h, watch } from 'vue';
import {toast} from 'vue-sonner';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import SearchableSelect from '@/components/ui/SearchableSelect.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice, cleanQuery, setCookie } from '@/lib/utils';
import { index, show } from '@/routes/employee/inventory/locations';
import type { Location } from '@/types/location';

const props = defineProps<{
    location: Location;
    variantsList: any[]; // Added this
    inventory: {
        data: any[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            path: string;
            per_page: number;
            to: number;
            total: number;
        };
        links: {
            first: string;
            last: string;
            prev: string | null;
            next: string | null;
        };
    };
    stats: {
        total_sku: number;
        total_quantity: number;
        total_value: number;
        low_stock_count: number;
    };
    filters: {
        search?: string;
        order_by?: string;
        order_direction?: string;
        per_page?: number;
    };
}>();

// --- STOCK ADJUSTMENT STATE ---
const isAdjustmentOpen = ref(false);
const selectedItem = ref<any>(null);

const adjustmentForm = useForm({
    variant_id: '',
    location_id: '',
    type: 'add', // 'add' | 'remove' | 'cost'
    quantity: 0,
    cost_per_unit: null as number | null,
    notes: '',
    force_update_price: false,
});

// --- BULK IMPORT STATE ---
const isBulkImportOpen = ref(false);
const bulkImportForm = useForm({
    items: [
        { variant_id: '', quantity: 1, cost_per_unit: null as number | null, notes: '' },
    ],
});

function addBulkItem() {
    bulkImportForm.items.push({ variant_id: '', quantity: 1, cost_per_unit: null as number | null, notes: '' });
}

function removeBulkItem(index: number) {
    if (bulkImportForm.items.length > 1) {
        bulkImportForm.items.splice(index, 1);
    }
}

async function handleVariantSelect(index: number, variantId: string) {
    if (!variantId) return;

    try {
        const response = await fetch(`/nhan-vien/kho-hang/variants-details/${variantId}`);
        const data = await response.json();

        if (data.vendor_name) {
            bulkImportForm.items[index].notes = `Nhập mới từ ${data.vendor_name}`;
        } else {
            bulkImportForm.items[index].notes = 'Nhập mới';
        }
    } catch (e) {
        console.error('Failed to fetch variant details', e);
    }
}

function submitBulkImport() {
    bulkImportForm.transform((data) => ({
        ...data,
            location_id: props.location.id,
    })).post(`/nhan-vien/kho-hang/vi-tri/${props.location.id}/bulk-import`, {
        onSuccess: () => {
            toast.success('Nhập kho hàng loạt thành công');
            isBulkImportOpen.value = false;
            bulkImportForm.reset();
            router.visit(show(props.location).url, {
                preserveScroll: true,
                preserveState: true
            });
        },
        onError: (errors) => {
            console.error(errors)
            toast.error('Có lỗi xảy ra khi nhập kho');
        },
    });
}


function getSampleNotes(type: string) {
    const mapping: Record<string, string[]> = {
        add: ['Nhập bổ sung', 'Hàng trả về', 'Nhập từ nhà cung cấp'],
        remove: ['Kiểm kê định kỳ', 'Điều chỉnh sai sót', 'Hàng hỏng', 'Hàng hết hạn'],
        cost: ['Điều chỉnh giá vốn', 'Sai lệch giá'],
    };
    return mapping[type] || [];
}

function openAdjustmentDialog(item: any) {
    selectedItem.value = item;
    adjustmentForm.variant_id = item.variant_id;
    adjustmentForm.location_id = props.location.id;
    adjustmentForm.cost_per_unit = item.cost_per_unit;
    adjustmentForm.notes = '';
    isAdjustmentOpen.value = true;
}

function submitAdjustment() {
    adjustmentForm.post('/nhan-vien/kho-hang/vi-tri/adjust', {
        onSuccess: () => {
            toast.success('Điều chỉnh tồn kho thành công');
            isAdjustmentOpen.value = false;
            router.visit(show(props.location).url, {
                preserveScroll: true,
                preserveState: true
            });
        },
        onError: (errors) => {
            console.error(errors)
            toast.error(errors.message);
        },
    });
}

// --- TABLE CONFIG ---
const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'product_name',
        header: 'Sản phẩm',
        size: 350,
        enableSorting: true,
        enableHiding: false,
        cell: ({ row }: any) => {
            const item = row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                h('img', {
                    src: item.primary_image,
                    class: 'size-10 rounded-md object-cover bg-muted',
                    alt: item.product_name,
                }),
                h('div', { class: 'flex flex-col' }, [
                    h(
                        'span',
                        {
                            class: 'w-full text-sm font-semibold text-zinc-900 truncate',
                        },
                        `${item.product_name} ${item.variant_name}`,
                    ),
                    h(
                        'span',
                        {
                            class: 'text-xs text-zinc-500 truncate',
                        },
                        item.sku,
                    ),
                ]),
            ]);
        },
    },
    {
        accessorKey: 'quantity',
        header: 'Số lượng',
        enableSorting: true,
        enableHiding: true,
        cell: ({ row }: any) => {
            const qty = row.original.quantity;
            const isLow = qty <= 5;
            return h('div', { class: 'flex items-center gap-2' }, [
                h(
                    'span',
                    {
                        class: `text-sm font-medium ${isLow ? 'text-red-500 font-bold' : 'text-zinc-700'}`,
                    },
                    qty,
                ),
                isLow
                    ? h(AlertTriangle, { class: 'size-3 text-red-500' })
                    : null,
            ]);
        },
        meta: { align: 'center' },
        size: 80,
    },
    {
        accessorKey: 'cost_per_unit',
        header: 'Giá vốn',
        enableSorting: true,
        enableHiding: true,
        cell: ({ row }: any) => {
            return h(
                'span',
                { class: 'text-sm text-zinc-600' },
                formatPrice(Number(row.original.cost_per_unit)),
            );
        },
        meta: { align: 'right' },
        size: 150,
    },
    {
        accessorKey: 'total_value',
        header: 'Thành tiền',
        enableSorting: true,
        enableHiding: true,
        cell: ({ row }: any) => {
            return h(
                'span',
                { class: 'text-sm font-semibold text-zinc-900' },
                formatPrice(Number(row.original.total_value)),
            );
        },
        meta: { align: 'right' },
        size: 150,
    },
    {
        id: 'actions',
        header: 'Hành động',
        enableSorting: false,
        enableHiding: false,
        cell: ({ row }: any) => {
            return h('div', { class: 'flex justify-end' }, [
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'sm',
                        class: 'h-8 w-8 p-0',
                        onClick: () => openAdjustmentDialog(row.original),
                    },
                    () => h(Settings2, { class: 'size-4' }),
                ),
            ]);
        },
        meta: { align: 'center' },
        size: 100,
    },
];

const search = ref(props.filters.search || '');
const isActuallyLoading = ref(true);

const hasActiveFilters = computed(() => {
    return !!props.filters.search || !!props.filters.order_by;
});

const updateSearch = debounce(() => {
    router.get(
        show(props.location).url,
        cleanQuery({
            search: search.value,
            order_by: props.filters.order_by,
            order_direction: props.filters.order_direction,
            page: 1,
        }),
        { preserveState: true, replace: true },
    );
}, 500);

watch(search, (val) => val !== (props.filters.search ?? '') && updateSearch());

watch(
    () => props.inventory,
    (newData) => {
        if (newData) {
            setTimeout(() => (isActuallyLoading.value = false), 200);
        }
    },
    { immediate: true },
);

function handleSort(column: string) {
    const direction = props.filters.order_direction === 'asc' ? 'desc' : 'asc';
    router.get(
        show(props.location).url,
        cleanQuery({
            ...props.filters,
            order_by: column,
            order_direction: direction,
            page: 1,
        }),
        { preserveState: true },
    );
}

function handlePageChange(page: number) {
    router.get(show(props.location).url, cleanQuery({ ...props.filters, page }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function handlePageSizeChange(per_page: number) {
    setCookie('per_page', per_page);
    const { per_page: _, ...restFilters } = props.filters;
    router.get(show(props.location).url, cleanQuery({ ...restFilters, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    router.get(show(props.location).url, {}, { preserveState: false });
}

function handleReturn() {
    window.history.back();
}

function handleExport() {
    window.open(`/nhan-vien/kho-hang/vi-tri/${props.location.id}/export`, '_blank');
}
</script>

<template>
    <Head :title="`Kho: ${location.name}`" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Kho hàng', href: index().url },
            { title: 'Vị trí', href: index().url },
            { title: location.name, href: '#' },
        ]"
    >
        <div class="space-y-6 p-6">
            <!-- Header & Navigation -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="h-8 w-8 p-0"
                        @click="handleReturn"
                    >
                        <ArrowLeft class="size-4" />
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-zinc-900">
                                {{ location.name }}
                            </h1>
                            <Badge
                                variant="outline"
                                class="bg-zinc-100 text-zinc-600"
                            >
                                {{ location.code }}
                            </Badge>
                        </div>
                        <p
                            class="flex items-center gap-1 text-sm text-muted-foreground"
                        >
                            <MapPin class="size-3" />
                            {{ location.full_address }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="handleExport"
                    >
                        Xuất báo cáo
                    </Button>
                    <Button
                        size="sm"
                        class="bg-zinc-900 text-white hover:bg-zinc-800"
                        @click="isBulkImportOpen = true"
                    >
                        <div class="flex items-center gap-2">
                            <PlusCircle class="size-4" /> Nhập kho
                        </div>
                    </Button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="border-none bg-zinc-50/50 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <Box class="size-4 text-zinc-400" /> Tổng SKU
                        </CardDescription>
                        <CardTitle class="text-2xl font-bold">{{
                            stats.total_sku
                        }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="border-none bg-zinc-50/50 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <Package class="size-4 text-zinc-400" /> Tổng số
                            lượng
                        </CardDescription>
                        <CardTitle class="text-2xl font-bold">{{
                            stats.total_quantity
                        }}
                        </CardTitle>
                    </CardHeader>
                </Card>
                <Card class="border-none bg-zinc-50/50 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <AlertTriangle class="size-4 text-red-400" /> Cảnh
                            báo tồn thấp
                        </CardDescription>
                        <CardTitle class="text-2xl font-bold text-red-500">{{
                            stats.low_stock_count
                        }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="border-none bg-zinc-50/50 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <DollarSign class="size-4 text-zinc-400" /> Tổng giá
                            trị vốn
                        </CardDescription>
                        <CardTitle class="text-2xl font-bold">{{
                            formatPrice(Number(stats.total_value))
                        }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Inventory Table -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900">
                        Chi tiết tồn kho
                    </h2>
                    <div
                        class="flex items-center gap-2 text-xs text-muted-foreground"
                    >
                        <span class="flex items-center gap-1">
                            <div class="size-2 rounded-full bg-red-500"></div>
                            {{ `Thấp (<= 5)` }}
                        </span>
                    </div>
                </div>

                <DataTableGroup
                    :columns="columns"
                    :data="props.inventory.data"
                    v-model:search="search"
                    :has-active-filters="hasActiveFilters"
                    :is-actually-loading="isActuallyLoading"
                    :total="props.inventory.meta.total"
                    :page-size="props.inventory.meta.per_page ?? 12"
                    :current-page="props.inventory.meta.current_page"
                    :last-page="props.inventory.meta.last_page"
                    :order-by="filters.order_by"
                    :order-direction="filters.order_direction"
                    @reset="resetFilters"
                    @sort="handleSort"
                    @update:page="handlePageChange"
                    @update:page-size="handlePageSizeChange"
                />
            </div>
        </div>

        <!-- Bulk Import Dialog -->
        <Dialog :open="isBulkImportOpen" @update:open="isBulkImportOpen = $event">
            <DialogContent class="sm:max-w-[800px]">
                <DialogHeader>
                    <DialogTitle>Nhập kho hàng loạt</DialogTitle>
                    <DialogDescription>
                        Thêm nhiều sản phẩm vào vị trí này cùng lúc.
                    </DialogDescription>
                </DialogHeader>

                <div class="py-4 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-zinc-500 uppercase bg-zinc-50">
                            <tr>
                                <th class="px-3 py-2 font-medium">Sản phẩm & Ghi chú</th>
                                <th class="px-3 py-2 font-medium w-32">Số lượng</th>
                                <th class="px-3 py-2 font-medium w-40">Giá vốn</th>
                                <th class="px-3 py-2 font-medium w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(item, index) in bulkImportForm.items" :key="index">
                                <td class="px-3 py-4">
                                    <div class="flex flex-col gap-2">
                                        <SearchableSelect
                                            v-model="bulkImportForm.items[index].variant_id"
                                            :options="variantsList"
                                            value-key="id"
                                            :label-key="'name'"
                                            placeholder="Chọn biến thể..."
                                            :searchable-keys="['sku', 'name']"
                                            :custom-label="(opt) => `${opt.product.name} - ${opt.name}`"
                                            @update:model-value="(val) => handleVariantSelect(index, String(val))"
                                        >
                                            <template #item="{ option }">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ option.product.name }} - {{ option.name }}</span>
                                                    <span class="text-xs text-muted-foreground">{{ option.sku }}</span>
                                                </div>
                                            </template>
                                        </SearchableSelect>
                                        <Input v-model="bulkImportForm.items[index].notes" placeholder="Ghi chú..." class="h-8 text-xs" />
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex items-center">
                                        <Input type="number" v-model.number="bulkImportForm.items[index].quantity" class="h-8" min="1" />
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex items-center">
                                        <Input type="number" v-model.number="bulkImportForm.items[index].cost_per_unit!" class="h-8" step="1000" />
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex items-center">
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-red-500" @click="removeBulkItem(index)">
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Button variant="outline" size="sm" class="mt-4 flex items-center gap-2" @click="addBulkItem">
                        <Plus class="size-4" /> Thêm dòng
                    </Button>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isBulkImportOpen = false">
                        Hủy
                    </Button>
                    <Button
                        class="bg-zinc-900 text-white hover:bg-zinc-800"
                        :disabled="bulkImportForm.processing"
                        @click="submitBulkImport"
                    >
                        {{ bulkImportForm.processing ? 'Đang xử lý...' : 'Xác nhận nhập kho' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Adjustment Dialog -->
        <Dialog :open="isAdjustmentOpen" @update:open="isAdjustmentOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Điều chỉnh tồn kho</DialogTitle>
                    <DialogDescription>
                        Điều chỉnh số lượng hoặc giá vốn cho sản phẩm này.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="space-y-2">
                        <Label>Sản phẩm</Label>
                        <div class="text-sm font-medium text-zinc-900">
                            {{ selectedItem?.product_name }} - {{ selectedItem?.variant_name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Loại điều chỉnh</Label>
                            <Select v-model="adjustmentForm.type">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="add">
                                        <div class="flex items-center gap-2">
                                            <Plus class="size-3 text-green-500" /> Tăng
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="remove">
                                        <div class="flex items-center gap-2">
                                            <Minus class="size-3 text-red-500" /> Giảm
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="cost">
                                        <div class="flex items-center gap-2">
                                            <Coins class="size-3 text-yellow-500" /> Giá vốn
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Số lượng</Label>
                            <Input
                                type="number"
                                v-model.number="adjustmentForm.quantity"
                                :disabled="adjustmentForm.type === 'cost'"
                                min="0"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Giá vốn mới (tùy chọn)</Label>
                        <Input
                            type="number"
                            v-model.number="adjustmentForm.cost_per_unit!"
                            step="0.01"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>Lý do</Label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <Button
                                v-for="note in getSampleNotes(adjustmentForm.type)"
                                :key="note"
                                variant="outline"
                                size="sm"
                                class="text-xs h-7 px-2"
                                @click="adjustmentForm.notes = note"
                            >
                                {{ note }}
                            </Button>
                        </div>
                        <Textarea
                            v-model="adjustmentForm.notes"
                            placeholder="Chọn lý do nhanh hoặc nhập chi tiết..."
                        />
                    </div>

                    <div class="flex items-center gap-2 py-2">
                        <Checkbox :model-value="adjustmentForm.force_update_price" @update:model-value="adjustmentForm.force_update_price = !adjustmentForm.force_update_price"/>
                        <Label for="force_price" class="text-xs cursor-pointer">
                            Cập nhật lại giá bán tự động
                        </Label>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isAdjustmentOpen = false">
                        Hủy
                    </Button>
                    <Button
                        class="bg-zinc-900 text-white hover:bg-zinc-800"
                        :disabled="adjustmentForm.processing"
                        @click="submitAdjustment"
                    >
                        {{ adjustmentForm.processing ? 'Đang lưu...' : 'Lưu điều chỉnh' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
