<template>
    <Carousel :opts="{ align: 'start' }" class="w-full">
      <CarouselContent class="-ml-4">
        <CarouselItem v-for="card in cards" :key="card.id" class="basis-1/2 sm:basis-1/3 lg:basis-1/4 pl-4">
          <slot :card="card" />
        </CarouselItem>
      </CarouselContent>
      <CarouselPrevious class="hidden sm:flex" />
      <CarouselNext class="hidden sm:flex" />
    </Carousel>
  </template>

  <script setup lang="ts">
  import {
      Carousel,
      CarouselContent,
      CarouselItem,
      CarouselNext,
      CarouselPrevious,
  } from '@/components/ui/carousel';

  defineProps<{
    cards: any[];
  }>();
  </script>
