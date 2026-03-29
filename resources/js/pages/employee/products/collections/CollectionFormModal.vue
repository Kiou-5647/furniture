<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, useTemplateRef, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import InputError from '@/components/InputError.vue';
import type { Collection } from '@/types/collection';
import { store, update } from '@/routes/employee/products/collections';
import { ImageIcon, X, Loader2, Globe, Star } from 'lucide-vue-next';
import { slugify } from '@/lib/utils';

const props = defineProps<{
    open: boolean;
    collection: Collection | null;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    is_featured: false,
    image_path: null as File | null,
    metadata: {
        title: '',
        description: '',
        canonical: '',
        robots: 'index, follow',
    },
});

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');

// Watch for collection changes and populate form
watch(
    () => props.collection,
    (newCollection) => {
        if (newCollection && props.open) {
            form.slug = newCollection.slug;
            form.display_name = newCollection.display_name;
            form.description = newCollection.description ?? '';
            form.is_active = newCollection.is_active;
            form.is_featured = newCollection.is_featured;
            form.image_path = null;
            form.metadata = {
                title: newCollection.metadata?.title ?? '',
                description: newCollection.metadata?.description ?? '',
                canonical: newCollection.metadata?.canonical ?? '',
                robots: newCollection.metadata?.robots ?? 'index, follow',
            };
        } else if (!newCollection && props.open) {
            form.reset();
            form.is_active = true;
            form.is_featured = false;
        }
    },
    { immediate: true }
);

watch(() => form.display_name, (newName) => {
    if (!props.collection) {
        form.slug = slugify(newName);
        form.metadata.title = newName.substring(0, 254);
    }
});

watch(() => form.description, (newDesc) => {
    if (!props.collection) {
        form.metadata.description = newDesc.substring(0, 499);
    }
});

const previewUrl = computed(() => {
    if (form.image_path) return URL.createObjectURL(form.image_path);
    return props.collection?.image_path ?? null;
});

function onFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.image_path = target.files[0];
    }
}

function submit() {
    if (props.collection) {
        // Use post for multipart/form-data with _method=PUT
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(update(props.collection.id).url, {
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
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ collection ? 'Chỉnh sửa' : 'Thêm' }} bộ sưu tập</DialogTitle>
                <DialogDescription>
                    Tạo các bộ sưu tập sản phẩm theo chủ đề, mùa hoặc phong cách thiết kế.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="display_name">Tên hiển thị <span class="text-destructive">*</span></Label>
                        <Input id="display_name" v-model="form.display_name" placeholder="VD: Bộ sưu tập mùa hè 2026"
                            required />
                        <InputError :message="form.errors.display_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">Slug</Label>
                        <Input id="slug" v-model="form.slug" placeholder="VD: bo-suu-tap-mua-he-2026"
                            :disabled="!!collection" />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Mô tả tóm tắt</Label>
                        <Textarea id="description" v-model="form.description"
                            placeholder="Mô tả ngắn gọn về bộ sưu tập này..." class="h-24 resize-none" />
                        <InputError :message="form.errors.description" />
                    </div>
                </div>

                <div class="grid gap-2 border-t pt-6">
                    <Label>Hình ảnh đại diện</Label>
                    <div class="flex items-center gap-4">
                        <div
                            class="relative w-32 h-20 rounded-lg border bg-muted flex items-center justify-center overflow-hidden">
                            <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                            <ImageIcon v-else class="w-8 h-8 text-muted-foreground/30" />
                            <button v-if="form.image_path" @click="form.image_path = null" type="button"
                                class="absolute top-1 right-1 bg-black/60 text-white rounded-full p-1 opacity-0 hover:opacity-100 transition-opacity">
                                <X class="w-3 h-3" />
                            </button>
                        </div>
                        <div class="flex-1 space-y-1">
                            <input type="file" ref="fileInput" class="hidden" @change="onFileSelect" accept="image/*" />
                            <Button type="button" variant="outline" size="sm" @click="fileInput?.click()">Thay đổi
                                ảnh</Button>
                            <p class="text-[10px] text-muted-foreground leading-tight">Khuyên dùng tỷ lệ 16:9 hoặc 3:1
                                cho banner.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-lg border p-4 bg-muted/20">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <Star class="w-4 h-4 text-yellow-500 fill-yellow-500" />
                                <Label class="text-base font-semibold">Bộ sưu tập nổi bật</Label>
                            </div>
                            <p class="text-xs text-muted-foreground">Ưu tiên hiển thị tại trang chủ hoặc vị trí đặc
                                biệt.</p>
                        </div>
                        <Switch id="is_featured" v-model="form.is_featured"
                            @update:checked="form.is_featured = $event" />
                    </div>

                    <div class="flex items-center justify-between rounded-lg border p-4 bg-muted/20">
                        <div class="space-y-0.5">
                            <Label class="text-base font-semibold">Kích hoạt</Label>
                            <p class="text-xs text-muted-foreground">Tắt để ẩn bộ sưu tập này khỏi website.</p>
                        </div>
                        <Switch id="is_active" v-model="form.is_active" @update:checked="form.is_active = $event" />
                    </div>
                </div>

                <Accordion type="single" collapsible class="border rounded-lg px-4 bg-muted/10">
                    <AccordionItem value="seo" class="border-none">
                        <AccordionTrigger class="hover:no-underline py-3">
                            <div class="flex items-center gap-2 text-sm font-semibold text-primary">
                                <Globe class="w-4 h-4" />
                                Tối ưu hóa SEO
                            </div>
                        </AccordionTrigger>
                        <AccordionContent class="pb-4 space-y-4">
                            <div class="grid gap-2">
                                <Label for="title">Tiêu đề SEO (Thẻ Title)</Label>
                                <Input id="title" v-model="form.metadata.title"
                                    placeholder="Mặc định lấy Tên hiển thị" />
                                <p class="text-[10px] text-muted-foreground">Tiêu đề hiện trên tab trình duyệt và kết
                                    quả Google.</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="seo_description">Mô tả SEO (Meta Description)</Label>
                                <Textarea id="seo_description" v-model="form.metadata.description"
                                    placeholder="Nhập mô tả tóm tắt cho Google..." class="h-20 resize-none" />
                                <p class="text-[10px] text-muted-foreground">Khuyên dùng: Dưới 160 ký tự.</p>
                            </div>
                            <div class="grid gap-2 pt-2">
                                <Label>Đường dẫn Canonical (Tùy chọn)</Label>
                                <Input v-model="form.metadata.canonical"
                                    placeholder="https://yourdomain.com/bo-suu-tap/..." />
                            </div>

                            <div class="grid gap-2">
                                <Label>Cấu hình Robots</Label>
                                <Select v-model="form.metadata.robots">
                                    <SelectTrigger>
                                        <SelectValue placeholder="index, follow" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="index, follow">Index, Follow (Mặc định)</SelectItem>
                                        <SelectItem value="noindex, follow">Noindex, Follow (Ẩn khỏi Google)
                                        </SelectItem>
                                        <SelectItem value="noindex, nofollow">Noindex, Nofollow (Chặn hoàn toàn)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="closeModal">Hủy</Button>
                    <Button type="submit" :disabled="form.processing" class="min-w-[120px]">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ collection ? 'Lưu thay đổi' : 'Tạo bộ sưu tập' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
