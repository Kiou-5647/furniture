<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Package, ShoppingBag, CalendarDays, User } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
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
import { formatPrice } from '@/lib';
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
    bookingOptions: {
        id: string;
        booking_number: string;
        customer_name: string;
        designer_name: string;
        total_amount: string;
        has_deposit: boolean;
        has_final: boolean;
    }[];
    depositPercentage: number;
    currentEmployeeId: string | null;
}>();

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
    props.bookingOptions.find((b) => b.id === selectedSourceId.value) || null,
);

const invoiceTypeLabel = computed(() => {
    if (selectedSourceType.value === 'order') return 'Toàn bộ';
    if (!selectedBooking.value) return '';
    return selectedBooking.value.has_deposit ? 'Thanh toán còn lại' : 'Đặt cọc';
});

const calculatedAmount = computed(() => {
    if (selectedSourceType.value === 'order' && selectedOrder.value) {
        return Number(selectedOrder.value.total_amount);
    }
    if (selectedSourceType.value === 'booking' && selectedBooking.value) {
        const total = Number(selectedBooking.value.total_amount);
        const percentage = props.depositPercentage / 100;
        return selectedBooking.value.has_deposit
            ? total * (1 - percentage)
            : total * percentage;
    }
    return 0;
});

watch([selectedSourceType, selectedSourceId], () => {
    if (selectedSourceType.value === 'order' && selectedOrder.value) {
        form.invoiceable_type = 'App\\Models\\Sales\\Order';
        form.invoiceable_id = selectedOrder.value.id;
        form.type = 'full';
        form.amount_due = String(selectedOrder.value.total_amount);
    } else if (selectedSourceType.value === 'booking' && selectedBooking.value) {
        form.invoiceable_type = 'App\\Models\\Booking\\Booking';
        form.invoiceable_id = selectedBooking.value.id;
        form.type = selectedBooking.value.has_deposit ? 'final_balance' : 'deposit';

        const total = Number(selectedBooking.value.total_amount);
        const percentage = props.depositPercentage / 100;
        const amt = selectedBooking.value.has_deposit
            ? total * (1 - percentage)
            : total * percentage;

        form.amount_due = String(Math.round(amt));
    } else {
        form.invoiceable_id = '';
        form.amount_due = '';
    }
});

function submit() {
    form.validated_by = props.currentEmployeeId ?? '';
    console.info(form);
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Đã tạo hóa đơn mới thành công');
            closeModal();
        },
        onError: (errors) => console.error(errors)
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
                    Chọn đơn hàng hoặc dịch vụ để khởi tạo hóa đơn thanh toán
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6 py-4">
                <!-- Source Selection -->
                <div class="space-y-3">
                    <FieldLabel>Nguồn hóa đơn</FieldLabel>
                    <div class="flex gap-2">
                        <Button
                            type="button"
                            :variant="selectedSourceType === 'order' ? 'default' : 'outline'"
                            class="flex-1 h-12"
                            @click="selectedSourceType = 'order'; selectedSourceId = ''"
                        >
                            <ShoppingBag class="mr-2 h-4 w-4" />
                            Đơn hàng
                        </Button>
                        <Button
                            type="button"
                            :variant="selectedSourceType === 'booking' ? 'default' : 'outline'"
                            class="flex-1 h-12"
                            @click="selectedSourceType = 'booking'; selectedSourceId = ''"
                        >
                            <CalendarDays class="mr-2 h-4 w-4" />
                            Dịch vụ
                        </Button>
                    </div>
                </div>

                <!-- Entity Picker -->
                <Field v-if="selectedSourceType">
                    <FieldLabel>
                        {{ selectedSourceType === 'order' ? 'Chọn đơn hàng' : 'Chọn dịch vụ' }}
                    </FieldLabel>
                    <FieldContent>
                        <Select v-model="selectedSourceId">
                            <SelectTrigger class="h-12">
                                <SelectValue :placeholder="selectedSourceType === 'order' ? 'Tìm đơn hàng...' : 'Tìm lịch hẹn...'" />
                            </SelectTrigger>
                            <SelectContent>
                                <template v-if="selectedSourceType === 'order'">
                                    <SelectItem
                                        v-for="order in orderOptions"
                                        :key="order.id"
                                        :value="order.id"
                                    >
                                        <div class="flex items-center justify-between w-full gap-4">
                                            <span class="font-mono text-xs">{{ order.order_number }}</span>
                                        </div>
                                    </SelectItem>
                                </template>
                                <template v-else>
                                    <SelectItem
                                        v-for="booking in bookingOptions"
                                        :key="booking.id"
                                        :value="booking.id"
                                    >
                                        <div class="flex items-center justify-between w-full gap-4">
                                            <span class="font-mono text-xs">{{ booking.booking_number }}</span>
                                        </div>
                                    </SelectItem>
                                </template>
                                <div
                                    v-if="
                                        (selectedSourceType === 'order' && orderOptions.length === 0) ||
                                        (selectedSourceType === 'booking' && bookingOptions.length === 0)
                                    "
                                    class="px-2 py-4 text-center text-sm text-muted-foreground"
                                >
                                    Không tìm thấy mục phù hợp
                                </div>
                            </SelectContent>
                        </Select>
                    </FieldContent>
                </Field>

                <!-- Selected Entity Context Card -->
                <div
                    v-if="selectedOrder || selectedBooking"
                    class="rounded-xl border bg-muted/30 p-4 space-y-4 transition-all animate-in fade-in slide-in-from-top-2"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <component
                                :is="selectedSourceType === 'order' ? Package : User"
                                class="h-4 w-4 text-primary"
                            />
                            <span class="text-sm font-semibold">
                                {{ selectedOrder?.order_number || selectedBooking?.booking_number }}
                            </span>
                        </div>
                        <Badge variant="outline" class="text-[10px] uppercase">
                            {{ selectedOrder ? selectedOrder.customer_name : selectedBooking?.customer_name }}
                        </Badge>
                    </div>

                    <Separator />

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="space-y-1">
                            <span class="text-xs text-muted-foreground block">Loại hình</span>
                            <span class="font-medium">{{ selectedSourceType === 'order' ? 'Bán hàng' : 'Dịch vụ' }}</span>
                        </div>
                        <div class="space-y-1 text-right">
                            <span class="text-xs text-muted-foreground block">Tổng giá trị</span>
                            <span class="font-medium tabular-nums">{{ formatPrice(calculatedAmount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Invoice Configuration -->
                <div v-if="selectedSourceId" class="space-y-4 pt-2">
                    <Separator />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Field>
                            <FieldLabel>Loại hóa đơn</FieldLabel>
                            <FieldContent>
                                <Input
                                    :model-value="invoiceTypeLabel"
                                    readonly
                                    class="bg-muted font-medium h-10"
                                />
                                <FieldError :errors="[form.errors.type]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Số tiền phải thu</FieldLabel>
                            <FieldContent>
                                <div class="relative">
                                    <Input
                                        v-model="form.amount_due"
                                        type="number"
                                        step="1000"
                                        class="tabular-nums font-bold h-10 pr-12"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">
                                        đ
                                    </span>
                                </div>
                                <FieldError :errors="[form.errors.amount_due]" />
                            </FieldContent>
                        </Field>
                    </div>
                </div>
            </div>

            <DialogFooter class="mt-6">
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button
                    type="button"
                    :disabled="form.processing || !selectedSourceId"
                    @click="submit"
                    class="min-w-[120px]"
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

<style scoped>
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
