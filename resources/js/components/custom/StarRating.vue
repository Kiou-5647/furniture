<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    rating?: number | string;
    max?: number;
    size?: string;
    count?: number;
    showRating?: boolean;
    showCount?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    rating: 0,
    max: 5,
    size: 'h-4 w-4',
    showCount: false,
    count: 0,
});

const numericRating = computed(() => parseFloat(String(props.rating)));

// Calculate how many full, half, and empty stars to show
const stars = computed(() => {
    const result = [];
    const rating = numericRating.value;

    for (let i = 1; i <= props.max; i++) {
        if (rating >= i) {
            result.push({ type: 'full', label: 'Full Star' });
        } else if (rating >= i - 0.5) {
            result.push({ type: 'half', label: 'Half Star' });
        } else {
            result.push({ type: 'empty', label: 'Empty Star' });
        }
    }
    return result;
});
</script>

<template>
    <div class="inline-flex items-center gap-1">
        <div class="flex items-center">
            <Star
                v-for="(star, index) in stars"
                :key="index"
                :class="cn(
                    size,
                    star.type === 'full' ? 'fill-orange-400 text-orange-400' :
                    star.type === 'half' ? 'fill-orange-400/50 text-orange-400' :
                    'text-zinc-300'
                )"
            />
        </div>
        <span v-if="showRating" class="ml-1 text-zinc-500 font-medium">
            {{ numericRating.toFixed(1) }}
        </span>
        <span v-if="showCount" class="ml-1 text-zinc-500 font-medium">
            ({{ count }})
        </span>
    </div>
</template>
