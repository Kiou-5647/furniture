<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Package, ShoppingBag } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
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
import {
    Field,
    FieldContent,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { store } from '@/routes/employee/sales/invoices';

const props = defineProps<{
    open: boolean;
    orderOptions: {
        id: string;
        order_number: string;
        total_amount: string;
        status: string;
        customer_name: string;
    }[];
    bookingOptions?: {
        id: string;
        booking_number: string;
        customer_name: string;
        designer_name: string;
        scheduled_at: string | null;
        total_amount: string;
        deposit_percentage: number;
        has_deposit: boolean;
        has_final: boolean;
        deposit_amount: number | null;
        final_amount: number | null;
    }[];
    currentEmployeeId: string | null;
}>();

const bookingOptions = computed(() => props.bookingOptions ?? []);

const emit = defineEmits<{
    close: [];
}>();

type SourceType = 'order' | 'booking' | null;

const selectedSourceType = ref<SourceType>(null);
const selectedSourceId = ref('');

const form = useForm({
    invoiceable_type: 'App\\Models\\Sales\\Order',
    invoiceable_id: '',
    type: 'full',
    amount_due: '',
    validated_by: props.currentEmployeeId ?? '',
});

const selectedOrder = computed(() =>
    props.orderOptions.find((o) => o.id === selectedSourceId.value),
);

const selectedBooking = computed(() =>
    // TODO: uncomment when Booking model exists
    bookingOptions.value.find((b) => b.id === selectedSourceId.value) || null,
);

// TODO: uncomment when Booking model exists
// const invoiceTypeLabel = computed(() => {
//     if (selectedSourceType.value === 'order') return 'Toàn bộ';
//     if (!selectedBooking.value) return '';
//     return selectedBooking.value.has_deposit ? 'Thanh toán còn lại' : 'Đặt cọc';
// });
const invoiceTypeLabel = computed(() => 'Toàn bộ');

const calculatedAmount = computed(() => {
    if (selectedSourceType.value === 'order' && selectedOrder.value) {
        return Number(selectedOrder.value.total_amount);
    }
    // TODO: uncomment when Booking model exists
    // if (selectedSourceType.value === 'booking' && selectedBooking.value) {
    //     return selectedBooking.value.has_deposit
    //         ? selectedBooking.value.final_amount ?? 0
    //         : selectedBooking.value.deposit_amount ?? 0;
    // }
    return 0;
});

watch([selectedSourceType, selectedSourceId], () => {
    if (selectedSourceType.value === 'order' && selectedOrder.value) {
        form.invoiceable_type = 'App\\Models\\Sales\\Order';
        form.invoiceable_id = selectedOrder.value.id;
        form.type = 'full';
        form.amount_due = String(selectedOrder.value.total_amount);
    }
    // TODO: uncomment when Booking model exists
    // else if (selectedSourceType.value === 'booking' && selectedBooking.value) {
    //     form.invoiceable_type = 'App\\Models\\Design\\Booking';
    //     form.invoiceable_id = selectedBooking.value.id;
    //     form.type = selectedBooking.value.has_deposit ? 'final_balance' : 'deposit';
    //     const amt = selectedBooking.value.has_deposit
    //         ? selectedBooking.value.final_amount
    //         : selectedBooking.value.deposit_amount;
    //     form.amount_due = amt !== null ? String(amt) : '';
    // }
    else {
        form.invoiceable_type = 'App\\Models\\Sales\\Order';
        form.invoiceable_id = '';
        form.type = 'full';
        form.amount_due = '';
    }
});

function resetSelection() {
    selectedSourceType.value = null;
    selectedSourceId.value = '';
}

function submit() {
    form.validated_by = props.currentEmployeeId ?? '';
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
}

function closeModal() {
    form.reset();
    form.clearErrors();
    form.validated_by = props.currentEmployeeId ?? '';
    selectedSourceType.value = null;
    selectedSourceId.value = '';
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="sm:max-w-[550px]">
            <DialogHeader>
                <DialogTitle>Tạo hóa đơn mới</DialogTitle>
                <DialogDescription>
                    Chọn đơn hàng hoặc dịch vụ để tạo hóa đơn
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Source type selector -->
                <div class="flex gap-2">
                    <Button
                        type="button"
                        :variant="selectedSourceType === 'order' ? 'default' : 'outline'"
                        class="flex-1"
                        @click="selectedSourceType = 'order'; selectedSourceId = ''"
                    >
                        <ShoppingBag class="mr-2 h-4 w-4" />
                        Đơn hàng
                    </Button>
                    <!-- TODO: uncomment when Booking model exists
                    <Button
                        type="button"
                        :variant="selectedSourceType === 'booking' ? 'default' : 'outline'"
                        class="flex-1"
                        @click="selectedSourceType = 'booking'; selectedSourceId = ''"
                    >
                        <CalendarDays class="mr-2 h-4 w-4" />
                        Dịch vụ
                    </Button>
                    -->
                </div>

                <!-- Source dropdown -->
                <Field v-if="selectedSourceType">
                    <FieldLabel>
                        {{ selectedSourceType === 'order' ? 'Đơn hàng' : 'Dịch vụ' }}
                    </FieldLabel>
                    <FieldContent>
                        <Select v-model="selectedSourceId">
                            <SelectTrigger>
                                <SelectValue :placeholder="
                                    selectedSourceType === 'order'
                                        ? 'Chọn đơn hàng...'
                                        : 'Chọn dịch vụ...'
                                " />
                            </SelectTrigger>
                            <SelectContent>
                                <template v-if="selectedSourceType === 'order'">
                                    <SelectItem
                                        v-for="order in orderOptions"
                                        :key="order.id"
                                        :value="order.id"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs">{{
                                                order.order_number
                                            }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ order.customer_name }}
                                            </span>
                                        </div>
                                    </SelectItem>
                                </template>
                                <template v-else>
                                    <!-- TODO: uncomment when Booking model exists
                                    <SelectItem
                                        v-for="booking in bookingOptions"
                                        :key="booking.id"
                                        :value="booking.id"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs">{{
                                                booking.booking_number
                                            }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ booking.customer_name }}
                                            </span>
                                        </div>
                                    </SelectItem>
                                    -->
                                    <div class="px-2 py-3 text-center text-sm text-muted-foreground">
                                        Chưa hỗ trợ dịch vụ
                                    </div>
                                </template>
                                <div
                                    v-if="
                                        (selectedSourceType === 'order' && orderOptions.length === 0) ||
                                        (selectedSourceType === 'booking' && bookingOptions.length === 0)
                                    "
                                    class="px-2 py-3 text-center text-sm text-muted-foreground"
                                >
                                    Không có mục nào
                                </div>
                            </SelectContent>
                        </Select>
                    </FieldContent>
                </Field>

                <!-- Selected source details -->
                <div
                    v-if="selectedOrder || selectedBooking"
                    class="rounded-lg border bg-muted/50 p-3 space-y-2"
                >
                    <!-- Order details -->
                    <template v-if="selectedOrder">
                        <div class="flex items-center gap-2">
                            <Package class="h-4 w-4 text-muted-foreground" />
                            <span class="font-mono text-sm font-medium">{{
                                selectedOrder.order_number
                            }}</span>
                            <Badge variant="outline" class="text-xs">
                                {{ selectedOrder.customer_name }}
                            </Badge>
                        </div>
                        <Separator />
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Tổng cộng</span>
                            <span class="font-medium tabular-nums">{{
                                Number(selectedOrder.total_amount).toLocaleString('vi-VN')
                            }}đ</span>
                        </div>
                    </template>

                    <!-- Booking details -->
                    <!-- TODO: uncomment when Booking model exists
                    <template v-if="selectedBooking">
                        <div class="flex items-center gap-2">
                            <User class="h-4 w-4 text-muted-foreground" />
                            <span class="text-sm">{{
                                selectedBooking.customer_name
                            }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <CalendarDays class="h-3 w-3" />
                            <span>{{ selectedBooking.scheduled_at ?? 'Chưa lên lịch' }}</span>
                            <span class="ml-auto">Thiết kế: {{ selectedBooking.designer_name }}</span>
                        </div>
                        <Separator />
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Tổng dịch vụ</span>
                                <span class="tabular-nums">{{
                                    Number(selectedBooking.total_amount).toLocaleString('vi-VN')
                                }}đ</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">
                                    {{ selectedBooking.has_deposit ? 'Đã cọc' : 'Cọc' }}
                                    ({{ selectedBooking.deposit_percentage }}%)
                                </span>
                                <span class="tabular-nums">{{
                                    selectedBooking.deposit_amount !== null
                                        ? Number(selectedBooking.deposit_amount).toLocaleString('vi-VN')
                                        : '—'
                                }}đ</span>
                            </div>
                            <div class="flex items-center justify-between text-sm font-medium">
                                <span>
                                    {{ selectedBooking.has_deposit ? 'Cần thu còn lại' : 'Cần thu' }}
                                    <Badge variant="outline" class="ml-1 text-[10px]">
                                        {{ invoiceTypeLabel }}
                                    </Badge>
                                </span>
                                <span class="tabular-nums text-primary">{{
                                    (selectedBooking.has_final
                                        ? selectedBooking.final_amount
                                        : selectedBooking.deposit_amount
                                    )?.toLocaleString('vi-VN') ?? '0'
                                }}đ</span>
                            </div>
                        </div>
                    </template>
                    -->
                </div>

                <!-- Invoice details -->
                <div v-if="selectedSourceType && selectedSourceId" class="space-y-3">
                    <Separator />

                    <Field>
                        <FieldLabel>Loại hóa đơn</FieldLabel>
                        <FieldContent>
                            <Input
                                :model-value="invoiceTypeLabel"
                                readonly
                                class="bg-muted font-medium"
                            />
                            <FieldError :errors="[form.errors.type]" />
                        </FieldContent>
                    </Field>

                    <Field>
                        <FieldLabel>Số tiền phải thu</FieldLabel>
                        <FieldContent>
                            <Input
                                v-model="form.amount_due"
                                type="number"
                                class="tabular-nums font-medium"
                            />
                            <FieldError :errors="[form.errors.amount_due]" />
                        </FieldContent>
                    </Field>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button
                    type="button"
                    :disabled="form.processing || !selectedSourceId"
                    @click="submit"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Tạo hóa đơn
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
