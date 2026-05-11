<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    User,
    CheckCircle2,
    CreditCard,
    XCircle,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { markAsPaid } from '@/actions/App/Http/Controllers/Employee/Booking/BookingController';
import { initiate } from '@/actions/App/Http/Controllers/Payment/VnPayPaymentController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib';
import { formatDateOnly, formatDateTime } from '@/lib/date-utils';
import {
    index,
    confirm,
    cancel,
    openInvoice as openInvoiceRoute,
} from '@/routes/employee/booking';
import type { BreadcrumbItem } from '@/types';
import type { Booking } from '@/types/booking';

const VnPayPaymentDialog = createLazyComponent(
    () => import('@/components/custom/paywall/VnPayPaymentDialog.vue'),
);

const props = defineProps<{
    booking: Booking;
}>();

const isProcessing = ref(false);
const showVnPayDialog = ref(false);
const vnPayUrl = ref('');

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Đặt lịch', href: index().url },
    { title: props.booking?.id.slice(0, 8) ?? '...', href: '#' },
]);

// --- Actions ---
async function handleConfirm() {
    if (!confirm('Xác nhận đặt lịch này?')) return;
    isProcessing.value = true;
    router.post(
        confirm({ booking: props.booking.id }).url,
        {},
        {
            onFinish: () => (isProcessing.value = false),
            onSuccess: () => toast.success('Đã xác nhận đặt lịch thành công.'),
        },
    );
}

async function handleCancel() {
    if (!confirm('Hủy đặt lịch này?')) return;
    isProcessing.value = true;
    router.post(
        cancel({ booking: props.booking.id }).url,
        {},
        {
            onFinish: () => (isProcessing.value = false),
            onSuccess: () => toast.success('Đã hủy đặt lịch.'),
        },
    );
}

function handleVnPayPayment(invoiceId: string) {
    vnPayUrl.value = initiate(invoiceId).url;
    showVnPayDialog.value = true;
}

