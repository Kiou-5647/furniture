<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, FileText, MapPin, Package, Truck, User, X, XCircle, CheckCircle2, DollarSign, RotateCcw, Plus, Loader2, CreditCard } from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { resend , returnItem as returnRoute } from '@/routes/employee/fulfillment/shipments';
import { cancel, complete, index, updateStatus, markPaid, createShipments, storeShipments } from '@/routes/employee/sales/orders';
import type { BreadcrumbItem, Order, ShipmentItem } from '@/types';

const VnPayPaymentDialog = createLazyComponent(
    () => import('@/components/custom/paywall/VnPayPaymentDialog.vue'),
);

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
    order: Order;
    variantStockOptions: Record<string, { location_id: string; location_name: string; location_code: string; available_qty: number }[]>;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Đơn hàng', href: index().url },
    { title: props.order?.order_number ?? '...', href: '#' },
]);

const canCancel = computed(() => !['completed', 'cancelled'].includes(props.order?.status));
const canAccept = computed(() => props.order?.status === 'pending');
const canComplete = computed(() => props.order?.status === 'processing' && !props.order?.shipping_method_id);
const canMarkPaid = computed(() => !props.order?.paid_at && props.order?.status === 'processing');
const canCreateShipments = computed(() => {
    const order = props.order;
    if (!order) return false;
    if (order.shipments && order.shipments.length > 0) return false;
    if (['completed', 'cancelled'].includes(order.status)) return false;
    if (order.payment_method !== 'cod' && !order.paid_at) return false;
    return true;
});

const showReturnDialog = ref(false);
const returnItem = ref<ShipmentItem | null>(null);
const returnShipmentId = ref('');
const returnReason = ref('');

// VNPay payment
const showVnPayDialog = ref(false);
const vnPayUrl = ref('');

function handleVnPayPayment() {
    if (!props.order?.invoices?.length) return;
    const openInvoice = props.order.invoices.find(i => i.status !== 'paid' && i.status !== 'cancelled');
    if (!openInvoice?.id) return;
    vnPayUrl.value = `/nhan-vien/ban-hang/thanh-toan/vnpay/${openInvoice.id}`;
    showVnPayDialog.value = true;
}

// Shipment creation dialog
const showShipmentsDialog = ref(false);
const shipmentRows = ref<ShipmentRow[]>([]);
const orderItemsWithStock = ref<OrderItemWithStock[]>([]);
const loadingShipments = ref(false);
const dialogError = ref('');

function getRowsForItem(variantId: string) {
    return shipmentRows.value.filter(r => r.variant_id === variantId);
}

function getAllocatedQuantity(variantId: string) {
    return shipmentRows.value
        .filter(r => r.quantity > 0 && r.variant_id === variantId)
        .reduce((sum, r) => sum + r.quantity, 0);
}

function getMaxQuantityForLocation(variantId: string, locationId: string): number {
    const item = orderItemsWithStock.value.find(i => i.variant_id === variantId);
    const stockOpt = item?.stock_options.find(s => s.location_id === locationId);
    return stockOpt?.available_qty ?? 0;
}

function getMaxQuantityForItem(variantId: string, locationId: string): number {
    return getMaxQuantityForLocation(variantId, locationId);
}

function isLocationUsed(variantId: string, locationId: string, currentIdx: number): boolean {
    const rows = getRowsForItem(variantId);
    return rows.some((r, idx) => idx !== currentIdx && r.location_id === locationId);
}

