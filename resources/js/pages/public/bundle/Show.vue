<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ShoppingCart, ChevronLeft, ExternalLink } from '@lucide/vue';
import { ref, computed, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import type { UnwrapRefCarouselApi as CarouselApi } from '@/components/ui/carousel/interface';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatPrice } from '@/lib/utils';
import { show as productShow } from '@/routes/products';
import { useCartStore } from '@/stores/cart';
import type { Bundle } from '@/types/public/bundle';

const props = defineProps<{
    bundle: Bundle;
}>();

const { addToCart, state } = useCartStore();
const selections = ref<Record<string, string>>({});

// --- CAROUSEL STATE ---
const selectedImageIndex = ref(0);
const carouselApi = ref<CarouselApi | null>(null);

onMounted(() => {
    props.bundle.items.forEach((item) => {
        if (item.variants.length > 0) {
            selections.value[item.id] = item.variants[0].id;
        }
    });
});

const getSelectedVariant = (itemId: string) => {
    const variantId = selections.value[itemId];
    const item = props.bundle.items.find((i) => i.id === itemId);
    return item?.variants.find((v) => v.id === variantId);
};

// --- IMAGE LIST COMPUTED ---
const allImages = computed(() => {
    const list: { full: string; thumb: string; label: string }[] = [];

    // 1. Bundle Level Images
    if (props.bundle.images.primary) {
        list.push({
            full: props.bundle.images.primary,
            thumb: props.bundle.images.primary,
            label: 'Bundle Primary',
        });
    }
    if (props.bundle.images.hover) {
        list.push({
            full: props.bundle.images.hover,
            thumb: props.bundle.images.hover,
            label: 'Bundle Hover',
        });
    }

    // 2. Selected Variant Images
    props.bundle.items.forEach((item) => {
        const v = getSelectedVariant(item.id);
        if (!v) return;

        const images = v.images;
        if (images.primary?.full) {
            list.push({
                full: images.primary.full,
                thumb: images.primary.thumb,
                label: `${item.product_name} - Primary`,
            });
        }
        if (images.hover?.full) {
            list.push({
                full: images.hover.full,
                thumb: images.hover.thumb,
                label: `${item.product_name} - Hover`,
            });
        }
        if (images.gallery?.full) {
            list.push({
                full: images.gallery.full,
                thumb: images.gallery.thumb,
                label: `${item.product_name} - Detail`,
            });
        }
        if (images.dimension?.full) {
            list.push({
                full: images.dimension.full,
                thumb: images.dimension.thumb,
                label: `${item.product_name} - Dimensions`,
            });
        }
    });

    return list;
});

// --- CAROUSEL METHODS ---
function onInitCarousel(api: CarouselApi) {
    carouselApi.value = api;
    api?.on('select', () => {
        selectedImageIndex.value = api.selectedScrollSnap();
    });
}

function selectImage(index: number) {
    selectedImageIndex.value = index;
    carouselApi.value?.scrollTo(index);
}

// --- PRICING ---
const individualTotal = computed(() => {
    let total = 0;
    props.bundle.items.forEach((item) => {
        const selected = getSelectedVariant(item.id);
        const price = selected?.sale_price ?? selected?.price ?? 0;
        total += price * item.quantity;
    });
    return total;
});

const dynamicBundlePrice = computed(() => {
    const total = individualTotal.value;
    switch (props.bundle.discount_type) {
        case 'percentage':
            return Math.max(0, total * (1 - props.bundle.discount_value / 100));
        case 'fixed_amount':
            return Math.max(0, total - props.bundle.discount_value);
        case 'fixed_price':
            return props.bundle.discount_value;
        default:
            return total;
    }
});

const savings = computed(
    () => individualTotal.value - dynamicBundlePrice.value,
);

async function handleAddToCart() {
    const missing = props.bundle.items.filter(
        (item) => !selections.value[item.id],
    );
    if (missing.length > 0)
        return toast.error('Vui lòng chọn phiên bản cho tất cả sản phẩm');

    const result = await addToCart({
        purchasable_id: props.bundle.id,
        purchasable_type: 'App\\Models\\Product\\Bundle',
        configuration: selections.value,
        quantity: 1,
    });

    if (result.success) {
        toast.success('Đã thêm gói sản phẩm vào giỏ hàng');
    }
}

function handleReturn() {
    window.history.back();
}
</script>

