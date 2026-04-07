<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Star, Type, Hash } from '@lucide/vue';
import { computed, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import StatusToggle from '@/components/custom/StatusToggle.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Field,
    FieldContent,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { slugify } from '@/lib/utils';
import { store, update } from '@/routes/employee/products/collections';
import type { Collection } from '@/types/collection';

const props = defineProps<{
    open: boolean;
    collection: Collection | null;
}>();

const emit = defineEmits(['close', 'delete']);

const form = useForm({
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    is_featured: false,
    image: null as File | null,
    banner: null as File | null,
    metadata: {},
});

watch(
    () => props.collection,
    (newCollection) => {
        if (newCollection && props.open) {
            form.slug = newCollection.slug;
            form.display_name = newCollection.display_name;
            form.description = newCollection.description ?? '';
            form.is_active = newCollection.is_active;
            form.is_featured = newCollection.is_featured;
            form.image = null;
            form.banner = null;
        } else if (!newCollection && props.open) {
            form.reset();
            form.is_active = true;
            form.is_featured = false;
        }
    },
    { immediate: true },
);

watch(
    () => form.display_name,
    (newName) => {
        if (!props.collection) {
            form.slug = slugify(newName);
        }
    },
);

const imagePreviewUrl = computed(() => {
    if (form.image) return URL.createObjectURL(form.image);
    return props.collection?.image_url ?? null;
});

const bannerPreviewUrl = computed(() => {
    if (form.banner) return URL.createObjectURL(form.banner);
    return props.collection?.banner_url ?? null;
});

function submit() {
    if (props.collection) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(update(props.collection).url, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(store().url, {
            onSuccess: () => closeModal(),
        });
    }
}

function closeModal() {
    form.reset();
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-[800px]"
        >
            <!-- Header -->
            <DialogHeader class="px-4 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <DialogTitle class="text-left text-lg sm:text-xl">
                            {{ collection ? 'Chỉnh sửa' : 'Thêm' }} bộ sưu tập
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{
                                collection
                                    ? 'Cập nhật thông tin bộ sưu tập'
                                    : 'Tạo bộ sưu tập sản phẩm mới'
                            }}
                        </DialogDescription>
                    </div>
                    <Badge
                        v-if="form.is_featured"
                        variant="default"
                        class="shrink-0 gap-1.5 bg-yellow-500 dark:bg-yellow-600"
                    >
                        <Star class="h-3.5 w-3.5 fill-current" />
                        <span class="hidden sm:inline">Nổi bật</span>
                    </Badge>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-[180px_1fr]">
                    <!-- Left: Images (desktop only) -->
                    <div class="hidden space-y-4 sm:block">
                        <div class="space-y-2">
                            <FieldLabel class="text-sm font-medium"
                                >Ảnh đại diện</FieldLabel
                            >
                            <ImageUploader
                                v-model="form.image"
                                :preview-url="imagePreviewUrl"
                                aspect-ratio="square"
                            />
                        </div>

                        <div class="space-y-2">
                            <FieldLabel class="text-sm font-medium"
                                >Banner</FieldLabel
                            >
                            <ImageUploader
                                v-model="form.banner"
                                :preview-url="bannerPreviewUrl"
                                aspect-ratio="wide"
                            />
                        </div>
                    </div>

                    <!-- Right: Fields -->
                    <div class="space-y-4">
                        <!-- Name + Slug -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <Field>
                                <FieldLabel>
                                    <Type
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Tên hiển thị
                                    <span class="text-destructive">*</span>
                                </FieldLabel>
                                <FieldContent>
                                    <Input
                                        v-model="form.display_name"
                                        placeholder="VD: Bộ sưu tập mùa hè"
                                        required
                                    />
                                    <FieldError
                                        :errors="[form.errors.display_name]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <Hash
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Slug
                                </FieldLabel>
                                <FieldContent>
                                    <Input
                                        v-model="form.slug"
                                        placeholder="VD: bo-suu-tap-mua-he"
                                        class="font-mono text-sm"
                                    />
                                    <FieldError :errors="[form.errors.slug]" />
                                </FieldContent>
                            </Field>
                        </div>

                        <!-- Description -->
                        <Field>
                            <FieldLabel>Mô tả</FieldLabel>
                            <FieldContent>
                                <Textarea
                                    v-model="form.description"
                                    placeholder="Mô tả chủ đề, phong cách của bộ sưu tập..."
                                    class="min-h-[60px] resize-y text-sm"
                                    rows="2"
                                />
                                <FieldError
                                    :errors="[form.errors.description]"
                                />
                            </FieldContent>
                        </Field>

                        <!-- Toggles: 2 items = even grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <StatusToggle
                                v-model="form.is_featured"
                                label="Nổi bật"
                                description="Ưu tiên trang chủ"
                                id="is_featured"
                            />
                            <StatusToggle
                                v-model="form.is_active"
                                label="Kích hoạt"
                                description="Ẩn bộ sưu tập khỏi website khi tắt"
                                id="is_active"
                            />
                        </div>
                        <!-- Mobile: Images on top -->
                        <div class="mb-4 flex flex-col gap-3 sm:hidden">
                            <div class="space-y-1.5">
                                <FieldLabel class="text-sm font-medium"
                                    >Ảnh đại diện</FieldLabel
                                >
                                <ImageUploader
                                    v-model="form.image"
                                    :preview-url="imagePreviewUrl"
                                    aspect-ratio="square"
                                    class="w-60 justify-self-center"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <FieldLabel class="text-sm font-medium"
                                    >Banner</FieldLabel
                                >
                                <ImageUploader
                                    v-model="form.banner"
                                    :preview-url="bannerPreviewUrl"
                                    aspect-ratio="wide"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <DialogFooter class="mt-6 border-t pt-4">
                    <div class="flex w-full gap-2 sm:justify-between">
                        <Button
                            v-if="collection"
                            type="button"
                            variant="outline"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            @click="emit('delete', collection)"
                        >
                            Xóa bộ sưu tập
                        </Button>
                        <div class="ml-auto flex gap-2">
                            <Button
                                type="button"
                                variant="ghost"
                                @click="closeModal"
                            >
                                Hủy
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="min-w-[120px]"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                {{
                                    collection
                                        ? 'Lưu thay đổi'
                                        : 'Tạo bộ sưu tập'
                                }}
                            </Button>
                        </div>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
