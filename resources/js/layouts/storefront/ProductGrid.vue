<template>
    <div :class="['grid gap-4', gridClasses]">
      <div v-for="card in cards" :key="card.id" class="col-span-1">
        <slot :card="card" />
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { computed } from 'vue';

  const props = defineProps<{
    cards: any[];
    cols?: number;
  }>();

  const gridClasses = computed(() => {
    // We define a base grid for small containers (2 cols)
    // and then use @container queries for larger widths.
    const colMap: Record<number, string> = {
      1: 'grid-cols-1',
      2: 'grid-cols-2',
      3: 'grid-cols-3',
      4: 'grid-cols-2 @lg:grid-cols-4',
      5: 'grid-cols-2 @lg:grid-cols-5',
    };

    return colMap[props.cols || 4] || colMap[4];
  });
  </script>
