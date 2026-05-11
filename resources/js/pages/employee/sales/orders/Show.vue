<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    FileText,
    MapPin,
    Package,
    Truck,
    User,
    XCircle,
    CheckCircle2,
    DollarSign,
    RotateCcw,
    Plus,
    CreditCard,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { initiate } from '@/actions/App/Http/Controllers/Payment/VnPayPaymentController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    cancel,
    complete,
    index,
    updateStatus,
    markPaid,
} from '@/routes/employee/orders';
import { resend, returnItem as returnRoute } from '@/routes/employee/shipments';
import type { BreadcrumbItem, Order, ShipmentItem } from '@/types';

const VnPayPaymentDialog = createLazyComponent(
    () => import('@/components/custom/paywall/VnPayPaymentDialog.vue'),
);

const ShipmentCreationDialog = createLazyComponent(
    () =>
        import('@/pages/employee/sales/orders/components/ShipmentCreationDialog.vue'),
);

const props = defineProps<{
    order: Order;
    variantStockOptions: Record<
        string,
        {
            location_id: string;
            location_name: string;
            location_code: string;
            available_qty: number;
        }[]
    >;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Đơn hàng', href: index().url },
    { title: props.order?.order_number ?? '...', href: '#' },
]);

const canAccept = computed(() => props.order?.can_accept);
const canMarkPaid = computed(() => props.order?.can_mark_paid);
const canComplete = computed(() => props.order?.can_complete);
const canCancel = computed(() => props.order?.can_cancel);
const canCreateShipments = computed(() => props.order?.can_create_shipment);

const showReturnDialog = ref(false);
const returnItem = ref<ShipmentItem | null>(null);
const returnShipmentId = ref('');
const returnReason = ref('');

const showShipmentsDialog = ref(false);
const showVnPayDialog = ref(false);
const vnPayUrl = ref('');

function handleVnPayPayment() {
    if (!props.order?.invoices?.length) return;
    const openInvoice = props.order.invoices.find(
        (i) => i.status !== 'paid' && i.status !== 'cancelled',
    );
    if (!openInvoice?.id) return;
    vnPayUrl.value = initiate(openInvoice.id).url;
    showVnPayDialog.value = true;
}

function canReturnItem(item: ShipmentItem): boolean {
    return item.can_return;
}

