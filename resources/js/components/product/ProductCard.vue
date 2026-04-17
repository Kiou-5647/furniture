<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff } from '@lucide/vue';
import { Heart } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import type { Product, SwatchOption } from '@/types/product';
import StarRating from '../custom/StarRating.vue';

const props = defineProps<{
    product: Product;
    cardIndex?: number;
}>();

const cards = computed(() => props.product.grouped_variants ?? []);
const hasCards = computed(() => cards.value.length > 0);
const cardIdx = computed(() => props.cardIndex ?? 0);

const activeCard = computed(() =>
    hasCards.value ? (cards.value[cardIdx.value] ?? null) : null,
);

const selectedVariantId = ref<string | null>(null);

// Rename from selectSwatch to previewSwatch
function previewSwatch(swatch: SwatchOption) {
    selectedVariantId.value = swatch.variant_id;
}

const initializeSelection = () => {
    if (!activeCard.value) {
        if (!activeCard.value) {
            selectedVariantId.value = props.product.variants?.[0]?.id ?? null;
            return;
        }
    }

    const urlParams = new URLSearchParams(window.location.search);
    const colorFilter = urlParams.get('mau-sac');

    if (colorFilter) {
        const matchingSwatch = activeCard.value.swatch_options.find(
            (s) => s.value === colorFilter,
        );
        if (matchingSwatch) {
            selectedVariantId.value = matchingSwatch.variant_id;
        }
    } else {
        selectedVariantId.value =
            activeCard.value.swatch_options[0]?.variant_id ?? null;
    }
};

onMounted(() => {
    initializeSelection();
});

const currentSwatch = computed<SwatchOption | null>(() => {
    if (activeCard.value) {
        return (
            activeCard.value.swatch_options.find(
                (s) => s.variant_id === selectedVariantId.value,
            ) ??
            activeCard.value.swatch_options[0] ??
            null
        );
    }

    const firstVariant = props.product.variants?.[0];
    if (firstVariant) {
        return {
            value: 'default',
            label: firstVariant.name || 'Default',
            variant_id: firstVariant.id,
            price: firstVariant.price,
            sale_price: firstVariant.sale_price,
            in_stock: firstVariant.in_stock ?? false,
            primary_image_url: firstVariant.primary_image_url,
            swatch_image_url: firstVariant.swatch_image_url,
            sku: firstVariant.sku,
            slug: firstVariant.slug ?? '',
        } as SwatchOption;
    }

    return null;
});
const displayName = computed(() => {
    const variant = currentSwatch.value;

    return `${props.product.name} ${variant?.name ?? variant?.name ?? ''}`;
});
const displayImage = computed(() => currentSwatch.value?.primary_image_url);

function formatPrice(value: number): string {
    return value?.toLocaleString('vi-VN') ?? '0';
}

function productUrl(): string {
    if (!currentSwatch.value) return '#';

    return `/san-pham/${currentSwatch.value.sku}/${currentSwatch.value.slug}`;
}
console.info(currentSwatch)
</script>

<template>
    <div class="product-item overflow-hidden rounded-lg border bg-white">
        <!-- Product Image -->
        <div class="relative aspect-square overflow-hidden bg-zinc-100">
            <Link :href="productUrl()" class="block h-full w-full">
                <img
                    v-if="displayImage"
                    :src="displayImage"
                    :alt="product.name"
                    class="h-full w-full object-cover transition-transform hover:scale-105"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center text-zinc-300"
                >
                    <ImageOff class="h-12 w-12" />
                </div>
            </Link>

            <!-- Wishlist -->
            <button
                class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 transition-colors hover:bg-white"
            >
                <span class="sr-only">Yêu thích</span>
                <Heart class="h-5 w-5" />
            </button>
        </div>

        <!-- Product Details -->
        <div class="space-y-2 p-3">
            <!-- Product Name -->
            <Link :href="productUrl()" class="block">
                <h3
                    class="line-clamp-2 text-sm font-medium text-zinc-900 transition-colors hover:text-zinc-600"
                >
                    {{ displayName }}
                </h3>
            </Link>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
                <!-- Show sale price in orange if it exists -->
                <span v-if="currentSwatch?.sale_price" class="text-base font-bold text-orange-500">
                    {{ formatPrice(Number(currentSwatch.sale_price)) }}đ
                </span>

                <!-- Show base price. If sale_price exists, strike it through and make it gray -->
                <span :class="currentSwatch?.sale_price ? 'text-sm text-zinc-400 line-through' : 'text-base font-bold text-zinc-900'">
                    {{ formatPrice(Number(currentSwatch?.price)) }}đ
                </span>
            </div>

            <!-- Rating -->
            <div v-if="product.average_rating" class="py-1">
                <StarRating
                    :rating="product.average_rating"
                    :count="product.review_count ?? 0"
                    show-count
                    size="h-5 w-5"
                />
            </div>

            <!-- Color Swatches (only show if swatch options exist) -->
            <div
                v-if="
                    hasCards &&
                    activeCard?.swatch_options &&
                    activeCard.swatch_options.length > 0
                "
                class="flex flex-wrap gap-1.5"
            >
                <button
                    v-for="swatch in activeCard.swatch_options"
                    :key="swatch.value"
                    type="button"
                    :title="swatch.label"
                    class="h-7 w-7 overflow-hidden rounded-md border-2 transition-all"
                    :class="
                        selectedVariantId === swatch.variant_id
                            ? 'border-zinc-900 ring-1 ring-zinc-900/20'
                            : 'border-transparent hover:border-zinc-300'
                    "
                    @mouseenter="previewSwatch(swatch)"
                >
                    <img
                        v-if="swatch.swatch_image_url"
                        :src="swatch.swatch_image_url"
                        :alt="swatch.label"
                        class="h-full w-full object-cover"
                    />
                    <span v-else class="block h-full w-full bg-zinc-200" />
                </button>
            </div>

            <!-- Add to Cart -->
            <button
                class="w-full rounded-md border border-zinc-200 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50"
            >
                Thêm vào giỏ hàng
            </button>
        </div>
    </div>
</template>