<template>
    <Head :title="bundle.name" />
    <ShopLayout>
        <div class="mx-auto max-w-7xl px-4 py-8">
            <Button
                variant="ghost"
                class="mb-6 -ml-4 gap-2 text-zinc-500"
                @click="handleReturn()"
            >
                <ChevronLeft class="h-4 w-4" /> Quay lại
            </Button>

            <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-2">
                <!-- LEFT SIDE: INTERACTIVE CAROUSEL -->
                <div class="sticky top-24 flex flex-col gap-4">
                    <Carousel
                        class="w-full"
                        :opts="{ loop: true }"
                        @init-api="onInitCarousel"
                    >
                        <CarouselContent>
                            <CarouselItem
                                v-for="(img, idx) in allImages"
                                :key="idx"
                            >
                                <div
                                    class="aspect-square overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100"
                                >
                                    <img
                                        v-if="
                                            Math.abs(
                                                selectedImageIndex - idx,
                                            ) <= 1
                                        "
                                        :src="img.full"
                                        :alt="img.label"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-zinc-300"
                                    >
                                        Loading...
                                    </div>
                                </div>
                            </CarouselItem>
                        </CarouselContent>
                        <CarouselPrevious class="left-2" />
                        <CarouselNext class="right-2" />
                    </Carousel>

                    <!-- Thumbnail Strip -->
                    <div class="flex flex-wrap justify-start gap-2">
                        <button
                            v-for="(img, idx) in allImages"
                            :key="idx"
                            @click="selectImage(idx)"
                            class="relative h-12 w-12 overflow-hidden rounded-md border-2 transition-all"
                            :class="
                                selectedImageIndex === idx
                                    ? 'border-zinc-900 ring-2 ring-zinc-900/20'
                                    : 'border-transparent hover:border-zinc-300'
                            "
                        >
                            <img
                                :src="img.thumb"
                                :alt="img.label"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            />
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-8">
                    <section>
                        <h1
                            class="text-4xl font-bold tracking-tight text-zinc-900"
                        >
                            {{ bundle.name }}
                        </h1>
                        <p class="mt-4 text-lg leading-relaxed text-zinc-600">
                            {{ bundle.description }}
                        </p>
                    </section>

                    <section class="flex flex-col gap-6">
                        <div
                            v-for="item in bundle.items"
                            :key="item.id"
                            class="flex items-start gap-4 rounded-2xl p-4 transition-colors hover:bg-zinc-50/50"
                        >
                            <div
                                class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border bg-white"
                            >
                                <img
                                    :src="
                                        getSelectedVariant(item.id)?.images
                                            .primary.thumb
                                    "
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <div class="flex flex-1 flex-col gap-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-zinc-900">
                                            {{ item.product_name }}
                                            <span
                                                v-if="
                                                    getSelectedVariant(item.id)
                                                "
                                            >
                                                {{
                                                    getSelectedVariant(item.id)
                                                        ?.name
                                                }}
                                            </span>
                                        </h3>
                                        <a
                                            href="#"
                                            class="mt-1 flex items-center gap-1 text-xs text-zinc-400 underline underline-offset-4 hover:text-primary"
                                            @click.prevent="
                                                () => {
                                                    const v =
                                                        getSelectedVariant(
                                                            item.id,
                                                        );
                                                    if (v) {
                                                        router.visit(
                                                            productShow({
                                                                sku: v.sku,
                                                                variant_slug:
                                                                    v.slug,
                                                            }).url,
                                                        );
                                                    }
                                                }
                                            "
                                        >
                                            Xem sản phẩm
                                            <ExternalLink class="h-3 w-3" />
                                        </a>
                                    </div>
                                    <span class="font-medium text-zinc-900">
                                        {{
                                            formatPrice(
                                                getSelectedVariant(item.id)
                                                    ?.sale_price ??
                                                    getSelectedVariant(item.id)
                                                        ?.price ??
                                                    0,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="v in item.variants"
                                        :key="v.id"
                                        @click="selections[item.id] = v.id"
                                        :disabled="!v.in_stock"
                                        class="group relative h-7 w-7 rounded-full border-2 p-0.5 transition-all"
                                        :class="[
                                            selections[item.id] === v.id
                                                ? 'scale-110 border-orange-400 ring-2 ring-orange-400/20'
                                                : 'border-transparent hover:scale-110',
                                            !v.in_stock
                                                ? 'cursor-not-allowed opacity-30 grayscale'
                                                : 'cursor-pointer',
                                        ]"
                                    >
                                        <img
                                            :src="v.images.swatch"
                                            class="h-full w-full rounded-full object-cover"
                                        />
                                        <span
                                            class="pointer-events-none absolute top-8 left-1/2 -translate-x-1/2 rounded bg-zinc-800 px-2 py-1 text-[10px] whitespace-nowrap text-white opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            {{ v.swatch_label || v.name }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- PRICING FOOTER -->
                    <div
                        class="mt-4 space-y-4 rounded-3xl border border-zinc-200 bg-zinc-50 p-6"
                    >
                        <div
                            class="flex items-center justify-between text-zinc-500"
                        >
                            <span class="text-sm">Giảm</span>
                            <!-- Changed text-red-500 to text-orange-400 -->
                            <span class="text-sm font-medium text-orange-400">
                                -{{ formatPrice(savings) }}
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between border-t border-zinc-200 pt-4"
                        >
                            <span class="text-lg font-semibold text-zinc-900"
                                >Tổng cộng</span
                            >
                            <div class="text-right">
                                <span
                                    class="mr-2 text-sm text-zinc-400 line-through"
                                >
                                    {{ formatPrice(individualTotal) }}
                                </span>
                                <span class="text-2xl font-bold text-zinc-900">
                                    {{ formatPrice(dynamicBundlePrice) }}
                                </span>
                            </div>
                        </div>
                        <!-- Changed bg-red-500/600 to bg-orange-400/500 -->
                        <Button
                            @click="handleAddToCart"
                            class="h-14 w-full rounded-xl bg-orange-400 text-lg font-bold text-white transition-all hover:bg-orange-500"
                            :disabled="state.isLoading"
                        >
                            <ShoppingCart class="mr-2 h-5 w-5" />
                            {{
                                state.isLoading
                                    ? 'Đang xử lý...'
                                    : 'ADD BUNDLE TO CART'
                            }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
