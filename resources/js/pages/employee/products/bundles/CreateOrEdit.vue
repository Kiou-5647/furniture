<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Trash2,
    PackagePlus,
    Image as ImageIcon,
    ImagePlus,
    Plus,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
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
import { createLazyComponent } from '@/composables/createLazyComponent';
import { useBundleForm } from '@/composables/useBundleForm';
import AppLayout from '@/layouts/AppLayout.vue';
import { slugify } from '@/lib/utils';
import { index } from '@/routes/employee/bundles';
import type { Bundle } from '@/types/bundle';

const ProductCardSearchModal = createLazyComponent(
    () =>
        import('@/pages/employee/products/products/components/ProductCardSearchModal.vue'),
);
const createObjectURL = URL.createObjectURL.bind(URL);

const props = defineProps<{
    bundle: Bundle | null;
}>();

const {
    form,
    isValid,
    bundlePricing,
    addCard,
    removeCard,
    submit,
    setPrimaryImage,
    setHoverImage,
} = useBundleForm(props.bundle);

const showSearchModal = ref(false);
const previewVariantMap = ref<Record<string, string>>({});

function handleCardSelected(cardData: any) {
    addCard(cardData);
    showSearchModal.value = false;
}

function requestClose() {
    router.visit(index());
}

function getActiveVariant(item: any) {
    // Safely navigate to the variants array
    const variants = item?.product_card?.variants;

    if (!variants || !Array.isArray(variants) || variants.length === 0) {
        return null;
    }

    // Use the item's own unique ID (the UUID) to track the preview state
    const variantId = previewVariantMap.value[item.id];

    return variants.find((v) => v.id === variantId) || variants[0];
}

function getImageSrc(file: File | null, url: string | null) {
    return file ? createObjectURL(file) : url;
}

function triggerUpload(id: string) {
    (document.getElementById(id) as HTMLInputElement)?.click();
}

watch(
    () => form.value.name,
    (newName) => {
        if(!props.bundle && newName) {
            form.value.slug = slugify(newName);
        }
    },
);
</script>

