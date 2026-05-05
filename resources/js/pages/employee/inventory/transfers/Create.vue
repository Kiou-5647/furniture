<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, ImageIcon } from '@lucide/vue';
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

function openImagePreview(url: string | null | undefined) {
    if (!url) return;
    previewImageSrc.value = url;
    previewImageOpen.value = true;
}

const destinationOptions = computed(() =>
    props.locationOptions.filter((l) => l.id !== form.from_location_id),
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

function getError(key: string): string | undefined {
    return (form.errors as Record<string, string>)[key];
}

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Tạo phiếu chuyển kho" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Tạo phiếu chuyển kho"
                    description="Tạo phiếu chuyển hàng giữa các vị trí kho"
                />
                <Button variant="outline" @click="router.get(index().url)">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Quay lại
                </Button>
            </div>

            <form
                class="grid gap-6 lg:grid-cols-3 lg:items-start"
                @submit.prevent="submit"
            >
                <!-- Left Column: General Information (Fixed Width) -->
                <div class="space-y-4 lg:col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle>Thông tin chung</CardTitle>
                            <CardDescription>
                                Thiết lập vị trí vận chuyển và ghi chú
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="from_location">Từ vị trí</Label>
                                <Select v-model="form.from_location_id">
                                    <SelectTrigger id="from_location">
                                        <SelectValue
                                            placeholder="Chọn vị trí nguồn"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="loc in locationOptions"
                                            :key="loc.id"
                                            :value="loc.id"
                                        >
                                            {{ loc.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.from_location_id"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.from_location_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="to_location">Đến vị trí</Label>
                                <Select v-model="form.to_location_id">
                                    <SelectTrigger id="to_location">
                                        <SelectValue
                                            placeholder="Chọn vị trí đích"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="loc in destinationOptions"
                                            :key="loc.id"
                                            :value="loc.id"
                                        >
                                            {{ loc.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.to_location_id"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.to_location_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="notes">Ghi chú</Label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    placeholder="Nhập ghi chú chi tiết..."
                                    rows="4"
                                />
                                <p
                                    v-if="form.errors.notes"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Products Section (Scrollable) -->
                <div class="space-y-4 lg:col-span-2">
                    <Card class="flex max-h-[calc(100vh-200px)] flex-col">
                        <CardHeader
                            class="flex shrink-0 flex-row items-center justify-between space-y-0 pb-4"
                        >
                            <div>
                                <CardTitle>Danh sách sản phẩm</CardTitle>
                                <CardDescription>
                                    Thêm các sản phẩm cần chuyển từ kho nguồn
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
                            >
                                <Plus class="mr-2 h-4 w-4" /> Thêm sản phẩm
                            </Button>
                        </CardHeader>
                        <CardContent class="space-y-4 overflow-y-auto pr-2">
                            <p
                                v-if="form.errors.items"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.items }}
                            </p>

                            <div
                                v-if="!form.from_location_id"
                                class="flex flex-col items-center justify-center py-12 text-center"
                            >
                                <div class="mb-4 rounded-full bg-muted p-4">
                                    <ImageIcon
                                        class="h-8 w-8 text-muted-foreground"
                                    />
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Vui lòng chọn vị trí nguồn trước để hiển thị
                                    sản phẩm
                                </p>
                            </div>

                            <div
                                v-else-if="isLoadingVariants"
                                class="flex flex-col items-center justify-center py-12 text-center"
                            >
                                <div
                                    class="mb-4 h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"
                                ></div>
                                <p class="text-sm text-muted-foreground">
                                    Đang tải danh sách sản phẩm...
                                </p>
                            </div>

                            <div
                                v-else-if="availableVariants.length === 0"
                                class="flex flex-col items-center justify-center py-12 text-center"
                            >
                                <div class="mb-4 rounded-full bg-muted p-4">
                                    <ImageIcon
                                        class="h-8 w-8 text-muted-foreground"
                                    />
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Không có sản phẩm tồn kho tại vị trí này
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="(item, idx) in form.items"
                                    :key="idx"
                                    class="group flex items-center gap-4 rounded-xl border bg-card p-3 transition-all hover:border-primary/50 hover:shadow-sm"
                                >
                                    <div
                                        class="relative h-12 w-12 shrink-0 overflow-hidden rounded-lg border bg-muted"
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
                                            class="h-full w-full object-cover"
                                            @click="
                                                openImagePreview(
                                                    getVariant(item.variant_id)
                                                        ?.full_image_url ??
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
                                                class="h-5 w-5 text-muted-foreground"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex-1 space-y-1">
                                        <Select v-model="item.variant_id">
                                            <SelectTrigger
                                                class="h-10 w-full border-none bg-transparent px-0 font-medium focus:ring-0"
                                            >
                                                <SelectValue
                                                    placeholder="Chọn sản phẩm"
                                                >
                                                    <span
                                                        v-if="item.variant_id"
                                                        class="truncate"
                                                    >
                                                        {{
                                                            getVariant(
                                                                item.variant_id,
                                                            )?.sku
                                                        }}
                                                        -
                                                        {{
                                                            getVariant(
                                                                item.variant_id,
                                                            )?.name
                                                        }}
                                                    </span>
                                                </SelectValue>
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="v in [
                                                        ...remainingVariants,
                                                        ...(item.variant_id
                                                            ? availableVariants.filter(
                                                                  (av) =>
                                                                      av.id ===
                                                                      item.variant_id,
                                                              )
                                                            : []),
                                                    ]"
                                                    :key="v.id"
                                                    :value="v.id"
                                                    class="py-3"
                                                >
                                                    <div
                                                        class="flex flex-col gap-1 pr-6"
                                                    >
                                                        <span
                                                            class="text-sm font-medium"
                                                            >{{ v.sku }} -
                                                            {{ v.name }}</span
                                                        >
                                                        <div
                                                            class="flex items-center gap-2"
                                                        >
                                                            <span
                                                                class="text-xs font-medium text-emerald-600"
                                                            >
                                                                Tồn:
                                                                {{
                                                                    v.available_quantity
                                                                }}
                                                            </span>
                                                            <span
                                                                v-if="v.price"
                                                                class="text-xs text-muted-foreground"
                                                            >
                                                                {{
                                                                    formatPrice(
                                                                        v.price,
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
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

                                    <div class="flex items-center gap-2">
                                        <Label
                                            class="hidden text-xs text-muted-foreground sm:block"
                                            >Số lượng</Label
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

                                <div
                                    v-if="
                                        form.items.length === 0 &&
                                        availableVariants.length > 0
                                    "
                                    class="flex flex-col items-center justify-center py-12 text-center"
                                >
                                    <p class="text-sm text-muted-foreground">
                                        Danh sách trống. Hãy thêm sản phẩm để
                                        bắt đầu chuyển kho.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>

            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    variant="outline"
                    @click="router.get(index().url)"
                >
                    Hủy
                </Button>
                <Button
                    type="submit"
                    :disabled="form.processing || form.items.length === 0"
                    class="px-8"
                >
                    Tạo phiếu chuyển kho
                </Button>
            </div>
        </div>

        <ImagePreviewDialog
            v-model:open="previewImageOpen"
            :src="previewImageSrc"
        />
    </AppLayout>
</template>
