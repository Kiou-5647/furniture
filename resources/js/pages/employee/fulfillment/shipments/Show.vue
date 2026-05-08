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
import { toast } from 'vue-sonner';
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

// Navigation & Breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Vận chuyển', href: index().url },
    { title: props.shipment?.shipment_number ?? '...', href: '#' },
]);

function goBack() {
    router.visit(index().url);
}

// Action Permissions
const canShip = computed(() => props.shipment?.can_ship);
const canDeliver = computed(() => props.shipment?.can_deliver);
const canCancel = computed(() => props.shipment?.can_cancel);
const canResend = computed(() => props.shipment?.can_resend);

// Action Handlers
function handleShip() {
    router.post(
        ship(props.shipment).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleDeliver() {
    router.post(
        deliver(props.shipment).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Đã xác nhận giao hàng thành công.'),
        },
    );
}

function handleCancel() {
    router.post(
        cancel(props.shipment).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Đã hủy đơn vận chuyển.'),
        },
    );
}

function handleResend() {
    router.post(
        resend(props.shipment).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Đã tạo yêu cầu gửi lại.'),
        },
    );
}

// Return Item Logic
const showReturnDialog = ref(false);
const returnItem = ref<ShipmentItem | null>(null);
const returnReason = ref('');

function canReturnItem(item: ShipmentItem): boolean {
    return ['shipped', 'delivered'].includes(item.status);
}

function handleReturnItem(item: ShipmentItem) {
    returnItem.value = item;
    returnReason.value = '';
    showReturnDialog.value = true;
}

