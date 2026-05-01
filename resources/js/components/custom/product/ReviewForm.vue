<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog, DialogContent, DialogDescription,
    DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import reviews from '@/routes/customer/reviews';
import StarRating from '../StarRating.vue';

interface Props {
    modelValue: boolean; // For v-model:open
    variantId: string;
    initialReview?: {
        id: string;
        variant_id: string;
        rating: number;
        comment: string;
        is_published: boolean;
    };
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'close']);

const form = useForm({
    variant_id: props.initialReview?.variant_id ?? props.variantId,
    rating: props.initialReview?.rating ?? 0,
    comment: props.initialReview?.comment ?? '',
    is_published: props.initialReview?.is_published ?? true,
});

const getOriginalState = () => JSON.stringify({
    rating: props.initialReview?.rating ?? 0,
    comment: props.initialReview?.comment ?? '',
});

const isDirty = computed(() => {
    return JSON.stringify({
        rating: form.rating,
        comment: form.comment,
    }) !== getOriginalState();
});

const isReadonly = computed(() => props.initialReview?.is_published);
const isSubmitting = ref(false);
const isCancelDialogOpen = ref(false);

watch(
    () => props.initialReview,
    (newReview) => {
        if (newReview) {
            form.rating = newReview.rating;
            form.comment = newReview.comment;
            form.is_published = newReview.is_published;
        } else {
            form.comment = '';
            form.rating = 0;
            form.is_published = true;
        }
    },
    { immediate: true }
);

watch(
    () => props.variantId,
    (newId) => {
        form.variant_id = newId;
    },
    { immediate: true }
);

function setRating(val: number) {
    form.rating = val;
}

function submit() {
    if (form.rating === 0) {
        toast.error('Vui lòng chọn đánh giá');
        return;
    }

    form.is_published = true;

    isSubmitting.value = true;

    const url = props.initialReview
        ? reviews.update(props.initialReview.id).url
        : reviews.store().url;

    // FIX: Call the method directly on the form object
    if (props.initialReview) {
        form.patch(url, {
            onSuccess: () => {
                toast.success('Cập nhật đánh giá thành công!');
                emit('close');
            },
            onError: (errors) => {
                toast.error(Object.values(errors).flat().join(' '));
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else {
        form.post(url, {
            onSuccess: () => {
                toast.success('Đăng tải đánh giá thành công!');
                form.reset('rating', 'comment');
                emit('close');
            },
            onError: (errors) => {
                toast.error(Object.values(errors).flat().join(' '));
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    }
}

function confirmDraft() {
    const isNew = !props.initialReview;

    if (isNew) {
        form.is_published = false;
        form.post(reviews.store().url, {
            onSuccess: () => {
                toast.success('Đã lưu bản nháp');
                isCancelDialogOpen.value = false;
                emit('update:modelValue', false);
            }
        });
    } else {
        form.is_published = false;
        form.patch(reviews.update(props.initialReview!.id).url, {
            onSuccess: () => {
                toast.success('Đã lưu bản nháp');
                isCancelDialogOpen.value = false;
                emit('update:modelValue', false);
            }
        });
    }
}

function confirmRemove() {
    form.delete(reviews.destroy(props.initialReview!.id).url, {
        onSuccess: () => {
            toast.success('Đã xóa đánh giá');
            isCancelDialogOpen.value = false;
            emit('update:modelValue', false);
        },
    });
}

function handleClose() {
    if (isDirty.value) {
        isCancelDialogOpen.value = true;
    } else {
        // Otherwise, just close normally
        emit('update:modelValue', false);
    }
}
</script>
<template>
    <Dialog :open="modelValue" @update:open="val => emit('update:modelValue', val)">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Đánh giá sản phẩm</DialogTitle>
                <DialogDescription>
                    Chia sẻ suy nghĩ của bạn về sản phẩm này.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="flex flex-col gap-2">
                    <Label class="text-sm font-medium">Đánh giá</Label>
                    <div class="flex gap-1">
                        <button
                            v-for="i in 5"
                            :key="i"
                            @click="setRating(i)"
                            :disabled="isReadonly"
                            class="transition-transform hover:scale-110 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <StarRating
                                :rating="form.rating >= i ? i : form.rating >= i - 0.5 ? i - 0.5 : 0"
                                :max="1"
                                class="cursor-pointer"
                            />
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="comment" class="text-sm font-medium">Bình luận</Label>
                    <Textarea
                        id="comment"
                        v-model="form.comment"
                        :disabled="isReadonly"
                        placeholder="Đánh giá của bạn về sản phẩm..."
                        class="resize-none"
                    />
                </div>

                <div class="flex gap-2">
                    <Button @click="handleClose" variant="ghost" class="flex-1 rounded-full">
                        Đóng
                    </Button>
                    <Button
                        @click="submit"
                        :disabled="isSubmitting || isReadonly"
                        class="flex-1 rounded-full bg-orange-400 text-white hover:bg-orange-500"
                    >
                        {{ isSubmitting ? 'Đang đăng tải...' : (props.initialReview ? 'Cập nhật' : 'Đăng đánh giá') }}
                    </Button>
                </div>
                <p v-if="isReadonly" class="text-center text-xs text-muted-foreground">
                    Đánh giá này đã được đăng và không thể chỉnh sửa.
                </p>
            </div>

            <!-- Internal Confirmation Dialog -->
            <Dialog v-model:open="isCancelDialogOpen">
                <DialogContent class="max-w-[300px]">
                    <DialogHeader>
                        <DialogTitle>Xác nhận hủy đánh giá</DialogTitle>
                        <DialogDescription>
                            Bạn muốn lưu bản nháp để chỉnh sửa sau hay xóa hoàn toàn đánh giá này?
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="flex flex-col sm:flex-row gap-2">
                        <Button variant="outline" class="flex-1" @click="isCancelDialogOpen = false;">
                            Quay lại
                        </Button>
                        <Button v-if="props.initialReview" variant="ghost" class="flex-1 text-red-500 hover:text-red-600 hover:bg-red-50" @click="confirmRemove">
                            Xóa bỏ
                        </Button>
                        <Button class="flex-1 bg-orange-400 hover:bg-orange-500 text-white" @click="confirmDraft">
                            Lưu bản nháp
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </DialogContent>
    </Dialog>
</template>
