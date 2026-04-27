<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, User, CheckCircle2 } from '@lucide/vue';
import { CreditCard } from '@lucide/vue';
import { computed, ref } from 'vue';
import { markAsPaid } from '@/actions/App/Http/Controllers/Employee/Booking/BookingController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { createLazyComponent } from '@/composables/createLazyComponent';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateOnly, formatDateTime } from '@/lib/date-utils';
import { formatPrice } from '@/lib/utils';
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Đặt lịch', href: index().url },
    { title: 'Chi tiết đặt lịch', href: '#' },
];

async function handleConfirm() {
    if (!confirm('Xác nhận đặt lịch này?')) return;
    isProcessing.value = true;
    router.post(
        confirm({ booking: props.booking.id }).url,
        {},
        {
            onFinish: () => (isProcessing.value = false),
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
        },
    );
}

function handleVnPayPayment() {
    if (!props.booking.deposit_invoice?.id) return;
    vnPayUrl.value = `/nhan-vien/ban-hang/thanh-toan/vnpay/${props.booking.deposit_invoice.id}`;
    showVnPayDialog.value = true;
}

function handleOpenInvoice() {
    if (!props.booking?.id) return;
    router.post(
        openInvoiceRoute({ booking: props.booking.id }).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleMarkDepositPaid() {
    if (!props.booking?.id || !props.booking.deposit_invoice?.id) return;
    router.post(
        markAsPaid({ booking: props.booking.id }).url,
        {
            invoice_type: 'deposit',
            gateway: 'manual',
        },
        {
            preserveScroll: true,
        },
    );
}

function handleVnPayFinalPayment() {
    if (!props.booking.final_invoice?.id) return;
    vnPayUrl.value = `/nhan-vien/ban-hang/thanh-toan/vnpay/${props.booking.final_invoice.id}`;
    showVnPayDialog.value = true;
}

function handleMarkFinalPaid() {
    if (!props.booking?.id || !props.booking.final_invoice?.id) return;
    router.post(
        markAsPaid({ booking: props.booking.id }).url,
        {
            invoice_type: 'final',
            gateway: 'manual',
        },
        {
            preserveScroll: true,
        },
    );
}

// Calculate duration for display (End - Start)
const duration = computed(() => {
    if (!props.booking.start_at || !props.booking.end_at) return '—';

    const start = new Date(props.booking.start_at);
    const end = new Date(props.booking.end_at);

    if (isNaN(start.getTime()) || isNaN(end.getTime())) return '—';

    const diffInMs = end.getTime() - start.getTime();
    const diffInHours = Math.round(diffInMs / (1000 * 60 * 60));

    return `${diffInHours} giờ`;
});

// Construct the full address string from the structured snapshot
const fullAddress = computed(() => props.booking.address.full_address);
</script>

<template>
    <Head title="Chi tiết đặt lịch" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="booking" class="space-y-6 p-6">
            <div class="flex items-center gap-4">
                <a
                    :href="index().url"
                    class="text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft class="h-5 w-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-semibold">Chi tiết đặt lịch</h1>
                    <p class="text-sm text-muted-foreground">
                        Mã đặt lịch: {{ booking.id.slice(0, 8) }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Customer Info -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold">Thông tin khách hàng</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <User class="h-4 w-4 text-muted-foreground" />
                            <span>{{ booking.customer.name }}</span>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            {{ booking.customer.email }}
                        </div>
                        <div
                            v-if="booking.customer.phone"
                            class="text-sm text-muted-foreground"
                        >
                            {{ booking.customer.phone }}
                        </div>
                        <div class="text-sm text-muted-foreground italic">
                            {{ fullAddress }}
                        </div>
                    </div>
                </div>

                <!-- Booking Details (REPLACED Service Info) -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold">Chi tiết tư vấn</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Tổng chi phí:</span
                            >
                            <span class="font-medium"
                                >{{ formatPrice(booking.total_price) }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Thời lượng:</span
                            >
                            <span class="font-medium">{{ duration }}</span>
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-blue-600"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            <span>Hình thức: Tư vấn theo giờ</span>
                        </div>
                    </div>
                </div>

                <!-- Designer Info -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold">Nhà thiết kế</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <User class="h-4 w-4 text-muted-foreground" />
                            <span class="font-medium">{{
                                booking.designer.name
                            }}</span>
                        </div>
                        <div
                            v-if="booking.designer.auto_confirm_bookings"
                            class="text-xs text-green-600"
                        >
                            Tự động xác nhận
                        </div>
                    </div>
                </div>

                <!-- Time Info -->
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold">Thời gian</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                            <span>{{ formatDateTime(booking.start_at!) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                            <span
                                >Kết thúc:
                                {{ formatDateTime(booking.end_at!) }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Section -->
            <div class="rounded-lg border p-4">
                <h3 class="mb-3 font-semibold">Hóa đơn thanh toán</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Tổng tiền:</span>
                        <span class="font-medium"
                            >{{ formatPrice(booking.total_price) }}</span
                        >
                    </div>

                    <!-- Deposit Invoice -->
                    <div
                        v-if="booking.deposit_invoice"
                        class="border-t pt-3 text-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span>Đặt cọc:</span>
                                <span
                                    :class="{
                                        'text-yellow-600':
                                            booking.deposit_invoice.status ===
                                            'open',
                                        'text-green-600':
                                            booking.deposit_invoice.status ===
                                            'paid',
                                    }"
                                >
                                    {{
                                        booking.deposit_invoice.status ===
                                        'open'
                                            ? 'Chờ thanh toán'
                                            : 'Đã thanh toán'
                                    }}
                                </span>
                            </div>
                            <div
                                v-if="booking.deposit_invoice.status === 'open'"
                                class="flex gap-1"
                            >
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="handleMarkDepositPaid"
                                >
                                    <CheckCircle2 class="mr-1 h-3 w-3" /> Tiền
                                    mặt
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="text-purple-600"
                                    @click="handleVnPayPayment"
                                >
                                    <CreditCard class="mr-1 h-3 w-3" /> Chuyển
                                    khoản
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Final Invoice -->
                    <div
                        v-if="booking.final_invoice"
                        class="border-t pt-3 text-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-muted-foreground"
                                    >Còn lại:</span
                                >
                                <span class="ml-1 font-medium"
                                    >{{
                                        formatPrice(
                                            booking.final_invoice.amount_due,
                                        )
                                    }}</span
                                >
                                <Badge
                                    v-if="
                                        booking.final_invoice.status === 'draft'
                                    "
                                    variant="secondary"
                                    class="ml-2 text-xs"
                                    >Nháp</Badge
                                >
                                <Badge
                                    v-else-if="
                                        booking.final_invoice.status === 'open'
                                    "
                                    variant="outline"
                                    class="ml-2 text-xs"
                                    >Đang mở</Badge
                                >
                                <Badge
                                    v-else-if="
                                        booking.final_invoice.status === 'paid'
                                    "
                                    variant="outline"
                                    class="ml-2 bg-green-100 text-xs text-green-800"
                                    >Đã thanh toán</Badge
                                >
                            </div>
                            <template
                                v-if="booking.final_invoice.status === 'draft'"
                            >
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="handleOpenInvoice"
                                    >Mở hóa đơn</Button
                                >
                            </template>
                            <template
                                v-else-if="
                                    booking.final_invoice.status === 'open'
                                "
                            >
                                <div class="flex gap-1">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="handleMarkFinalPaid"
                                    >
                                        <CheckCircle2 class="mr-1 h-3 w-3" />
                                        Tiền mặt
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="text-purple-600"
                                        @click="handleVnPayFinalPayment"
                                    >
                                        <CreditCard class="mr-1 h-3 w-3" />
                                        Chuyển khoản
                                    </Button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Actions -->
            <div class="rounded-lg border p-4">
                <h3 class="mb-3 font-semibold">Trạng thái</h3>
                <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                    :class="{
                        'bg-yellow-100 text-yellow-800':
                            booking.status === 'pending_deposit',
                        'bg-orange-100 text-orange-800':
                            booking.status === 'pending_confirmation',
                        'bg-blue-100 text-blue-800':
                            booking.status === 'confirmed',
                        'bg-green-100 text-green-800':
                            booking.status === 'completed',
                        'bg-gray-100 text-gray-800':
                            booking.status === 'cancelled',
                    }"
                >
                    {{ booking.status_label }}
                </span>
                <div class="mt-2 text-sm text-muted-foreground">
                    Ngày tạo: {{ formatDateOnly(booking.created_at) }}
                </div>
            </div>

            <div
                v-if="booking.can_confirm || booking.can_cancel"
                class="flex gap-4"
            >
                <Button
                    v-if="booking.can_confirm"
                    :disabled="isProcessing"
                    @click="handleConfirm"
                    >Xác nhận đặt lịch</Button
                >
                <Button
                    v-if="booking.can_cancel"
                    variant="destructive"
                    :disabled="isProcessing"
                    @click="handleCancel"
                    >Hủy đặt lịch</Button
                >
            </div>
        </div>
        <div v-else class="p-6 text-center text-muted-foreground">
            Đang tải...
        </div>
    </AppLayout>

    <VnPayPaymentDialog
        :open="showVnPayDialog"
        :payment-url="vnPayUrl"
        :amount="booking.deposit_invoice?.amount_due ?? '0'"
        @close="showVnPayDialog = false"
    />
</template>