function confirmReturn() {
    if (!returnItem.value || !props.shipment) return;
    router.post(
        returnItemRoute({
            shipment: props.shipment.id,
            shipmentItem: returnItem.value.id,
        }).url,
        { reason: returnReason.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Đã ghi nhận yêu cầu trả hàng.');
                showReturnDialog.value = false;
                returnItem.value = null;
            },
        },
    );
}
</script>
<template>
    <Head :title="shipment?.shipment_number ?? 'Vận chuyển'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="shipment" class="space-y-6 p-4 lg:p-6">
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
                            <Heading :title="shipment.shipment_number" />
                            <Badge
                                :class="[
                                    'text-xs',
                                    shipment.status_color
                                        ? `text-[${shipment.status_color}]`
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
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Tạo ngày {{ shipment.created_at }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2 border-l pl-2">
                        <Button
                            v-if="canShip"
                            variant="outline"
                            class="border-blue-200 bg-blue-50/50 text-blue-600"
                            @click="handleShip"
                        >
                            <Truck class="mr-2 h-4 w-4" /> Xuất kho
                        </Button>
                        <Button
                            v-if="canDeliver"
                            variant="outline"
                            class="border-green-200 bg-green-50/50 text-green-600"
                            @click="handleDeliver"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" /> Đã giao
                        </Button>
                        <Button
                            v-if="canResend"
                            variant="outline"
                            class="border-blue-200 bg-blue-50/50 text-blue-600"
                            @click="handleResend"
                        >
                            <Truck class="mr-2 h-4 w-4" /> Gửi lại
                        </Button>
                        <Button
                            v-if="canCancel"
                            variant="ghost"
                            class="text-destructive hover:bg-destructive/10"
                            @click="handleCancel"
                        >
                            <Ban class="mr-2 h-4 w-4" /> Hủy đơn
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column: Info Sidebar -->
                <div class="space-y-6 lg:col-span-4">
                    <!-- Order Link Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                        v-if="shipment.order"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <Package class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Thông tin đơn hàng
                            </h3>
                        </div>
                        <div class="space-y-3 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground"
                                    >Mã đơn</span
                                >
                                <span class="font-mono text-sm font-bold">{{
                                    shipment.order.order_number
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground"
                                    >Tổng giá trị</span
                                >
                                <span class="text-sm font-bold tabular-nums"
                                    >{{
                                        Number(
                                            shipment.order.total_amount,
                                        ).toLocaleString('vi-VN')
                                    }}đ</span
                                >
                            </div>
                            <div
                                v-if="
                                    parseFloat(shipment.order.shipping_cost) > 0
                                "
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-muted-foreground"
                                    >Phí giao hàng</span
                                >
                                <span class="text-sm tabular-nums"
                                    >{{
                                        Number(
                                            shipment.order.shipping_cost,
                                        ).toLocaleString('vi-VN')
                                    }}đ</span
                                >
                            </div>
                            <div
                                v-if="shipment.order.notes"
                                class="border-t pt-3"
                            >
                                <p
                                    class="mb-1 text-[11px] font-bold text-muted-foreground uppercase"
                                >
                                    Ghi chú đơn hàng
                                </p>
                                <p class="text-xs text-foreground/80 italic">
                                    {{ shipment.order.notes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <User class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Thông tin người nhận
                            </h3>
                        </div>
                        <div class="space-y-3 p-4">
                            <div
                                v-if="shipment.order?.customer"
                                class="space-y-1"
                            >
                                <p class="text-base font-bold">
                                    {{ shipment.order.customer.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ shipment.order.customer.email }}
                                </p>
                            </div>
                            <template v-else>
                                <div class="space-y-1">
                                    <p
                                        class="text-base font-bold text-muted-foreground italic"
                                    >
                                        Khách vãng lai
                                    </p>
                                    <p
                                        v-if="shipment.order?.guest_name"
                                        class="text-sm"
                                    >
                                        {{ shipment.order.guest_name }}
                                    </p>
                                    <p
                                        v-if="shipment.order?.guest_phone"
                                        class="text-sm text-muted-foreground"
                                    >
                                        {{ shipment.order.guest_phone }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Address Card -->
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
                                    shipment.order?.shipping_address_text ||
                                    'Chưa có thông tin địa chỉ'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Origin Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Nguồn gửi & Nhân viên
                            </h3>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="space-y-1">
                                <p
                                    class="text-[11px] font-bold text-muted-foreground uppercase"
                                >
                                    Kho xuất hàng
                                </p>
                                <p class="text-sm font-medium">
                                    {{ shipment.origin_location?.name || '—' }}
                                </p>
                            </div>
                            <div
                                v-if="shipment.handled_by"
                                class="space-y-1 border-t pt-3"
                            >
                                <p
                                    class="text-[11px] font-bold text-muted-foreground uppercase"
                                >
                                    Nhân viên xử lý
                                </p>
                                <p class="text-sm font-bold">
                                    {{ shipment.handled_by.full_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ shipment.handled_by.phone ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Items Data -->
                <div class="space-y-6 lg:col-span-8">
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <Package class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Sản phẩm vận chuyển ({{
                                    shipment.items?.length ?? 0
                                }})
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
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            Trạng thái
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            Thao tác
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center font-medium"
                                        >
                                            Xử lý
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="item in shipment.items"
                                        :key="item.id"
                                        class="text-sm transition-colors hover:bg-muted/20"
                                    >
                                        <td class="px-4 py-4">
                                            <div
                                                class="font-medium text-foreground"
                                            >
                                                {{
                                                    item.variant?.name ||
                                                    item.order_item
                                                        ?.purchasable_name
                                                }}
                                            </div>
                                            <span
                                                class="font-mono text-[10px] text-muted-foreground"
                                                >{{ item.variant?.sku }}</span
                                            >
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center tabular-nums"
                                        >
                                            {{ item.quantity_shipped }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Badge
                                                :class="[
                                                    'px-1.5 py-0 text-[10px]',
                                                    item.status_color
                                                        ? `text-${item.status_color}-600 border-${item.status_color}-200 bg-${item.status_color}-50`
                                                        : '',
                                                ]"
                                                variant="outline"
                                            >
                                                {{ item.status_label }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Button
                                                v-if="canReturnItem(item)"
                                                variant="outline"
                                                size="sm"
                                                class="h-7 border-orange-200 text-xs text-orange-600 hover:bg-orange-50"
                                                @click="handleReturnItem(item)"
                                            >
                                                <RotateCcw
                                                    class="mr-1 h-3 w-3"
                                                />
                                                Trả hàng
                                            </Button>
                                            <span
                                                v-else
                                                class="text-xs text-muted-foreground"
                                                >—</span
                                            >
                                        </td>
                                        <td class="px-4 py-4 text-center">
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
                                    <tr v-if="!shipment.items?.length">
                                        <td
                                            colspan="5"
                                            class="px-4 py-12 text-center text-sm text-muted-foreground italic"
                                        >
                                            Không có sản phẩm nào trong đơn vận
                                            chuyển này
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Return Dialog (Shadcn Version) -->
            <Dialog v-model:open="showReturnDialog">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle class="text-xl"
                            >Yêu cầu trả hàng</DialogTitle
                        >
                        <DialogDescription>
                            Vui lòng cung cấp lý do chi tiết cho việc hoàn trả
                            sản phẩm.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div
                            class="flex items-center gap-3 rounded-lg border bg-muted/50 p-3"
                        >
                            <div class="rounded-md bg-orange-100 p-2">
                                <Package class="h-4 w-4 text-orange-600" />
                            </div>
                            <div class="text-sm">
                                <p class="font-bold">
                                    {{
                                        returnItem?.order_item
                                            ?.purchasable_name || 'Sản phẩm'
                                    }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Số lượng trả:
                                    {{ returnItem?.quantity_shipped }}
                                </p>
                            </div>
                        </div>
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
                        <Button
                            variant="ghost"
                            @click="showReturnDialog = false"
                            >Hủy bỏ</Button
                        >
                        <Button variant="destructive" @click="confirmReturn">
                            Xác nhận trả hàng
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
