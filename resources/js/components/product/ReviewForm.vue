<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import reviews from '@/routes/customer/reviews';
import StarRating from '../custom/StarRating.vue';

interface Props {
    reviewableId: string;
    reviewableType: string;
}

const props = defineProps<Props>();

const form = useForm({
    reviewable_id: props.reviewableId,
    reviewable_type: props.reviewableType,
    rating: 0,
    comment: '',
});

const isSubmitting = ref(false);

function setRating(val: number) {
    form.rating = val;
}

function submit() {
    if (form.rating === 0) {
        toast.error('Please select a star rating before submitting.');
        return;
    }

    isSubmitting.value = true;
    form.post(reviews.store().url, {
        onSuccess: () => {
            toast.success('Your review has been submitted.');
            form.reset('rating', 'comment');
        },
        onError: (errors) => {
            toast.error(Object.values(errors).flat().join(' '));
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-6 rounded-xl border border-zinc-200 bg-zinc-50/50 p-6">
        <div class="flex flex-col gap-1">
            <h3 class="text-lg font-bold text-zinc-900">Write a Review</h3>
            <p class="text-sm text-zinc-500">Share your experience with this product.</p>
        </div>

        <div class="space-y-4">
            <div class="flex flex-col gap-2">
                <Label class="text-sm font-medium">Rating</Label>
                <div class="flex gap-1">
                    <!-- Simple star selector -->
                    <button
                        v-for="i in 5"
                        :key="i"
                        @click="setRating(i)"
                        class="transition-transform hover:scale-110"
                    >
                        <StarRating
                            :rating="form.rating >= i ? i : (form.rating >= i - 0.5 ? i - 0.5 : 0)"
                            :max="1"
                            class="cursor-pointer"
                        />
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <Label for="comment" class="text-sm font-medium">Your Feedback</Label>
                <Textarea
                    id="comment"
                    v-model="form.comment"
                    placeholder="What did you like or dislike?"
                    class="resize-none"
                />
            </div>

            <Button
                @click="submit"
                :disabled="isSubmitting"
                class="w-full rounded-full bg-orange-400 hover:bg-orange-500 text-white"
            >
                {{ isSubmitting ? 'Submitting...' : 'Post Review' }}
            </Button>
        </div>
    </div>
</template>
