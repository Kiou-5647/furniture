<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    CheckCircle2,
    Package,
    Truck,
    MapPin,
    User,
    Calendar,
    FileText,
} from '@lucide/vue';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, ref } from 'vue';
import DataTable from '@/components/custom/data-table/DataTable.vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    cancel,
    index,
    receive,
    ship,
} from '@/routes/employee/inventory/transfers';
import type { BreadcrumbItem } from '@/types';
import type { StockTransfer } from '@/types/stock-transfer';
import { getItemColumns } from './types/item-columns';

const props = defineProps<{
    transfer: StockTransfer;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: index().url },
    { title: 'Chuyển kho', href: index().url },
    { title: props.transfer.transfer_number, href: '#' },
];

const statusColorMap: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
    blue: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-800',
    green: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900 dark:text-green-300 dark:border-green-800',
    red: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900 dark:text-red-300 dark:border-red-800',
};

const isDraft = computed(() => props.transfer.status === 'draft');
const isInTransit = computed(() => props.transfer.status === 'in_transit');
const canShip = computed(() => isDraft.value);
const canReceive = computed(() => isInTransit.value);
const canCancel = computed(() => isDraft.value || isInTransit.value);

const showReceiveForm = ref(false);
const previewImageOpen = ref(false);
const previewImageSrc = ref<string | null>(null);

function openImagePreview(url: string | null | undefined) {
    if (!url) return;
    previewImageSrc.value = url;
    previewImageOpen.value = true;
}

const receiveForm = useForm({
    items: props.transfer.items.map((item) => ({
        item_id: item.id,
        quantity_received: item.quantity_shipped,
    })),
});

const receiveTableColumns = computed(() =>
    getItemColumns(props.transfer.status, openImagePreview, true, receiveForm),
);

const receiveTable = useVueTable({
    get data() {
        return props.transfer.items;
    },
    get columns() {
        return receiveTableColumns.value;
    },
    getCoreRowModel: getCoreRowModel(),
});

const viewTableColumns = computed(() =>
    getItemColumns(props.transfer.status, openImagePreview, false),
);

const viewTable = useVueTable({
    get data() {
        return props.transfer.items;
    },
    get columns() {
        return viewTableColumns.value;
    },
    getCoreRowModel: getCoreRowModel(),
});

function handleShip() {
    router.post(
        ship(props.transfer).url,
        {},
        {
            preserveScroll: true,
        },
    );
}

function handleReceive() {
    receiveForm.post(receive(props.transfer).url, {
        preserveScroll: true,
        onSuccess: () => {
            showReceiveForm.value = false;
        },
    });
}

