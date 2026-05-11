<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, FileText, User, Calendar, CreditCard, Link, Clock } from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as bookingShow } from '@/routes/employee/booking';
import { index } from '@/routes/employee/sales/invoices';
import { show as orderShow } from '@/routes/employee/sales/orders';
import type { BreadcrumbItem } from '@/types';
import type { Invoice } from '@/types/invoice';

const props = defineProps<{
    invoice: Invoice;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Hóa đơn', href: index().url },
    { title: props.invoice?.invoice_number ?? '...', href: '#' },
]);

function goBack() {
    router.visit(index().url);
}

function navigateToInvoiceable() {
    const inv = props.invoice.invoiceable;
    if (!inv) return;
    if (inv.type === 'Order') {
        router.visit(orderShow({ order: inv.id }).url);
    } else if (inv.type === 'Booking') {
        router.visit(bookingShow({ booking: inv.id }).url);
    }
}

function formatCurrency(amount: string | number): string {
    return `${Number(amount).toLocaleString('vi-VN')}đ`;
}
</script>

<template>
    <Head :title="invoice?.invoice_number ?? 'Hóa đơn'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="invoice" class="space-y-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <Heading
                            :title="invoice.invoice_number"
                            :description="'Tạo ngày ' + invoice.created_at"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge
                        variant="outline"
                        class="text-xs"
                        :style="{ borderColor: `var(--color-${invoice.status_color}-300)`, color: `var(--color-${invoice.status_color}-700)` }"
                    >
                        {{ invoice.status_label }}
                    </Badge>
                    <Badge
                        variant="outline"
                        class="text-xs"
                        :style="{ borderColor: `var(--color-${invoice.type === 'deposit' ? 'orange' : invoice.type === 'final_balance' ? 'blue' : 'green'}-300)`, color: `var(--color-${invoice.type === 'deposit' ? 'orange' : invoice.type === 'final_balance' ? 'blue' : 'green'}-700)` }"
                    >
                        {{ invoice.type_label }}
                    </Badge>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Invoice Details -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-medium">
                        <FileText class="h-4 w-4" /> Thông tin hóa đơn
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Loại hóa đơn</span>
                            <span class="font-medium">{{ invoice.type_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Trạng thái</span>
                            <Badge
                                variant="outline"
                                class="text-xs"
                                :style="{ borderColor: `var(--color-${invoice.status_color}-300)`, color: `var(--color-${invoice.status_color}-700)` }"
                            >
                                {{ invoice.status_label }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Phải thu</span>
                            <span class="font-medium tabular-nums">{{ formatCurrency(invoice.amount_due) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Đã thu</span>
                            <span class="tabular-nums text-green-600">{{ formatCurrency(invoice.amount_paid) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-medium">Còn lại</span>
                            <span
                                class="font-bold tabular-nums"
                                :class="Number(invoice.remaining_balance) > 0 ? 'text-red-600' : 'text-green-600'"
                            >
                                {{ formatCurrency(invoice.remaining_balance) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Người xác nhận</span>
                            <span class="font-medium">{{ invoice.validated_by ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Ngày tạo</span>
                            <span class="text-muted-foreground tabular-nums">{{ invoice.created_at }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Cập nhật</span>
                            <span class="text-muted-foreground tabular-nums">{{ invoice.updated_at }}</span>
                        </div>
                    </div>
                </div>

                <!-- Linked Entity (Order / Booking) -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-medium">
                        <Link class="h-4 w-4" /> Liên kết
                    </h3>
                    <template v-if="invoice.invoiceable">
                        <div class="space-y-3">
                            <Button
                                variant="outline"
                                class="w-full justify-start text-sm"
                                @click="navigateToInvoiceable"
                            >
                                <component
                                    :is="invoice.invoiceable.type === 'Order' ? CreditCard : Calendar"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ invoice.invoiceable.type === 'Order' ? 'Đơn hàng' : 'Đặt lịch' }}:
                                <span class="ml-1 font-mono">{{ invoice.invoiceable.number }}</span>
                            </Button>

                            <!-- Order-specific details -->
                            <template v-if="invoice.invoiceable.type === 'Order' && (invoice as any).invoiceable_details">
                                <div class="rounded-md border bg-muted/30 p-3 text-sm">
                                    <h4 class="mb-2 font-medium">Chi tiết đơn hàng</h4>
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Mã đơn</span>
                                            <span class="font-mono">{{ (invoice as any).invoiceable_details.order_number }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Trạng thái</span>
                                            <span>{{ (invoice as any).invoiceable_details.status_label }}</span>
                                        </div>
                                        <div v-if="(invoice as any).invoiceable_details.customer_name" class="flex justify-between">
                                            <span class="text-muted-foreground">Khách hàng</span>
                                            <span class="flex items-center gap-1">
                                                <User class="h-3 w-3" />
                                                {{ (invoice as any).invoiceable_details.customer_name }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Tổng tiền</span>
                                            <span class="font-medium tabular-nums">{{ formatCurrency((invoice as any).invoiceable_details.total_amount) }}</span>
                                        </div>
                                        <div v-if="(invoice as any).invoiceable_details.source" class="flex justify-between">
                                            <span class="text-muted-foreground">Nguồn</span>
                                            <span>{{ (invoice as any).invoiceable_details.source === 'in_store' ? 'Tại quầy' : 'Online' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order items -->
                                <div v-if="(invoice as any).invoiceable_details?.items?.length" class="rounded-md border bg-muted/30 p-3">
                                    <h4 class="mb-2 text-sm font-medium">Sản phẩm</h4>
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="border-b text-muted-foreground">
                                                <th class="pb-1.5 text-left">Sản phẩm</th>
                                                <th class="pb-1.5 text-center">SL</th>
                                                <th class="pb-1.5 text-right">Đơn giá</th>
                                                <th class="pb-1.5 text-right">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="item in (invoice as any).invoiceable_details.items"
                                                :key="item.id"
                                                class="border-b"
                                            >
                                                <td class="py-1.5">{{ item.purchasable_name }}</td>
                                                <td class="py-1.5 text-center tabular-nums">{{ item.quantity }}</td>
                                                <td class="py-1.5 text-right tabular-nums">{{ formatCurrency(item.unit_price) }}</td>
                                                <td class="py-1.5 text-right tabular-nums font-medium">{{ formatCurrency(item.subtotal) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <!-- Booking-specific details -->
                            <template v-if="invoice.invoiceable.type === 'Booking' && (invoice as any).invoiceable_details">
                                <div class="rounded-md border bg-muted/30 p-3 text-sm">
                                    <h4 class="mb-2 font-medium">Chi tiết đặt lịch</h4>
                                    <div class="space-y-1.5">
                                        <div v-if="(invoice as any).invoiceable_details.designer_name" class="flex justify-between">
                                            <span class="text-muted-foreground">Nhà thiết kế</span>
                                            <span class="flex items-center gap-1">
                                                <User class="h-3 w-3" />
                                                {{ (invoice as any).invoiceable_details.designer_name }}
                                            </span>
                                        </div>
                                        <div v-if="(invoice as any).invoiceable_details.scheduled_date" class="flex justify-between">
                                            <span class="text-muted-foreground">Ngày dự kiến</span>
                                            <span class="flex items-center gap-1">
                                                <Calendar class="h-3 w-3" />
                                                {{ (invoice as any).invoiceable_details.scheduled_date }}
                                            </span>
                                        </div>
                                        <div v-if="(invoice as any).invoiceable_details.status_label" class="flex justify-between">
                                            <span class="text-muted-foreground">Trạng thái</span>
                                            <span>{{ (invoice as any).invoiceable_details.status_label }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template v-else>
                        <p class="text-sm text-muted-foreground">Không có liên kết</p>
                    </template>
                </div>
            </div>

            <!-- Payment Allocations -->
            <div class="rounded-lg border">
                <div class="flex items-center gap-2 border-b px-4 py-3">
                    <CreditCard class="h-4 w-4" />
                    <h3 class="text-sm font-medium">
                        Lịch sử thanh toán ({{ invoice.allocations?.length ?? 0 }})
                    </h3>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs text-muted-foreground">
                            <th class="px-4 py-2 text-left">Mã giao dịch</th>
                            <th class="px-4 py-2 text-center">Cổng thanh toán</th>
                            <th class="px-4 py-2 text-right">Số tiền</th>
                            <th class="px-4 py-2 text-center">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="allocation in invoice.allocations" :key="allocation.id" class="border-b text-sm">
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ allocation.payment?.transaction_id ?? allocation.payment?.id ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                {{ allocation.payment?.gateway ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium">
                                {{ formatCurrency(allocation.amount_applied) }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-muted-foreground tabular-nums">
                                <span class="inline-flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    {{ allocation.applied_at ?? allocation.payment?.created_at ?? '—' }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!invoice.allocations?.length">
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">
                                Chưa có thanh toán nào
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="invoice.allocations?.length">
                        <tr class="bg-muted/30">
                            <td colspan="2" class="px-4 py-3 text-right font-medium">Tổng đã thu</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums text-green-600">
                                {{ formatCurrency(invoice.amount_paid) }}
                            </td>
                            <td />
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
