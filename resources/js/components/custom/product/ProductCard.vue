<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff, ShoppingCart } from '@lucide/vue';
import { computed, ref, onMounted } from 'vue';
import StarRating from '@/components/custom/StarRating.vue';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { formatPrice } from '@/lib/utils';
import type { ProductCard, ProductCardVariant } from '@/types/public/product';
import VariantSelectorDialog from './VariantSelectorDialog.vue';

const props = defineProps<{
    productCard: ProductCard;
}>();

const isSelectorOpen = ref(false);
const hasCards = computed(() => props.productCard.swatches.length > 0);
const activeCard = computed(() => props.productCard);

const selectedVariantId = ref<string | null>(null);

function previewSwatch(swatch: ProductCardVariant) {
    selectedVariantId.value = swatch.id;
}

const initializeSelection = () => {
    if (!activeCard.value) return;

    selectedVariantId.value =
        activeCard.value.default_variant_id ??
        activeCard.value.swatches[0]?.id ??
        null;
};

onMounted(() => {
    initializeSelection();
});

const currentSwatch = computed<ProductCardVariant | null>(() => {
    if (!activeCard.value) return null;

    return (
        activeCard.value.swatches.find(
            (s) => s.id === selectedVariantId.value,
        ) ??
        activeCard.value.swatches[0] ??
        null
    );
});

const displayName = computed(() => {
    const variant = currentSwatch.value;
    // FIX 2: Use productCard.product.name instead of product.name
    return `${props.productCard.product.name} ${variant?.name ?? ''}`;
});

const isHovered = ref(false);

const displayImage = computed(() => {
    const swatch = currentSwatch.value;
    if (!swatch) return null;

    if (isHovered.value && swatch.hover_image_url) {
        return swatch.hover_image_url;
    }

    return swatch.primary_image_url;
});

function productUrl(): string {
    if (!currentSwatch.value) return '#';
    return `/san-pham/${currentSwatch.value.sku}/${currentSwatch.value.slug}`;
}

function openSelector() {
    isSelectorOpen.value = true;
}
</script>

<template>
    <div class="product-item min-w-[300px] overflow-hidden">
        <!-- Product Image -->
        <div
            class="relative aspect-square overflow-hidden"
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
        >
            <div
                v-if="productCard.product.is_new_arrival"
                class="absolute top-4 left-4 z-10 rounded-full bg-orange-400 px-2 py-0.5 text-sm font-bold tracking-wider text-white uppercase shadow-sm"
            >
                Mới ra mắt
            </div>
            <Link :href="productUrl()" class="block h-full w-full">
                <img
                    v-if="displayImage"
                    :src="displayImage"
                    :alt="productCard.product.name"
                    class="h-full w-full object-cover transition-all duration-300 hover:scale-105"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center"
                >
                    <ImageOff class="h-12 w-12" />
                </div>
            </Link>
        </div>

        <!-- Product Details -->
        <div class="space-y-2 p-3">
            <!-- Product Name -->
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Link :href="productUrl()" class="block">
                            <h3
                                class="transition-color truncate text-sm font-medium"
                            >
                                {{ displayName }}
                            </h3>
                        </Link>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p class="text-xs">{{ displayName }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
                <span
                    v-if="
                        currentSwatch &&
                        Number(currentSwatch.sale_price) <
                            Number(currentSwatch.price)
                    "
                    class="text-base font-bold text-orange-500"
                >
                    {{ formatPrice(Number(currentSwatch.sale_price)) }}
                </span>

                <span
                    :class="
                        currentSwatch &&
                        Number(currentSwatch.sale_price) <
                            Number(currentSwatch.price)
                            ? 'text-sm line-through'
                            : 'text-base font-bold'
                    "
                >
                    {{ formatPrice(Number(currentSwatch?.price)) }}
                </span>
            </div>

            <!-- Rating -->
            <div
                v-if="
                    productCard.metrics.average_rating ||
                    productCard.metrics.sales_count > 0
                "
                class="flex items-center justify-between py-1"
            >
                <!-- Left Side: Rating -->
                <div v-if="productCard.metrics.average_rating">
                    <StarRating
                        :rating="productCard.metrics.average_rating"
                        :count="productCard.metrics.reviews_count ?? 0"
                        show-count
                        show-rating
                        size="h-5 w-5 text-xs"
                    />
                </div>
                <div v-else class="w-0"></div>
                <!-- Spacer to keep sales count on the right if no rating exists -->

                <!-- Right Side: Sell Count -->
                <div
                    v-if="productCard.metrics.sales_count > 0"
                    class="text-xs text-zinc-500"
                >
                    Đã bán
                    {{
                        productCard.metrics.sales_count.toLocaleString('vi-VN')
                    }}
                </div>
            </div>

            <div
                v-if="
                    hasCards &&
                    activeCard?.swatches &&
                    activeCard.swatches.length > 0
                "
                class="flex flex-wrap gap-1.5"
            >
                <button
                    v-for="swatch in activeCard.swatches.slice(0, 8)"
                    :key="swatch.id"
                    type="button"
                    :title="swatch.label! || swatch.name!"
                    class="h-7 w-7 overflow-hidden rounded-md border-2 transition-all"
                    :class="
                        selectedVariantId === swatch.id
                            ? 'border-zinc-900 ring-1 ring-zinc-900/20 dark:border-zinc-100 dark:ring-zinc-100/20'
                            : 'border-transparent hover:border-zinc-300 dark:hover:border-zinc-700'
                    "
                    @mouseenter="previewSwatch(swatch)"
                >
                    <img
                        v-if="swatch.swatch_image_url"
                        :src="swatch.swatch_image_url"
                        :alt="swatch.label!"
                        class="h-full w-full object-cover"
                    />
                    <span v-else class="block h-full w-full bg-zinc-200" />
                </button>
                <button
                    v-if="activeCard.swatches.length > 8"
                    @click="openSelector"
                    class="text-xs"
                >
                    +{{ activeCard.swatches.length - 8 }}
                </button>
            </div>

            <!-- Add to Cart -->
            <Button
                @click="openSelector"
                variant="outline"
                class="w-full rounded-md border-gray-400 py-2 text-sm font-medium transition-colors"
            >
                <span class="flex items-center justify-center gap-2">
                    Thêm vào giỏ hàng
                    <ShoppingCart class="h-4 w-4" />
                </span>
            </Button>
        </div>
    </div>
    <VariantSelectorDialog
        v-model:open="isSelectorOpen"
        :product-card="productCard"
    />
</template>
