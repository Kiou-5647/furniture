<script setup lang="ts">
import { computed, ref } from 'vue';
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
    Loader2,
} from '@lucide/vue';
import {
    getCoreRowModel,
    useVueTable,
    VisibilityState,
} from '@tanstack/vue-table';
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
import type { StockTransfer } from '@/types';
import { getItemColumns } from './types/item-columns';

const props = defineProps<{
    transfer: StockTransfer;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: index().url },
    { title: 'Chuyển kho', href: index().url },
    { title: props.transfer.transfer_number, href: '#' },
];

// --- Status styling ---
const statusColorMap: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
    blue: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-800',
    green: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900 dark:text-green-300 dark:border-green-800',
    red: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900 dark:text-red-300 dark:border-red-800',
};

// --- State & Permissions ---
const isDraft = computed(() => props.transfer.status === 'draft');
const isInTransit = computed(() => props.transfer.status === 'in_transit');
const canShip = computed(() => isDraft.value);
const canReceive = computed(() => isInTransit.value);
const canCancel = computed(() => isDraft.value || isInTransit.value);

const showReceiveForm = ref(false);
const previewImageOpen = ref(false);
const previewImageSrc = ref<string | null>(null);

// --- Image Preview ---
function openImagePreview(url: string | null | undefined) {
    if (!url) return;
    previewImageSrc.value = url;
    previewImageOpen.value = true;
}

// --- Receipt Logic (The "Brain" of the page) ---
const receiveForm = useForm({
    items: Array.isArray(props.transfer.items)
        ? props.transfer.items //Casting to ensure type safety
              .map((item: any) => ({
                  item_id: item.id,
                  quantity_received: item.quantity_shipped,
              }))
        : [],
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

const columnVisibility = ref<VisibilityState>({});

const viewTable = useVueTable({
    get data() {
        return props.transfer.items;
    },
    get columns() {
        return viewTableColumns.value;
    },
    state: {
        get columnVisibility() {
            return columnVisibility.value;
        },
    },
    getCoreRowModel: getCoreRowModel(),
});

// --- Action Handlers ---
function handleShip() {
    router.post(ship(props.transfer).url, {}, { preserveScroll: true });
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
    router.post(cancel(props.transfer).url, {}, { preserveScroll: true });
}

function goBack() {
    router.visit(index().url);
}
</script>

<template>
    <Head :title="`Phiếu ${transfer.transfer_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="transfer" class="space-y-6 p-4 lg:p-6">
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
                                :title="`Phiếu ${transfer.transfer_number}`"
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
                        <p class="text-xs text-muted-foreground">
                            Tạo ngày {{ transfer.created_at }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="canCancel"
                        variant="ghost"
                        class="text-destructive hover:bg-destructive/10"
                        @click="handleCancel"
                    >
                        <Ban class="mr-2 h-4 w-4" /> Hủy phiếu
                    </Button>
                    <Button
                        v-if="canShip"
                        variant="outline"
                        class="border-blue-200 bg-blue-50/50 text-blue-600"
                        @click="handleShip"
                    >
                        <Truck class="mr-2 h-4 w-4" /> Xuất kho
                    </Button>
                    <Button
                        v-if="canReceive && !showReceiveForm"
                        variant="default"
                        class="bg-emerald-600 hover:bg-emerald-700"
                        @click="showReceiveForm = true"
                    >
                        <Package class="mr-2 h-4 w-4" /> Nhận hàng
                    </Button>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column: Info Sidebar -->
                <div class="space-y-6 lg:col-span-4">
                    <Card class="overflow-hidden shadow-sm">
                        <CardHeader class="bg-muted/30 pb-4">
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Warehouse class="h-4 w-4 text-primary" /> Thông
                                tin vận chuyển
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-8 p-6">
                            <!-- Source Location -->
                            <div
                                class="relative pl-8 before:absolute before:top-2 before:bottom-0 before:left-3 before:w-px before:bg-border"
                            >
                                <div
                                    class="absolute top-1 left-0 flex h-6 w-6 items-center justify-center rounded-full border bg-background shadow-sm"
                                >
                                    <MapPin class="h-3 w-3 text-blue-600" />
                                </div>
                                <div class="space-y-1">
                                    <p
                                        class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Từ vị trí nguồn
                                    </p>
                                    <p class="text-sm font-bold">
                                        {{ transfer.from_location?.name }}
                                    </p>
                                    <Badge
                                        variant="secondary"
                                        class="px-1.5 py-0 text-[10px]"
                                        >{{
                                            transfer.from_location?.code
                                        }}</Badge
                                    >
                                </div>
                            </div>

                            <!-- Destination Location -->
                            <div
                                class="relative pl-8 before:absolute before:top-2 before:bottom-0 before:left-3 before:w-px before:bg-border"
                            >
                                <div
                                    class="absolute top-1 left-0 flex h-6 w-6 items-center justify-center rounded-full border bg-background shadow-sm"
                                >
                                    <MapPin class="h-3 w-3 text-emerald-600" />
                                </div>
                                <div class="space-y-1">
                                    <p
                                        class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Đến vị trí đích
                                    </p>
                                    <p class="text-sm font-bold">
                                        {{ transfer.to_location?.name }}
                                    </p>
                                    <Badge
                                        variant="secondary"
                                        class="px-1.5 py-0 text-[10px]"
                                        >{{ transfer.to_location?.code }}</Badge
                                    >
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
                                            {{ transfer.received_by.full_name }}
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
                                            class="text-sm text-foreground/80 italic"
                                        >
                                            {{ transfer.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Items Area -->
                <div class="space-y-6 lg:col-span-8">
                    <Card class="overflow-hidden shadow-sm">
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-4"
                        >
                            <div>
                                <CardTitle
                                    class="flex items-center gap-2 text-base"
                                >
                                    <Package class="h-4 w-4 text-primary" />
                                    Danh sách sản phẩm
                                </CardTitle>
                                <CardDescription>
                                    {{ transfer.items?.length }} sản phẩm trong
                                    phiếu chuyển kho
                                </CardDescription>
                            </div>
                            <Badge
                                v-if="showReceiveForm"
                                variant="default"
                                class="bg-emerald-600"
                            >
                                Chế độ nhận hàng
                            </Badge>
                        </CardHeader>
                        <CardContent>
                            <form
                                v-if="showReceiveForm"
                                @submit.prevent="handleReceive"
                                class="space-y-4"
                            >
                                <DataTable :table="receiveTable" />
                                <div class="flex justify-end gap-3 pt-4">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="showReceiveForm = false"
                                    >
                                        Hủy bỏ
                                    </Button>
                                    <Button
                                        type="submit"
                                        :disabled="receiveForm.processing"
                                        class="bg-emerald-600 hover:bg-emerald-700"
                                    >
                                        <CheckCircle2 class="mr-2 h-4 w-4" />
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

        <ImagePreviewDialog
            v-model:open="previewImageOpen"
            :src="previewImageSrc"
        />
    </AppLayout>
</template>
