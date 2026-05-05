<template>
    <div class="flex flex-col gap-4 p-5 rounded-2xl border bg-white dark:bg-zinc-900 dark:border-zinc-800 h-full">
        <!-- Header: User Info -->
        <div class="flex items-center gap-3">
            <!-- Avatar -->
            <div class="h-10 w-10 shrink-0 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 font-bold text-sm ring-2 ring-white dark:ring-zinc-800">
                {{ avatarInitials }}
            </div>
        <div class="min-w-0">
                <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate">
                    {{ review.customer_name }}
                </h4>
                <div class="flex flex-col">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                        {{ review.variant_name }}
                    </p>
                    <p class="text-[10px] text-zinc-600 dark:text-zinc-500 font-medium uppercase tracking-tight">
                        SKU: {{ review.sku }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Content: Rating & Comment -->
        <div class="flex flex-col gap-3">
            <div class="flex gap-1">
                <template v-for="star in 5" :key="star">
                    <svg 
                        :class="[
                            'w-3.5 h-3.5', 
                            star <= review.rating ? 'text-orange-500 fill-orange-500' : 'text-zinc-300 dark:text-zinc-700 fill-zinc-300 dark:fill-zinc-700'
                        ]" 
                        xmlns="http://www.w3.org/2000/svg" 
                        viewBox="0 0 24 24"
                    >
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                    </svg>
                </template>
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed line-clamp-4 italic">
                "{{ review.comment }}"
            </p>
        </div>
        
        <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <span class="text-[10px] uppercase tracking-wider font-medium text-zinc-400">
                {{ review.review_date }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Review {
    id: string;
    customer_name: string;
    review_date: string;
    variant_name: string;
    sku: string;
    rating: number;
    comment: string;
}

const props = defineProps<{
    review: Review;
}>();

const avatarInitials = computed(() => {
    const name = props.review.customer_name;
    if (!name) return '??';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});
</script>
