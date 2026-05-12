<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Trash2,
    PackagePlus,
    Image as ImageIcon,
    ImagePlus,
} from '@lucide/vue';
import { ref, watch, computed } from 'vue';
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
import { CheckUserPermission } from '@/lib';
import { formatPrice, slugify } from '@/lib';
import type { Bundle } from '@/types';
import { index } from '@/routes/employee/bundles';

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

const canUpdate = computed(() => CheckUserPermission('Sửa gói sản phẩm') && props.bundle != null);

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
        if (!props.bundle && newName) {
            form.value.slug = slugify(newName);
        }
    },
);
</script>

<template>

    <Head :title="bundle ? 'Chỉnh sửa gói sản phẩm' : 'Thêm gói sản phẩm'" />
    <AppLayout>
        <div class="flex flex-col overflow-hidden" style="height: calc(100vh - 80px)">
            <!-- HEADER -->
            <div class="flex shrink-0 items-center justify-start gap-6 border-b bg-background px-6 py-4">
                <Button variant="outline" class="h-9 w-9" @click="requestClose">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ bundle ? 'Chỉnh sửa gói sản phẩm' : 'Thêm gói sản phẩm mới' }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ bundle ?
                            'Cập nhật thông tin và các sản phẩm trong gói.'
                            : 'Tạo gói combo mới để thu hút khách hàng.' }}
                    </p>
                </div>
            </div>

            <!-- MAIN BODY -->
            <div class="flex flex-1 overflow-hidden">
                <!-- LEFT COLUMN: Configuration (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-6 space-y-10 custom-scrollbar">
                    <div class="max-w-4xl mx-auto space-y-12">
                        <!-- 1. Basic Information -->
                        <section class="space-y-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-6 w-1 rounded-full bg-primary" />
                                <h2 class="text-lg font-bold tracking-tight">Thông tin cơ bản</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 rounded-2xl border bg-card shadow-sm">
                                <!-- 2x2 Grid -->
                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
                                    <div class="space-y-2 lg:col-span-4">
                                        <Label class="text-xs font-medium text-muted-foreground">Tên gói sản
                                            phẩm</Label>
                                        <Input v-model="form.name" :disabled="!canUpdate"
                                            placeholder="Ví dụ: Combo Phòng Ngủ" />
                                    </div>
                                    <div class="space-y-2 lg:col-span-2">
                                        <Label class="text-xs font-medium text-muted-foreground">Loại giảm giá</Label>
                                        <Select v-model="form.discount_type" :disabled="!canUpdate">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Chọn loại..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="percentage">Phần trăm (%)</SelectItem>
                                                <SelectItem value="fixed_amount">Số tiền cố định (đ)</SelectItem>
                                                <SelectItem value="fixed_price">Giá cố định (đ)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="space-y-2 lg:col-span-4">
                                        <Label class="text-xs font-medium text-muted-foreground">Slug</Label>
                                        <Input v-model="form.slug" :disabled="!canUpdate" placeholder="vi-du-combo" />
                                    </div>
                                    <div class="space-y-2 lg:col-span-2">
                                        <Label class="text-xs font-medium text-muted-foreground">
                                            Giá trị giảm giá
                                        </Label>
                                        <div class="relative">
                                            <Input type="number" v-model.number="form.discount_value"
                                                :disabled="!canUpdate" class="pr-10" />
                                            <div
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-muted-foreground">
                                                {{ form.discount_type === 'percentage' ? '%' : '₫' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description (shorter) -->
                                <div class="space-y-2 pt-2 border-t border-border">
                                    <Label class="text-xs font-medium text-muted-foreground">Mô tả ngắn</Label>
                                    <Textarea v-model="form.description" :disabled="!canUpdate"
                                        placeholder="Mô tả ngắn gọn về gói sản phẩm..."
                                        class="min-h-[80px]" />
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 2: MEDIA (2 Columns) -->
                        <section class="space-y-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-6 w-1 rounded-full bg-primary" />
                                <h2 class="text-lg font-bold tracking-tight">Hình ảnh minh họa</h2>
                            </div>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium">Ảnh chính</Label>
                                    <div :class="[
                                        'group relative aspect-square overflow-hidden rounded-xl border-2 border-dashed bg-muted/30 transition-all',
                                        canUpdate ? 'cursor-pointer hover:border-primary' : 'cursor-not-allowed'
                                    ]" @click="canUpdate && triggerUpload('primary-upload')">
                                        <img v-if="getImageSrc(form.primary_image_file, form.primary_image_url)"
                                            :src="getImageSrc(form.primary_image_file, form.primary_image_url)!"
                                            class="h-full w-full object-cover" />
                                        <div v-else
                                            class="flex h-full w-full items-center justify-center text-muted-foreground">
                                            <ImageIcon class="h-6 w-6 opacity-40" />
                                        </div>
                                        <div
                                            class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <ImagePlus class="h-5 w-5 text-white" />
                                        </div>
                                    </div>
                                    <input id="primary-upload" type="file" class="hidden"
                                        @change="setPrimaryImage(($event.target as HTMLInputElement).files?.[0] || null)" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs font-medium">Ảnh hover</Label>
                                    <div :class="[
                                        'group relative aspect-square overflow-hidden rounded-xl border-2 border-dashed bg-muted/30 transition-all',
                                        canUpdate ? 'cursor-pointer hover:border-primary' : 'cursor-not-allowed'
                                    ]" @click="canUpdate && triggerUpload('hover-upload')">
                                        <img v-if="getImageSrc(form.hover_image_file, form.hover_image_url)"
                                            :src="getImageSrc(form.hover_image_file, form.hover_image_url)!"
                                            class="h-full w-full object-cover" />
                                        <div v-else
                                            class="flex h-full w-full items-center justify-center text-muted-foreground">
                                            <ImageIcon class="h-6 w-6 opacity-40" />
                                        </div>
                                        <div
                                            class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <ImagePlus class="h-5 w-5 text-white" />
                                        </div>
                                    </div>
                                    <input id="hover-upload" type="file" class="hidden"
                                        @change="setHoverImage(($event.target as HTMLInputElement).files?.[0] || null)" />
                                </div>
                            </div>
                        </section>

                        <!-- 2. Bundle Builder -->
                        <section class="space-y-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-1.5 rounded-full bg-primary" />
                                    <h2 class="text-xl font-bold tracking-tight">Sản phẩm trong gói</h2>
                                </div>
                                <Button @click="showSearchModal = true" variant="outline" size="sm"
                                    :disabled="!canUpdate"
                                    class="rounded-full px-4 h-9 border-primary/20 hover:bg-primary/5">
                                    <PackagePlus class="mr-2 h-4 w-4" /> Thêm sản phẩm
                                </Button>
                            </div>

                            <!-- Empty State -->
                            <div v-if="form.contents.length === 0"
                                class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed p-16 text-muted-foreground bg-muted/10">
                                <div class="p-4 rounded-full bg-muted mb-4">
                                    <PackagePlus class="h-10 w-10 opacity-40" />
                                </div>
                                <p class="text-base font-medium">Chưa có sản phẩm nào trong gói này</p>
                                <p class="text-sm opacity-60 mb-6">Hãy bắt đầu thêm các sản phẩm để tạo nên combo tuyệt
                                    vời.</p>
                                <Button variant="outline" size="sm" class="rounded-full"
                                    @click="showSearchModal = true">
                                    Tìm sản phẩm ngay
                                </Button>
                            </div>

                            <!-- Product Grid -->
                            <div v-else class="grid grid-cols-1 gap-4">
                                <div v-for="(item, index) in form.contents" :key="item.id || index"
                                    class="group relative flex items-center gap-6 rounded-2xl border bg-card p-4 transition-all hover:shadow-md hover:border-primary/30">
                                    <!-- Product Image with Variant Preview -->
                                    <div
                                        class="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-muted border border-border">
                                        <img :src="getActiveVariant(item)?.primary_image"
                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex flex-1 flex-col justify-center">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <p class="text-sm font-bold leading-none">
                                                        {{ item.product_card.product.name }}
                                                    </p>
                                                    <span
                                                        class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-primary/10 text-primary uppercase tracking-wider">
                                                        {{ getActiveVariant(item)?.name }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-muted-foreground flex items-center gap-2">
                                                    <span class="font-mono opacity-80">{{ getActiveVariant(item)?.sku
                                                    }}</span>
                                                    <span class="w-1 h-1 rounded-full bg-muted-foreground/30" />
                                                    <span class="font-semibold text-foreground">
                                                        {{ formatPrice(Number(getActiveVariant(item)?.sale_price ||
                                                            getActiveVariant(item)?.price || 0)) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <Button variant="ghost" size="icon"
                                                class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors"
                                                :disabled="!canUpdate" @click="removeCard(index)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>

                                        <!-- Bottom Controls: Variants & Quantity -->
                                        <div class="mt-3 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-[10px] font-medium text-muted-foreground uppercase tracking-tight mr-1">Màu
                                                    sắc:</span>
                                                <div class="flex gap-1.5">
                                                    <button v-for="variant in item.product_card.variants"
                                                        :key="variant.id"
                                                        @mouseenter="previewVariantMap[item.id] = variant.id"
                                                        :disabled="!canUpdate"
                                                        class="h-5 w-5 overflow-hidden rounded-full border transition-all disabled:opacity-40"
                                                        :class="previewVariantMap[item.id] === variant.id
                                                            ? 'border-primary ring-2 ring-primary/20 scale-110'
                                                            : 'border-transparent hover:border-zinc-400'">
                                                        <img :src="variant.swatch_image!"
                                                            class="h-full w-full object-cover" />
                                                    </button>
                                                </div>
                                            </div>

                                            <div
                                                class="flex items-center gap-3 bg-muted/40 pl-3 pr-1 py-1 rounded-lg border border-border/50">
                                                <span
                                                    class="text-[10px] font-medium text-muted-foreground uppercase">SL:</span>
                                                <Input type="number" v-model.number="item.quantity"
                                                    :disabled="!canUpdate"
                                                    class="h-7 w-12 px-1 text-center bg-background text-xs font-bold" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Summary & Media (Sticky) -->
                <div class="w-96 shrink-0 border-l bg-muted/20 p-6 overflow-y-auto custom-scrollbar">
                    <div class="sticky top-0 space-y-8">
                        <!-- Pricing Summary -->
                        <section class="space-y-4">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="h-6 w-1 rounded-full bg-primary" />
                                <h2 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Chi tiết
                                    giá</h2>
                            </div>

                            <div class="rounded-3xl bg-background border border-border shadow-sm overflow-hidden">
                                <div class="p-5 space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Tổng giá trị gốc</span>
                                        <span class="font-mono font-medium">
                                            {{ formatPrice(bundlePricing.individualTotal) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Tiết kiệm</span>
                                        <span class="font-mono font-bold text-green-600">-
                                            {{ formatPrice(bundlePricing.savings) }}
                                        </span>
                                    </div>
                                    <div class="pt-3 mt-3 border-t border-border flex justify-between items-end">
                                        <span class="text-xs font-bold uppercase text-muted-foreground">
                                            Giá cuối cùng
                                        </span>
                                        <span class="text-2xl font-black text-primary tracking-tighter">
                                            {{ formatPrice(bundlePricing.finalPrice) }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Footer of summary card -->
                                <div class="bg-primary/5 px-5 py-3 border-t border-primary/10">
                                    <p class="text-[10px] text-center text-muted-foreground italic">
                                        Giá được tính dựa trên các sản phẩm đã chọn trong gói.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 3. Global Actions -->
                        <section class="pt-4 space-y-3">
                            <Button size="lg"
                                class="w-full h-12 rounded-xl font-bold text-base shadow-lg transition-all active:scale-95"
                                :disabled="!isValid || !canUpdate" @click="submit">
                                {{ bundle ? 'Cập nhật gói sản phẩm' : 'Tạo gói sản phẩm' }}
                            </Button>
                            <Button variant="ghost" size="sm"
                                class="w-full rounded-xl text-muted-foreground hover:text-foreground"
                                @click="requestClose">
                                Hủy bỏ
                            </Button>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Modal -->
        <ProductCardSearchModal v-if="showSearchModal" :open="showSearchModal" @selected="handleCardSelected"
            @close="showSearchModal = false" />
    </AppLayout>
</template>
