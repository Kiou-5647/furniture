<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ShoppingCart,
    Eye,
    Star,
    TrendingUp,
    MessageSquare,
    ShieldCheck,
    Tag,
    CheckCircle2,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import StarRating from '@/components/custom/StarRating.vue';
import ProductReviewItem from '@/components/custom/product/ProductReviewItem.vue';
import ProductCard from '@/components/custom/product/ProductCard.vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import Heading from '@/components/Heading.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import type { UnwrapRefCarouselApi as CarouselApi } from '@/components/ui/carousel/interface';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatPrice } from '@/lib/utils';
import { useCartStore } from '@/stores/cart';
import type { BreadcrumbItem } from '@/types';
import { AssemblyDifficultyLabels } from '@/types';
import type { Feature, ProductPage } from '@/types/public/product';

const props = defineProps<{
    product_page: ProductPage;
}>();

const { state, addToCart } = useCartStore();
// --- COMPUTED ---
const activeVariant = computed(() => props.product_page.active_variant);

const displayName = computed(() => {
    return `${props.product_page.name} ${activeVariant.value.name || activeVariant.value.swatch_label || ''}`.trim();
});

const availableSwatches = computed(() => {
    return props.product_page.variants.filter((v) => {
        return props.product_page.option_groups
            .filter((g) => !g.is_swatches)
            .every(
                (g) =>
                    v.option_values[g.namespace] ===
                    activeVariant.value.option_values[g.namespace],
            );
    });
});

const allImages = computed(() => {
    const images = activeVariant.value.images;
    const list: { full: string; thumb: string; label: string }[] = [];

    if (images.primary?.full)
        list.push({
            full: images.primary.full,
            thumb: images.primary.thumb || images.primary.full,
            label: 'Ảnh chính',
        });
    if (images.hover?.full)
        list.push({
            full: images.hover.full,
            thumb: images.hover.thumb || images.hover.full,
            label: 'Ảnh hover',
        });
    if (images.gallery) {
        images.gallery.forEach((img, idx) => {
            if (img.full)
                list.push({
                    full: img.full,
                    thumb: img.thumb || img.full,
                    label: `Chi tiết ${idx + 1}`,
                });
        });
    }
    if (images.dimension?.full)
        list.push({
            full: images.dimension.full,
            thumb: images.dimension.thumb || images.dimension.full,
            label: 'Kích thước',
        });
    if (images.swatch?.full)
        list.push({
            full: images.swatch.full,
            thumb: images.swatch.thumb || images.swatch.full,
            label: '',
        });
    return list;
});

// --- NAVIGATION ---
function navigateToVariant(sku: string, slug: string) {
    router.visit(`/san-pham/${sku}/${slug}`, { replace: true });
}

function navigateViaMap(namespace: string, value: string) {
    const url = props.product_page.navigation_map[namespace]?.[value];
    if (url) router.visit(url, { replace: true });
}

// --- CAROUSEL LOGIC ---
const selectedImageIndex = ref(0);
const carouselApi = ref<CarouselApi | null>(null);

const isMobile = ref(false);

const updateScreenSize = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    updateScreenSize();
    window.addEventListener('resize', updateScreenSize);
    fetchReviews();
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScreenSize);
});

const selectedFeature = ref();
const previewState = ref({
    open: false,
    src: null as string | null,
    images: [] as string[],
    currentIndex: 0,
});

function openFeature(feature: Feature) {
    selectedFeature.value = feature;
}

function openImagePreview(src: string) {
    const images = allImages.value.map(img => img.full);
    const index = images.indexOf(src);

    previewState.value = {
        open: true,
        src: src,
        images: images,
        currentIndex: index === -1 ? 0 : index,
    };
}

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

