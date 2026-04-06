<template>
    <Button v-if="hasActiveFilters" variant="outline" size="sm" @click="emit('reset')"
        class="max-w-28 h-8 px-2 text-muted-foreground hover:text-foreground lg:px-3">
        Xóa lọc
        <X class="ml-2 h-4 w-4" />
    </Button>

</template>
<script setup lang="ts">
import { X } from '@lucide/vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    hasActiveFilters: boolean;
}>();

const emit = defineEmits(['reset']);
</script>
