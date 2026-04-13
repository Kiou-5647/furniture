<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, AlertCircle, CheckCircle2, XCircle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/employee/booking';

const props = defineProps<{
    error?: string;
    response?: Record<string, string>;
}>();

function goBack() {
    router.visit(index().url);
}
</script>

<template>
    <Head title="Kết quả thanh toán VNPay" />
    <div class="min-h-screen flex items-center justify-center bg-background p-4">
        <div class="w-full max-w-md space-y-6 text-center">
            <!-- Status Icon -->
            <div class="flex justify-center">
                <XCircle
                    class="h-16 w-16 text-destructive"
                    v-if="error"
                />
            </div>

            <!-- Message -->
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ error ? 'Thanh toán không thành công' : 'Đang xử lý...' }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ error || 'Vui lòng thử lại.' }}
                </p>
            </div>

            <!-- Response Details -->
            <div
                v-if="response"
                class="rounded-lg border bg-card p-4 text-left"
            >
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Mã giao dịch:</span>
                        <span class="font-mono">{{ response.vnp_TxnRef || '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Mã VNPay:</span>
                        <span class="font-mono">{{ response.vnp_TransactionNo || '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Mã phản hồi:</span>
                        <span class="font-mono">{{ response.vnp_ResponseCode || '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Ngân hàng:</span>
                        <span>{{ response.vnp_BankCode || '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-center gap-3">
                <Button variant="outline" @click="goBack">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Quay lại
                </Button>
            </div>
        </div>
    </div>
</template>