<template>
    <Head :title="bundle ? 'Chỉnh sửa gói sản phẩm' : 'Thêm gói sản phẩm'" />
    <AppLayout>
        <div
            class="flex flex-col overflow-hidden"
            style="height: calc(100vh - 80px)"
        >
            <!-- HEADER (Same as before) -->
            <div
                class="flex shrink-0 items-center justify-start gap-6 border-b bg-background px-4 py-3"
            >
                <Button variant="outline" class="h-8 w-8" @click="requestClose">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">
                        {{
                            bundle
                                ? 'Chỉnh sửa gói sản phẩm'
                                : 'Thêm gói sản phẩm mới'
                        }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{
                            bundle
                                ? 'Cập nhật thông tin và các sản phẩm trong gói.'
                                : 'Tạo gói combo mới để thu hút khách hàng.'
                        }}
                    </p>
                </div>
            </div>

            <!-- BODY -->
            <div class="flex-1 space-y-8 overflow-y-auto p-6">
                <!-- 1. Basic Information (Same as before) -->
                <section class="space-y-4">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="h-6 w-1 rounded-full bg-primary" />
                        <h2 class="text-lg font-semibold">Thông tin cơ bản</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label>Tên gói sản phẩm</Label>
                            <Input
                                v-model="form.name"
                                placeholder="Ví dụ: Combo Phòng Ngủ Hiện Đại"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Slug</Label>
                            <Input
                                v-model="form.slug"
                                placeholder="vi-du-combo-phong-ngu"
                            />
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <Label>Mô tả</Label>
                            <Textarea
                                v-model="form.description"
                                placeholder="Mô tả chi tiết về gói sản phẩm..."
                            />
                        </div>
                        <div class="space-y-2">
                            <Label>Loại giảm giá</Label>
                            <Select v-model="form.discount_type">
                                <SelectTrigger
                                    ><SelectValue
                                        placeholder="Chọn loại giảm giá"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="percentage"
                                        >Phần trăm (%)</SelectItem
                                    >
                                    <SelectItem value="fixed_amount"
                                        >Số tiền cố định (đ)</SelectItem
                                    >
                                    <SelectItem value="fixed_price"
                                        >Giá cố định (đ)</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Giá trị giảm giá</Label>
                            <Input
                                type="number"
                                v-model.number="form.discount_value"
                            />
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-4 p-4 rounded-lg bg-primary/5 border border-primary/20">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Tổng giá trị gốc:</span>
                        <span class="text-sm font-mono">{{ bundlePricing.individualTotal.toLocaleString() }}₫</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Tiết kiệm:</span>
                        <span class="text-sm font-mono text-green-600">- {{ bundlePricing.savings.toLocaleString() }}₫</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-primary/10">
                        <span class="text-base font-bold">Giá gói sản phẩm:</span>
                        <span class="text-lg font-bold text-primary">{{ bundlePricing.finalPrice.toLocaleString() }}₫</span>
                    </div>
                </div>

                <!-- 2. UPGRADED Bundle Builder -->
                <section class="space-y-4">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-1 rounded-full bg-primary" />
                            <h2 class="text-lg font-semibold">
                                Sản phẩm trong gói
                            </h2>
                        </div>
                        <Button
                            @click="showSearchModal = true"
                            variant="outline"
                            size="sm"
                        >
                            <PackagePlus class="mr-2 h-4 w-4" /> Thêm sản phẩm
                        </Button>
                    </div>

                    <div
                        v-if="form.contents.length === 0"
                        class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-12 text-muted-foreground"
                    >
                        <PackagePlus class="mb-4 h-12 w-12 opacity-20" />
                        <p>Chưa có sản phẩm nào được thêm vào gói.</p>
                        <Button variant="link" @click="showSearchModal = true"
                            >Bắt đầu thêm sản phẩm</Button
                        >
                    </div>

                    <div v-else class="grid gap-4">
                        <div
                            v-for="(item, index) in form.contents"
                            :key="item.id || index"
                            class="flex gap-4 rounded-lg border bg-card p-4 shadow-sm transition-all hover:border-primary/30"
                        >
                            <!-- Image Preview -->
                            <div
                                class="relative h-24 w-24 shrink-0 overflow-hidden rounded-md bg-muted"
                            >
                                <img
                                    :src="getActiveVariant(item)?.primary_image"
                                    class="h-full w-full object-cover transition-all duration-200"
                                />
                            </div>

                            <div class="flex flex-1 flex-col justify-between">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-bold">
                                            {{ item.product_card.product.name }}
                                            <span
                                                class="font-normal text-muted-foreground"
                                            >
                                                -
                                                {{
                                                    getActiveVariant(item)?.name
                                                }}
                                            </span>
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            SKU:
                                            {{ getActiveVariant(item)?.sku }}
                                            | Giá:
                                            <span
                                                class="font-medium text-foreground"
                                            >
                                                {{
                                                    getActiveVariant(item)
                                                        ?.sale_price ??
                                                    getActiveVariant(item)
                                                        ?.price
                                                }}đ
                                            </span>
                                        </p>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive"
                                        @click="removeCard(index)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>

                                <div
                                    class="mt-4 flex items-center justify-between"
                                >
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="variant in item.product_card
                                                .variants"
                                            :key="variant.id"
                                            @mouseenter="
                                                previewVariantMap[item.id] =
                                                    variant.id
                                            "
                                            class="h-6 w-6 overflow-hidden rounded-full border-2 transition-all"
                                            :class="
                                                previewVariantMap[item.id] ===
                                                variant.id
                                                    ? 'border-primary ring-1 ring-primary/30'
                                                    : 'border-transparent hover:border-zinc-300'
                                            "
                                        >
                                            <img
                                                :src="variant.swatch_image!"
                                                class="h-full w-full object-cover"
                                            />
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >Số lượng:</span
                                        >
                                        <Input
                                            type="number"
                                            v-model.number="item.quantity"
                                            class="h-8 w-16 px-2"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Media Section (Same as before) -->
                <section class="space-y-4">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="h-6 w-1 rounded-full bg-primary" />
                        <h2 class="text-lg font-semibold">Hình ảnh</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <!-- Primary Image -->
                        <div class="space-y-2">
                            <Label>Ảnh chính</Label>
                            <div
                                @click="triggerUpload('primary-upload')"
                                class="group relative flex aspect-square cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed bg-muted/30 transition-colors hover:border-primary"
                            >
                                <img
                                    v-if="
                                        getImageSrc(
                                            form.primary_image_file,
                                            form.primary_image_url,
                                        )
                                    "
                                    :src="
                                        getImageSrc(
                                            form.primary_image_file,
                                            form.primary_image_url,
                                        )!
                                    "
                                    class="h-full w-full object-cover"
                                />
                                <ImageIcon
                                    v-else
                                    class="h-8 w-8 text-muted-foreground"
                                />

                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <ImagePlus class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <input
                                id="primary-upload"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="
                                    setPrimaryImage(
                                        ($event.target as HTMLInputElement)
                                            .files?.[0] || null,
                                    )
                                "
                            />
                        </div>

                        <!-- Hover Image -->
                        <div class="space-y-2">
                            <Label>Ảnh hover</Label>
                            <div
                                @click="triggerUpload('hover-upload')"
                                class="group relative flex aspect-square cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed bg-muted/30 transition-colors hover:border-primary"
                            >
                                <img
                                    v-if="
                                        getImageSrc(
                                            form.hover_image_file,
                                            form.hover_image_url,
                                        )
                                    "
                                    :src="
                                        getImageSrc(
                                            form.hover_image_file,
                                            form.hover_image_url,
                                        )!
                                    "
                                    class="h-full w-full object-cover"
                                />
                                <ImageIcon
                                    v-else
                                    class="h-8 w-8 text-muted-foreground"
                                />

                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <ImagePlus class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <input
                                id="hover-upload"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="
                                    setHoverImage(
                                        ($event.target as HTMLInputElement)
                                            .files?.[0] || null,
                                    )
                                "
                            />
                        </div>
                    </div>
                </section>
            </div>

            <!-- FOOTER (Same as before) -->
            <div
                class="flex shrink-0 justify-end gap-3 border-t bg-background p-4"
            >
                <Button variant="outline" size="sm" @click="requestClose">
                    Hủy
                </Button>
                <Button size="sm" :disabled="!isValid" @click="submit">
                    {{ bundle ? 'Cập nhật gói sản phẩm' : 'Tạo gói sản phẩm' }}
                </Button>
            </div>
        </div>

        <!-- Search Modal -->
        <ProductCardSearchModal
            v-if="showSearchModal"
            :open="showSearchModal"
            @selected="handleCardSelected"
            @close="showSearchModal = false"
        />
    </AppLayout>
</template>
