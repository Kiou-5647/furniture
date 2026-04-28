<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    CheckCircle2,
    MapPin,
    Package,
    Truck,
    User,
    RotateCcw,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    index,
    ship,
    deliver,
    cancel,
    resend,
    returnItem as returnItemRoute,
} from '@/routes/employee/fulfillment/shipments';
import type { BreadcrumbItem } from '@/types';
import type { Shipment, ShipmentItem } from '@/types/order';

const props = defineProps<{
    shipment: Shipment;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Vận chuyển', href: index().url },
    { title: props.shipment?.shipment_number ?? '...', href: '#' },
]);

const canShip = computed(() => props.shipment?.can_ship);
const canDeliver = computed(() => props.shipment?.can_deliver);
const canCancel = computed(() => props.shipment?.can_cancel);
const canResend = computed(() => props.shipment?.can_resend);

const showReturnDialog = ref(false);
const returnItem = ref<ShipmentItem | null>(null);
const returnReason = ref('');

function canReturnItem(item: ShipmentItem): boolean {
    return ['shipped', 'delivered'].includes(item.status);
}

function handleShip() {
    router.post(ship(props.shipment).url, {}, { preserveScroll: true });
}

function handleDeliver() {
    router.post(deliver(props.shipment).url, {}, { preserveScroll: true });
}

function handleCancel() {
    router.post(cancel(props.shipment).url, {}, { preserveScroll: true });
}

function handleResend() {
    router.post(resend(props.shipment).url, {}, { preserveScroll: true });
}

function handleReturnItem(item: ShipmentItem) {
    returnItem.value = item;
    returnReason.value = 'Hàng lỗi / Khách từ chối';
    showReturnDialog.value = true;
}

function confirmReturn() {
    if (!returnItem.value || !props.shipment) return;
    router.post(
        returnItemRoute({
            shipment: props.shipment.id,
            shipmentItem: returnItem.value.id,
        }).url,
        {
            reason: returnReason.value,
        },
        { preserveScroll: true },
    );
    showReturnDialog.value = false;
    returnItem.value = null;
}

function goBack() {
    router.visit(index().url);
}
</script>

