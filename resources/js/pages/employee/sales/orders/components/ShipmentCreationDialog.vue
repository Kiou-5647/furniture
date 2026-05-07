<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Plus, X, PackageCheck, AlertCircle } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    createShipments,
    storeShipments,
} from '@/routes/employee/sales/orders';

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

async function loadShipmentData() {
    loading.value = true;
    dialogError.value = '';
    try {
        const res = await fetch(createShipments({ order: props.orderId }).url, {
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

// Logic helpers
function getRowsForItem(variantId: string) {
    return shipmentRows.value.filter((r) => r.variant_id === variantId);
}

function getAllocatedQuantity(variantId: string) {
    return getRowsForItem(variantId).reduce(
        (sum, r) => sum + (Number(r.quantity) || 0),
        0,
    );
}

function isLocationUsed(
    variantId: string,
    locationId: string,
    currentIdx: number,
): boolean {
    return getRowsForItem(variantId).some(
        (r, idx) => idx !== currentIdx && r.location_id === locationId,
    );
}

function addLocationRow(variantId: string) {
    const item = orderItemsWithStock.value.find(
        (i) => i.variant_id === variantId,
    );
    const used = new Set(getRowsForItem(variantId).map((r) => r.location_id));
    const firstAvailable = item?.stock_options.find(
        (o) => !used.has(o.location_id),
    );

    if (firstAvailable) {
        shipmentRows.value.push({
            order_item_id: item!.order_item_id,
            variant_id: variantId,
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
        const allocated = getAllocatedQuantity(item.variant_id);
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
        storeShipments({ order: props.orderId }).url,
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
        <DialogContent class="max-w-2xl max-h-[90vh] overflow-hidden flex flex-col p-0">
            <!-- Header -->
            <DialogHeader class="p-6 pb-4 border-b bg-muted/20">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <PackageCheck class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle class="text-lg font-bold">Phân bổ vận chuyển</DialogTitle>
                        <DialogDescription>
                            Chọn kho và số lượng cho từng sản phẩm để tạo đơn vận chuyển.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <div v-if="loading" class="py-20 text-center space-y-4">
                    <Loader2 class="h-10 w-10 animate-spin mx-auto text-primary" />
                    <p class="text-sm text-muted-foreground animate-pulse">Đang đồng bộ kho hàng...</p>
                </div>

                <div v-else class="space-y-6">
                    <div v-if="dialogError" class="flex items-start gap-3 p-4 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-xl">
                        <AlertCircle class="h-5 w-5 shrink-0" />
                        <p>{{ dialogError }}</p>
                    </div>

                    <div class="grid gap-6">
                        <div v-for="item in orderItemsWithStock" :key="item.variant_id" class="rounded-xl border bg-card shadow-sm overflow-hidden">
                            <!-- Item Header -->
                            <div class="px-4 py-3 border-b bg-muted/30 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-sm">{{ item.name }}</span>
                                    <Badge variant="outline" class="text-[10px] h-5">{{ item.sku }}</Badge>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <span class="text-[10px] text-muted-foreground block leading-none">Tiến độ</span>
                                        <span class="text-xs font-bold" :class="getAllocatedQuantity(item.variant_id) === item.quantity ? 'text-green-600' : 'text-orange-600'">
                                            {{ getAllocatedQuantity(item.variant_id) }} / {{ item.quantity }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Allocation Rows -->
                            <div class="p-4 space-y-3">
                                <!-- Use a unique key combining variant and index to prevent duplication issues -->
                                <div v-for="(row, index) in getRowsForItem(item.variant_id)" :key="`${item.variant_id}-${index}`" class="flex items-center gap-3 group">
                                    <Select v-model="row.location_id">
                                        <SelectTrigger class="h-9 text-xs w-full">
                                            <SelectValue placeholder="Chọn kho..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="opt in item.stock_options" :key="opt.location_id" :value="opt.location_id" :disabled="isLocationUsed(item.variant_id, opt.location_id, index)">
                                                <div class="flex justify-between w-full gap-4">
                                                    <span>{{ opt.location_name }} ({{ opt.location_code }})</span>
                                                    <span class="text-muted-foreground font-mono">SL: {{ opt.available_qty }}</span>
                                                </div>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-muted-foreground">SL:</span>
                                        <Input v-model.number="row.quantity" type="number" class="h-9 text-xs w-24 text-center" />
                                    </div>
                                    <Button v-if="getRowsForItem(item.variant_id).length > 1" variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground hover:text-destructive transition-colors" @click="removeRow(index)">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>

                                <Button variant="outline" size="sm" class="h-8 text-xs w-full border-dashed" @click="addLocationRow(item.variant_id)">
                                    <Plus class="mr-2 h-3 w-3" /> Thêm nguồn cấp hàng
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t bg-muted/20 flex justify-end gap-3">
                <Button variant="ghost" @click="emit('update:modelValue', false)">Hủy bỏ</Button>
                <Button variant="default" class="px-8" :disabled="loading" @click="confirmCreateShipments">
                    <PackageCheck v-if="!loading" class="mr-2 h-4 w-4" />
                    <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                    Xác nhận tạo đơn
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