function handleCancel() {
    if (!props.order) return;
    router.post(
        cancel(props.order).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleAccept() {
    if (!props.order) return;
    router.post(
        updateStatus(props.order).url,
        {
            status: 'processing',
        },
        {
            preserveScroll: true,
        },
    );
}

function handleMarkPaid() {
    if (!props.order) return;
    router.post(
        markPaid(props.order).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleComplete() {
    if (!props.order) return;
    router.post(
        complete(props.order).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function goBack() {
    router.visit(index().url);
}

function handleResendShipment(shipmentId: string) {
    router.post(
        resend({ shipment: shipmentId }).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleReturnItem(item: ShipmentItem, shipmentId: string) {
    returnItem.value = item;
    returnShipmentId.value = shipmentId;
    showReturnDialog.value = true;
}

function confirmReturn() {
    if (!returnItem.value || !returnShipmentId.value) return;
    router.post(
        returnRoute({
            shipment: returnShipmentId.value,
            shipmentItem: returnItem.value.id,
        }).url,
        {
            reason: returnReason.value,
        },
        { preserveScroll: true },
    );
    showReturnDialog.value = false;
    returnItem.value = null;
    returnShipmentId.value = '';
}
</script>

<template>
    <Head :title="order?.order_number ?? 'Đơn hàng'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="order" class="space-y-6 p-4 lg:p-6">
            <!-- Top Action Bar -->
            <div
                class="flex flex-col justify-between gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="goBack"
                        class="rounded-full"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <Heading :title="order.order_number" />
                            <Badge
                                :class="[
                                    'text-xs',
                                    order.status_color
                                        ? `text-[${order.status_color}]`
                                        : '',
                                ]"
                                variant="outline"
                            >
                                <component
                                    :is="
                                        order.status === 'completed'
                                            ? CheckCircle2
                                            : order.status === 'cancelled'
                                              ? XCircle
                                              : Package
                                    "
                                    class="mr-1.5 h-3.5 w-3.5"
                                />
                                {{ order.status_label }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Tạo ngày {{ order.created_at }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Payment Status Badges -->
                    <div class="mr-2 flex items-center gap-2">
                        <Badge
                            v-if="order.paid_at"
                            variant="secondary"
                            class="border-green-200 bg-green-100 text-green-700 hover:bg-green-100"
                        >
                            Đã thanh toán
                        </Badge>
                        <Badge
                            v-if="order.payment_method"
                            :variant="
                                order.payment_method === 'cod'
                                    ? 'secondary'
                                    : 'outline'
                            "
                        >
                            <component
                                :is="
                                    order.payment_method === 'cod'
                                        ? Truck
                                        : DollarSign
                                "
                                class="mr-1.5 h-3 w-3"
                            />
                            {{ order.payment_method_label }}
                        </Badge>
                    </div>

                    <!-- Primary Actions -->
                    <div class="flex items-center gap-2 border-l pl-2">
                        <Button
                            v-if="canAccept"
                            variant="outline"
                            class="border-blue-200 bg-blue-50/50 text-blue-600"
                            @click="handleAccept"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" /> Duyệt đơn
                        </Button>
                        <Button
                            v-if="canMarkPaid"
                            variant="outline"
                            class="border-green-200 bg-green-50/50 text-green-600"
                            @click="handleMarkPaid"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" /> Thu tiền mặt
                        </Button>
                        <Button
                            v-if="
                                canMarkPaid &&
                                order.payment_method === 'bank_transfer'
                            "
                            variant="outline"
                            class="border-purple-200 bg-purple-50/50 text-purple-600"
                            @click="handleVnPayPayment"
                        >
                            <CreditCard class="mr-2 h-4 w-4" /> Chuyển khoản
                        </Button>
                        <Button
                            v-if="canComplete"
                            variant="default"
                            class="bg-green-600 hover:bg-green-700"
                            @click="handleComplete"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" /> Hoàn thành
                        </Button>
                        <Button
                            v-if="canCancel"
                            variant="ghost"
                            class="text-destructive hover:bg-destructive/10"
                            @click="handleCancel"
                        >
                            <XCircle class="mr-2 h-4 w-4" /> Hủy
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column: Order Info Sidebar -->
                <div class="space-y-6 lg:col-span-4">
                    <!-- Customer Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <User class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Thông tin khách hàng
                            </h3>
                        </div>
                        <div class="space-y-3 p-4">
                            <template v-if="order.customer">
                                <div class="space-y-1">
                                    <p
                                        class="text-base font-bold text-muted-foreground italic"
                                    >
                                        Khách vãng lai
                                    </p>
                                    <p v-if="order.guest_name" class="text-sm">
                                        {{ order.guest_name }}
                                    </p>
                                    <p
                                        v-if="order.guest_phone"
                                        class="text-sm text-muted-foreground"
                                    >
                                        {{ order.guest_phone }}
                                    </p>
                                    <p
                                        v-if="order.guest_email"
                                        class="text-sm text-muted-foreground"
                                    >
                                        {{ order.guest_email }}
                                    </p>
                                </div>
                            </template>
                            <div v-else class="space-y-1">
                                <p class="text-base font-bold">
                                    {{ order.customer!.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ order.customer!.email }}
                                </p>
                                <p
                                    v-if="order.customer!.phone"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ order.customer!.phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Địa chỉ giao hàng
                            </h3>
                        </div>
                        <div class="p-4">
                            <p
                                class="text-sm leading-relaxed text-muted-foreground"
                            >
                                {{
                                    order.shipping_address_text ||
                                    'Chưa có thông tin địa chỉ'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Chi tiết thanh toán
                            </h3>
                        </div>
                        <div class="space-y-3 p-4">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-muted-foreground"
                                    >Số lượng sản phẩm</span
                                >
                                <span class="font-medium"
                                    >{{ order.total_items }} SP</span
                                >
                            </div>
                            <div
                                v-if="parseFloat(order.shipping_cost) > 0"
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-muted-foreground"
                                    >Phí vận chuyển</span
                                >
                                <span class="font-medium tabular-nums"
                                    >{{
                                        Number(
                                            order.shipping_cost,
                                        ).toLocaleString('vi-VN')
                                    }}đ</span
                                >
                            </div>
                            <div
                                class="flex items-end justify-between border-t pt-3"
                            >
                                <span class="text-sm font-medium"
                                    >Tổng cộng</span
                                >
                                <span
                                    class="text-2xl font-black text-primary tabular-nums"
                                >
                                    {{
                                        Number(
                                            order.total_amount,
                                        ).toLocaleString('vi-VN')
                                    }}đ
                                </span>
                            </div>
                            <div
                                v-if="order.paid_at"
                                class="mt-2 rounded border border-green-100 bg-green-50 p-2 text-center"
                            >
                                <p
                                    class="text-[11px] font-medium tracking-wider text-green-600 uppercase"
                                >
                                    Đã thanh toán lúc
                                </p>
                                <p class="text-xs font-bold text-green-700">
                                    {{ order.paid_at }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Meta -->
                    <div class="space-y-4 rounded-xl border bg-muted/20 p-4">
                        <div v-if="order.notes" class="space-y-1">
                            <p
                                class="text-[11px] font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Ghi chú đơn hàng
                            </p>
                            <p class="text-xs text-foreground/80 italic">
                                {{ order.notes }}
                            </p>
                        </div>
                        <div
                            v-if="order.accepted_by"
                            class="flex items-center gap-2 border-t border-muted pt-2"
                        >
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary"
                            >
                                {{ order.accepted_by.charAt(0).toUpperCase() }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Nhận đơn:
                                <span class="font-medium text-foreground">{{
                                    order.accepted_by
                                }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Right Column: Order Data -->
                <div class="space-y-6 lg:col-span-8">
                    <!-- Products Section -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <Package class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Sản phẩm ({{ order.items?.length ?? 0 }})
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr
                                        class="bg-muted/50 text-xs tracking-wider text-muted-foreground uppercase"
                                    >
                                        <th
                                            class="px-4 py-3 text-left font-medium"
                                        >
                                            Sản phẩm
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            SL
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-medium"
                                        >
                                            Đơn giá
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-medium"
                                        >
                                            Thành tiền
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="item in order.items"
                                        :key="item.id"
                                        class="text-sm transition-colors hover:bg-muted/20"
                                    >
                                        <td
                                            class="px-4 py-4 font-medium text-foreground"
                                        >
                                            {{ item.purchasable_name }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center tabular-nums"
                                        >
                                            {{ item.quantity }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-muted-foreground tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    item.unit_price,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right font-semibold tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    item.subtotal,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </td>
                                    </tr>
                                    <tr v-if="!order.items?.length">
                                        <td
                                            colspan="4"
                                            class="px-4 py-12 text-center text-sm text-muted-foreground italic"
                                        >
                                            Không có sản phẩm nào trong đơn hàng
                                            này
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot
                                    v-if="order.items?.length"
                                    class="bg-muted/30"
                                >
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                        >
                                            Tổng cộng
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right text-lg font-bold text-primary tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    order.total_amount,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Invoices Section -->
                    <div
                        v-if="order.invoices?.length"
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <FileText class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Hóa đơn ({{ order.invoices.length }})
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr
                                        class="bg-muted/50 text-xs tracking-wider text-muted-foreground uppercase"
                                    >
                                        <th
                                            class="px-4 py-3 text-left font-medium"
                                        >
                                            Mã hóa đơn
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            Loại
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            Trạng thái
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-medium"
                                        >
                                            Phải thu
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-medium"
                                        >
                                            Đã thu
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-medium"
                                        >
                                            Còn nợ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y text-sm">
                                    <tr
                                        v-for="inv in order.invoices"
                                        :key="inv.id"
                                        class="transition-colors hover:bg-muted/20"
                                    >
                                        <td
                                            class="px-4 py-3 font-mono text-[11px] font-bold"
                                        >
                                            {{ inv.invoice_number }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            {{ inv.type_label }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <Badge
                                                :class="[
                                                    'px-1.5 py-0 text-[10px]',
                                                    inv.status_color
                                                        ? `text-${inv.status_color}-600 border-${inv.status_color}-200 bg-${inv.status_color}-50`
                                                        : '',
                                                ]"
                                                variant="outline"
                                            >
                                                {{ inv.status_label }}
                                            </Badge>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    inv.amount_due,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right font-medium text-green-600 tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    inv.amount_paid,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right font-bold tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    inv.remaining_balance,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Shipments Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Truck class="h-4 w-4 text-muted-foreground" />
                                <h3 class="text-sm font-semibold">
                                    Vận chuyển ({{
                                        order.shipments?.length ?? 0
                                    }})
                                </h3>
                            </div>
                            <Button
                                v-if="canCreateShipments"
                                variant="outline"
                                size="sm"
                                class="border-blue-200 text-blue-600 hover:bg-blue-50"
                                @click="showShipmentsDialog = true"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Tạo đơn vận chuyển
                            </Button>
                        </div>

                        <div
                            v-if="!order.shipments?.length"
                            class="rounded-xl border border-dashed py-12 text-center text-sm text-muted-foreground italic"
                        >
                            Chưa có đơn vận chuyển cho đơn hàng này
                        </div>

                        <div
                            v-for="shipment in order.shipments"
                            :key="shipment.id"
                            class="overflow-hidden rounded-xl border bg-card shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between border-b bg-muted/20 px-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="font-mono text-sm font-bold text-primary"
                                        >{{ shipment.shipment_number }}</span
                                    >
                                    <Badge
                                        :class="[
                                            'text-[10px]',
                                            shipment.status_color
                                                ? `text-${shipment.status_color}-600 border-${shipment.status_color}-200 bg-${shipment.status_color}-50`
                                                : '',
                                        ]"
                                        variant="outline"
                                    >
                                        {{ shipment.status_label }}
                                    </Badge>
                                    <span
                                        v-if="shipment.origin_location"
                                        class="text-xs text-muted-foreground"
                                    >
                                        Từ:
                                        <span class="font-medium">{{
                                            shipment.origin_location.name
                                        }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button
                                        v-if="
                                            shipment.status === 'cancelled' &&
                                            order.status !== 'cancelled'
                                        "
                                        variant="outline"
                                        size="sm"
                                        class="h-7 text-xs text-blue-600"
                                        @click="
                                            handleResendShipment(shipment.id)
                                        "
                                    >
                                        <Truck class="mr-1 h-3 w-3" /> Gửi lại
                                    </Button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr
                                            class="bg-muted/10 text-xs tracking-wider text-muted-foreground uppercase"
                                        >
                                            <th
                                                class="px-4 py-2 text-left font-medium"
                                            >
                                                Sản phẩm
                                            </th>
                                            <th
                                                class="px-4 py-2 text-center font-medium"
                                            >
                                                SL
                                            </th>
                                            <th
                                                class="px-4 py-2 text-center font-medium"
                                            >
                                                Trạng thái
                                            </th>
                                            <th
                                                class="px-4 py-2 text-center font-medium"
                                            >
                                                Thao tác
                                            </th>
                                            <th
                                                class="px-4 py-2 text-center font-medium"
                                            >
                                                Người gửi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr
                                            v-for="item in shipment.items"
                                            :key="item.id"
                                            class="transition-colors hover:bg-muted/20"
                                        >
                                            <td class="px-4 py-3 font-medium">
                                                {{
                                                    item.variant?.name ||
                                                    item.order_item
                                                        ?.purchasable_name
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-3 text-center tabular-nums"
                                            >
                                                {{ item.quantity_shipped }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <Badge
                                                    :class="[
                                                        'text-[10px]',
                                                        item.status_color
                                                            ? `text-${item.status_color}-600 border-${item.status_color}-200 bg-${item.status_color}-50`
                                                            : '',
                                                    ]"
                                                    variant="outline"
                                                >
                                                    {{ item.status_label }}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <Button
                                                    v-if="canReturnItem(item)"
                                                    variant="outline"
                                                    size="sm"
                                                    class="h-7 border-orange-200 text-xs text-orange-600 hover:bg-orange-50"
                                                    @click="
                                                        handleReturnItem(
                                                            item,
                                                            shipment.id,
                                                        )
                                                    "
                                                >
                                                    <RotateCcw
                                                        class="mr-1 h-3 w-3"
                                                    />
                                                    Trả
                                                </Button>
                                                <span
                                                    v-else
                                                    class="text-xs text-muted-foreground"
                                                    >—</span
                                                >
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div
                                                    v-if="shipment.handled_by"
                                                    class="flex flex-col items-center"
                                                >
                                                    <span
                                                        class="text-xs font-semibold"
                                                        >{{
                                                            shipment.handled_by
                                                                .full_name
                                                        }}</span
                                                    >
                                                    <span
                                                        class="text-[10px] text-muted-foreground"
                                                        >{{
                                                            shipment.handled_by
                                                                .phone ?? '—'
                                                        }}</span
                                                    >
                                                </div>
                                                <span
                                                    v-else
                                                    class="text-xs text-muted-foreground"
                                                    >—</span
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Refunds Section -->
                    <div v-if="order.refunds?.length" class="space-y-3">
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Yêu cầu hoàn tiền ({{ order.refunds.length }})
                            </h3>
                        </div>
                        <div
                            v-for="refund in order.refunds"
                            :key="refund.id"
                            class="overflow-hidden rounded-xl border bg-card shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between border-b bg-muted/20 px-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="font-mono text-xs font-bold text-muted-foreground"
                                        >{{
                                            refund.id.substring(0, 8)
                                        }}...</span
                                    >
                                    <Badge
                                        :class="[
                                            'text-[10px]',
                                            refund.status_color
                                                ? `text-${refund.status_color}-600 border-${refund.status_color}-200 bg-${refund.status_color}-50`
                                                : '',
                                        ]"
                                        variant="outline"
                                    >
                                        {{ refund.status_label }}
                                    </Badge>
                                </div>
                                <span
                                    class="text-sm font-bold text-destructive tabular-nums"
                                >
                                    -{{
                                        Number(refund.amount).toLocaleString(
                                            'vi-VN',
                                        )
                                    }}đ
                                </span>
                            </div>
                            <div
                                class="flex flex-wrap gap-x-6 gap-y-2 px-4 py-3 text-xs text-muted-foreground"
                            >
                                <span
                                    v-if="refund.reason"
                                    class="flex items-center gap-1"
                                >
                                    <span class="font-medium text-foreground"
                                        >Lý do:</span
                                    >
                                    {{ refund.reason }}
                                </span>
                                <span
                                    v-if="refund.requested_by"
                                    class="flex items-center gap-1"
                                >
                                    <span class="font-medium text-foreground"
                                        >Yêu cầu:</span
                                    >
                                    {{ refund.requested_by.full_name }}
                                </span>
                                <span
                                    v-if="refund.processed_by"
                                    class="flex items-center gap-1"
                                >
                                    <span class="font-medium text-foreground"
                                        >Duyệt:</span
                                    >
                                    {{ refund.processed_by.full_name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <ShipmentCreationDialog
        v-model="showShipmentsDialog"
        :order-id="order.id"
        @success="router.reload()"
    />
    <VnPayPaymentDialog
        v-if="showVnPayDialog"
        :url="vnPayUrl"
        @close="showVnPayDialog = false"
    />
    <!-- Return Item Dialog -->
    <Dialog v-model:open="showReturnDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-xl">Yêu cầu trả hàng</DialogTitle>
                <DialogDescription>
                    Vui lòng cung cấp lý do chi tiết để chúng tôi xử lý yêu cầu
                    hoàn trả.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Product Info Card -->
                <div
                    class="flex items-center gap-3 rounded-lg border bg-muted/50 p-3"
                >
                    <div class="rounded-md bg-orange-100 p-2">
                        <Package class="h-4 w-4 text-orange-600" />
                    </div>
                    <div class="text-sm">
                        <p class="font-bold">
                            {{
                                returnItem?.order_item?.purchasable_name ||
                                'Sản phẩm'
                            }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Số lượng trả: {{ returnItem?.quantity_shipped }}
                        </p>
                    </div>
                </div>

                <!-- Reason Input -->
                <div class="space-y-2">
                    <Label
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >Lý do trả hàng</Label
                    >
                    <Input
                        v-model="returnReason"
                        placeholder="Nhập lý do chi tiết..."
                        class="h-10"
                    />
                </div>
            </div>

            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="ghost" @click="showReturnDialog = false"
                    >Hủy bỏ</Button
                >
                <Button variant="destructive" @click="confirmReturn">
                    Xác nhận trả hàng
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
