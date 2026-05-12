<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, CreditCard, Link, Clock, Trash2, XCircle, Receipt, Info } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib';
import { show as bookingShow } from '@/routes/employee/booking';
import { index, cancel, destroy } from '@/routes/employee/sales/invoices';
import { show as orderShow } from '@/routes/employee/sales/orders';
import type { BreadcrumbItem } from '@/types';
import type { Invoice } from '@/types';
import { toast } from 'vue-sonner';

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

function handleCancel() {
    if (!confirm('Bạn có chắc chắn muốn hủy hóa đơn này?')) return;
    router.post(cancel(props.invoice).url, {}, {
        onSuccess: () => toast.success('Đã hủy hóa đơn thành công'),
    });
}

function handleDelete() {
    if (!confirm('Bạn có chắc chắn muốn xóa hóa đơn này?')) return;
    router.delete(destroy(props.invoice).url, {
        onSuccess: () => toast.success('Đã xóa hóa đơn thành công'),
    });
}
</script>

<template>
    <Head :title="invoice?.invoice_number ?? 'Hóa đơn'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="invoice" class="space-y-6 p-4 w-full">
            <!-- Top Action Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" class="rounded-full" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight">{{ invoice.invoice_number }}</h1>
                            <Badge
                                variant="outline"
                                class="text-xs"
                                :style="{ borderColor: `var(--color-${invoice.status_color}-300)`, color: `var(--color-${invoice.status_color}-700)` }"
                            >
                                {{ invoice.status_label }}
                            </Badge>
                            <Badge
                                variant="secondary"
                                class="text-xs"
                            >
                                {{ invoice.type_label }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">
                            Hóa đơn được tạo ngày {{ invoice.created_at }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="invoice.can_cancel"
                        variant="outline"
                        size="sm"
                        class="text-destructive hover:bg-destructive/10"
                        @click="handleCancel"
                    >
                        <XCircle class="mr-2 h-4 w-4" />
                        Hủy hóa đơn
                    </Button>
                    <Button
                        v-if="invoice.can_delete"
                        variant="outline"
                        size="sm"
                        class="text-destructive hover:bg-destructive/10"
                        @click="handleDelete"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Xóa
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column: Core Info -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Summary Card -->
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-6 text-sm font-semibold text-muted-foreground uppercase tracking-wider">
                            <Receipt class="h-4 w-4" />
                            Tổng quan tài chính
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-muted-foreground">Phải thu</span>
                                <span class="font-medium tabular-nums">{{ formatPrice(invoice.amount_due) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-muted-foreground">Đã thu</span>
                                <span class="font-medium tabular-nums text-green-600">{{ formatPrice(invoice.amount_paid) }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between items-center pt-2">
                                <span class="font-semibold">Còn lại</span>
                                <span
                                    class="text-xl font-bold tabular-nums"
                                    :class="Number(invoice.remaining_balance) > 0 ? 'text-red-600' : 'text-green-600'"
                                >
                                    {{ formatPrice(invoice.remaining_balance) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-6 text-sm font-semibold text-muted-foreground uppercase tracking-wider">
                            <Info class="h-4 w-4" />
                            Chi tiết vận hành
                        </div>
                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Người xác nhận</span>
                                <span class="font-medium">{{ invoice.validated_by ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Ngày tạo</span>
                                <span class="tabular-nums">{{ invoice.created_at }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Cập nhật cuối</span>
                                <span class="tabular-nums">{{ invoice.updated_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Linked Data & Payments -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Link Section -->
                    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                        <div class="flex items-center gap-2 border-b px-6 py-4 bg-muted/30">
                            <Link class="h-4 w-4" />
                            <h3 class="text-sm font-semibold">Liên kết nghiệp vụ</h3>
                        </div>
                        <div class="p-6">
                            <template v-if="invoice.invoiceable">
                                <div class="flex flex-col gap-4">
                                    <Button
                                        variant="outline"
                                        class="w-full sm:w-auto justify-start gap-3 px-4 py-6 h-auto text-left transition-all hover:border-primary hover:bg-primary/5"
                                        @click="navigateToInvoiceable"
                                    >
                                        <div class="p-2 rounded-lg bg-muted">
                                            <component
                                                :is="invoice.invoiceable.type === 'Order' ? CreditCard : Calendar"
                                                class="h-5 w-5"
                                            />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs text-muted-foreground uppercase font-semibold">
                                                {{ invoice.invoiceable.type === 'Order' ? 'Đơn hàng' : 'Đặt lịch' }}
                                            </span>
                                            <span class="font-mono font-bold text-base">{{ invoice.invoiceable.number }}</span>
                                        </div>
                                    </Button>

                                    <!-- Detailed Context Card -->
                                    <div v-if="(invoice as any).invoiceable_details" class="rounded-xl border bg-muted/20 p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-3">
                                            <h4 class="text-xs font-bold uppercase text-muted-foreground tracking-widest">Thông tin chi tiết</h4>
                                            <div class="space-y-2 text-sm">
                                                <div v-if="invoice.invoiceable.type === 'Order'" class="flex justify-between">
                                                    <span class="text-muted-foreground">Khách hàng</span>
                                                    <span class="font-medium">{{ (invoice as any).invoiceable_details.customer_name ?? '—' }}</span>
                                                </div>
                                                <div v-if="invoice.invoiceable.type === 'Booking'" class="flex justify-between">
                                                    <span class="text-muted-foreground">Nhà thiết kế</span>
                                                    <span class="font-medium">{{ (invoice as any).invoiceable_details.designer_name ?? '—' }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-muted-foreground">Trạng thái gốc</span>
                                                    <span class="font-medium">{{ (invoice as any).invoiceable_details.status_label ?? '—' }}</span>
                                                </div>
                                                <div v-if="invoice.invoiceable.type === 'Booking'" class="flex justify-between">
                                                    <span class="text-muted-foreground">Ngày hẹn</span>
                                                    <span class="font-medium">{{ (invoice as any).invoiceable_details.scheduled_date ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="invoice.invoiceable.type === 'Order' && (invoice as any).invoiceable_details.items?.length" class="space-y-3">
                                            <h4 class="text-xs font-bold uppercase text-muted-foreground tracking-widest">Danh sách sản phẩm</h4>
                                            <div class="max-h-40 overflow-y-auto rounded-lg border bg-white">
                                                <table class="w-full text-xs">
                                                    <thead class="bg-muted/50 sticky top-0">
                                                        <tr class="text-muted-foreground">
                                                            <th class="px-3 py-2 text-left">Tên</th>
                                                            <th class="px-3 py-2 text-center">SL</th>
                                                            <th class="px-3 py-2 text-right">Thành tiền</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="item in (invoice as any).invoiceable_details.items" :key="item.id" class="border-t">
                                                            <td class="px-3 py-2 truncate max-w-[120px]">{{ item.purchasable_name }}</td>
                                                            <td class="px-3 py-2 text-center">{{ item.quantity }}</td>
                                                            <td class="px-3 py-2 text-right font-medium tabular-nums">{{ formatPrice(item.subtotal) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex flex-col items-center justify-center py-8 text-center">
                                    <Link class="h-8 w-8 text-muted-foreground mb-2 opacity-20" />
                                    <p class="text-sm text-muted-foreground">Không có dữ liệu liên kết</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Payment Table -->
                    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between border-b px-6 py-4 bg-muted/30">
                            <div class="flex items-center gap-2">
                                <CreditCard class="h-4 w-4" />
                                <h3 class="text-sm font-semibold">Lịch sử thanh toán</h3>
                            </div>
                            <Badge variant="secondary" class="text-xs">{{ invoice.allocations?.length ?? 0 }} giao dịch</Badge>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50 text-xs text-muted-foreground">
                                        <th class="px-6 py-3 text-left">Mã giao dịch</th>
                                        <th class="px-6 py-3 text-center">Cổng thanh toán</th>
                                        <th class="px-6 py-3 text-right">Số tiền</th>
                                        <th class="px-6 py-3 text-center">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="allocation in invoice.allocations" :key="allocation.id" class="border-b text-sm hover:bg-muted/20 transition-colors">
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ allocation.payment?.transaction_id ?? allocation.payment?.id ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <Badge variant="outline" class="text-[10px] uppercase">{{ allocation.payment?.gateway ?? '—' }}</Badge>
                                        </td>
                                        <td class="px-6 py-4 text-right tabular-nums font-semibold">
                                            {{ formatPrice(allocation.amount_applied) }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-xs text-muted-foreground tabular-nums">
                                            <span class="inline-flex items-center gap-1">
                                                <Clock class="h-3 w-3" />
                                                {{ allocation.applied_at ?? allocation.payment?.created_at ?? '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!invoice.allocations?.length">
                                        <td colspan="4" class="px-6 py-12 text-center text-sm text-muted-foreground italic">
                                            Chưa có bản ghi thanh toán nào cho hóa đơn này
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="invoice.allocations?.length">
                                    <tr class="bg-muted/50">
                                        <td colspan="2" class="px-6 py-4 text-right font-semibold text-sm">Tổng cộng đã thu</td>
                                        <td class="px-6 py-4 text-right font-bold tabular-nums text-green-600 text-base">
                                            {{ formatPrice(invoice.amount_paid) }}
                                        </td>
                                        <td />
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
