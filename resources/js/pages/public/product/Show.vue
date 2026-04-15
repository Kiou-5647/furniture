<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import type { UnwrapRefCarouselApi as CarouselApi } from '@/components/ui/carousel/interface';
import ShopLayout from '@/layouts/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import type { Product, ProductVariant } from '@/types/public/product';

// UI Components

const props = defineProps<{
    product: Product;
    activeVariant: ProductVariant;
}>();

const breadcrumbs = computed(() => {
    const items: BreadcrumbItem[] = [
        { title: 'Trang chủ', href: '/' },
    ];

    if (props.product.category) {
        const cat = props.product.category;

        items.push({
            title: cat.product_type.name,
            href: `/san-pham?loai=${cat.product_type.slug}`,
        });

        if(cat.room) {
            items.push({
                title: cat.room.name,
                href: `/san-pham?phong=${cat.room.slug}`,
            });
        }

        if (cat.group) {
            items.push({
                title: cat.group.name,
                href: `/san-pham?nhom=${cat.group.slug}`,
            });
        }

        items.push({
            title: cat.name,
            href: `/san-pham?danh-muc=${cat.slug}`,
        });
    }

    const variantSuffix = props.activeVariant.name
        ? ` ${props.activeVariant.name}`
        : ` (${props.activeVariant.sku})`;

    items.push({
        title: `${props.product.name}${variantSuffix}`,
        href: '',
    });

    return items;
});

const allImages = computed(() => {
    const images = props.activeVariant.images;
    if (!images) return [];

    const list: { full: string; thumb: string; label: string }[] = [];

    if (images.primary) {
        list.push({
            full: images.primary.full,
            thumb: images.primary.thumb || images.primary.full,
            label: 'Ảnh chính'
        });
    }
    if (images.hover) {
        list.push({
            full: images.hover.full,
            thumb: images.hover.thumb || images.hover.full,
            label: 'Ảnh hover'
        });
    }
    if (images.dimension) {
        list.push({
            full: images.dimension.full,
            thumb: images.dimension.thumb || images.dimension.full,
            label: 'Kích thước'
        });
    }
    if (images.gallery) {
        images.gallery.forEach((img, idx) => {
            list.push({
                full: img.full,
                thumb: img.thumb || img.full,
                label: `Chi tiết ${idx + 1}`
            });
        });
    }

    return list;
});

const selectedImageIndex = ref(0);
const carouselApi = ref<CarouselApi | null>(null);

function onInitCarousel(api: CarouselApi) {
    carouselApi.value = api;
    // Sync: When carousel slides, update the thumbnail highlight
    api!.on('select', () => {
        selectedImageIndex.value = api!.selectedScrollSnap();
    });
}

function selectImage(index: number) {
    selectedImageIndex.value = index;
    // Sync: When thumbnail is clicked, scroll carousel to that image
    carouselApi.value?.scrollTo(index);
}
</script>

<template>
    <Head :title="product.name" />
    <ShopLayout>
        <div class="mx-auto max-w-7xl px-4 py-6">
            <Breadcrumbs :breadcrumbs="breadcrumbs" class="mb-6" />

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Left Side: Image Gallery -->
                <div class="flex flex-col gap-4">
                    <Carousel
                        class="w-full"
                        :opts="{
                            loop: true
                        }"
                        @init-api="onInitCarousel"
                    >
                        <CarouselContent>
                            <CarouselItem v-for="(img, idx) in allImages" :key="idx">
                                <div class="aspect-square overflow-hidden rounded-xl bg-zinc-100">
                                    <img
                                        :src="img.full"
                                        :alt="img.label"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                            </CarouselItem>
                        </CarouselContent>
                        <CarouselPrevious class="left-2"/>
                        <CarouselNext class="right-2"/>
                    </Carousel>

                    <!-- Thumbnails Strip -->
                    <div class="flex flex-wrap gap-2 justify-start">
                        <button
                            v-for="(img, idx) in allImages"
                            :key="idx"
                            @click="selectImage(idx)"
                            class="relative h-16 w-16 overflow-hidden rounded-md border-2 transition-all"
                            :class="selectedImageIndex === idx ? 'border-zinc-900 ring-2 ring-zinc-900/20' : 'border-transparent hover:border-zinc-300'"
                        >
                            <img :src="img.thumb" :alt="img.label" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Right Side: Product Info -->
                <div class="flex flex-col gap-4">
                    <h1 class="text-3xl font-bold">{{ product.name }}</h1>
                    <div class="flex items-center gap-2 text-muted-foreground text-sm">
                        <span>SKU: {{ activeVariant.sku }}</span>
                        <span>•</span>
                        <span>Biến thể: {{ activeVariant.slug }}</span>
                    </div>

                    <div class="text-2xl font-semibold text-zinc-900">
                        {{ activeVariant.price }}
                    </div>

                    <div class="mt-8 p-12 border-2 border-dashed rounded-xl text-center text-zinc-400">
                        Chi tiết sản phẩm đang được xây dựng...
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
