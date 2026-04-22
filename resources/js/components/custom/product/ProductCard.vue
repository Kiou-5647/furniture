<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff, ShoppingCart } from '@lucide/vue';
import { Heart } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import StarRating from '@/components/custom/StarRating.vue';
import { Button } from '@/components/ui/button';
import { useCartStore } from '@/stores/cart';
import type { ProductCard, ProductCardVariant } from '@/types/public/product';
import { toast } from 'vue-sonner';

const { addToCart, state } = useCartStore();

const props = defineProps<{
    productCard: ProductCard;
}>();

const hasCards = computed(() => props.productCard.swatches.length > 0);
const activeCard = computed(() => props.productCard);

const selectedVariantId = ref<string | null>(null);

function previewSwatch(swatch: ProductCardVariant) {
    selectedVariantId.value = swatch.id;
}

const initializeSelection = () => {
    if (!activeCard.value) return;

    const urlParams = new URLSearchParams(window.location.search);
    const colorFilter = urlParams.get('mau-sac');

    if (colorFilter) {
        const matchingSwatch = activeCard.value.swatches.find(
            (s) => s.option_values?.['mau-sac'] === colorFilter,
        );
        if (matchingSwatch) {
            selectedVariantId.value = matchingSwatch.id;
        }
    } else {
        selectedVariantId.value = activeCard.value.swatches[0]?.id ?? null;
    }
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

function formatPrice(value: number): string {
    return value?.toLocaleString('vi-VN') ?? '0';
}

function productUrl(): string {
    if (!currentSwatch.value) return '#';
    return `/san-pham/${currentSwatch.value.sku}/${currentSwatch.value.slug}`;
}

async function handleAddToCart() {
    if (!currentSwatch.value) return;

    const result = await addToCart({
        purchasable_id: currentSwatch.value.id,
        purchasable_type: 'App\\Models\\Product\\ProductVariant',
        quantity: 1,
    });

    if (result.success) {
        toast.success('Đã thêm sản phẩm vào giỏ hàng');
    } else {
        toast.error('Có lỗi xảy ra, vui lòng thử lại');
    }
}

</script>

<template>
    <div class="product-item overflow-hidden rounded-lg border">
        <!-- Product Image -->
        <div class="relative aspect-square overflow-hidden" @mouseenter="isHovered = true"
            @mouseleave="isHovered = false">
            <div v-if="productCard.product.is_new_arrival"
                class="absolute top-4 left-4 z-10 rounded-full bg-orange-400 px-2 py-0.5 text-sm font-bold uppercase tracking-wider text-white shadow-sm">
                Mới ra mắt
            </div>
            <Link :href="productUrl()" class="block h-full w-full">
                <img v-if="displayImage" :src="displayImage" :alt="productCard.product.name"
                    class="h-full w-full object-cover transition-all duration-300 hover:scale-105" loading="lazy" />
                <div v-else class="flex h-full w-full items-center justify-center">
                    <ImageOff class="h-12 w-12" />
                </div>
            </Link>

            <!-- Wishlist -->
            <Button
                class="absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full transition-colors bg-white text-black hover:bg-muted">
                <span class="sr-only">Yêu thích</span>
                <Heart class="h-5 w-5" />
            </Button>
        </div>

        <!-- Product Details -->
        <div class="space-y-2 p-3">
            <!-- Product Name -->
            <Link :href="productUrl()" class="block">
                <h3 class="transition-color line-clamp-2 text-sm font-medium">
                    {{ displayName }}
                </h3>
            </Link>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
                <span v-if="currentSwatch?.sale_price" class="text-base font-bold text-orange-500">
                    {{ formatPrice(Number(currentSwatch.sale_price)) }}đ
                </span>

                <span :class="currentSwatch?.sale_price
                    ? 'text-sm line-through'
                    : 'text-base font-bold'
                    ">
                    {{ formatPrice(Number(currentSwatch?.price)) }}đ
                </span>
            </div>

            <!-- Sell Count -->
            <div v-if="productCard.metrics.sales_count > 0" class="flex items-center gap-1 text-xs text-zinc-500">
                <span>
                    Đã bán {{ productCard.metrics.sales_count.toLocaleString('vi-VN') }}
                </span>
            </div>

            <!-- Rating -->
            <div v-if="productCard.metrics.average_rating" class="py-1">
                <StarRating :rating="productCard.metrics.average_rating" :count="productCard.metrics.reviews_count ?? 0"
                    show-count size="h-5 w-5" />
            </div>

            <!-- Color Swatches -->
            <!-- FIX 3: Use .swatches instead of .swatch_options -->
            <div v-if="
                hasCards &&
                activeCard?.swatches &&
                activeCard.swatches.length > 0
            " class="flex flex-wrap gap-1.5">
                <button v-for="swatch in activeCard.swatches" :key="swatch.id" type="button"
                    :title="swatch.label! || swatch.name!"
                    class="h-7 w-7 overflow-hidden rounded-md border-2 transition-all" :class="selectedVariantId === swatch.id
                        ? 'border-zinc-900 ring-1 ring-zinc-900/20 dark:border-zinc-100 dark:ring-zinc-100/20'
                        : 'border-transparent hover:border-zinc-300 dark:hover:border-zinc-700'
                        " @mouseenter="previewSwatch(swatch)">
                    <img v-if="swatch.swatch_image_url" :src="swatch.swatch_image_url" :alt="swatch.label!"
                        class="h-full w-full object-cover" />
                    <span v-else class="block h-full w-full bg-zinc-200" />
                </button>
            </div>

            <!-- Add to Cart -->
            <Button @click="handleAddToCart" :disabled="state.isLoading" variant="outline"
                class="w-full rounded-md border-gray-400 py-2 text-sm font-medium transition-colors">
                <span v-if="state.isLoading">Đang thêm...</span>
                <span v-else class="flex items-center justify-center gap-2">
                    Thêm vào giỏ hàng
                    <ShoppingCart class="h-4 w-4" />
                </span>
            </Button>
        </div>
    </div>
</template>