<template>
    <Head :title="shipment?.shipment_number ?? 'Vận chuyển'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="shipment" class="space-y-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <Heading
                            :title="shipment.shipment_number"
                            :description="'Tạo ngày ' + shipment.created_at"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge
                        :class="[
                            'text-xs',
                            shipment.status_color
                                ? `text-${shipment.status_color}-600`
                                : '',
                        ]"
                        variant="outline"
                    >
                        <component
                            :is="
                                shipment.status === 'delivered'
                                    ? CheckCircle2
                                    : shipment.status === 'cancelled'
                                      ? Ban
                                      : shipment.status === 'shipped'
                                        ? Truck
                                        : Package
                            "
                            class="mr-1.5 h-3.5 w-3.5"
                        />
                        {{ shipment.status_label }}
                    </Badge>
                    <Button
                        v-if="canShip"
                        variant="outline"
                        class="text-blue-600"
                        @click="handleShip"
                    >
                        <Truck class="mr-2 h-4 w-4" /> Xuất kho
                    </Button>
                    <Button
                        v-if="canDeliver"
                        variant="outline"
                        class="text-green-600"
                        @click="handleDeliver"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" /> Đã giao
                    </Button>
                    <Button
                        v-if="canCancel"
                        variant="outline"
                        class="text-destructive"
                        @click="handleCancel"
                    >
                        <Ban class="mr-2 h-4 w-4" /> Hủy đơn
                    </Button>
                    <Button
                        v-if="canResend"
                        variant="outline"
                        class="text-blue-600"
                        @click="handleResend"
                    >
                        <Truck class="mr-2 h-4 w-4" /> Gửi lại
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Order Info -->
                <div v-if="shipment.order" class="rounded-lg border p-4">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-medium"
                    >
                        <Package class="h-4 w-4" /> Đơn hàng
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Mã đơn</span>
                            <span class="font-mono">{{
                                shipment.order.order_number
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Tổng tiền</span>
                            <span class="font-medium tabular-nums"
                                >{{
                                    Number(
                                        shipment.order.total_amount,
                                    ).toLocaleString('vi-VN')
                                }}đ</span
                            >
                        </div>
                        <div
                            v-if="parseFloat(shipment.order.shipping_cost) > 0"
                            class="flex justify-between"
                        >
                            <span class="text-muted-foreground">Phí ship</span>
                            <span class="tabular-nums"
                                >{{
                                    Number(
                                        shipment.order.shipping_cost,
                                    ).toLocaleString('vi-VN')
                                }}đ</span
                            >
                        </div>
                        <p
                            v-if="shipment.order.notes"
                            class="text-muted-foreground"
                        >
                            Ghi chú: {{ shipment.order.notes }}
                        </p>
                    </div>
                </div>

                <!-- Recipient Info -->
                <div class="rounded-lg border p-4">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-medium"
                    >
                        <User class="h-4 w-4" /> Người nhận
                    </h3>
                    <div class="space-y-2 text-sm">
                        <template v-if="shipment.order?.customer">
                            <p class="font-medium">
                                {{ shipment.order.customer.name }}
                            </p>
                            <p class="text-muted-foreground">
                                {{ shipment.order.customer.email }}
                            </p>
                        </template>
                        <template v-else>
                            <p class="font-medium">
                                {{
                                    shipment.order?.guest_name ||
                                    'Khách vãng lai'
                                }}
                            </p>
                            <p
                                v-if="shipment.order?.guest_phone"
                                class="text-muted-foreground"
                            >
                                {{ shipment.order.guest_phone }}
                            </p>
                            <p
                                v-if="shipment.order?.guest_email"
                                class="text-muted-foreground"
                            >
                                {{ shipment.order.guest_email }}
                            </p>
                        </template>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="rounded-lg border p-4">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-medium"
                    >
                        <MapPin class="h-4 w-4" /> Địa chỉ giao hàng
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        {{ shipment.order?.shipping_address_text || '—' }}
                    </p>
                </div>

                <!-- Origin Location & Shipper -->
                <div class="rounded-lg border p-4">
                    <h3
                        class="mb-3 flex items-center gap-2 text-sm font-medium"
                    >
                        <MapPin class="h-4 w-4" /> Nơi gửi & Người gửi
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p v-if="shipment.origin_location" class="font-medium">
                            {{ shipment.origin_location.name }}
                        </p>
                        <p v-else class="text-muted-foreground">—</p>
                        <template v-if="shipment.handled_by">
                            <p class="font-medium">
                                {{ shipment.handled_by.full_name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ shipment.handled_by.phone ?? '—' }}
                            </p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="rounded-lg border">
                <div class="border-b px-4 py-3">
                    <h3 class="flex items-center gap-2 text-sm font-medium">
                        <Package class="h-4 w-4" /> Sản phẩm ({{
                            shipment.items?.length ?? 0
                        }})
                    </h3>
                </div>
                <table class="w-full table-fixed">
                    <thead>
                        <tr
                            class="border-b bg-muted/50 text-xs text-muted-foreground"
                        >
                            <th class="w-[30%] px-4 py-2 text-left">
                                Sản phẩm
                            </th>
                            <th class="w-[8%] px-4 py-2 text-center">SL</th>
                            <th class="w-[15%] px-4 py-2 text-center">
                                Trạng thái
                            </th>
                            <th class="w-[15%] px-4 py-2 text-center">
                                Thao tác
                            </th>
                            <th class="w-[12%] px-4 py-2 text-center">
                                Người gửi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in shipment.items"
                            :key="item.id"
                            class="border-b text-sm"
                        >
                            <td class="w-[30%] truncate px-4 py-3">
                                <div>
                                    {{
                                        item.variant?.name
                                            ? item.variant?.name
                                            : item.order_item?.purchasable_name
                                    }}
                                </div>
                                <span class="text-[10px] text-muted-foreground">
                                    {{ item.variant.sku }}
                                </span>
                            </td>
                            <td
                                class="w-[8%] px-4 py-3 text-center tabular-nums"
                            >
                                {{ item.quantity_shipped }}
                            </td>
                            <td class="w-[15%] px-4 py-3 text-center">
                                <Badge
                                    :class="[
                                        'text-xs',
                                        item.status_color
                                            ? `text-${item.status_color}-600`
                                            : '',
                                    ]"
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
                                    class="h-7 text-xs text-orange-600"
                                    @click="handleReturnItem(item)"
                                >
                                    <RotateCcw class="mr-1 h-3 w-3" /> Trả hàng
                                </Button>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >—</span
                                >
                            </td>
                            <td class="w-[12%] px-4 py-3 text-center">
                                <template v-if="shipment.handled_by">
                                    <div class="text-xs font-medium">
                                        {{ shipment.handled_by.full_name }}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ shipment.handled_by.phone ?? '—' }}
                                    </div>
                                </template>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >—</span
                                >
                            </td>
                        </tr>
                        <tr v-if="!shipment.items?.length">
                            <td
                                colspan="6"
                                class="px-4 py-8 text-center text-sm text-muted-foreground"
                            >
                                Không có sản phẩm nào
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Return Item Dialog -->
        <div
            v-if="showReturnDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showReturnDialog = false"
        >
            <div
                class="w-full max-w-md rounded-lg border bg-background p-6 shadow-lg"
            >
                <h3 class="mb-4 text-lg font-semibold">Trả hàng</h3>
                <p v-if="returnItem" class="mb-3 text-sm text-muted-foreground">
                    {{
                        returnItem.order_item?.purchasable_name ?? 'Sản phẩm'
                    }}
                    — SL: {{ returnItem.quantity_shipped }}
                </p>
                <Input
                    v-model="returnReason"
                    placeholder="Lý do trả hàng..."
                    class="mb-4"
                />
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="showReturnDialog = false"
                        >Hủy</Button
                    >
                    <Button variant="destructive" @click="confirmReturn"
                        >Xác nhận trả hàng</Button
                    >
                </div>
            </div>
        </div>
    </AppLayout>
</template>
