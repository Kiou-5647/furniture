<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Calendar, MapPin, ArrowLeft, ExternalLink } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import CustomerLayout from '@/layouts/settings/CustomerLayout.vue';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatDateTime } from '@/lib/date-utils';
import { formatPrice } from '@/lib/utils';
import { bookings } from '@/routes/customer/profile';
import { cancel } from '@/routes/customer/profile/bookings';
import { initiate } from '@/routes/payment/vnpay';

type Props = {
    booking: {
        id: string;
        booking_number: string;
        status: string;
        status_label: string;
        total_price: number;
        start_at: string;
        end_at: string;
        full_address: string;
        can_cancel: boolean;
        notes?: string;
        designer: {
            name: string;
            avatar: string;
            rate: number;
            bio: string;
            portfolio: string;
        };
        deposit_invoice: {
            id: string;
            invoice_number: string;
            amount_due: number;
            amount_paid: number;
            status: string;
            status_label: string;
            created_at: string;
        } | null;
        final_invoice: {
            id: string;
            invoice_number: string;
            amount_due: number;
            amount_paid: number;
            status: string;
            status_label: string;
            created_at: string;
        } | null;
        is_deposit_paid?: boolean;
        is_final_paid?: boolean;
    };
};

const props = defineProps<Props>();

// State for the cancellation confirmation dialog
const isCancelConfirming = ref(false);

/**
 * Handles the actual API call to cancel the booking
 */
function cancelBooking() {
    router.post(
        cancel(props.booking.booking_number).url,
        {},
        {
            onSuccess: () => {
                isCancelConfirming.value = false;
            },
            onError: (errors) => {
                console.error('Lỗi:', errors);
            },
        },
    );
}
</script>

