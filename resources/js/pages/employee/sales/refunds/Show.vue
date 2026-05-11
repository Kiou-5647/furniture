<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    XCircle,
    DollarSign,
    FileText,
    AlertCircle,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib';
import { index, approve, reject } from '@/routes/employee/sales/refunds';
import type { BreadcrumbItem } from '@/types';
import type { Refund } from '@/types/refund';

const props = defineProps<{
    refund: Refund;
}>();

const isProcessing = ref(false);
const rejectNotes = ref('');

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Hoàn tiền', href: index().url },
    { title: `Yêu cầu #${props.refund?.id.slice(0, 8)}`, href: '#' },
]);

// --- Actions ---
async function handleApprove() {
    if (!confirm('Xác nhận hoàn tiền cho yêu cầu này?')) return;
    isProcessing.value = true;
    router.post(
        approve({ refund: props.refund.id }).url,
        {},
        {
            onFinish: () => (isProcessing.value = false),
            onSuccess: () => toast.success('Đã duyệt và hoàn thành hoàn tiền.'),
        },
    );
}

async function handleReject() {
    if (!rejectNotes.value) {
        toast.error('Vui lòng nhập lý do từ chối.');
        return;
    }
    if (!confirm('Xác nhận từ chối yêu cầu hoàn tiền này?')) return;

    isProcessing.value = true;
    router.post(
        reject({ refund: props.refund.id }).url,
        {
            notes: rejectNotes.value,
        },
        {
            onFinish: () => (isProcessing.value = false),
            onSuccess: () => toast.success('Đã từ chối yêu cầu hoàn tiền.'),
        },
    );
}

function goBack() {
    router.visit(index().url);
}
</script>

<template>
    <Head title="Chi tiết hoàn tiền" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="refund" class="space-y-6 p-4 lg:p-6">
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
                                :title="`Yêu cầu hoàn tiền #${refund.id.slice(0, 8)}`"
                            />
                            <Badge
                                :class="[
                                    'text-xs',
                                    refund.status === 'completed'
                                        ? 'border-green-200 bg-green-100 text-green-700'
                                        : refund.status === 'rejected'
                                          ? 'border-red-200 bg-red-100 text-red-700'
                                          : 'border-yellow-200 bg-yellow-100 text-yellow-700',
                                ]"
                                variant="outline"
                            >
                                {{ refund.status_label }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Tạo ngày {{ refund.created_at }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="refund.status === 'pending'"
                    class="flex items-center gap-2 border-l pl-2"
                >
                    <Button
                        variant="outline"
                        class="border-red-200 bg-red-50/50 text-red-600"
                        :disabled="isProcessing"
                        @click="handleReject"
                    >
                        <XCircle class="mr-2 h-4 w-4" /> Từ chối
                    </Button>
                    <Button
                        variant="default"
                        class="bg-green-600 hover:bg-green-700"
                        :disabled="isProcessing"
                        @click="handleApprove"
                    >
                        <CheckCircle2 class="mr-2 h-4 w-4" /> Duyệt & Hoàn tiền
                    </Button>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column: Status & Meta -->
                <div class="space-y-6 lg:col-span-4">
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Thông tin hoàn tiền
                            </h3>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="flex items-end justify-between">
                                <span class="text-sm text-muted-foreground"
                                    >Số tiền hoàn lại</span
                                >
                                <span
                                    class="text-3xl font-black text-primary tabular-nums"
                                    >{{ formatPrice(refund.amount) }}</span
                                >
                            </div>
                            <div class="space-y-2 border-t pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground"
                                        >Người yêu cầu</span
                                    >
                                    <span class="font-medium">{{
                                        refund.requested_by?.full_name || '—'
                                    }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground"
                                        >Người xử lý</span
                                    >
                                    <span class="font-medium">{{
                                        refund.processed_by?.full_name ||
                                        'Chưa xử lý'
                                    }}</span>
                                </div>
                                <div
                                    v-if="refund.processed_at"
                                    class="flex justify-between text-xs"
                                >
                                    <span class="text-muted-foreground"
                                        >Ngày xử lý</span
                                    >
                                    <span class="font-medium">{{
                                        refund.processed_at
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="refund.status === 'pending'"
                        class="space-y-3 rounded-xl border bg-yellow-50/50 p-4"
                    >
                        <div class="flex items-center gap-2 text-yellow-700">
                            <AlertCircle class="h-4 w-4" />
                            <span class="text-xs font-bold uppercase"
                                >Lý do từ chối (nếu có)</span
                            >
                        </div>
                        <Input
                            v-model="rejectNotes"
                            placeholder="Nhập lý do từ chối tại đây..."
                            class="h-9 text-sm"
                        />
                    </div>
                </div>

                <!-- Right Column: Context -->
                <div class="space-y-6 lg:col-span-8">
                    <!-- Source Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <FileText class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-sm font-semibold">
                                Nguồn gốc hoàn tiền
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-8 p-6 md:grid-cols-2">
                            <div class="space-y-3">
                                <p
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Đơn hàng / Đặt lịch
                                </p>
                                <div v-if="refund.order" class="space-y-1">
                                    <p class="text-sm font-bold">
                                        Đơn hàng #{{
                                            refund.order.order_number
                                        }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Khách: {{ refund.order.customer?.name }}
                                    </p>
                                </div>
                                <div
                                    v-else-if="refund.booking"
                                    class="space-y-1"
                                >
                                    <p class="text-sm font-bold">
                                        Đặt lịch #{{
                                            refund.booking.booking_number
                                        }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Khách:
                                        {{ refund.booking.customer?.name }}
                                    </p>
                                </div>
                                <div
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Không tìm thấy thông tin nguồn
                                </div>
                            </div>
                            <div class="space-y-3">
                                <p
                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Hóa đơn liên quan
                                </p>
                                <div v-if="refund.invoice" class="space-y-1">
                                    <p class="font-mono text-sm font-bold">
                                        {{ refund.invoice.invoice_number }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Trạng thái:
                                        {{ refund.invoice.status_label }}
                                    </p>
                                </div>
                                <div
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Không có hóa đơn liên kết
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason Card -->
                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center gap-2 border-b bg-muted/30 px-4 py-3"
                        >
                            <AlertCircle
                                class="h-4 w-4 text-muted-foreground"
                            />
                            <h3 class="text-sm font-semibold">
                                Chi tiết lý do
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <p
                                        class="mb-1 text-[11px] font-bold text-muted-foreground uppercase"
                                    >
                                        Yêu cầu từ khách hàng
                                    </p>
                                    <p
                                        class="text-sm leading-relaxed text-foreground italic"
                                    >
                                        "{{
                                            refund.reason ||
                                            'Không có lý do cụ thể được cung cấp.'
                                        }}"
                                    </p>
                                </div>
                                <div v-if="refund.notes" class="border-t pt-4">
                                    <p
                                        class="mb-1 text-[11px] font-bold text-muted-foreground uppercase"
                                    >
                                        Ghi chú từ nhân viên xử lý
                                    </p>
                                    <p class="text-sm text-foreground">
                                        {{ refund.notes }}
                                    </p>
                                </div>
                            </div>
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
