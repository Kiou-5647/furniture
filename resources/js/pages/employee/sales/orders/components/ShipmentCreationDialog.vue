<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Plus, X, PackageCheck, AlertCircle } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { create, store } from '@/routes/employee/shipments';


interface OrderItemWithStock {
    bundle_name: string | null;
    order_item_id: string;
    variant_id: string;
    sku: string;
    name: string;
    quantity: number;
    is_bundle_component: boolean;
    stock_options: Array<{
        location_id: string;
        location_name: string;
        location_code: string;
        available_qty: number;
    }>;
}

interface ShipmentRow {
    order_item_id: string;
    variant_id: string;
    location_id: string;
    quantity: number;
}

const props = defineProps<{
    modelValue: boolean;
    orderId: string;
}>();

const emit = defineEmits(['update:modelValue', 'success']);

const shipmentRows = ref<ShipmentRow[]>([]);
const orderItemsWithStock = ref<OrderItemWithStock[]>([]);
const loading = ref(false);
const dialogError = ref('');

const bundleItems = computed(() => {
    return orderItemsWithStock.value.filter((item) => item.is_bundle_component);
});
const standaloneItems = computed(() => {
    return orderItemsWithStock.value.filter(
        (item) => !item.is_bundle_component,
    );
});

async function loadShipmentData() {
    loading.value = true;
    dialogError.value = '';
    try {
        const res = await fetch(create({ order: props.orderId }).url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
            if (res.status === 403) {
                toast.error('Bạn không có quyền tạo đơn vận chuyển.');
                return;
            }
            const errorData = await res
                .json()
                .catch(() => ({ message: 'Lỗi không xác định' }));
            toast.error(errorData.message || 'Lỗi tải dữ liệu vận chuyển.');
            return;
        }

        const data = await res.json();
        if (data.error) {
            toast.error(data.error);
            return;
        }

        orderItemsWithStock.value = data.items;
        shipmentRows.value = data.items.map((item: OrderItemWithStock) => ({
            order_item_id: item.order_item_id,
            variant_id: item.variant_id,
            location_id: item.stock_options?.[0]?.location_id ?? '',
            quantity: 0,
        }));
    } catch {
        toast.error('Lỗi kết nối xảy ra.');
    } finally {
        loading.value = false;
    }
}

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) loadShipmentData();
    },
);

function getRowsForItem(orderItemId: string, variantId: string) {
    return shipmentRows.value.filter((r) => r.order_item_id === orderItemId && r.variant_id === variantId);
}

function getAllocatedQuantity(orderItemId: string, variantId: string) {
    return getRowsForItem(orderItemId, variantId).reduce(
        (sum, r) => sum + (Number(r.quantity) || 0),
        0,
    );
}

function isLocationUsed(
    orderItemId: string,
    variantId: string,
    locationId: string,
    currentIdx: number,
) {
    return getRowsForItem(orderItemId, variantId).some(
        (r, idx) => idx !== currentIdx && r.location_id === locationId,
    );
}

function addLocationRow(orderItemId: string, variantId: string) {
    const item = orderItemsWithStock.value.find(i => i.order_item_id === orderItemId);
    const used = new Set(getRowsForItem(orderItemId, variantId).map(r => r.location_id));
    const firstAvailable = item?.stock_options.find(o => !used.has(o.location_id));

    if (firstAvailable) {
        shipmentRows.value.push({
            order_item_id: item!.order_item_id,
            variant_id: item!.variant_id,
            location_id: firstAvailable.location_id,
            quantity: 0,
        });
    } else {
        toast.error('Đã sử dụng hết các kho có sẵn cho sản phẩm này.');
    }
}

function removeRow(index: number) {
    shipmentRows.value.splice(index, 1);
}

function confirmCreateShipments() {
    dialogError.value = '';
    const activeRows = shipmentRows.value.filter((r) => r.quantity > 0);

    // 1. Validate totals
    for (const item of orderItemsWithStock.value) {
        const allocated = getAllocatedQuantity(item.order_item_id, item.variant_id);
        if (allocated !== item.quantity) {
            dialogError.value = `Tổng SL "${item.name}" phải bằng ${item.quantity} (hiện tại: ${allocated})`;
            return;
        }
    }

    // 2. Validate individual row stock
    for (const row of activeRows) {
        const item = orderItemsWithStock.value.find(
            (i) => i.variant_id === row.variant_id,
        );
        const stock = item?.stock_options.find(
            (s) => s.location_id === row.location_id,
        );
        if (!stock || row.quantity > stock.available_qty) {
            dialogError.value = `Kho ${stock?.location_name || '?'} chỉ còn ${stock?.available_qty || 0} cho sản phẩm này.`;
            return;
        }
    }

    router.post(
        store({ order: props.orderId }).url,
        {
            items: activeRows.map((r) => ({
                order_item_id: r.order_item_id,
                location_id: r.location_id,
                quantity: r.quantity,
                variant_id: r.variant_id,
            })),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:modelValue', false);
                emit('success');
                toast.success('Đã tạo vận chuyển thành công.');
            },
        },
    );
}
</script>

