<script setup lang="ts">
import { Loader2, ExternalLink } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
    paymentUrl: string;
    amount: string;
}>();

const emit = defineEmits<{
    close: [];
    paid: [];
}>();

function goToPayment() {
    window.location.href = props.paymentUrl;
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[420px]">
            <DialogHeader>
                <DialogTitle>Thanh toán qua VNPay</DialogTitle>
                <DialogDescription>
                    Bạn đang được chuyển đến cổng thanh toán VNPay để hoàn tất giao dịch.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <div class="rounded-lg border bg-muted p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted-foreground">Số tiền:</span>
                        <span class="font-semibold text-lg">
                            {{ Number(amount).toLocaleString('vi-VN') }}đ
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-muted-foreground">Cổng thanh toán:</span>
                        <span class="font-medium">VNPay</span>
                    </div>
                </div>

                <div class="flex items-center justify-center py-4">
                    <Loader2 class="h-6 w-6 animate-spin text-primary" />
                    <span class="ml-3 text-sm text-muted-foreground">
                        Đang chuyển đến cổng thanh toán...
                    </span>
                </div>
            </div>

            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="emit('close')">
                    Đóng
                </Button>
                <Button @click="goToPayment">
                    <ExternalLink class="mr-2 h-4 w-4" />
                    Đến VNPay
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