async function openCreateShipments() {
    loadingShipments.value = true;
    dialogError.value = '';
    try {
        const res = await fetch(createShipments({ order: props.order.id }).url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!res.ok) {
            // If it's a 403, it's a Gate authorization failure
            if (res.status === 403) {
                toast.error('Bạn không có quyền tạo đơn vận chuyển cho đơn hàng này.');
                return;
            }
            const errorData = await res.json().catch(() => ({ message: 'Lỗi không xác định' }));
            toast.error(errorData.message || 'Có lỗi xảy ra khi tải dữ liệu vận chuyển.');
            return;
        }

        const data = await res.json();

        if (data.error) {
            toast.error(data.error);
            return;
        }

        if (!data.items || data.items.length === 0) {
            toast.error('Không tìm thấy sản phẩm nào có thể vận chuyển cho đơn hàng này.');
            return;
        }

        orderItemsWithStock.value = data.items;

        shipmentRows.value = data.items.map((item: OrderItemWithStock) => ({
            order_item_id: item.order_item_id,
            variant_id: item.variant_id,
            location_id: item.stock_options?.[0]?.location_id ?? '',
            quantity: 0,
        }));

        showShipmentsDialog.value = true;
    } catch (e) {
        console.error(e);
        toast.error('Có lỗi kết nối xảy ra. Vui lòng thử lại.');
    } finally {
        loadingShipments.value = false;
    }
}

function getUsedLocationsForItem(variantId: string): Set<string> {
    const rows = getRowsForItem(variantId);
    return new Set(rows.map(r => r.location_id).filter(Boolean));
}

function addLocationRow(variantId: string) {
    const item = orderItemsWithStock.value.find(i => i.order_item_id === variantId);
    const used = getUsedLocationsForItem(variantId);
    const firstAvailable = item?.stock_options.find(o => !used.has(o.location_id));
    if (firstAvailable) {
        shipmentRows.value.push({
            order_item_id: item!.order_item_id,
            variant_id: variantId,
            location_id: firstAvailable.location_id,
            quantity: 0,
        });
    }
}

function removeRow(index: number) {
    shipmentRows.value.splice(index, 1);
}

function confirmCreateShipments() {
    dialogError.value = '';

    // Only consider rows with quantity > 0 for validation
    const activeRows = shipmentRows.value.filter(r => r.quantity > 0);

    // Validate each order item total equals required quantity
    for (const item of orderItemsWithStock.value) {
        const allocated = activeRows
            .filter(r => r.variant_id === item.variant_id)
            .reduce((sum, r) => sum + r.quantity, 0);
        if (allocated !== item.quantity) {
            const name = item.name || 'Sản phẩm';
            dialogError.value = `Tổng SL "${name}" phải bằng ${item.quantity} (hiện tại: ${allocated})`;
            return;
        }
    }

    // Validate each row quantity doesn't exceed location stock
    for (let i = 0; i < activeRows.length; i++) {
        const row = activeRows[i];
        if (!row.location_id) {
            dialogError.value = 'Vui lòng chọn kho cho tất cả các dòng';
            return;
        }
        const maxQty = getMaxQuantityForLocation(row.variant_id, row.location_id);
        if (row.quantity > maxQty) {
            const item = orderItemsWithStock.value.find(item => item.variant_id === row.variant_id);
            const stockOpt = item?.stock_options.find(s => s.location_id === row.location_id);
            dialogError.value = `"${item?.name}" tại ${stockOpt?.location_name ?? '?'} chỉ còn ${maxQty} (bạn nhập ${row.quantity})`;
            return;
        }
    }

    if (activeRows.length === 0) {
        dialogError.value = 'Vui lòng chọn ít nhất một kho';
        return;
    }

    router.post(storeShipments({ order: props.order.id }).url, {
        items: activeRows.map(r => ({
            order_item_id: r.order_item_id,
            location_id: r.location_id,
            quantity: r.quantity,
            variant_id: r.variant_id
        })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showShipmentsDialog.value = false;
            toast.success('Đã tạo vận chuyển thành công.');
        }
    });
    showShipmentsDialog.value = false;
}

function canReturnItem(item: ShipmentItem): boolean {
    return ['shipped', 'delivered'].includes(item.status);
}

function handleCancel() {
    if (!props.order) return;
    router.post(cancel(props.order).url, {}, {
        preserveScroll: true,
    });
}

function handleAccept() {
    if (!props.order) return;
    router.post(updateStatus(props.order).url, {
        status: 'processing',
    }, {
        preserveScroll: true,
    });
}

function handleMarkPaid() {
    if (!props.order) return;
    router.post(markPaid(props.order).url, {}, {
        preserveScroll: true,
    });
}

function handleComplete() {
    if (!props.order) return;
    router.post(complete(props.order).url, {}, {
        preserveScroll: true,
    });
}

