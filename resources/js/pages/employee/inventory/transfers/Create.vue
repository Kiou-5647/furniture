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

            <form class="space-y-6" @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Thông tin chuyển kho</CardTitle>
                        <CardDescription>
                            Chọn vị trí nguồn và đích cho phiếu chuyển kho
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6 sm:grid-cols-2">
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

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="notes">Ghi chú</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Ghi chú cho phiếu chuyển kho (không bắt buộc)"
                                rows="3"
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

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Sản phẩm chuyển kho</CardTitle>
                                <CardDescription>
                                    Chọn các sản phẩm và số lượng cần chuyển
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
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p
                            v-if="form.errors.items"
                            class="mb-4 text-sm text-destructive"
                        >
                            {{ form.errors.items }}
                        </p>

                        <div
                            v-if="!form.from_location_id"
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            Vui lòng chọn vị trí nguồn trước
                        </div>

                        <div
                            v-else-if="isLoadingVariants"
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            Đang tải danh sách sản phẩm...
                        </div>

                        <div
                            v-else-if="
                                availableVariants.length === 0 &&
                                !isLoadingVariants
                            "
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            Không có sản phẩm tồn kho tại vị trí này
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="flex items-start gap-3 rounded-lg border p-3"
                            >
                                <div class="min-w-0 flex-1 space-y-2">
                                    <Label>Sản phẩm</Label>
                                    <Select v-model="item.variant_id">
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder="Chọn sản phẩm"
                                            >
                                                <span
                                                    v-if="item.variant_id"
                                                    class="block truncate"
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
                                                class="py-2"
                                            >
                                                <div
                                                    class="flex flex-col gap-1 pr-6"
                                                >
                                                    <span
                                                        class="text-sm font-medium"
                                                    >
                                                        {{ v.sku }} -
                                                        {{ v.name }}
                                                    </span>
                                                    <div
                                                        class="flex flex-wrap items-center gap-2"
                                                    >
                                                        <span
                                                            class="text-xs font-medium text-emerald-600 dark:text-emerald-500"
                                                        >
                                                            Tồn kho:
                                                            {{
                                                                v.available_quantity
                                                            }}
                                                        </span>
                                                        <span
                                                            v-if="v.price"
                                                            class="border-l border-border pl-2 text-xs text-muted-foreground"
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
                                            getError(`items.${idx}.variant_id`)
                                        "
                                        class="text-sm text-destructive"
                                    >
                                        {{
                                            getError(`items.${idx}.variant_id`)
                                        }}
                                    </p>

                                    <!-- Panel below for selected variant -->
                                    <div
                                        v-if="item.variant_id"
                                        class="mt-2 flex items-start gap-3 rounded-md border bg-muted/50 p-3"
                                    >
                                        <div
                                            v-if="
                                                getVariant(item.variant_id)
                                                    ?.image_url
                                            "
                                            class="relative h-12 w-12 shrink-0 overflow-hidden rounded border bg-background"
                                        >
                                            <img
                                                :src="
                                                    getVariant(item.variant_id)
                                                        ?.image_url!
                                                "
                                                class="h-full w-full cursor-zoom-in object-cover transition-all hover:scale-105"
                                                @click="
                                                    openImagePreview(
                                                        getVariant(
                                                            item.variant_id,
                                                        )?.full_image_url ??
                                                            getVariant(
                                                                item.variant_id,
                                                            )?.image_url,
                                                    )
                                                "
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded border bg-background"
                                        >
                                            <ImageIcon
                                                class="h-5 w-5 text-muted-foreground"
                                            />
                                        </div>

                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-medium">
                                                {{
                                                    getVariant(item.variant_id)
                                                        ?.sku
                                                }}
                                                -
                                                {{
                                                    getVariant(item.variant_id)
                                                        ?.name
                                                }}
                                            </span>
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <span
                                                    class="text-xs font-medium text-emerald-600 dark:text-emerald-500"
                                                >
                                                    Tồn kho:
                                                    {{
                                                        getVariant(
                                                            item.variant_id,
                                                        )?.available_quantity
                                                    }}
                                                </span>
                                                <span
                                                    v-if="
                                                        getVariant(
                                                            item.variant_id,
                                                        )?.price
                                                    "
                                                    class="border-l border-border pl-2 text-xs text-muted-foreground"
                                                >
                                                    {{
                                                        formatPrice(
                                                            getVariant(
                                                                item.variant_id,
                                                            )?.price!,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-32 space-y-2">
                                    <Label>Số lượng</Label>
                                    <Input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="1"
                                        :max="
                                            getVariant(item.variant_id)
                                                ?.available_quantity ?? 999999
                                        "
                                    />
                                    <p
                                        v-if="getError(`items.${idx}.quantity`)"
                                        class="text-sm text-destructive"
                                    >
                                        {{ getError(`items.${idx}.quantity`) }}
                                    </p>
                                </div>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="mt-7 shrink-0 text-destructive hover:text-destructive"
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
                                class="py-6 text-center text-sm text-muted-foreground"
                            >
                                Nhấn "Thêm sản phẩm" để bắt đầu
                            </div>
                        </div>
                    </CardContent>
                </Card>

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
                    >
                        Tạo phiếu chuyển kho
                    </Button>
                </div>
            </form>
        </div>

        <ImagePreviewDialog
            v-model:open="previewImageOpen"
            :src="previewImageSrc"
        />
    </AppLayout>
</template>