<template>
    <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
        <DialogContent
            class="flex max-h-[90vh] max-w-2xl flex-col overflow-hidden p-0"
        >
            <!-- Header -->
            <DialogHeader class="border-b bg-muted/20 p-6 pb-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <PackageCheck class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle class="text-lg font-bold"
                            >Phân bổ vận chuyển</DialogTitle
                        >
                        <DialogDescription>
                            Chọn kho và số lượng cho từng sản phẩm để tạo đơn
                            vận chuyển.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <!-- Body -->
            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                <div v-if="loading" class="space-y-4 py-20 text-center">
                    <Loader2
                        class="mx-auto h-10 w-10 animate-spin text-primary"
                    />
                    <p class="animate-pulse text-sm text-muted-foreground">
                        Đang đồng bộ kho hàng...
                    </p>
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-if="dialogError"
                        class="flex items-start gap-3 rounded-xl border border-destructive/20 bg-destructive/10 p-4 text-sm text-destructive"
                    >
                        <AlertCircle class="h-5 w-5 shrink-0" />
                        <p>{{ dialogError }}</p>
                    </div>

                    <div v-if="bundleItems.length > 0" class="space-y-4">
                        <div class="flex items-center gap-2 px-1">
                            <div class="h-4 w-1 bg-primary rounded-full"></div>
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sản phẩm trong Combo</h3>
                        </div>

                        <div class="grid gap-6">
                            <div v-for="item in bundleItems" :key="item.order_item_id" class="rounded-xl border bg-card shadow-sm overflow-hidden">
                                <!-- Item Header (Dùng order_item_id cho tiến độ) -->
                                <div class="px-4 py-3 border-b bg-muted/30 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-sm">{{ item.name }}</span>
                                        <Badge variant="secondary" class="text-[10px] h-5">Gói sản phẩm</Badge>
                                        <Badge variant="outline" class="text-[10px] h-5">{{ item.sku }}</Badge>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold" :class="getAllocatedQuantity(item.order_item_id, item.variant_id) === item.quantity ? 'text-green-600' : 'text-orange-600'">
                                            {{ getAllocatedQuantity(item.order_item_id, item.variant_id) }} / {{ item.quantity }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Allocation Rows (Logic Split theo order_item_id) -->
                                <div class="p-4 space-y-3">
                                    <div v-for="(row, index) in getRowsForItem(item.order_item_id, item.variant_id)" :key="`${item.order_item_id}-${index}`" class="flex items-center gap-3">
                                        <Select v-model="row.location_id">
                                            <SelectTrigger class="h-9 text-xs w-full">
                                                <SelectValue placeholder="Chọn kho..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="opt in item.stock_options" :key="opt.location_id" :value="opt.location_id" :disabled="isLocationUsed(item.order_item_id, item.variant_id, opt.location_id, index)">
                                                    {{ opt.location_name }} (SL: {{ opt.available_qty }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <Input v-model.number="row.quantity" type="number" class="h-9 text-xs w-24 text-center" />
                                        <Button v-if="getRowsForItem(item.order_item_id, item.variant_id).length > 1" variant="ghost" size="icon" @click="removeRow(index)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <Button variant="outline" size="sm" class="h-8 text-xs w-full border-dashed" @click="addLocationRow(item.order_item_id, item.variant_id)">
                                        <Plus class="mr-2 h-3 w-3" /> Thêm nguồn cấp hàng
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="standaloneItems.length > 0" class="space-y-4">
                        <div class="flex items-center gap-2 px-1">
                            <div class="h-4 w-1 bg-muted-foreground rounded-full"></div>
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sản phẩm lẻ</h3>
                        </div>

                        <div class="grid gap-6">
                            <div v-for="item in standaloneItems" :key="item.order_item_id" class="rounded-xl border bg-card shadow-sm overflow-hidden">
                                <div class="px-4 py-3 border-b bg-muted/30 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-sm">{{ item.name }}</span>
                                        <Badge variant="secondary" class="text-[10px] h-5">Sản phẩm lẻ</Badge>
                                        <Badge variant="outline" class="text-[10px] h-5">{{ item.sku }}</Badge>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold" :class="getAllocatedQuantity(item.order_item_id, item.variant_id) === item.quantity ? 'text-green-600' : 'text-orange-600'">
                                            {{ getAllocatedQuantity(item.order_item_id, item.variant_id) }} / {{ item.quantity }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Allocation Rows (Logic Split theo order_item_id) -->
                                <div class="p-4 space-y-3">
                                    <div v-for="(row, index) in getRowsForItem(item.order_item_id, item.variant_id)" :key="`${item.order_item_id}-${index}`" class="flex items-center gap-3">
                                        <Select v-model="row.location_id">
                                            <SelectTrigger class="h-9 text-xs w-full">
                                                <SelectValue placeholder="Chọn kho..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="opt in item.stock_options" :key="opt.location_id" :value="opt.location_id" :disabled="isLocationUsed(item.order_item_id, item.variant_id, opt.location_id, index)">
                                                    {{ opt.location_name }} (SL: {{ opt.available_qty }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <Input v-model.number="row.quantity" type="number" class="h-9 text-xs w-24 text-center" />
                                        <Button v-if="getRowsForItem(item.order_item_id, item.variant_id).length > 1" variant="ghost" size="icon" @click="removeRow(index)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <Button variant="outline" size="sm" class="h-8 text-xs w-full border-dashed" @click="addLocationRow(item.order_item_id, item.variant_id)">
                                        <Plus class="mr-2 h-3 w-3" /> Thêm nguồn cấp hàng
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t bg-muted/20 p-6">
                <Button
                    variant="ghost"
                    @click="emit('update:modelValue', false)"
                    >Hủy bỏ</Button
                >
                <Button
                    variant="default"
                    class="px-8"
                    :disabled="loading"
                    @click="confirmCreateShipments"
                >
                    <PackageCheck v-if="!loading" class="mr-2 h-4 w-4" />
                    <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                    Xác nhận tạo đơn
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