function goBack() {
    router.visit(index().url);
}

function handleResendShipment(shipmentId: string) {
    router.post(resend({ shipment: shipmentId }).url, {}, {
        preserveScroll: true,
    });
}

function handleReturnItem(item: ShipmentItem, shipmentId: string) {
    returnItem.value = item;
    returnShipmentId.value = shipmentId;
    returnReason.value = 'Hàng lỗi / Khách từ chối';
    showReturnDialog.value = true;
}

function confirmReturn() {
    if (!returnItem.value || !returnShipmentId.value) return;
    router.post(returnRoute({ shipment: returnShipmentId.value, shipmentItem: returnItem.value.id }).url, {
        reason: returnReason.value,
    }, { preserveScroll: true });
    showReturnDialog.value = false;
    returnItem.value = null;
    returnShipmentId.value = '';
}

</script>

<template>
    <Head :title="order?.order_number ?? 'Đơn hàng'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="order" class="space-y-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <Heading
                            :title="order.order_number"
                            :description="'Tạo ngày ' + order.created_at"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge
                        :class="['text-xs', order.status_color ? `text-[${order.status_color}]` : '']"
                        variant="outline"
                    >
                        <component
                            :is="order.status === 'completed' ? CheckCircle2 : order.status === 'cancelled' ? XCircle : Package"
                            class="mr-1.5 h-3.5 w-3.5"
                        />
                        {{ order.status_label }}
                    </Badge>
                    <Badge
                        v-if="order.paid_at"
                        variant="secondary"
                        class="text-green-600"
                    >
                        Đã thanh toán
                    </Badge>
                    <Badge
                        v-if="order.payment_method"
                        :variant="order.payment_method === 'cod' ? 'secondary' : 'outline'"
                    >
                        <component
                            :is="order.payment_method === 'cod' ? Truck : DollarSign"
                            class="mr-1.5 h-3 w-3"
                        />
                        {{ order.payment_method_label }}
                    </Badge>
                    <Button
                        v-if="canMarkPaid && order.payment_method === 'cash'"
                        variant="outline"
                        class="text-green-600"
                        @click="handleMarkPaid"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" /> Thanh toán tiền mặt
                    </Button>
                    <Button
                        v-if="canMarkPaid && order.payment_method === 'bank_transfer'"
                        variant="outline"
                        class="text-purple-600"
                        @click="handleVnPayPayment"
                    >
                        <CreditCard class="mr-2 h-4 w-4" /> Chuyển khoản
                    </Button>
                    <Button
                        v-if="canAccept"
                        variant="outline"
                        class="text-blue-600"
                        @click="handleAccept"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" /> Duyệt đơn
                    </Button>
                    <Button
                        v-if="canCancel"
                        variant="outline"
                        class="text-destructive"
                        @click="handleCancel"
                    >
                        <XCircle class="mr-2 h-4 w-4" /> Hủy đơn
                    </Button>
                    <Button
                        v-if="canComplete"
                        variant="outline"
                        class="text-green-600"
                        @click="handleComplete"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" /> Hoàn thành
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Customer Info -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-medium">
                        <User class="h-4 w-4" /> Khách hàng
                    </h3>
                    <div class="space-y-1 text-sm">
                        <p v-if="order.customer" class="font-medium">{{ order.customer.name }}</p>
                        <p v-if="order.customer" class="text-muted-foreground">{{ order.customer.email }}</p>
                        <template v-else>
                            <p class="font-medium">{{ order.guest_name || 'Khách vãng lai' }}</p>
                            <p v-if="order.guest_phone" class="text-muted-foreground">{{ order.guest_phone }}</p>
                            <p v-if="order.guest_email" class="text-muted-foreground">{{ order.guest_email }}</p>
                        </template>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-medium">
                        <MapPin class="h-4 w-4" /> Địa chỉ giao hàng
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ order.shipping_address_text || '—' }}
                    </p>
                </div>

                <!-- Total -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 text-sm font-medium">Tổng tiền</h3>
                    <p class="text-2xl font-bold tabular-nums">
                        {{ Number(order.total_amount).toLocaleString('vi-VN') }}đ
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ order.total_items }} sản phẩm
                    </p>
                    <p v-if="parseFloat(order.shipping_cost) > 0" class="mt-1 text-xs text-muted-foreground">
                        Phí vận chuyển: {{ Number(order.shipping_cost).toLocaleString('vi-VN') }}đ
                    </p>
                    <p v-if="order.paid_at" class="mt-2 text-xs text-green-600 font-medium">
                        Đã thanh toán: {{ order.paid_at }}
                    </p>
                    <p v-if="order.accepted_by" class="mt-2 text-xs text-muted-foreground">
                        Người nhận: {{ order.accepted_by }}
                    </p>
                    <p v-if="order.notes" class="mt-2 text-xs text-muted-foreground">
                        Ghi chú: {{ order.notes }}
                    </p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="rounded-lg border">
                <div class="px-4 py-3 border-b">
                    <h3 class="flex items-center gap-2 text-sm font-medium">
                        <Package class="h-4 w-4" /> Sản phẩm ({{ order.items?.length ?? 0 }})
                    </h3>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs text-muted-foreground">
                            <th class="px-4 py-2 text-left">Sản phẩm</th>
                            <th class="px-4 py-2 text-center">SL</th>
                            <th class="px-4 py-2 text-right">Đơn giá</th>
                            <th class="px-4 py-2 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in order.items" :key="item.id" class="border-b text-sm">
                            <td class="px-4 py-3">{{ item.purchasable_name }}</td>
                            <td class="px-4 py-3 text-center tabular-nums">{{ item.quantity }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Number(item.unit_price).toLocaleString('vi-VN') }}đ</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium">{{ Number(item.subtotal).toLocaleString('vi-VN') }}đ</td>
                        </tr>
                        <tr v-if="!order.items?.length">
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">
                                Không có sản phẩm nào
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="order.items?.length">
                        <tr class="bg-muted/30">
                            <td colspan="3" class="px-4 py-3 text-right font-medium">Tổng cộng</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums">
                                {{ Number(order.total_amount).toLocaleString('vi-VN') }}đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Invoices -->
            <div v-if="order.invoices?.length" class="rounded-lg border">
                <div class="px-4 py-3 border-b">
                    <h3 class="flex items-center gap-2 text-sm font-medium">
                        <FileText class="h-4 w-4" /> Hóa đơn ({{ order.invoices.length }})
                    </h3>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs text-muted-foreground">
                            <th class="px-4 py-2 text-left">Mã hóa đơn</th>
                            <th class="px-4 py-2 text-center">Loại</th>
                            <th class="px-4 py-2 text-center">Trạng thái</th>
                            <th class="px-4 py-2 text-right">Phải thu</th>
                            <th class="px-4 py-2 text-right">Đã thu</th>
                            <th class="px-4 py-2 text-right">Còn nợ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="inv in order.invoices" :key="inv.id" class="border-b text-sm">
                            <td class="px-4 py-3 font-mono text-xs">{{ inv.invoice_number }}</td>
                            <td class="px-4 py-3 text-center">{{ inv.type_label }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['text-xs', inv.status_color ? `text-${inv.status_color}-600` : '']">
                                    {{ inv.status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Number(inv.amount_due).toLocaleString('vi-VN') }}đ</td>
                            <td class="px-4 py-3 text-right tabular-nums text-green-600">{{ Number(inv.amount_paid).toLocaleString('vi-VN') }}đ</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium">{{ Number(inv.remaining_balance).toLocaleString('vi-VN') }}đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Shipments -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <Truck class="h-4 w-4" /> Đơn vận chuyển ({{ order.shipments?.length ?? 0 }})
                    </div>
                    <Button
                        v-if="canCreateShipments"
                        variant="outline"
                        size="sm"
                        class="text-blue-600"
                        :disabled="loadingShipments"
                        @click="openCreateShipments"
                    >
                        <Loader2 v-if="loadingShipments" class="mr-2 h-4 w-4 animate-spin" />
                        <Plus v-else class="mr-2 h-4 w-4" />
                        Tạo đơn vận chuyển
                    </Button>
                </div>

                <div
                    v-for="shipment in order.shipments"
                    :key="shipment.id"
                    class="rounded-lg border"
                >
                    <div class="flex items-center justify-between border-b px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm">{{ shipment.shipment_number }}</span>
                            <Badge :class="['text-xs', shipment.status_color ? `text-${shipment.status_color}-600` : '']" variant="outline">
                                {{ shipment.status_label }}
                            </Badge>
                            <span v-if="shipment.origin_location" class="text-xs text-muted-foreground">
                                Từ: {{ shipment.origin_location.name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                v-if="shipment.status === 'cancelled' && order.status !== 'cancelled'"
                                variant="outline"
                                size="sm"
                                class="text-blue-600 text-xs h-7"
                                @click="handleResendShipment(shipment.id)"
                            >
                                <Truck class="mr-1 h-3 w-3" /> Gửi lại
                            </Button>
                        </div>
                    </div>
                    <table class="w-full table-fixed">
                        <thead>
                            <tr class="border-b bg-muted/50 text-xs text-muted-foreground">
                                <th class="w-[25%] px-4 py-2 text-left">Sản phẩm</th>
                                <th class="w-[7%] px-4 py-2 text-center">SL</th>
                                <th class="w-[15%] px-4 py-2 text-center">Nguồn</th>
                                <th class="w-[15%] px-4 py-2 text-center">Trạng thái</th>
                                <th class="w-[15%] px-4 py-2 text-center">Thao tác</th>
                                <th class="w-[23%] px-4 py-2 text-center">Người gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in shipment.items" :key="item.id" class="border-b text-sm">
                                <td class="w-[25%] truncate px-4 py-3">{{ item.order_item?.purchasable_name ?? '—' }}</td>
                                <td class="w-[7%] px-4 py-3 text-center tabular-nums">{{ item.quantity_shipped }}</td>
                                <td class="w-[20%] truncate px-4 py-3 text-center text-xs text-muted-foreground">
                                    {{ item.source_location?.name ?? '—' }}
                                </td>
                                <td class="w-[15%] px-4 py-3 text-center">
                                    <Badge
                                        :class="['text-xs', item.status_color ? `text-${item.status_color}-600` : '']"
                                        variant="outline"
                                    >
                                        {{ item.status_label }}
                                    </Badge>
                                </td>
                                <td class="w-[15%] px-4 py-3 text-center">
                                    <Button
                                        v-if="canReturnItem(item)"
                                        variant="outline"
                                        size="sm"
                                        class="text-orange-600 text-xs h-7"
                                        @click="handleReturnItem(item, shipment.id)"
                                    >
                                        <RotateCcw class="mr-1 h-3 w-3" /> Trả
                                    </Button>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </td>
                                <td class="w-[23%] px-4 py-3 text-center">
                                    <template v-if="shipment.handled_by">
                                        <div class="text-xs font-medium">{{ shipment.handled_by.full_name }}</div>
                                        <div class="text-[10px] text-muted-foreground">{{ shipment.handled_by.phone ?? '—' }}</div>
                                    </template>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Refunds -->
            <div v-if="order.refunds?.length" class="space-y-3">
                <div class="flex items-center gap-2 text-sm font-medium">
                    <DollarSign class="h-4 w-4" /> Yêu cầu hoàn tiền ({{ order.refunds.length }})
                </div>
                <div
                    v-for="refund in order.refunds"
                    :key="refund.id"
                    class="rounded-lg border"
                >
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs">{{ refund.id.substring(0, 8) }}...</span>
                            <Badge :class="['text-xs', refund.status_color ? `text-${refund.status_color}-600` : '']" variant="outline">
                                {{ refund.status_label }}
                            </Badge>
                        </div>
                        <span class="text-sm font-medium tabular-nums">{{ Number(refund.amount).toLocaleString('vi-VN') }}đ</span>
                    </div>
                    <div class="flex gap-4 px-4 py-2 text-xs">
                        <span v-if="refund.reason" class="text-muted-foreground">Lý do: {{ refund.reason }}</span>
                        <span v-if="refund.requested_by" class="text-muted-foreground">Tạo bởi: {{ refund.requested_by.full_name }}</span>
                        <span v-if="refund.processed_by" class="text-muted-foreground">Duyệt bởi: {{ refund.processed_by.full_name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Item Dialog -->
        <div
            v-if="showReturnDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showReturnDialog = false"
        >
            <div class="w-full max-w-md rounded-lg border bg-background p-6 shadow-lg">
                <h3 class="mb-4 text-lg font-semibold">Trả hàng</h3>
                <p v-if="returnItem" class="mb-3 text-sm text-muted-foreground">
                    {{ returnItem.order_item?.purchasable_name ?? 'Sản phẩm' }} — SL: {{ returnItem.quantity_shipped }}
                </p>
                <Input v-model="returnReason" placeholder="Lý do trả hàng..." class="mb-4" />
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="showReturnDialog = false">Hủy</Button>
                    <Button variant="destructive" @click="confirmReturn">Xác nhận trả hàng</Button>
                </div>
            </div>
        </div>

        <!-- TODO: Create Shipments Dialog -->
        <div
            v-if="showShipmentsDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showShipmentsDialog = false"
        >
            <div class="w-full max-w-lg rounded-lg border bg-background p-6 shadow-lg max-h-[80vh] overflow-y-auto">
                <h3 class="mb-4 text-lg font-semibold">Tạo đơn vận chuyển</h3>
                <div class="space-y-4">
                    <div
                        v-for="item in orderItemsWithStock"
                        :key="item.variant_id"
                        class="rounded-lg border p-3"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-sm font-medium">{{ item.name }}</span>
                                <span class="ml-2 text-xs text-muted-foreground">
                                    (SL: {{ item.quantity }})
                                </span>
                            </div>
                            <div class="text-xs">
                                <span :class="getAllocatedQuantity(item.variant_id) === item.quantity ? 'text-green-600' : 'text-orange-600'">
                                    Đã chia: {{ getAllocatedQuantity(item.variant_id) }}/{{ item.quantity }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(row, idx) in getRowsForItem(item.variant_id)"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <Select
                                    :model-value="row.location_id"
                                    @update:model-value="(val) => { row.location_id = val as string; row.quantity = Math.min(row.quantity, getMaxQuantityForItem(item.variant_id, row.location_id)); }"
                                >
                                    <SelectTrigger class="flex-1 text-sm">
                                        <SelectValue placeholder="Chọn kho..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="opt in item.stock_options.filter(o => !isLocationUsed(item.variant_id, o.location_id, idx))"
                                            :key="opt.location_id"
                                            :value="opt.location_id"
                                        >
                                            {{ opt.location_name }} ({{ opt.available_qty }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Input
                                    :model-value="row.quantity"
                                    type="number"
                                    min="0"
                                    :max="getMaxQuantityForItem(item.variant_id, row.location_id)"
                                    class="w-20 text-sm"
                                    @update:model-value="(val: any) => { const v = parseInt(val) || 0; row.quantity = Math.min(v, getMaxQuantityForItem(item.variant_id, row.location_id)); }"
                                />
                                <Button
                                    v-if="getRowsForItem(item.variant_id).length > 1"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 shrink-0"
                                    @click="removeRow(shipmentRows.indexOf(row))"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <Button
                            v-if="getRowsForItem(item.variant_id).length < item.stock_options.length"
                            variant="outline"
                            size="sm"
                            class="mt-2 text-xs h-7"
                            @click="addLocationRow(item.variant_id)"
                        >
                            <Plus class="mr-1 h-3 w-3" /> Thêm kho
                        </Button>
                    </div>
                </div>

                <p v-if="dialogError" class="mt-3 text-sm text-destructive">{{ dialogError }}</p>

                <div class="flex justify-end gap-2 mt-4">
                    <Button variant="outline" @click="showShipmentsDialog = false">Hủy</Button>
                    <Button @click="confirmCreateShipments">Xác nhận</Button>
                </div>
            </div>
        </div>
    </AppLayout>

    <VnPayPaymentDialog
        :open="showVnPayDialog"
        :payment-url="vnPayUrl"
        :amount="props.order?.total_amount ?? '0'"
        @close="showVnPayDialog = false"
    />
</template>