function handleCancel() {
    router.post(
        cancel(props.transfer).url,
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head :title="`Phiếu ${transfer.transfer_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div
                class="grid grid-cols-1 items-start sm:grid-cols-12 sm:gap-3"
            ></div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        @click="router.get(index().url)"
                        class="flex h-8 w-8"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <Heading
                        :title="`Phiếu ${transfer.transfer_number}`"
                        description="Chi tiết phiếu chuyển kho"
                        class="mr-3"
                    />
                    <Badge
                        variant="outline"
                        :class="
                            statusColorMap[transfer.status_color] ??
                            statusColorMap.gray
                        "
                    >
                        {{ transfer.status_label }}
                    </Badge>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="canCancel"
                        variant="outline"
                        class="text-destructive hover:text-destructive"
                        @click="handleCancel"
                    >
                        <Ban class="mr-2 h-4 w-4" /> Hủy phiếu
                    </Button>
                    <Button v-if="canShip" @click="handleShip">
                        <Truck class="mr-2 h-4 w-4" /> Xuất kho
                    </Button>
                    <Button
                        v-if="canReceive && !showReceiveForm"
                        @click="showReceiveForm = true"
                    >
                        <Package class="mr-2 h-4 w-4" /> Nhận hàng
                    </Button>
                </div>
            </div>

            <div class="@container">
                <div class="flex flex-col gap-4 @lg:flex-row @lg:items-start">
                    <!-- Sidebar Card -->
                    <div class="w-full shrink-0 @lg:w-60">
                        <Card class="overflow-hidden">
                            <CardContent class="space-y-6 pt-5">
                                <!-- Locations Stepper -->
                                <div
                                    class="relative space-y-5 pl-6 before:absolute before:top-2 before:bottom-0.5 before:left-[11px] before:w-px before:bg-border"
                                >
                                    <div class="relative">
                                        <div
                                            class="absolute -top-[3px] -left-[23px] flex h-5 w-5 items-center justify-center rounded-full border bg-background shadow-xs"
                                        >
                                            <MapPin
                                                class="h-3 w-3"
                                            />
                                        </div>
                                        <p
                                            class="mb-1 text-xs font-medium text-muted-foreground"
                                        >
                                            Từ vị trí
                                        </p>
                                        <div
                                            class="flex flex-col items-start gap-1"
                                        >
                                            <span class="text-sm font-medium">{{
                                                transfer.from_location?.name
                                            }}</span>
                                            <Badge
                                                variant="secondary"
                                                class="text-[10px]"
                                                >{{
                                                    transfer.from_location?.code
                                                }}</Badge
                                            >
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <div
                                            class="absolute -top-[3px] -left-[23px] flex h-5 w-5 items-center justify-center rounded-full border bg-background shadow-xs"
                                        >
                                            <MapPin
                                                class="h-3 w-3"
                                            />
                                        </div>
                                        <p
                                            class="mb-1 text-xs font-medium text-muted-foreground"
                                        >
                                            Đến vị trí
                                        </p>
                                        <div
                                            class="flex flex-col items-start gap-1"
                                        >
                                            <span class="text-sm font-medium">{{
                                                transfer.to_location?.name
                                            }}</span>
                                            <Badge
                                                variant="secondary"
                                                class="text-[10px]"
                                                >{{
                                                    transfer.to_location?.code
                                                }}</Badge
                                            >
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <!-- Metadata -->
                                <div class="space-y-4">
                                    <div class="flex items-start gap-3">
                                        <User
                                            class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                        />
                                        <div class="space-y-0.5">
                                            <p
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Người tạo
                                            </p>
                                            <p class="text-sm">
                                                {{
                                                    transfer.requested_by
                                                        ?.full_name ?? '—'
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="transfer.received_by"
                                        class="flex items-start gap-3"
                                    >
                                        <CheckCircle2
                                            class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"
                                        />
                                        <div class="space-y-0.5">
                                            <p
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Người nhận
                                            </p>
                                            <p class="text-sm">
                                                {{
                                                    transfer.received_by
                                                        .full_name
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <Calendar
                                            class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                        />
                                        <div class="space-y-0.5">
                                            <p
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Ngày tạo
                                            </p>
                                            <p class="text-sm tabular-nums">
                                                {{ transfer.created_at }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="transfer.received_at"
                                        class="flex items-start gap-3"
                                    >
                                        <Calendar
                                            class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                        />
                                        <div class="space-y-0.5">
                                            <p
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Ngày nhận
                                            </p>
                                            <p class="text-sm tabular-nums">
                                                {{ transfer.received_at }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="transfer.notes"
                                        class="flex items-start gap-3"
                                    >
                                        <FileText
                                            class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                                        />
                                        <div class="space-y-0.5">
                                            <p
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                Ghi chú
                                            </p>
                                            <p
                                                class="text-sm text-foreground/90"
                                            >
                                                {{ transfer.notes }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Main Content -->
                    <div class="w-full min-w-0 flex-1">
                        <Card class="flex h-full flex-col">
                            <CardHeader>
                                <CardTitle class="text-base">
                                    Danh sách sản phẩm
                                </CardTitle>
                                <CardDescription>
                                    {{ transfer.items?.length }} sản phẩm trong
                                    phiếu chuyển kho
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form
                                    v-if="showReceiveForm"
                                    @submit.prevent="handleReceive"
                                >
                                    <DataTable :table="receiveTable" />
                                    <div class="mt-4 flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="showReceiveForm = false"
                                        >
                                            Hủy
                                        </Button>
                                        <Button
                                            type="submit"
                                            :disabled="receiveForm.processing"
                                        >
                                            <CheckCircle2
                                                class="mr-2 h-4 w-4"
                                            />
                                            Xác nhận nhận hàng
                                        </Button>
                                    </div>
                                </form>

                                <DataTable v-else :table="viewTable" />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <ImagePreviewDialog
            v-model:open="previewImageOpen"
            :src="previewImageSrc"
        />
    </AppLayout>
</template>
