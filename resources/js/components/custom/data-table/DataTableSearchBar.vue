<template>
    <div class="flex flex-1 items-center space-x-2">
        <div class="relative w-full max-w-lg">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input :model-value="search" @update:model-value="emit('update:search', $event)" id="search" placeholder="Tìm kiếm..."
                class="pl-9 h-9" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { Search } from '@lucide/vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    search: string;
}>();

const emit = defineEmits(['update:search']);
</script>
