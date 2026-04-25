<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, User, FileText, CreditCard, ClipboardList } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/employee/sales/refunds';
import { approve, reject } from '@/routes/employee/sales/refunds';
import type { BreadcrumbItem } from '@/types';
import type { Refund } from '@/types/refund';

const props = defineProps<{
    refund: Refund;
}>();

const isProcessing = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Hoàn tiền', href: index().url },
    { title: 'Chi tiết', href: '#' },
];

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

function handleApprove() {
    if (!confirm('Duyệt yêu cầu hoàn tiền này?')) return;
    isProcessing.value = true;
    router.post(approve(props.refund.id), {}, {
        onFinish: () => (isProcessing.value = false),
    });
}

function handleReject() {
    if (!confirm('Từ chối yêu cầu hoàn tiền này?')) return;
    isProcessing.value = true;
    router.post(reject(props.refund.id), {}, {
        onFinish: () => (isProcessing.value = false),
    });
}

function goBack() {
    router.visit(index().url);
}
</script>

<template>
    <Head title="Chi tiết hoàn tiền" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="refund" class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a
                        :href="index().url"
                        class="text-muted-foreground hover:text-foreground cursor-pointer"
                        @click.prevent="goBack"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </a>
                    <div>
                        <h1 class="text-2xl font-semibold">Chi tiết hoàn tiền</h1>
                        <p class="text-sm text-muted-foreground">
                            Mã: {{ refund.id.slice(0, 8) }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="refund.status === 'pending'"
                        variant="outline"
                        class="text-green-600"
                        :disabled="isProcessing"
                        @click="handleApprove"
                    >
                        Duyệt
                    </Button>
                    <Button
                        v-if="refund.status === 'pending'"
                        variant="outline"
                        class="text-destructive"
                        :disabled="isProcessing"
                        @click="handleReject"
                    >
                        Từ chối
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold flex items-center gap-2">
                        <FileText class="h-4 w-4 text-muted-foreground" />
                        Đơn hàng liên quan
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-if="refund.order"
                            class="text-sm"
                        >
                            <span class="text-muted-foreground">Mã đơn:</span>
                            <span class="ml-2 font-mono">{{ refund.order.order_number }}</span>
                        </div>
                        <div
                            v-else
                            class="text-sm text-muted-foreground"
                        >
                            Không có
                        </div>
                        <div
                            v-if="refund.order"
                            class="text-sm"
                        >
                            <span class="text-muted-foreground">Tổng tiền:</span>
                            <span class="ml-2 font-medium">{{ Number(refund.order.total_amount).toLocaleString('vi-VN') }}đ</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold flex items-center gap-2">
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                        Thanh toán gốc
                    </h3>
                    <div class="space-y-2">
                        <div class="text-sm">
                            <span class="text-muted-foreground">Cổng:</span>
                            <span class="ml-2">{{ refund.payment?.gateway ?? '—' }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-muted-foreground">Số tiền gốc:</span>
                            <span class="ml-2 font-medium">{{ refund.payment ? Number(refund.payment.amount).toLocaleString('vi-VN') + 'đ' : '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold flex items-center gap-2">
                        <ClipboardList class="h-4 w-4 text-muted-foreground" />
                        Thông tin hoàn tiền
                    </h3>
                    <div class="space-y-2">
                        <div class="text-lg font-bold text-destructive">
                            {{ Number(refund.amount).toLocaleString('vi-VN') }}đ
                        </div>
                        <div v-if="refund.reason" class="text-sm">
                            <span class="text-muted-foreground">Lý do:</span>
                            <span class="ml-2">{{ refund.reason }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-muted-foreground">Trạng thái:</span>
                            <Badge
                                class="ml-2 text-xs"
                                :class="statusColors[refund.status] || 'bg-gray-100'"
                            >
                                {{ refund.status_label }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold flex items-center gap-2">
                        <User class="h-4 w-4 text-muted-foreground" />
                        Người xử lý
                    </h3>
                    <div class="space-y-2">
                        <div class="text-sm">
                            <span class="text-muted-foreground">Người tạo:</span>
                            <span class="ml-2">{{ refund.requested_by?.full_name ?? '—' }}</span>
                        </div>
                        <div
                            v-if="refund.processed_by"
                            class="text-sm"
                        >
                            <span class="text-muted-foreground">Xử lý bởi:</span>
                            <span class="ml-2">{{ refund.processed_by.full_name }}</span>
                        </div>
                        <div
                            v-if="refund.notes"
                            class="text-sm"
                        >
                            <span class="text-muted-foreground">Ghi chú:</span>
                            <span class="ml-2">{{ refund.notes }}</span>
                        </div>
                        <div
                            v-if="refund.processed_at"
                            class="text-sm text-muted-foreground"
                        >
                            Xử lý lúc: {{ refund.processed_at }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="p-6 text-center text-muted-foreground">
            Đang tải...
        </div>
    </AppLayout>
</template>