function handleOpenInvoice() {
    router.post(
        openInvoiceRoute({ booking: props.booking.id }).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleMarkPaid(type: 'deposit' | 'final') {
    const invoiceId =
        type === 'deposit'
            ? props.booking.deposit_invoice?.id
            : props.booking.final_invoice?.id;
    if (!props.booking?.id || !invoiceId) return;

    router.post(
        markAsPaid({ booking: props.booking.id }).url,
        {
            invoice_type: type,
            gateway: 'manual',
        },
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success(
                    `Đã xác nhận thanh toán ${type === 'deposit' ? 'đặt cọc' : 'phần còn lại'}.`,
                ),
        },
    );
}

function goBack() {
    router.visit(index().url);
}

// --- Computed ---
const duration = computed(() => {
    if (!props.booking.start_at || !props.booking.end_at) return '—';
    const start = new Date(props.booking.start_at);
    const end = new Date(props.booking.end_at);
    if (isNaN(start.getTime()) || isNaN(end.getTime())) return '—';
    const diffInHours = Math.round(
        (end.getTime() - start.getTime()) / (1000 * 60 * 60),
    );
    return `${diffInHours} giờ`;
});

const fullAddress = computed(
    () => props.booking.address?.full_address || 'Chưa có địa chỉ',
);
</script>

<template>
    <Head title="Chi tiết đặt lịch" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="booking" class="space-y-6 p-4 lg:p-6">
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
                            <Heading
                                :title="`Đặt lịch #${booking.booking_number}`"
                            />
                            <Badge
                                :class="[
                                    'text-xs',
                                    booking.status === 'confirmed'
                                        ? 'border-blue-200 bg-blue-100 text-blue-700'
                                        : booking.status === 'completed'
                                          ? 'border-green-200 bg-green-100 text-green-700'
                                          : booking.status === 'cancelled'
                                            ? 'border-gray-200 bg-gray-100 text-gray-700'
                                            : 'border-yellow-200 bg-yellow-100 text-yellow-700',
                                ]"
                                variant="outline"
                            >
                                {{ booking.status_label }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Tạo ngày {{ formatDateOnly(booking.created_at) }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2 border-l pl-2">
                        <Button
                            v-if="booking.can_confirm"
                            variant="outline"
                            class="border-blue-200 bg-blue-50/50 text-blue-600"
                            :disabled="isProcessing"
                            @click="handleConfirm"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" /> Xác nhận
                        </Button>
                        <Button
                            v-if="booking.can_cancel"
                            variant="ghost"
                            class="text-destructive hover:bg-destructive/10"
                            :disabled="isProcessing"
                            @click="handleCancel"
                        >
                            <XCircle class="mr-2 h-4 w-4" /> Hủy lịch
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column: Info Sidebar -->
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
                            <div class="space-y-1">
                                <p class="text-base font-bold">
                                    {{ booking.customer?.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ booking.customer?.email }}
                                </p>
                                <p
                                    v-if="booking.customer?.phone"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ booking.customer?.phone }}
                                </p>
                            </div>
                            <div class="border-t pt-3">
                                <p
                                    class="mb-1 text-[11px] font-bold text-muted-foreground uppercase"
                                >
                                    Địa chỉ
                                </p>
                                <p
                                    class="text-xs leading-relaxed text-foreground/80"
                                >
                                    {{ fullAddress }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Time & Duration Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Thời gian tư vấn
                            </h3>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600"
                                >
                                    <Clock class="h-4 w-4" />
                                </div>
                                <div>
                                    <p
                                        class="text-[11px] font-bold text-muted-foreground uppercase"
                                    >
                                        Bắt đầu
                                    </p>
                                    <p class="text-sm font-medium">
                                        {{ formatDateTime(booking.start_at!) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-lg bg-orange-50 p-2 text-orange-600"
                                >
                                    <Clock class="h-4 w-4" />
                                </div>
                                <div>
                                    <p
                                        class="text-[11px] font-bold text-muted-foreground uppercase"
                                    >
                                        Kết thúc
                                    </p>
                                    <p class="text-sm font-medium">
                                        {{ formatDateTime(booking.end_at!) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between border-t pt-3"
                            >
                                <span class="text-xs text-muted-foreground"
                                    >Tổng thời lượng</span
                                >
                                <span class="text-sm font-bold">{{
                                    duration
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Designer Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <User class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Nhà thiết kế phụ trách
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary"
                                >
                                    {{ booking.designer?.name?.[0] || '?' }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold">
                                        {{
                                            booking.designer?.name ??
                                            'Chưa phân công'
                                        }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Chuyên gia tư vấn
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Financials -->
                <div class="space-y-6 lg:col-span-8">
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <CreditCard class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Quản lý thanh toán
                            </h3>
                        </div>
                        <div class="space-y-8 p-6">
                            <!-- Total Summary -->
                            <div
                                class="flex items-center justify-between rounded-xl border border-primary/10 bg-primary/5 p-4"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold text-muted-foreground uppercase"
                                    >
                                        Tổng chi phí tư vấn
                                    </p>
                                    <p
                                        class="text-3xl font-black text-primary tabular-nums"
                                    >
                                        {{ formatPrice(booking.total_price) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <Badge
                                        variant="secondary"
                                        class="px-3 py-1 text-xs"
                                        >Tư vấn theo giờ</Badge
                                    >
                                </div>
                            </div>
                            <!-- Deposit Section -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-bold">
                                        1. Đặt cọc (Deposit)
                                    </h4>
                                    <Badge
                                        :class="
                                            booking.deposit_invoice?.status ===
                                            'paid'
                                                ? 'border-green-200 bg-green-100 text-green-700'
                                                : 'border-yellow-200 bg-yellow-100 text-yellow-700'
                                        "
                                        variant="outline"
                                        class="text-[10px]"
                                    >
                                        {{
                                            booking.deposit_invoice?.status ===
                                            'paid'
                                                ? 'Đã thu'
                                                : 'Chờ thu'
                                        }}
                                    </Badge>
                                </div>
                                <div
                                    v-if="booking.deposit_invoice"
                                    class="space-y-4 rounded-lg border bg-muted/20 p-4"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground"
                                            >Số tiền cọc</span
                                        >
                                        <span class="font-bold tabular-nums">{{
                                            formatPrice(
                                                booking.deposit_invoice
                                                    .amount_due,
                                            )
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="
                                            booking.can_pay_deposit
                                        "
                                        class="flex gap-2 pt-2"
                                    >
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="h-8 flex-1 text-xs"
                                            @click="handleMarkPaid('deposit')"
                                        >
                                            <CheckCircle2
                                                class="mr-1 h-3 w-3"
                                            />
                                            Tiền mặt
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            class="h-8 flex-1 border-purple-200 bg-purple-50/50 text-xs text-purple-600"
                                            @click="
                                                handleVnPayPayment(
                                                    booking.deposit_invoice.id,
                                                )
                                            "
                                        >
                                            <CreditCard class="mr-1 h-3 w-3" />
                                            Chuyển khoản
                                        </Button>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="rounded-lg border border-dashed p-4 text-center text-xs text-muted-foreground italic"
                                >
                                    Không có hóa đơn đặt cọc
                                </div>
                            </div>

                            <!-- Final Payment Section -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-bold">
                                        2. Thanh toán cuối
                                    </h4>
                                    <Badge
                                        :class="
                                            booking.final_invoice?.status ===
                                            'paid'
                                                ? 'border-green-200 bg-green-100 text-green-700'
                                                : 'border-gray-200 bg-gray-100 text-gray-700'
                                        "
                                        variant="outline"
                                        class="text-[10px]"
                                    >
                                        {{
                                            booking.final_invoice?.status ===
                                            'paid'
                                                ? 'Đã thu'
                                                : 'Chờ thu'
                                        }}
                                    </Badge>
                                </div>
                                <div
                                    v-if="booking.final_invoice"
                                    class="space-y-4 rounded-lg border bg-muted/20 p-4"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground"
                                            >Số tiền còn lại</span
                                        >
                                        <span class="font-bold tabular-nums">{{
                                            formatPrice(
                                                booking.final_invoice
                                                    .amount_due,
                                            )
                                        }}</span>
                                    </div>
                                    <div class="flex gap-2 pt-2">
                                        <template
                                            v-if="
                                                booking.can_open_invoice
                                            "
                                        >
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="h-8 w-full text-xs"
                                                @click="handleOpenInvoice"
                                                >Mở hóa đơn</Button
                                            >
                                        </template>
                                        <template
                                            v-else-if="
                                                booking.can_mark_final_paid
                                            "
                                        >
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="h-8 flex-1 text-xs"
                                                @click="handleMarkPaid('final')"
                                            >
                                                <CheckCircle2
                                                    class="mr-1 h-3 w-3"
                                                />
                                                Tiền mặt
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="h-8 flex-1 border-purple-200 bg-purple-50/50 text-xs text-purple-600"
                                                @click="
                                                    handleVnPayPayment(
                                                        booking.final_invoice
                                                            .id,
                                                    )
                                                "
                                            >
                                                <CreditCard
                                                    class="mr-1 h-3 w-3"
                                                />
                                                Chuyển khoản
                                            </Button>
                                        </template>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="rounded-lg border border-dashed p-4 text-center text-xs text-muted-foreground italic"
                                >
                                    Hóa đơn cuối sẽ được tạo sau khi xác nhận
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="space-y-4 p-12 text-center">
            <Loader2
                class="mx-auto h-10 w-10 animate-spin text-muted-foreground"
            />
            <p class="text-muted-foreground italic">
                Đang tải thông tin đặt lịch...
            </p>
        </div>

        <!-- VNPay Payment Dialog -->
        <VnPayPaymentDialog
            v-if="showVnPayDialog"
            :open="showVnPayDialog"
            :payment-url="vnPayUrl"
            :amount="booking.deposit_invoice?.amount_due ?? '0'"
            @close="showVnPayDialog = false"
        />
    </AppLayout>
</template>
