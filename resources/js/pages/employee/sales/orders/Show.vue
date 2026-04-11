<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, MapPin, Package, User, XCircle, CheckCircle2 } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cancel, complete, index, updateStatus, markPaid } from '@/routes/employee/sales/orders';
import type { BreadcrumbItem, Order } from '@/types';

const props = defineProps<{
    order: Order;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Đơn hàng', href: index().url },
    { title: props.order?.order_number ?? '...', href: '#' },
]);

const canCancel = computed(() => !['completed', 'cancelled'].includes(props.order?.status));
const canAccept = computed(() => props.order?.status === 'pending');
const canComplete = computed(() => props.order?.status === 'processing');
const canMarkPaid = computed(() => !props.order?.paid_at && props.order?.source === 'in_store');

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
                    <Button
                        v-if="canMarkPaid"
                        variant="outline"
                        class="text-green-600"
                        @click="handleMarkPaid"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" /> Đã thanh toán
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
        </div>
    </AppLayout>
</template>
