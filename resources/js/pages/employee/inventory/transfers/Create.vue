<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Plus,
    Trash2,
    ImageIcon,
    Warehouse,
    Package,
    Loader2,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/SearchableSelect.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import {
    index,
    store,
    variants as variantsRoute,
} from '@/routes/employee/inventory/transfers';
import type { BreadcrumbItem } from '@/types';
import type { LocationOption, VariantOption } from '@/types/stock-transfer';

const props = defineProps<{
    locationOptions: LocationOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kho hàng', href: index().url },
    { title: 'Chuyển kho', href: index().url },
    { title: 'Tạo phiếu', href: '#' },
];

interface FormItem {
    variant_id: string;
    quantity: number;
}

const form = useForm({
    from_location_id: '',
    to_location_id: '',
    notes: '',
    items: [] as FormItem[],
});

const availableVariants = ref<VariantOption[]>([]);
const isLoadingVariants = ref(false);
const previewImageOpen = ref(false);
const previewImageSrc = ref<string | null>(null);

const destinationOptions = computed(() =>
    props.locationOptions.filter((l) => l.id !== form.from_location_id),
);

const totalItemsCount = computed(() =>
    form.items.reduce((sum, item) => sum + (item.quantity || 0), 0),
);

watch(
    () => form.from_location_id,
    async (locationId) => {
        form.items = [];
        availableVariants.value = [];
        if (!locationId) return;

        isLoadingVariants.value = true;
        try {
            const response = await fetch(variantsRoute(locationId).url);
            availableVariants.value = await response.json();
        } catch (e) {
            console.error('Failed to load variants', e);
        } finally {
            isLoadingVariants.value = false;
        }
    },
);

const usedVariantIds = computed(
    () => new Set(form.items.map((i) => i.variant_id)),
);
const remainingVariants = computed(() =>
    availableVariants.value.filter((v) => !usedVariantIds.value.has(v.id)),
);

function addItem() {
    form.items.push({ variant_id: '', quantity: 1 });
}

function removeItem(idx: number) {
    form.items.splice(idx, 1);
}

function getVariant(variantId: string) {
    return availableVariants.value.find((v) => v.id === variantId);
}

function openImagePreview(url: string | null | undefined) {
    if (!url) return;
    previewImageSrc.value = url;
    previewImageOpen.value = true;
}

function submit() {
    form.post(store().url);
}

function getError(key: string): string | undefined {
    return (form.errors as Record<string, string>)[key];
}
</script>

