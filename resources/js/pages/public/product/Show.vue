<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ShoppingCart } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import StarRating from '@/components/custom/StarRating.vue';
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
    router.visit(`/san-pham/${sku}/${slug}`, {replace: true});
}

function navigateViaMap(namespace: string, value: string) {
    const url = props.product_page.navigation_map[namespace]?.[value];
    if (url) router.visit(url, {replace: true});
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
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScreenSize);
});

const selectedFeature = ref();

function openFeature(feature: Feature) {
    selectedFeature.value = feature;
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
        // Room/Group logic can be added back here if provided in the resource
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
    // 1. Try the Variant's specific swatch image
    if (swatch.images?.swatch?.full) {
        return { type: 'image', value: swatch.images.swatch.full };
    }

    // Find the option group marked as a swatch
    const swatchGroup = props.product_page.option_groups.find(
        (g) => g.is_swatches,
    );
    if (swatchGroup) {
        const option = swatchGroup.options.find(
            (o) => o.value === swatch.option_values[swatchGroup.namespace],
        );

        // 2. Try the Option Group's image (The one we just added in the Resource)
        if (option?.image_url) {
            return { type: 'image', value: option.image_url };
        }

        // 3. Fallback to Hex Color from metadata
        const hex = option?.metadata?.hex_code || undefined;
        if (hex) {
            return { type: 'color', value: hex };
        }
    }

    // Final fallback if everything is missing
    return { type: 'color', value: '#E5E5E5' };
}
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
                class="grid max-w-[1200px] grid-cols-1 items-start gap-3 @lg:grid-cols-12 @lg:gap-12"
            >
                <div class="flex flex-col @lg:hidden">
                    <h1 class="text-lg font-bold">{{ displayName }}</h1>
                    <div class="text-md font-semibold">
                        <span class="text-zinc-900">
                            {{ formatPrice(Number(activeVariant.price)) }}
                        </span>
                    </div>
                    <StarRating
                        :rating="activeVariant.average_rating"
                        :count="activeVariant.reviews_count"
                        show-count
                        size="w-4 h-4"
                        class="text-xs"
                    />
                </div>
                <!-- Left Side: Image Gallery -->
                <div class="select-none @lg:sticky @lg:top-20 @lg:col-span-7">
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
                                class="relative h-10 w-10 overflow-hidden rounded-md border-2 transition-all @lg:h-12 @lg:w-12"
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
                    <div
                        v-if="product_page.featured_highlights.length"
                        class="mt-8 flex flex-col gap-4"
                    >
                        <Heading title="Đặc trưng"></Heading>
                        <div class="flex gap-4">
                            <div
                                v-for="(
                                    feature, idx
                                ) in product_page.featured_highlights"
                                :key="idx"
                                @click="openFeature(feature)"
                                class="group cursor-pointer rounded-xl border bg-white p-2 text-center transition-all hover:border-orange-400 hover:shadow-sm"
                            >
                                <div class="mb-2 flex justify-center">
                                    <img
                                        :src="feature.image"
                                        class="h-20 w-20 object-contain @lg:h-30 @lg:w-30"
                                        :alt="feature.name"
                                    />
                                </div>
                                <span
                                    class="text-md font-bold text-zinc-700 transition-colors group-hover:text-orange-400"
                                >
                                    {{ feature.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Product Info -->
                <div class="flex flex-col gap-2 select-none @lg:col-span-5">
                    <div class="hidden @lg:block">
                        <h1 class="text-lg font-bold">{{ displayName }}</h1>
                        <div class="text-md font-semibold">
                            <span
                                v-if="
                                    activeVariant.sale_price <
                                    activeVariant.price
                                "
                                class="text-orange-500"
                                >{{
                                    formatPrice(
                                        Number(activeVariant.sale_price),
                                    )
                                }}</span
                            >
                            <span
                                :class="
                                    activeVariant.sale_price <
                                    activeVariant.price
                                        ? 'text-md ml-2 text-zinc-400 line-through'
                                        : 'text-zinc-900'
                                "
                            >
                                {{ formatPrice(Number(activeVariant.price)) }}
                            </span>
                        </div>
                        <StarRating
                            :rating="activeVariant.average_rating"
                            :count="activeVariant.reviews_count"
                            show-count
                            size="w-4 h-4"
                            class="text-xs"
                        />
                    </div>

                    <Separator class="my-3" />

                    <div class="space-y-6">
                        <!-- Non-Swatch Options (Pills) -->
                        <div
                            v-for="group in product_page.option_groups.filter(
                                (g) => !g.is_swatches && g.options.length >= 2,
                            )"
                            :key="group.namespace"
                            class="space-y-3"
                        >
                            <Label
                                class="@lg:text-md text-sm font-bold text-zinc-700"
                            >
                                {{ group.name }}:
                                <span class="font-normal">
                                    {{
                                        group.options.find(
                                            (opt) =>
                                                opt.value ===
                                                activeVariant.option_values[
                                                    group.namespace
                                                ],
                                        )?.label
                                    }}
                                </span>
                            </Label>
                            <div
                                v-if="
                                    group.options.every((opt) => opt.image_url)
                                "
                                class="flex flex-wrap gap-3"
                            >
                                <button
                                    v-for="opt in group.options"
                                    :key="opt.value"
                                    @click="
                                        navigateViaMap(
                                            group.namespace,
                                            opt.value,
                                        )
                                    "
                                    class="group relative h-24 w-24 rounded-xl border-2 p-0.5 transition-all"
                                    :class="
                                        activeVariant.option_values[
                                            group.namespace
                                        ] === opt.value
                                            ? 'border-3 border-orange-400'
                                            : 'border-transparent hover:border-orange-400'
                                    "
                                >
                                    <img
                                        :src="opt.image_url!"
                                        loading="lazy"
                                        class="h-full w-full rounded-lg object-cover"
                                    />
                                    <span
                                        class="absolute -bottom-8 left-1/2 z-50 -translate-x-1/2 scale-0 rounded bg-zinc-800 px-2 py-1 text-sm whitespace-nowrap text-white transition-transform group-hover:scale-100"
                                    >
                                        {{ opt.label }}
                                    </span>
                                </button>
                            </div>

                            <!-- Case 2: Any option lacks image -> Show as Text Pills -->
                            <div v-else class="flex flex-wrap gap-2">
                                <button
                                    v-for="opt in group.options"
                                    :key="opt.value"
                                    @click="
                                        navigateViaMap(
                                            group.namespace,
                                            opt.value,
                                        )
                                    "
                                    class="@lg:text-md rounded-full border-1 px-4 py-1 text-sm font-extrabold transition-all @lg:min-w-fit"
                                    :class="
                                        activeVariant.option_values[
                                            group.namespace
                                        ] === opt.value
                                            ? 'border-3 border-orange-400 text-orange-400'
                                            : 'border-zinc-20 text-zinc-600 hover:border-orange-400'
                                    "
                                >
                                    {{ opt.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Swatch Options (Direct Variant Navigation) -->
                        <div v-if="availableSwatches.length" class="space-y-3">
                            <Label
                                class="@lg:text-md text-sm font-bold text-zinc-700"
                            >
                                {{
                                    product_page.option_groups.find(
                                        (g) => g.is_swatches,
                                    )?.name
                                }}:
                                <span class="font-normal">{{
                                    activeVariant.swatch_label
                                }}</span>
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
                                    class="group relative h-8 w-8 rounded-full border-2 p-0.5 transition-all"
                                    :class="
                                        activeVariant.id === swatch.id
                                            ? 'border-3 border-orange-400'
                                            : 'border-transparent hover:border-orange-400'
                                    "
                                >
                                    <img
                                        v-if="
                                            getSwatchVisual(swatch).type ===
                                            'image'
                                        "
                                        :src="getSwatchVisual(swatch).value"
                                        loading="lazy"
                                        class="h-full w-full rounded-full object-center"
                                    />
                                    <!-- Render Solid Color if fallback to hex code -->
                                    <div
                                        v-else
                                        :style="{
                                            backgroundColor:
                                                getSwatchVisual(swatch).value,
                                        }"
                                        class="h-full w-full rounded-full border border-zinc-200"
                                    ></div>
                                    <span
                                        class="absolute -bottom-8 left-1/2 z-50 -translate-x-1/2 scale-0 rounded bg-zinc-800 px-2 py-1 text-sm whitespace-nowrap text-white transition-transform group-hover:scale-100"
                                    >
                                        {{ swatch.swatch_label }}
                                    </span>
                                </button>
                            </div>
                            <Separator class="mt-5" />
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
                    <div class="mt-8 space-y-4">
                        <div class="space-y-4">
                            <h3 class="text-md font-bold">Mô tả sản phẩm</h3>
                            <p
                                class="font-sans leading-relaxed whitespace-pre-line text-zinc-600"
                            >
                                {{ activeVariant.description }}
                            </p>
                        </div>
                        <Accordion
                            type="multiple"
                            collapsible
                            class="w-full space-y-4"
                        >
                            <AccordionItem
                                value="details"
                                class="rounded-xl border bg-white px-4 transition-all hover:border-orange-200"
                            >
                                <AccordionTrigger class="hover:no-underline">
                                    <span
                                        class="text-md font-bold text-zinc-800"
                                        >Đặc trưng sản phẩm</span
                                    >
                                </AccordionTrigger>
                                <AccordionContent class="pb-6">
                                    <div class="grid grid-cols-1 gap-y-4">
                                        <!-- Merge Linked Features and Plain Features into one list -->
                                        <template
                                            v-for="(feature, idx) in [
                                                ...product_page.featured_highlights,
                                                ...product_page.plain_features,
                                            ]"
                                            :key="idx"
                                        >
                                            <div
                                                class="grid grid-cols-1 gap-2 border-b border-zinc-50 py-2 last:border-0 @sm:grid-cols-[160px_1fr] @sm:gap-6"
                                            >
                                                <div
                                                    class="text-sm font-bold text-zinc-700"
                                                >
                                                    {{ feature.name }}
                                                </div>
                                                <div
                                                    class="text-sm leading-relaxed text-zinc-600"
                                                >
                                                    {{ feature.description }}
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </AccordionContent>
                            </AccordionItem>
                            <AccordionItem
                                value="specs"
                                class="rounded-xl border bg-white px-4 transition-all hover:border-orange-200"
                            >
                                <AccordionTrigger class="hover:no-underline">
                                    <span
                                        class="text-md font-bold text-zinc-800"
                                        >Thông số kỹ thuật</span
                                    >
                                </AccordionTrigger>
                                <AccordionContent class="pb-6">
                                    <div class="grid grid-cols-1 gap-y-4">
                                        <!-- Loop through categories (e.g., "Chất liệu", "Kích thước") -->
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
                                                class="mb-3 text-sm font-bold text-zinc-900"
                                            >
                                                {{ categoryName }}:
                                            </div>

                                            <div
                                                class="grid grid-cols-1 gap-y-2"
                                            >
                                                <!-- Loop through items within that category -->
                                                <div
                                                    v-for="(
                                                        item, idx
                                                    ) in product_page
                                                        .specifications[
                                                        categoryName
                                                    ].items"
                                                    :key="idx"
                                                    class="grid grid-cols-1"
                                                    :class="
                                                        item.description !=
                                                            '' &&
                                                        item.description != null
                                                            ? `@sm:grid-cols-[120px_1fr]`
                                                            : ``
                                                    "
                                                >
                                                    <div
                                                        class="text-sm font-semibold text-zinc-700"
                                                    >
                                                        {{ item.display_name }}
                                                    </div>

                                                    <div
                                                        v-if="item.description"
                                                        class="text-sm leading-relaxed text-zinc-600"
                                                    >
                                                        {{
                                                            item.description ||
                                                            ''
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
                                            />
                                        </div>
                                    </div>
                                </AccordionContent>
                            </AccordionItem>
                            <AccordionItem
                                value="care-assembly"
                                class="rounded-xl border bg-white px-4 transition-all hover:border-orange-200"
                            >
                                <AccordionTrigger class="hover:no-underline">
                                    <span
                                        class="text-md font-bold text-zinc-800"
                                        >Bảo quản & Lắp ráp</span
                                    >
                                </AccordionTrigger>
                                <AccordionContent class="space-y-6 pb-6">
                                    <!-- Care Information -->
                                    <div
                                        v-if="
                                            product_page.care_information.length
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

                                    <!-- Assembly Information -->
                                    <div
                                        v-if="
                                            product_page.assembly_information
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
                                                    >Thời gian ước tính</span
                                                >
                                                <span
                                                    class="font-semibold text-zinc-700"
                                                >
                                                    {{
                                                        product_page
                                                            .assembly_information
                                                            .estimated_minutes ||
                                                        'N/A'
                                                    }}
                                                    phút
                                                </span>
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
                                    </div>
                                </AccordionContent>
                            </AccordionItem>
                        </Accordion>
                    </div>
                </div>
            </div>
        </div>
        <Separator />
    </ShopLayout>
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