const breadcrumbs = computed((): BreadcrumbItem[] => {
    const items: BreadcrumbItem[] = [{ title: 'Trang chủ', href: '/' }];
    const cat = props.product_page.category;
    if (cat) {
        items.push({
            title: cat.product_type.name,
            href: `/san-pham?loai=${cat.product_type.slug}`,
        });
        items.push({ title: cat.name, href: `/san-pham?danh-muc=${cat.slug}` });
    }
    if (!isMobile.value) {
        items.push({ title: displayName.value, href: '' });
    } else {
        items.push({ title: '', href: '' });
    }
    return items;
});

function getSwatchVisual(swatch: any) {
    if (swatch.images?.swatch?.full) {
        return { type: 'image', value: swatch.images.swatch.full };
    }

    const swatchGroup = props.product_page.option_groups.find(
        (g) => g.is_swatches,
    );
    if (swatchGroup) {
        const option = swatchGroup.options.find(
            (o) => o.value === swatch.option_values[swatchGroup.namespace],
        );

        if (option?.image_url) {
            return { type: 'image', value: option.image_url };
        }

        const hex = option?.metadata?.hex_code || undefined;
        if (hex) {
            return { type: 'color', value: hex };
        }
    }

    return { type: 'color', value: '#E5E5E5' };
}

// --- REVIEWS LOGIC ---
const reviews = ref([] as any[]);
const reviewsMeta = ref({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0,
    average_rating: 0,
    reviews_count: 0,
});
const loadingReviews = ref(false);
const currentPage = ref(1);
const reviewScope = ref('all');
const selectedRating = ref<number | null>(null);
const selectedVariantId = ref<string | null>(null);

async function fetchReviews(page = 1) {
    loadingReviews.value = true;
    try {
        const params = new URLSearchParams();
        params.append('page', page.toString());
        params.append('scope', reviewScope.value);
        if (selectedRating.value) params.append('rating', selectedRating.value.toString());
        if (selectedVariantId.value) params.append('variant_id', selectedVariantId.value);

        const response = await fetch(
            `/api/products/${activeVariant.value.sku}/reviews?${params.toString()}`,
        );
        const result = await response.json();
        reviews.value = result.data;
        reviewsMeta.value = result.meta;
        currentPage.value = page;
    } catch (error) {
        console.error('Failed to fetch reviews:', error);
    } finally {
        loadingReviews.value = false;
    }
}

function updateScope(scope: string) {
    reviewScope.value = scope;
    fetchReviews(1);
}

function updateRating(rating: number | null) {
    selectedRating.value = rating;
    fetchReviews(1);
}

function updateVariant(id: string | null) {
    selectedVariantId.value = id;
    fetchReviews(1);
}

console.info(props.product_page.collection_products)
console.info(props.product_page.similar_products)
</script>