<template>
    <Head title="Tạo phiếu chuyển kho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 lg:p-6">
            <!-- Top Header -->
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <Heading
                        title="Tạo phiếu chuyển kho"
                        description="Quản lý luân chuyển hàng hóa giữa các vị trí kho"
                    />
                </div>
                <Button
                    variant="outline"
                    @click="router.get(index().url)"
                    class="rounded-full"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" /> Quay lại
                </Button>
            </div>

            <form
                class="grid gap-6 lg:grid-cols-12 lg:items-start"
                @submit.prevent="submit"
            >
                <!-- Left Column: Configuration Sidebar -->
                <div class="space-y-6 lg:col-span-4">
                    <Card class="shadow-sm">
                        <CardHeader class="pb-4">
                            <CardTitle
                                class="flex items-center gap-2 text-base"
                            >
                                <Warehouse class="h-4 w-4 text-primary" />
                                Cấu hình vận chuyển
                            </CardTitle>
                            <CardDescription>
                                Thiết lập nguồn và đích của phiếu chuyển
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Source Location -->
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                    >Từ vị trí nguồn</Label
                                >
                                <SearchableSelect
                                    v-model="form.from_location_id"
                                    :options="locationOptions"
                                    value-key="id"
                                    label-key="name"
                                    placeholder="Chọn kho gửi..."
                                    :searchable-keys="[
                                        'name',
                                        'code',
                                        'full_address',
                                    ]"
                                    :custom-label="
                                        (opt: any) =>
                                            `${opt.name} (${opt.code})`
                                    "
                                >
                                    <template #item="{ option }">
                                        <div class="flex flex-col py-1">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-medium"
                                                    >{{ option.name }}</span
                                                >
                                                <span
                                                    class="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px]"
                                                    >{{ option.code }}</span
                                                >
                                            </div>
                                            <span
                                                class="truncate text-[11px] text-muted-foreground"
                                            >
                                                {{
                                                    option.full_address ||
                                                    'Không có địa chỉ'
                                                }}
                                            </span>
                                        </div>
                                    </template>
                                </SearchableSelect>
                                <p
                                    v-if="form.errors.from_location_id"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.from_location_id }}
                                </p>
                            </div>

                            <!-- Destination Location -->
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                    >Đến vị trí đích</Label
                                >
                                <SearchableSelect
                                    v-model="form.to_location_id"
                                    :options="destinationOptions"
                                    value-key="id"
                                    label-key="name"
                                    placeholder="Chọn kho nhận..."
                                    :searchable-keys="[
                                        'name',
                                        'code',
                                        'full_address',
                                    ]"
                                    :custom-label="
                                        (opt: any) =>
                                            `${opt.name} (${opt.code})`
                                    "
                                >
                                    <template #item="{ option }">
                                        <div class="flex flex-col py-1">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-medium"
                                                    >{{ option.name }}</span
                                                >
                                                <span
                                                    class="rounded bg-muted px-1.5 py-0.5 font-mono text-[10px]"
                                                    >{{ option.code }}</span
                                                >
                                            </div>
                                            <span
                                                class="truncate text-[11px] text-muted-foreground"
                                            >
                                                {{
                                                    option.full_address ||
                                                    'Không có địa chỉ'
                                                }}
                                            </span>
                                        </div>
                                    </template>
                                </SearchableSelect>
                                <p
                                    v-if="form.errors.to_location_id"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.to_location_id }}
                                </p>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <Label
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                    >Ghi chú chuyển kho</Label
                                >
                                <Textarea
                                    v-model="form.notes"
                                    placeholder="Lý do chuyển hàng, lưu ý vận chuyển..."
                                    rows="3"
                                    class="resize-none"
                                />
                                <p
                                    v-if="form.errors.notes"
                                    class="text-xs text-destructive"
                                >
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Product Management Area -->
                <div class="space-y-4 lg:col-span-8">
                    <Card
                        class="flex max-h-[calc(100vh-220px)] flex-col shadow-sm"
                    >
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
                                    Thêm sản phẩm có tồn kho tại
                                    {{ availableVariants.length }} loại variant
                                </CardDescription>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :disabled="
                                    !form.from_location_id ||
                                    remainingVariants.length === 0
                                "
                                @click="addItem"
                                class="rounded-full"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Thêm sản phẩm
                            </Button>
                        </CardHeader>

                        <CardContent
                            class="flex-1 space-y-4 overflow-y-auto pr-2"
                        >
                            <!-- Empty State: No source selected -->
                            <div
                                v-if="!form.from_location_id"
                                class="flex flex-col items-center justify-center space-y-4 py-20 text-center"
                            >
                                <div class="rounded-full bg-muted p-4">
                                    <Warehouse
                                        class="h-8 w-8 text-muted-foreground"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium">
                                        Hãy chọn vị trí nguồn
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Để xem danh sách sản phẩm khả dụng để
                                        chuyển
                                    </p>
                                </div>
                            </div>

                            <!-- Loading State -->
                            <div
                                v-else-if="isLoadingVariants"
                                class="flex flex-col items-center justify-center space-y-4 py-20 text-center"
                            >
                                <Loader2
                                    class="h-8 w-8 animate-spin text-primary"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Đang tải dữ liệu tồn kho...
                                </p>
                            </div>

                            <!-- Empty State: No variants in stock -->
                            <div
                                v-else-if="availableVariants.length === 0"
                                class="flex flex-col items-center justify-center space-y-4 py-20 text-center"
                            >
                                <div class="rounded-full bg-muted p-4">
                                    <Package
                                        class="h-8 w-8 text-muted-foreground"
                                    />
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Không có sản phẩm nào tồn kho tại vị trí này
                                </p>
                            </div>

                            <!-- Product Items List -->
                            <div v-else class="space-y-3">
                                <div
                                    v-for="(item, idx) in form.items"
                                    :key="idx"
                                    class="group flex items-center gap-4 rounded-xl border bg-card p-3 transition-all hover:border-primary/50 hover:shadow-sm"
                                >
                                    <!-- Product Thumbnail (Primary) -->
                                    <div
                                        class="relative h-14 w-14 shrink-0 overflow-hidden rounded-lg border bg-muted"
                                    >
                                        <img
                                            v-if="
                                                getVariant(item.variant_id)
                                                    ?.image_url
                                            "
                                            :src="
                                                getVariant(item.variant_id)
                                                    ?.image_url!
                                            "
                                            class="h-full w-full cursor-zoom-in object-cover"
                                            @click="
                                                openImagePreview(
                                                    getVariant(item.variant_id)
                                                        ?.full_image_url ||
                                                        getVariant(
                                                            item.variant_id,
                                                        )?.image_url,
                                                )
                                            "
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center"
                                        >
                                            <ImageIcon
                                                class="h-6 w-6 text-muted-foreground"
                                            />
                                        </div>
                                    </div>

                                    <!-- Searchable Product Selection -->
                                    <div class="flex-1 space-y-1">
                                        <SearchableSelect
                                            v-model="item.variant_id"
                                            :options="availableVariants"
                                            value-key="id"
                                            label-key="name"
                                            placeholder="Chọn sản phẩm..."
                                            :searchable-keys="[
                                                'name',
                                                'sku',
                                                'product_name',
                                            ]"
                                            :custom-label="
                                                (v) =>
                                                    `${v.product_name || ''} ${v.name} (${v.sku})`
                                            "
                                            class="h-10"
                                        >
                                            <template #item="{ option }">
                                                <div
                                                    class="flex items-center gap-3 py-1"
                                                >
                                                    <!-- Tiny Thumb in Dropdown -->
                                                    <div
                                                        class="h-8 w-8 shrink-0 overflow-hidden rounded border bg-muted"
                                                    >
                                                        <img
                                                            v-if="
                                                                option.image_url
                                                            "
                                                            :src="
                                                                option.image_url
                                                            "
                                                            class="h-full w-full object-cover"
                                                        />
                                                        <div
                                                            v-else
                                                            class="flex h-full w-full items-center justify-center"
                                                        >
                                                            <ImageIcon
                                                                class="h-3 w-3 text-muted-foreground"
                                                            />
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="flex flex-col overflow-hidden"
                                                    >
                                                        <div
                                                            class="flex items-center gap-1.5"
                                                        >
                                                            <span
                                                                class="truncate text-sm font-medium"
                                                            >
                                                                {{
                                                                    option.product_name
                                                                }}
                                                                {{
                                                                    option.name
                                                                }}
                                                            </span>
                                                            <span
                                                                class="rounded bg-muted px-1 font-mono text-[10px] text-muted-foreground"
                                                            >
                                                                {{ option.sku }}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-3"
                                                        >
                                                            <span
                                                                class="text-[11px] font-medium text-emerald-600"
                                                            >
                                                                Tồn:
                                                                {{
                                                                    option.available_quantity
                                                                }}
                                                            </span>
                                                            <span
                                                                class="text-[11px] text-muted-foreground"
                                                            >
                                                                Giá vốn:
                                                                {{
                                                                    formatPrice(
                                                                        option.price ||
                                                                            0,
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </SearchableSelect>
                                        <p
                                            v-if="
                                                getError(
                                                    `items.${idx}.variant_id`,
                                                )
                                            "
                                            class="text-xs text-destructive"
                                        >
                                            {{
                                                getError(
                                                    `items.${idx}.variant_id`,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="flex items-center gap-2">
                                        <Label
                                            class="hidden text-xs text-muted-foreground sm:block"
                                            >SL</Label
                                        >
                                        <Input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            :max="
                                                getVariant(item.variant_id)
                                                    ?.available_quantity ??
                                                999999
                                            "
                                            class="h-10 w-24 text-center"
                                        />
                                        <p
                                            v-if="
                                                getError(
                                                    `items.${idx}.quantity`,
                                                )
                                            "
                                            class="absolute text-xs text-destructive"
                                        >
                                            {{
                                                getError(
                                                    `items.${idx}.quantity`,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 shrink-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:text-destructive"
                                        @click="removeItem(idx)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>

            <!-- Bottom Action Bar -->
            <div
                class="flex items-center justify-between rounded-xl border bg-muted/30 p-4"
            >
                <div
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <Package class="h-4 w-4" />
                    <span
                        >Tổng cộng:
                        <span class="font-bold text-foreground">{{
                            totalItemsCount
                        }}</span>
                        sản phẩm được chọn</span
                    >
                </div>
                <div class="flex gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.get(index().url)"
                        >Hủy bỏ</Button
                    >
                    <Button
                        type="submit"
                        :disabled="form.processing || form.items.length === 0"
                        class="bg-primary px-8 hover:bg-primary/90"
                        @click="submit"
                    >
                        Tạo phiếu chuyển kho
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
    <ImagePreviewDialog
        v-model:open="previewImageOpen"
        :src="previewImageSrc"
    />
</template>
