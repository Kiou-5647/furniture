<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import StarRating from '@/components/custom/StarRating.vue';
import ReviewForm from '@/components/product/ReviewForm.vue';
import { Button } from '@/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import type { UnwrapRefCarouselApi as CarouselApi } from '@/components/ui/carousel/interface';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatPrice } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';
import type { Product, ProductVariant } from '@/types/public/product';

const props = defineProps<{
    product: Product;
    activeVariant: ProductVariant;
}>();

const selectedVariantId = ref<string>(props.activeVariant.id);

const currentVariant = computed(() => {
    return (
        props.product.variants.find((v) => v.id === selectedVariantId.value) ??
        props.product.variants[0]
    );
});

const displayName = computed(() => {
    const v = currentVariant.value;

    return `${props.product.name} ${v?.name ?? v?.swatch_label ?? ''}`.trim();
});

const allImages = computed(() => {
    const images = currentVariant.value?.images;
    if (!images) return [];

    const list: { full: string; thumb: string; label: string }[] = [];
    if (images.primary)
        list.push({
            full: images.primary.full,
            thumb: images.primary.thumb || images.primary.full,
            label: 'Ảnh chính',
        });
    if (images.hover)
        list.push({
            full: images.hover.full,
            thumb: images.hover.thumb || images.hover.full,
            label: 'Ảnh hover',
        });
    if (images.gallery) {
        images.gallery.forEach((img, idx) => {
            list.push({
                full: img.full,
                thumb: img.thumb || img.full,
                label: `Chi tiết ${idx + 1}`,
            });
        });
    }
    if (images.dimension)
        list.push({
            full: images.dimension.full,
            thumb: images.dimension.thumb || images.dimension.full,
            label: 'Kích thước',
        });
    if (images.swatch)
        list.push({
            full: images.swatch.full,
            thumb: images.swatch.thumb || images.swatch.full,
            label: '',
        });

    return list;
});

// --- SELECTOR LOGIC ---
const selections = ref<Record<string, string>>({});

const activeCard = computed(() => {
    const cards = props.product.grouped_variants ?? [];
    return (
        cards.find((card) =>
            Object.entries(card.option_values).every(
                ([key, value]) => selections.value[key] === value,
            ),
        ) ??
        cards[0] ??
        null
    );
});

function selectOption(namespace: string, value: string) {
    selections.value[namespace] = value;
    // When a base option changes, we find the first available variant in the new card
    if (activeCard.value?.swatch_options?.length) {
        selectedVariantId.value = activeCard.value.swatch_options[0].variant_id;
    }
}

function selectSwatch(variantId: string) {
    selectedVariantId.value = variantId;
}

// --- CAROUSEL LOGIC ---
const selectedImageIndex = ref(0);
const carouselApi = ref<CarouselApi | null>(null);

function onInitCarousel(api: CarouselApi) {
    carouselApi.value = api;
    api!.on('select', () => {
        selectedImageIndex.value = api!.selectedScrollSnap();
    });
}

function selectImage(index: number) {
    selectedImageIndex.value = index;
    carouselApi.value?.scrollTo(index);
}

const breadcrumbs = computed(() => {
    const items: BreadcrumbItem[] = [{ title: 'Trang chủ', href: '/' }];
    if (props.product.category) {
        const cat = props.product.category;
        items.push({
            title: cat.product_type.name,
            href: `/san-pham?loai=${cat.product_type.slug}`,
        });
        if (cat.room)
            items.push({
                title: cat.room.name,
                href: `/san-pham?phong=${cat.room.slug}`,
            });
        if (cat.group)
            items.push({
                title: cat.group.name,
                href: `/san-//pham?nhom=${cat.group.slug}`,
            });
        items.push({
            title: cat.name,
            href: `/san-//pham?danh-muc=${cat.slug}`,
        });
    }
    const variantSuffix = currentVariant.value?.name
        ? ` ${currentVariant.value.name}`
        : ` (${currentVariant.value?.sku})`;
    items.push({ title: `${props.product.name}${variantSuffix}`, href: '' });
    return items;
});

onMounted(() => {
    // 1. Handle the non-swatch selections first
    if (activeCard.value) {
        // Copy the option_values from the first card into the selections state
        // This ensures the "pills" are highlighted on first load
        selections.value = { ...activeCard.value.option_values };
    }

    // 2. Handle the variant/swatch selection
    // If there's a URL filter, use it, otherwise use the first variant of the active card
    const urlParams = new URLSearchParams(window.location.search);
    const colorFilter = urlParams.get('mau-sac');

    if (colorFilter && activeCard.value) {
        const matchingSwatch = activeCard.value.swatch_options.find(
            (s) => s.value === colorFilter,
        );
        if (matchingSwatch) {
            selectedVariantId.value = matchingSwatch.variant_id;
        }
    } else if (activeCard.value?.swatch_options?.length) {
        // Default to first swatch in the active card
        selectedVariantId.value = activeCard.value.swatch_options[0].variant_id;
    } else {
        // Fallback to first variant of the product
        selectedVariantId.value = props.product.variants?.[0]?.id ?? null;
    }
});
</script>

