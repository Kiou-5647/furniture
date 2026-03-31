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
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import InputError from '@/components/InputError.vue';
import type { Lookup } from '@/types/lookup';
import { store, update } from '@/routes/employee/settings/lookups';
import { ImageIcon, X, Loader2, Globe } from 'lucide-vue-next';
import { slugify } from '@/lib/utils';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{
    open: boolean;
    namespace: string;
    display_namespace: string;
    lookup: Lookup | null;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    namespace: props.namespace,
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    image: null as File | null,
    metadata: {
        hex_code: '',
        title: '',
        description: '',
        canonical: '',
        robots: '',
    },
});

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');

watch(() => form.display_name, (newName) => {
    form.slug = slugify(newName);
});

// Watch for lookup changes and populate form
watch(
    () => props.lookup,
    (newLookup) => {
        if (newLookup && props.open) {
            form.namespace = newLookup.namespace;
            form.slug = newLookup.slug;
            form.display_name = newLookup.display_name;
            form.description = newLookup.description ?? '';
            form.is_active = newLookup.is_active;
            form.image = null;
            form.metadata = {
                title: newLookup.metadata?.title ?? '',
                description: newLookup.metadata?.description ?? '',
                canonical: newLookup.metadata?.canonical ?? '',
                robots: newLookup.metadata?.robots ?? '',
                hex_code: newLookup.metadata?.hex_code ?? '',
            };
        } else if (!newLookup && props.open) {
            // Create mode
            form.reset();
            form.namespace = props.namespace;
            form.is_active = true;
        }
    },
    { immediate: true }
);

watch(() => form.display_name, (newName) => {
    form.slug = slugify(newName);
    form.metadata.title = newName.substring(0, 254);
});

watch(() => form.description, (newDesc) => {
    form.metadata.description = newDesc.substring(0, 499);
});

const previewUrl = computed(() => {
    if (form.image) return URL.createObjectURL(form.image);
    return props.lookup?.image_url ?? null;
});

function onFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.image = target.files[0];
    }
}

function submit() {
    if (props.lookup) {
        form.put(update(props.lookup).url, {
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

const placeholders = computed(() => {
    const configs: Record<string, { slug: string, name: string }> = {
        'mau-sac': { slug: 'do-do', name: 'Đỏ Đô' },
        'phong': { slug: 'phong-khach', name: 'Phòng Khách' },
        'phong-cach': { slug: 'hien-dai', name: 'Hiện Đại' },
    };
    return configs[props.namespace] ?? { slug: 'ma-dinh-danh', name: 'Tên hiển thị' };
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="sm:max-w-137.5 max-h-[95vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ lookup ? 'Chỉnh sửa' : 'Thêm' }} {{ display_namespace }}</DialogTitle>
                <DialogDescription>
                    Quản lý thông tin hiển thị và cấu hình tối ưu hóa tìm kiếm (SEO).
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <!-- Section 1: Thông tin cơ bản -->
                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="display_name">Tên hiển thị <span class="text-destructive">*</span></Label>
                        <Input id="display_name" v-model="form.display_name" :placeholder="`VD: ${placeholders.name}`"
                            required />
                        <InputError :message="form.errors.display_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">Khóa (Slug)</Label>
                        <Input id="slug" v-model="form.slug" :placeholder="`VD: ${placeholders.slug}`" />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Mô tả hiển thị</Label>
                        <Textarea id="description" v-model="form.description"
                            placeholder="Mô tả ngắn gọn về danh mục này..." class="h-20 resize-none" />
                        <InputError :message="form.errors.description" />
                    </div>
                </div>

                <!-- Section 2: Metadata & Media -->
                <div class="grid gap-4 border-t pt-6">
                    <div v-if="namespace === 'mau-sac'" class="grid gap-2">
                        <Label for="hex_code">Mã màu HEX</Label>
                        <div class="flex gap-2">
                            <Input id="hex_code" v-model="form.metadata.hex_code" placeholder="#FFFFFF"
                                class="flex-1" />
                            <div class="w-10 h-10 rounded-md border"
                                :style="{ backgroundColor: form.metadata.hex_code || '#eee' }" />
                        </div>
                        <InputError :message="form.errors['metadata.hex_code']" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Hình ảnh đại diện (Banner)</Label>
                        <div class="flex items-center gap-4">
                            <div
                                class="relative w-24 h-24 rounded-lg border bg-muted flex items-center justify-center overflow-hidden group">
                                <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                                <ImageIcon v-else class="w-10 h-10 text-muted-foreground/30" />
                                <button v-if="form.image" @click="form.image = null" type="button"
                                    class="absolute top-1 right-1 bg-black/60 text-white rounded-full p-1 opacity-0 group-hover:opacity-100">
                                    <X class="w-3 h-3" />
                                </button>
                            </div>
                            <div class="flex-1 space-y-2">
                                <input type="file" id="image" ref="fileInput" class="hidden" @change="onFileSelect"
                                    accept="image/*" />
                                <Button type="button" variant="outline" size="sm" @click="fileInput?.click()">Chọn
                                    ảnh</Button>
                                <p class="text-[11px] text-muted-foreground leading-tight">Khuyên dùng ảnh ngang (16:9)
                                    để làm banner Landing Page.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Cấu hình SEO (Dùng Accordion để tiết kiệm diện tích) -->
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
                                <Label for="description">Mô tả SEO (Meta Description)</Label>
                                <Textarea id="description" v-model="form.metadata.description"
                                    placeholder="Nhập mô tả tóm tắt cho Google..." class="h-20 resize-none" />
                                <p class="text-[10px] text-muted-foreground">Khuyên dùng: Dưới 160 ký tự.</p>
                            </div>
                            <div class="grid gap-2 pt-2">
                                <Label>Khóa Canonical (Tùy chọn)</Label>
                                <Input v-model="form.metadata.canonical"
                                    placeholder="https://yourdomain.com/danh-muc/..." />
                                <p class="text-[10px] text-muted-foreground italic">Để trống để tự động lấy link
                                    website.</p>
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

                <!-- Section 4: Trạng thái -->
                <div class="flex items-center justify-between rounded-lg border p-4 bg-muted/20">
                    <div class="space-y-0.5">
                        <Label class="text-base">Kích hoạt mục này</Label>
                        <p class="text-xs text-muted-foreground">Nếu tắt, mục này sẽ không xuất hiện trên website.</p>
                    </div>
                    <Switch id="is_active" v-model="form.is_active" @update:model-value="form.is_active = $event" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="closeModal">Hủy</Button>
                    <Button type="submit" :disabled="form.processing" class="min-w-30">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ lookup ? 'Lưu thay đổi' : 'Tạo mục mới' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
