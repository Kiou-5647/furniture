<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <AppLogoIcon class="size-5 bg-none text-white dark:text-black" />
    </div>
    <div class="ml-1 items-center grid flex-1 text-left text-lg">
        <span class="truncate font-extrbold"
            >{{ page.props.name }}
        </span>
    </div>
</template>
