<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ImageOff, Package } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    bundle: {
        id: string;
        name: string;
        slug: string;
        price: number;
        images: {
            primary: string | null;
            hover: string | null;
        };
    };
}>();

const isHovered = ref(false);

const displayImage = computed(() => {
    if (isHovered.value && props.bundle.images.hover) {
        return props.bundle.images.hover;
    }
    return props.bundle.images.primary;
});

function formatPrice(value: number): string {
    return value?.toLocaleString('vi-VN') ?? '0';
}
</script>

<template>
    <div class="product-item overflow-hidden rounded-lg border group"
         @mouseenter="isHovered = true"
         @mouseleave="isHovered = false">

        <!-- Bundle Image -->
        <div class="relative aspect-square overflow-hidden">
            <div class="absolute top-4 left-4 z-10 rounded-full bg-indigo-600 px-2 py-0.5 text-sm font-bold uppercase tracking-wider text-white shadow-sm">
                Combo
            </div>
            <Link :href="`/goi-san-pham/${bundle.slug}`" class="block h-full w-full">
                <img
                    v-if="displayImage"
                    :src="displayImage"
                    :alt="bundle.name"
                    class="h-full w-full object-cover transition-all duration-300 group-hover:scale-105"
                    loading="lazy"
                />
                <div v-else class="flex h-full w-full items-center justify-center bg-muted">
                    <ImageOff class="h-12 w-12 text-muted-foreground" />
                </div>
            </Link>
        </div>

        <!-- Bundle Details -->
        <div class="space-y-2 p-3">
            <Link :href="`/goi-san-pham/${bundle.slug}`" class="block">
                <h3 class="transition-color line-clamp-2 text-sm font-medium">
                    {{ bundle.name }}
                </h3>
            </Link>

            <!-- Bundle Price -->
            <div class="flex items-baseline gap-2">
                <span class="text-base font-bold text-orange-500">
                    {{ formatPrice(bundle.price) }}đ
                </span>
            </div>

            <!-- Metrics removed as requested -->

            <!-- View Details Button -->
            <Button @click="router.visit(`/goi-san-pham/${bundle.slug}`)" variant="outline" class="w-full rounded-md border-gray-400 py-2 text-sm font-medium transition-colors">
                <span class="flex items-center justify-center gap-2">
                    Xem chi tiết gói
                    <Package class="h-4 w-4" />
                </span>
            </Button>
        </div>
    </div>
</template>