<template>
    <Head :title="product.name" />
    <ShopLayout>
        <div class="mx-auto max-w-[1600px] px-4 py-6">
            <Breadcrumbs :breadcrumbs="breadcrumbs" class="mb-6" />
            <div class="grid grid-cols-1 gap-12 @lg:grid-cols-2">
                <!-- Left Side: Image Gallery -->
                <div class="flex flex-col gap-4 select-none">
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
                                    class="aspect-square overflow-hidden rounded-xl bg-zinc-100"
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

                    <div class="flex flex-wrap justify-start gap-2">
                        <button
                            v-for="(img, idx) in allImages"
                            :key="idx"
                            @click="selectImage(idx)"
                            class="relative h-16 w-16 overflow-hidden rounded-md border-2 transition-all"
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

                <!-- Right Side: Product Info -->
                <div class="flex flex-col gap-2 select-none">
                    <h1 class="text-3xl font-bold">{{ displayName }}</h1>
                    <div class="text-2xl font-semibold">
                        <span
                            v-if="currentVariant?.sale_price"
                            class="text-orange-500"
                        >
                            {{
                                formatPrice(Number(currentVariant.sale_price))
                            }}đ
                        </span>
                        <span
                            :class="
                                currentVariant?.sale_price
                                    ? 'ml-2 text-lg text-zinc-400 line-through'
                                    : 'text-zinc-900'
                            "
                        >
                            {{ formatPrice(Number(currentVariant?.price)) }}đ
                        </span>
                    </div>
                    <StarRating
                        :rating="product.average_rating"
                        :count="product.reviews_count"
                        show-count
                        size="w-5 h-5"
                    />
                    <Separator class="my-6" />

                    <div class="space-y-6 border-zinc-100">
                        <div
                            v-for="group in props.product.option_groups.filter(
                                (g) => !g.is_swatches,
                            )"
                            :key="group.namespace"
                            class="space-y-3"
                        >
                            <Label
                                class="text-sm font-bold text-zinc-700 @lg:text-lg"
                                >{{ group.name }}</Label
                            >
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="opt in group.options"
                                    :key="opt.value"
                                    @click="
                                        selectOption(group.namespace, opt.value)
                                    "
                                    class="@lg:text-md min-w-32 rounded-full border-1 px-4 py-1 text-sm font-extrabold transition-all"
                                    :class="
                                        selections[group.namespace] ===
                                        opt.value
                                            ? 'border-3 border-orange-100 text-orange-400'
                                            : 'border-zinc-20 text-zinc-600 hover:border-orange-400'
                                    "
                                >
                                    {{ opt.label }}
                                </button>
                            </div>
                            <Separator class="mt-5" />
                        </div>

                        <div
                            v-if="activeCard?.swatch_options?.length"
                            class="space-y-3"
                        >
                            <Label
                                class="text-sm font-bold text-zinc-700 @lg:text-lg"
                                >{{
                                    props.product.option_groups.find(
                                        (g) => g.is_swatches,
                                    )?.name
                                }}:
                                <span class="font-normal">{{
                                    currentVariant.swatch_label
                                }}</span>
                            </Label>
                            <div class="flex flex-wrap gap-3">
                                <button
                                    v-for="swatch in activeCard.swatch_options"
                                    :key="swatch.variant_id"
                                    @click="selectSwatch(swatch.variant_id)"
                                    class="group relative h-10 w-10 rounded-full border-2 p-0.5 transition-all"
                                    :class="
                                        selectedVariantId === swatch.variant_id
                                            ? 'border-3 border-orange-400'
                                            : 'border-transparent hover:border-orange-400'
                                    "
                                >
                                    <img
                                        :src="swatch.swatch_image_url!"
                                        loading="lazy"
                                        class="h-full w-full rounded-full object-center"
                                    />
                                    <span
                                        class="absolute -bottom-8 left-1/2 z-50 -translate-x-1/2 scale-0 rounded bg-zinc-800 px-2 py-1 text-sm whitespace-nowrap text-white transition-transform group-hover:scale-100"
                                    >
                                        {{ swatch.label }}
                                    </span>
                                </button>
                            </div>
                            <Separator class="mt-5" />
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <Button
                            class="mt-5 h-12 flex-1 rounded-full border-none bg-orange-400 text-base font-semibold text-white hover:bg-orange-400"
                            :disabled="!currentVariant?.in_stock"
                        >
                            {{
                                currentVariant?.in_stock
                                    ? 'Thêm vào giỏ hàng'
                                    : 'Hết hàng'
                            }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-12 @lg:grid-cols-3">
                <!-- Review Submission -->
                <div class="@lg:col-span-1">
                    <ReviewForm
                        :reviewable-id="currentVariant?.id ?? ''"
                        :reviewable-type="
                            currentVariant?.id
                                ? 'App\\\\Models\\\\Product\\\\ProductVariant'
                                : ''
                        "
                    />
                </div>

                <!-- Review List (Placeholder for now) -->
                <div class="space-y-6 @lg:col-span-2">
                    <h2 class="text-2xl font-bold">Customer Reviews</h2>
                    <div
                        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 text-center"
                    >
                        <p class="text-zinc-500">
                            No reviews yet. Be the first to review this product!
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <Separator />
    </ShopLayout>
</template>
<style scoped></style>