<template>
    <ShopLayout>
        <CustomerLayout>
            <Head :title="`Chi tiết lịch đặt ${booking.booking_number}`" />
            <div class="space-y-8 p-6">
                <!-- Header Section -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="router.visit(bookings().url)"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                        </Button>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">
                                Chi tiết lịch đặt
                            </h1>
                            <p class="text-muted-foreground">
                                Mã đặt lịch: {{ booking.booking_number }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge
                            :variant="
                                booking.status === 'completed'
                                    ? 'default'
                                    : 'outline'
                            "
                        >
                            {{ booking.status_label }}
                        </Badge>
                        <Button
                            v-if="booking.can_cancel"
                            variant="destructive"
                            size="sm"
                            @click="isCancelConfirming = true"
                        >
                            Hủy lịch đặt
                        </Button>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-12">
                    <!-- LEFT SIDE: Booking & Payment Information -->
                    <div class="space-y-6 lg:col-span-4">
                        <!-- Time & Location Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base"
                                    >Thông tin thời gian & địa điểm</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <Calendar
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    <div>
                                        <p class="text-sm font-medium">
                                            Thời gian tư vấn
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                formatDateTime(booking.start_at)
                                            }}
                                            →
                                            {{ formatDateTime(booking.end_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <MapPin
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    <div>
                                        <p class="text-sm font-medium">
                                            Địa điểm
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ booking.full_address }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Payment Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base"
                                    >Thông tin thanh toán</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground"
                                        >Tổng giá trị</span
                                    >
                                    <span class="font-bold text-orange-500">{{
                                        formatPrice(booking.total_price)
                                    }}</span>
                                </div>

                                <!-- Deposit Section -->
                                <div
                                    v-if="booking.deposit_invoice"
                                    class="space-y-2 border-t pt-2"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground"
                                            >Đặt cọc</span
                                        >
                                        <span class="font-medium">{{
                                            booking.deposit_invoice.status_label
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="!booking.is_deposit_paid"
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            Còn lại:
                                            {{
                                                formatPrice(
                                                    booking.deposit_invoice
                                                        .amount_due -
                                                        booking.deposit_invoice
                                                            .amount_paid,
                                                )
                                            }}
                                        </span>
                                        <a
                                            :href="
                                                initiate({
                                                    invoice:
                                                        booking.deposit_invoice
                                                            .id,
                                                }).url
                                            "
                                        >
                                            <Button
                                                variant="default"
                                                size="sm"
                                                class="h-7 px-2 text-sm"
                                            >
                                                Thanh toán cọc
                                            </Button>
                                        </a>
                                    </div>
                                    <div
                                        v-else
                                        class="flex items-center justify-end"
                                    >
                                        <span
                                            class="text-sm text-green-500"
                                        >
                                            {{
                                                formatPrice(
                                                    booking.deposit_invoice
                                                        .amount_paid,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Final Payment Section -->
                                <div
                                    v-if="booking.final_invoice"
                                    class="space-y-2 border-t pt-2"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground"
                                            >Thanh toán nốt</span
                                        >
                                        <span class="font-medium">{{
                                            booking.final_invoice.status_label
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="
                                            !booking.is_final_paid &&
                                            booking.final_invoice.status ==
                                                'open'
                                        "
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm text-red-500"
                                        >
                                            Còn lại:
                                            {{
                                                formatPrice(
                                                    booking.final_invoice
                                                        .amount_due -
                                                        booking.final_invoice
                                                            .amount_paid,
                                                )
                                            }}
                                        </span>
                                        <a
                                            :href="
                                                initiate({
                                                    invoice:
                                                        booking.final_invoice
                                                            .id,
                                                }).url
                                            "
                                        >
                                            <Button
                                                variant="default"
                                                size="sm"
                                                class="h-7 px-2 text-sm"
                                            >
                                                Thanh toán nốt
                                            </Button>
                                        </a>
                                    </div>
                                    <div
                                        v-else-if="booking.is_final_paid"
                                        class="text-right text-sm text-muted-foreground"
                                    >
                                    <span
                                    class="text-sm text-green-500"
                                >
                                    {{
                                        formatPrice(
                                            booking.final_invoice
                                                .amount_paid,
                                        )
                                    }}
                                </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- RIGHT SIDE: Designer Profile & Notes -->
                    <div class="space-y-6 lg:col-span-8">
                        <!-- Designer Profile Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base"
                                    >Thông tin Nhà thiết kế</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div class="flex items-center gap-4">
                                        <img
                                            :src="booking.designer.avatar"
                                            class="h-16 w-16 rounded-full bg-muted object-cover"
                                        />
                                        <div>
                                            <p class="font-bold">
                                                {{ booking.designer.name }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                Chuyên gia tư vấn
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >Phí tư vấn/giờ</span
                                        >
                                        &nbsp;
                                        <span
                                            class="text-sm font-bold text-orange-500"
                                            >{{
                                                formatPrice(
                                                    booking.designer.rate,
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <p
                                        class="text-sm leading-relaxed text-muted-foreground"
                                    >
                                        {{ booking.designer.bio }}
                                    </p>
                                    <a
                                        :href="booking.designer.portfolio"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-xs text-blue-500 hover:underline"
                                    >
                                        Xem Portfolio
                                        <ExternalLink class="h-3 w-3" />
                                    </a>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Booking Notes Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base"
                                    >Ghi chú lịch đặt</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <p class="text-sm text-muted-foreground italic">
                                    {{
                                        booking.notes ||
                                        'Không có ghi chú nào cho lịch đặt này.'
                                    }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <!-- Confirmation Dialog for Cancellation -->
            <Dialog
                :open="isCancelConfirming"
                @update:open="(val) => (isCancelConfirming = val)"
            >
                <DialogContent class="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Xác nhận hủy lịch đặt</DialogTitle>
                        <DialogDescription>
                            Bạn có chắc chắn muốn hủy lịch đặt này không? Hành
                            động này không thể hoàn tác.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="flex gap-2 sm:justify-end">
                        <Button
                            variant="ghost"
                            @click="isCancelConfirming = false"
                            >Quay lại</Button
                        >
                        <Button variant="destructive" @click="cancelBooking"
                            >Xác nhận hủy</Button
                        >
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </CustomerLayout>
    </ShopLayout>
</template>