<template>
    <Head :title="product_page.name" />
    <ShopLayout>
        <div
            class="mx-auto flex max-w-[1600px] flex-col items-center justify-center px-4 py-6"
        >
            <Breadcrumbs
                :breadcrumbs="breadcrumbs"
                class="mb-3 self-start @lg:mb-6"
            />

            <div
                class="grid max-w-[1400px] grid-cols-1 items-start gap-8 md:grid-cols-12 md:gap-16"
            >
                <!-- Left Side: Pure Visuals -->
                <div class="flex flex-col md:sticky md:top-24 gap-8 md:col-span-7">
                    <!-- Image Gallery -->
                    <div class="select-none">
                        <div class="flex flex-col gap-4">
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
                                            class="aspect-square overflow-hidden rounded-2xl bg-zinc-100 shadow-sm cursor-zoom-in"
                                            @click="openImagePreview(img.full)"
                                        >
                                            <img
                                                v-if="
                                                    Math.abs(
                                                        selectedImageIndex -
                                                            idx,
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
                                    class="relative h-12 w-12 overflow-hidden rounded-lg border-2 transition-all"
                                    :class="
                                        selectedImageIndex === idx
                                            ? 'border-orange-400 ring-2 ring-orange-400/20'
                                            : 'border-transparent hover:border-zinc-300'
                                    "
                                    @dblclick="openImagePreview(img.full)"
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
                    </div>

                    <!-- Featured Highlights -->
                    <div
                        v-if="product_page.featured_highlights.length"
                        class="space-y-4"
                    >
                        <Heading title="Đặc trưng nổi bật"></Heading>
                        <div
                            class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
                        >
                            <div
                                v-for="(
                                    feature, idx
                                ) in product_page.featured_highlights"
                                :key="idx"
                                @click="openFeature(feature)"
                                class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border bg-white p-4 text-center transition-all hover:border-orange-400 hover:shadow-md"
                            >
                                <div class="mb-3 flex justify-center">
                                    <img
                                        :src="feature.image"
                                        class="h-16 w-16 object-contain transition-transform group-hover:scale-110"
                                        :alt="feature.name"
                                    />
                                </div>
                                <span
                                    class="text-sm font-bold text-zinc-700 transition-colors group-hover:text-orange-400"
                                >
                                    {{ feature.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: All Product Info & Selection (Sticky) -->
                <div class="flex flex-col gap-6 md:col-span-5">
                    <div class="sticky top-24 space-y-6">
                        <!-- Product Core Info -->
                        <div class="space-y-4">
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <Badge
                                        variant="outline"
                                        class="font-normal text-zinc-500"
                                    >
                                        {{ product_page.category?.name }}
                                    </Badge>
                                    <Badge
                                        variant="outline"
                                        class="font-normal text-zinc-500"
                                    >
                                        {{
                                            product_page.category?.product_type
                                                .name
                                        }}
                                    </Badge>
                                </div>
                                <h1
                                    class="text-3xl font-bold tracking-tight text-zinc-900 @lg:text-4xl"
                                >
                                    {{ product_page.name }}
                                </h1>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <StarRating
                                    :rating="activeVariant.average_rating"
                                    :count="activeVariant.reviews_count"
                                    show-count
                                    size="w-5 h-5"
                                    class="text-orange-400"
                                />
                                <div
                                    class="flex items-center gap-1 text-sm text-zinc-500"
                                >
                                    <ShieldCheck
                                        class="h-4 w-4 text-green-500"
                                    />
                                    <span>Bảo hành chính hãng</span>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-2 gap-4 py-2 sm:grid-cols-4"
                            >
                                <div
                                    class="flex flex-col items-center justify-center rounded-2xl border border-zinc-100 bg-white p-4 text-center transition-all hover:shadow-md"
                                >
                                    <TrendingUp
                                        class="mb-2 h-6 w-6 text-orange-400"
                                    />
                                    <span
                                        class="text-xs font-medium tracking-wider text-zinc-400 uppercase"
                                        >Đã bán</span
                                    >
                                    <span
                                        class="text-lg font-bold text-zinc-900"
                                        >{{
                                            product_page.active_variant
                                                .sales_count
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex flex-col items-center justify-center rounded-2xl border border-zinc-100 bg-white p-4 text-center transition-all hover:shadow-md"
                                >
                                    <Eye class="mb-2 h-6 w-6 text-blue-400" />
                                    <span
                                        class="text-xs font-medium tracking-wider text-zinc-400 uppercase"
                                        >Lượt xem</span
                                    >
                                    <span
                                        class="text-lg font-bold text-zinc-900"
                                        >{{
                                            product_page.active_variant
                                                .views_count
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex flex-col items-center justify-center rounded-2xl border border-zinc-100 bg-white p-4 text-center transition-all hover:shadow-md"
                                >
                                    <MessageSquare
                                        class="mb-2 h-6 w-6 text-purple-400"
                                    />
                                    <span
                                        class="text-xs font-medium tracking-wider text-zinc-400 uppercase"
                                        >Đánh giá</span
                                    >
                                    <span
                                        class="text-lg font-bold text-zinc-900"
                                        >{{
                                            product_page.active_variant
                                                .reviews_count
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex flex-col items-center justify-center rounded-2xl border border-zinc-100 bg-white p-4 text-center transition-all hover:shadow-md"
                                >
                                    <Tag class="mb-2 h-6 w-6 text-green-400" />
                                    <span
                                        class="text-xs font-medium tracking-wider text-zinc-400 uppercase"
                                        >Biến thể</span
                                    >
                                    <span
                                        class="text-lg font-bold text-zinc-900"
                                        >{{
                                            product_page.variants.length
                                        }}</span
                                    >
                                </div>
                            </div>
                            <!-- Variant Selection Hub -->
                            <div
                                class="rounded-3xl border bg-white p-6 shadow-sm"
                            >
                                <div class="mb-6 flex flex-col gap-1">
                                    <h2 class="text-xl font-bold text-zinc-900">
                                        Chi tiết phiên bản
                                    </h2>
                                    <p class="text-sm text-zinc-500">
                                        Hãy chọn phiên bản phù hợp nhất với bạn
                                    </p>
                                </div>

                                <!-- Selection Summary -->
                                <div
                                    class="mb-6 flex flex-wrap items-center gap-2 rounded-xl border border-orange-100 bg-orange-50 p-3 text-orange-700"
                                >
                                    <Tag class="h-4 w-4 shrink-0" />
                                    <span
                                        class="text-xs font-medium tracking-wider uppercase opacity-70"
                                        >Đang chọn:</span
                                    >
                                    <span class="text-sm font-bold">{{
                                        displayName
                                    }}</span>
                                </div>

                                <!-- Non-Swatch Options (Premium Pills) -->
                                <div
                                    v-for="group in product_page.option_groups.filter(
                                        (g) =>
                                            !g.is_swatches &&
                                            g.options.length >= 2,
                                    )"
                                    :key="group.namespace"
                                    class="space-y-3"
                                >
                                    <Label
                                        class="@lg:text-md flex items-center gap-2 text-sm font-bold text-zinc-700"
                                    >
                                        {{ group.name }}
                                        <span
                                            class="text-xs font-normal text-zinc-400"
                                        >
                                            {{
                                                group.options.find(
                                                    (opt) =>
                                                        opt.value ===
                                                        activeVariant
                                                            .option_values[
                                                            group.namespace
                                                        ],
                                                )?.label
                                            }}
                                        </span>
                                    </Label>
                                    <div class="flex flex-wrap gap-3">
                                        <button
                                            v-for="opt in group.options"
                                            :key="opt.value"
                                            @click="
                                                navigateViaMap(
                                                    group.namespace,
                                                    opt.value,
                                                )
                                            "
                                            class="group relative border transition-all duration-300"
                                            :class="[
                                                opt.image_url
                                                    ? 'flex h-14 w-14 items-center justify-center rounded-lg p-1'
                                                    : 'rounded-full px-5 py-2 text-sm font-bold',
                                                activeVariant.option_values[
                                                    group.namespace
                                                ] === opt.value
                                                    ? 'border-orange-400 bg-orange-50 text-orange-600 shadow-sm ring-1 ring-orange-400 ring-offset-1'
                                                    : 'border-zinc-100 bg-zinc-50/50 text-zinc-500 hover:border-orange-300 hover:bg-white hover:text-zinc-700',
                                            ]"
                                        >
                                            <div
                                                v-if="opt.image_url"
                                                class="relative h-12 w-12"
                                            >
                                                <img
                                                    :src="opt.image_url"
                                                    class="h-full w-full rounded-md object-cover"
                                                    loading="lazy"
                                                />
                                                <div
                                                    v-if="
                                                        activeVariant
                                                            .option_values[
                                                            group.namespace
                                                        ] === opt.value
                                                    "
                                                    class="absolute -top-1 -right-1 rounded-full bg-orange-400 p-0.5 text-white shadow-md ring-2 ring-white"
                                                >
                                                    <CheckCircle2
                                                        class="h-3 w-3"
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                v-else
                                                class="flex items-center gap-2"
                                            >
                                                <CheckCircle2
                                                    v-if="
                                                        activeVariant
                                                            .option_values[
                                                            group.namespace
                                                        ] === opt.value
                                                    "
                                                    class="h-3.5 w-3.5"
                                                />
                                                {{ opt.label }}
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Swatch Options (Premium Circles) -->
                                <div
                                    v-if="availableSwatches.length"
                                    class="space-y-3"
                                >
                                    <Label
                                        class="@lg:text-md flex items-center gap-2 text-sm font-bold text-zinc-700"
                                    >
                                        {{
                                            product_page.option_groups.find(
                                                (g) => g.is_swatches,
                                            )?.name
                                        }}:
                                        <span
                                            class="text-xs font-normal text-zinc-400"
                                        >
                                            {{ activeVariant.swatch_label }}
                                        </span>
                                    </Label>
                                    <div class="flex flex-wrap gap-3">
                                        <button
                                            v-for="swatch in availableSwatches"
                                            :key="swatch.id"
                                            @click="
                                                navigateToVariant(
                                                    swatch.sku,
                                                    swatch.slug,
                                                )
                                            "
                                            class="group relative h-10 w-10 rounded-full p-1 transition-all duration-300"
                                            :class="
                                                activeVariant.id === swatch.id
                                                    ? 'scale-110 ring-2 ring-orange-400 ring-offset-2'
                                                    : 'ring-1 ring-zinc-200 hover:scale-105 hover:ring-orange-300'
                                            "
                                        >
                                            <div
                                                class="h-full w-full overflow-hidden rounded-full border border-white shadow-sm"
                                            >
                                                <img
                                                    v-if="
                                                        getSwatchVisual(swatch)
                                                            .type === 'image'
                                                    "
                                                    :src="
                                                        getSwatchVisual(swatch)
                                                            .value
                                                    "
                                                    loading="lazy"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    :style="{
                                                        backgroundColor:
                                                            getSwatchVisual(
                                                                swatch,
                                                            ).value,
                                                    }"
                                                    class="h-full w-full"
                                                ></div>
                                            </div>
                                            <div
                                                v-if="
                                                    activeVariant.id ===
                                                    swatch.id
                                                "
                                                class="absolute -top-1 -right-1 rounded-full bg-orange-400 p-0.5 text-white shadow-md ring-2 ring-white"
                                            >
                                                <CheckCircle2 class="h-3 w-3" />
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button
                                    @click="
                                        addToCart({
                                            purchasable_id: activeVariant.id,
                                            purchasable_type:
                                                'App\\Models\\Product\\ProductVariant',
                                            quantity: 1,
                                        })
                                    "
                                    class="h-10 flex-1 rounded-full border-none bg-orange-400 text-base font-semibold text-white hover:bg-orange-300"
                                    :disabled="!activeVariant.in_stock"
                                >
                                    {{
                                        activeVariant.in_stock
                                            ? 'Thêm vào giỏ hàng'
                                            : 'Hết hàng'
                                    }}
                                </Button>
                            </div>
                            <span class="text-sm text-orange-400"
                                >Đã bán:
                                {{ product_page.active_variant.sales_count }}</span
                            >

                            <div class="space-y-2">
                                <h3 class="text-md font-bold">
                                    Mô tả sản phẩm
                                </h3>
                                <p
                                    class="font-sans leading-relaxed whitespace-pre-line text-zinc-600"
                                >
                                    {{ activeVariant.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Details & Specs (Accordions) -->
                        <div class="space-y-4">
                            <Accordion
                                type="multiple"
                                collapsible
                                class="w-full space-y-4"
                            >
                                <AccordionItem
                                    value="details"
                                    class="rounded-2xl border bg-white px-4 transition-all hover:border-orange-200"
                                >
                                    <AccordionTrigger
                                        class="hover:no-underline"
                                    >
                                        <span
                                            class="text-md font-bold text-zinc-800"
                                            >Chi tiết sản phẩm</span
                                        >
                                    </AccordionTrigger>
                                    <AccordionContent class="pb-6">
                                        <div class="grid grid-cols-1 gap-y-4">
                                            <template
                                                v-for="(feature, idx) in [
                                                    ...product_page.featured_highlights,
                                                    ...product_page.plain_features,
                                                ]"
                                                :key="idx"
                                            >
                                                <div
                                                    class="grid grid-cols-1 gap-2 border-b border-zinc-50 py-3 last:border-0 @sm:grid-cols-[160px_1fr] @sm:gap-6"
                                                >
                                                    <div
                                                        class="text-sm font-bold text-zinc-700"
                                                    >
                                                        {{ feature.name }}
                                                    </div>
                                                    <div
                                                        class="text-sm leading-relaxed text-zinc-600"
                                                    >
                                                        {{
                                                            feature.description
                                                        }}
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                                <AccordionItem
                                    value="specs"
                                    class="rounded-2xl border bg-white px-4 transition-all hover:border-orange-200"
                                >
                                    <AccordionTrigger
                                        class="hover:no-underline"
                                    >
                                        <span
                                            class="text-md font-bold text-zinc-800"
                                            >Thông số kỹ thuật</span
                                        >
                                    </AccordionTrigger>
                                    <AccordionContent class="pb-6">
                                        <div class="grid grid-cols-1 gap-y-6">
                                            <div
                                                v-for="(
                                                    categoryName, index
                                                ) in Object.keys(
                                                    product_page.specifications,
                                                )"
                                                :key="categoryName"
                                                class="space-y-3"
                                            >
                                                <div
                                                    class="text-sm font-bold tracking-wider text-zinc-900 uppercase"
                                                >
                                                    {{ categoryName }}
                                                </div>
                                                <div
                                                    class="grid grid-cols-1 gap-y-3"
                                                >
                                                    <div
                                                        v-for="(
                                                            item, idx
                                                        ) in product_page
                                                            .specifications[
                                                            categoryName
                                                        ].items"
                                                        :key="idx"
                                                        class="grid grid-cols-1 border-b border-zinc-50 last:border-0"
                                                        :class="
                                                            item.description
                                                                ? `@sm:grid-cols-[160px_1fr]`
                                                                : ``
                                                        "
                                                    >
                                                        <div
                                                            class="rounded-l-xl py-3 text-sm font-semibold text-zinc-700 @sm:bg-zinc-50/50 @sm:px-4"
                                                        >
                                                            {{
                                                                item.display_name
                                                            }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                item.description
                                                            "
                                                            class="py-3 text-sm leading-relaxed text-zinc-600 @sm:px-4"
                                                        >
                                                            {{
                                                                item.description
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <Separator
                                                    v-if="
                                                        index <
                                                        Object.keys(
                                                            product_page.specifications,
                                                        ).length -
                                                            1
                                                    "
                                                    class="mt-4"
                                                />
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                                <AccordionItem
                                    value="care-assembly"
                                    class="rounded-2xl border bg-white px-4 transition-all hover:border-orange-200"
                                >
                                    <AccordionTrigger
                                        class="hover:no-underline"
                                    >
                                        <span
                                            class="text-md font-bold text-zinc-800"
                                            >Bảo quản & Lắp ráp</span
                                        >
                                    </AccordionTrigger>
                                    <AccordionContent class="space-y-6 pb-6">
                                        <div
                                            v-if="
                                                product_page.care_information
                                                    .length
                                            "
                                            class="space-y-2"
                                        >
                                            <div
                                                class="text-sm font-bold text-zinc-900"
                                            >
                                                Hướng dẫn bảo quản:
                                            </div>
                                            <ul
                                                class="list-inside list-disc space-y-1 text-sm text-zinc-600"
                                            >
                                                <li
                                                    v-for="(
                                                        tip, idx
                                                    ) in product_page.care_information"
                                                    :key="idx"
                                                >
                                                    {{ tip }}
                                                </li>
                                            </ul>
                                        </div>
                                        <div
                                            v-if="
                                                product_page
                                                    .assembly_information
                                                    .required
                                            "
                                            class="space-y-3 border-t border-zinc-50 pt-4"
                                        >
                                            <div
                                                class="text-sm font-bold text-zinc-900"
                                            >
                                                Hướng dẫn lắp ráp:
                                            </div>
                                            <div
                                                class="grid grid-cols-2 gap-4 text-sm"
                                            >
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-xs text-zinc-500"
                                                        >Thời gian ước
                                                        tính</span
                                                    >
                                                    <span
                                                        class="font-semibold text-zinc-700"
                                                        >{{
                                                            product_page
                                                                .assembly_information
                                                                .estimated_minutes ||
                                                            'N/A'
                                                        }}
                                                        phút</span
                                                    >
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-xs text-zinc-500"
                                                        >Mức độ khó</span
                                                    >
                                                    <span
                                                        class="font-semibold text-zinc-700"
                                                    >
                                                        {{
                                                            AssemblyDifficultyLabels[
                                                                product_page
                                                                    .assembly_information
                                                                    .difficulty_level as keyof typeof AssemblyDifficultyLabels
                                                            ] || 'N/A'
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-if="
                                                product_page
                                                    .assembly_information
                                                    .additional_info
                                            "
                                            class="text-sm leading-relaxed text-zinc-600"
                                        >
                                            {{
                                                product_page
                                                    .assembly_information
                                                    .additional_info
                                            }}
                                        </div>
                                        <Button
                                            v-if="
                                                product_page
                                                    .assembly_information
                                                    .manual_url
                                            "
                                            variant="outline"
                                            size="sm"
                                            class="w-fit rounded-full border-orange-200 text-orange-600 hover:bg-orange-50"
                                            :href="
                                                product_page
                                                    .assembly_information
                                                    .manual_url
                                            "
                                            target="_blank"
                                        >
                                            Tải hướng dẫn lắp ráp (PDF)
                                        </Button>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Recommendations Section -->
            <div class="mt-16 w-full max-w-[1400px] space-y-16">
                <!-- Same Collection Products -->
                <div v-if="product_page.collection_products.length" class="space-y-8">
                    <div class="flex items-center justify-between gap-4">
                        <Heading :title="`Sản phẩm cùng bộ sưu tập ${product_page.collection?.name}`" />
                    </div>
                    <Carousel
                        class="w-full"
                        :opts="{ align: 'start', loop: true }"
                    >
                        <CarouselContent class="-ml-4">
                            <CarouselItem
                                v-for="card in product_page.collection_products"
                                :key="card.id"
                                class="pl-4 basis-1/1 sm:basis-1/2 lg:basis-1/3 xl:basis-1/4"
                            >
                                <ProductCard :productCard="card" />
                            </CarouselItem>
                        </CarouselContent>
                        <CarouselPrevious class="-left-12 hidden lg:flex" />
                        <CarouselNext class="-right-12 hidden lg:flex" />
                    </Carousel>
                </div>

                <!-- Similar Products -->
                <div v-if="product_page.similar_products.length" class="space-y-8">
                    <div class="flex items-center justify-between gap-4">
                        <Heading title="Sản phẩm tương tự" />
                    </div>
                    <Carousel
                        class="w-full"
                        :opts="{ align: 'start', loop: true }"
                    >
                        <CarouselContent class="-ml-4">
                            <CarouselItem
                                v-for="card in product_page.similar_products"
                                :key="card.id"
                                class="pl-4 basis-1/1 sm:basis-1/2 lg:basis-1/3 xl:basis-1/4"
                            >
                                <ProductCard :productCard="card" />
                            </CarouselItem>
                        </CarouselContent>
                        <CarouselPrevious class="-left-12 hidden lg:flex" />
                        <CarouselNext class="-right-12 hidden lg:flex" />
                    </Carousel>
                </div>

                <div class="flex flex-col gap-6">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                        <div class="space-y-2">
                            <Heading title="Đánh giá khách hàng"></Heading>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <StarRating
                                        :rating="reviewsMeta.average_rating"
                                        :count="reviewsMeta.reviews_count"
                                        size="w-6 h-6"
                                        class="text-orange-400"
                                    />
                                    <span class="text-lg font-bold text-zinc-900">
                                        {{ reviewsMeta.average_rating.toFixed(1) }}
                                    </span>
                                </div>
                                <span class="text-zinc-300">|</span>
                                <span class="text-sm text-zinc-500">
                                    Tổng cộng {{ reviewsMeta.reviews_count }} đánh giá
                                </span>
                            </div>
                        </div>

                        <!-- Filters Hub -->
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Scope Toggle -->
                            <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <button
                                    @click="updateScope('all')"
                                    :class="[
                                        'px-4 py-1.5 text-xs font-bold rounded-lg transition-all',
                                        reviewScope === 'all'
                                        ? 'bg-white dark:bg-zinc-700 text-orange-600 shadow-sm'
                                        : 'text-zinc-500 hover:text-zinc-700'
                                    ]"
                                >
                                    Tất cả
                                </button>
                                <button
                                    @click="updateScope('variant')"
                                    :class="[
                                        'px-4 py-1.5 text-xs font-bold rounded-lg transition-all',
                                        reviewScope === 'variant'
                                        ? 'bg-white dark:bg-zinc-700 text-orange-600 shadow-sm'
                                        : 'text-zinc-500 hover:text-zinc-700'
                                    ]"
                                >
                                    Biến thể này
                                </button>
                            </div>

                            <!-- Rating Filter -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-zinc-400 uppercase">Sao:</span>
                                <div class="flex gap-1">
                                    <button
                                        v-for="star in [5, 4, 3, 2, 1]"
                                        :key="star"
                                        @click="updateRating(selectedRating === star ? null : star)"
                                        :class="[
                                            'w-8 h-8 rounded-full text-xs font-bold transition-all border',
                                            selectedRating === star
                                            ? 'bg-orange-500 border-orange-500 text-white'
                                            : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-600 hover:border-orange-300'
                                        ]"
                                    >
                                        {{ star }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="loadingReviews" class="flex justify-center py-12">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-orange-400 border-t-transparent"></div>
                </div>

                <div v-else-if="reviews.length" class="space-y-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <ProductReviewItem
                            v-for="review in reviews"
                            :key="review.id"
                            :review="review"
                        />
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="reviewsMeta.last_page > 1"
                        class="flex justify-center items-center gap-2 pt-8"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="fetchReviews(currentPage - 1)"
                        >
                            Trước
                        </Button>
                        <span class="text-sm font-medium text-zinc-600">
                            Trang {{ currentPage }} / {{ reviewsMeta.last_page }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === reviewsMeta.last_page"
                            @click="fetchReviews(currentPage + 1)"
                        >
                            Sau
                        </Button>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-200 p-12 text-center"
                >
                    <MessageSquare class="mb-4 h-12 w-12 text-zinc-300" />
                    <p class="text-zinc-500">Hiện chưa có đánh giá nào phù hợp với bộ lọc.</p>
                </div>
            </div>
        </div>
    </ShopLayout>
    <ImagePreviewDialog
        v-model:open="previewState.open"
        :src="previewState.images[previewState.currentIndex] || previewState.src"
        :images="previewState.images"
        :current-index="previewState.currentIndex"
        @update:open="(val) => previewState.open = val"
        @update:current-index="(val) => previewState.currentIndex = val"
    />
    <Dialog :open="!!selectedFeature" @update:open="selectedFeature = null">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex flex-col items-center gap-4">
                    <!-- Show the icon again in the dialog for visual continuity -->
                    <img
                        v-if="selectedFeature?.image"
                        :src="selectedFeature.image"
                        class="h-30 w-30 rounded-lg object-cover"
                    />
                    <DialogTitle class="text-xl">{{
                        selectedFeature?.name
                    }}</DialogTitle>
                    <DialogDescription />
                </div>
            </DialogHeader>
            <div class="text-center leading-relaxed text-zinc-600">
                {{ selectedFeature?.description }}
            </div>
        </DialogContent>
    </Dialog>
</template>
