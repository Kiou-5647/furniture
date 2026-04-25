<template>
    <section class="py-12 @container">
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900">{{ title }}</h2>
        <Link :href="moreUrl" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors flex items-center gap-1">
          Xem thêm <span class="text-lg">→</span>
        </Link>
      </div>

      <component :is="layoutComponent" :cards="cards" v-bind="layoutProps">
        <template #default="{ card }">
          <!-- Logic to split based on backend 'type' -->
          <ProductCard v-if="card.type === 'product'" :product-card="card" />
          <BundleCard v-else-if="card.type === 'bundle'" :bundle="card" />
        </template>
      </component>
    </section>
  </template>

  <script setup lang="ts">
  import { Link } from '@inertiajs/vue3';
  import { computed } from 'vue';
  import BundleCard from '@/components/custom/product/BundleCard.vue';
  import ProductCard from '@/components/custom/product/ProductCard.vue';
  import ProductCarousel from './ProductCarousel.vue';
  import ProductGrid from './ProductGrid.vue';

  const props = defineProps<{
    title: string;
    cards: any[];
    moreUrl: string;
    layout?: 'grid' | 'carousel';
    layoutProps?: Record<string, any>;
  }>();

  const layoutComponent = computed(() => props.layout === 'carousel' ? ProductCarousel : ProductGrid);
  </script>
