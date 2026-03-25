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
import type { Category } from '@/types/category';
import type { ProductType } from '@/types';
import { store, update } from '@/routes/employee/products/categories';
import { ImageIcon, X, Loader2, Globe, LayoutGrid, Package, Sparkles, Lamp } from 'lucide-vue-next';
import { slugify } from '@/lib/utils';
import MultiSelect from '@/components/custom/MultiSelect.vue';

const props = defineProps<{
    open: boolean;
    categoryGroups: any[];
    roomOptions: any[];
    category: Category | null;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    group_id: null as number | null,
    room_ids: [] as number[],
    product_type: 'noi-that' as ProductType,
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    image_path: null as File | null,
    metadata: {
        title: '',
        description: '',
        canonical: '',
        robots: '',
    },
});

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');

const typeOptions = [
    { label: 'Nội thất', value: 'noi-that', icon: LayoutGrid },
    { label: 'Phụ kiện', value: 'phu-kien', icon: Package },
    { label: 'Trang trí', value: 'trang-tri', icon: Sparkles },
    { label: 'Thắp sáng', value: 'thap-sang', icon: Lamp },
];

// Watch for category changes and populate form
watch(
    () => props.category,
    (newCategory) => {
        if (newCategory && props.open) {
            form.group_id = newCategory.group_id;
            form.room_ids = newCategory.room_ids || newCategory.rooms?.map(r => r.id) || [];
            form.product_type = newCategory.product_type;
            form.slug = newCategory.slug;
            form.display_name = newCategory.display_name;
            form.description = newCategory.description ?? '';
            form.is_active = newCategory.is_active;
            form.image_path = null;
            form.metadata = {
                title: newCategory.metadata?.title ?? '',
                description: newCategory.metadata?.description ?? '',
                canonical: newCategory.metadata?.canonical ?? '',
                robots: newCategory.metadata?.robots ?? '',
            };
        } else if (!newCategory && props.open) {
            form.reset();
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
    if (form.image_path) return URL.createObjectURL(form.image_path);
    return props.category?.image_path ?? null;
});

function onFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.image_path = target.files[0];
    }
}

function submit() {
    if (props.category) {
        form.put(update(props.category.id).url, {
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
        'noi-that': { slug: 'sofa-da', name: 'Sofa da cao cấp' },
        'phu-kien': { slug: 'goi-tua-lung', name: 'Gối tựa lưng' },
        'trang-tri': { slug: 'den-trang-tri', name: 'Đèn trang trí' },
        'thap-sang': { slug: 'den-ban', name: 'Đèn bàn' },
    };
    return configs[props.category?.product_type || form.product_type] ?? { slug: 'ten-san-pham', name: 'Tên sản phẩm' };
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ category ? 'Chỉnh sửa' : 'Thêm' }} danh mục</DialogTitle>
                <DialogDescription>
                    Phân loại sản phẩm và cấu hình hiển thị Landing Page cho danh mục.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6 py-4">
                <div class="grid grid-cols-2 gap-8">
                    <div class="grid gap-2">
                        <Label>Nhóm danh mục <span class="text-destructive">*</span></Label>
                        <Select v-model="form.group_id" @update:model-value="(val) => form.group_id = Number(val)">
                            <SelectTrigger class="w-auto">
                                <SelectValue placeholder="Chọn nhóm..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="g in categoryGroups" :key="g.id" :value="g.id">
                                    {{ g.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.group_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Loại sản phẩm <span class="text-destructive">*</span></Label>
                        <Select v-model="form.product_type">
                            <SelectTrigger class="w-auto">
                                <SelectValue placeholder="Chọn loại..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in typeOptions" :key="t.value" :value="t.value">
                                    <div class="flex items-center gap-2 text-sm">
                                        <component :is="t.icon" class="w-4 h-4 text-muted-foreground" />
                                        {{ t.label }}
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.product_type" />
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label>Phòng</Label>
                        <MultiSelect v-model="form.room_ids" title="Chọn phòng" placeholder="Phòng phù hợp..."
                            :options="roomOptions.map(r => ({ label: r.display_name, value: r.id }))" />
                        <InputError :message="form.errors.room_ids" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="display_name">Tên hiển thị <span class="text-destructive">*</span></Label>
                        <Input id="display_name" v-model="form.display_name" :placeholder="`VD: ${placeholders.name}`"
                            required />
                        <InputError :message="form.errors.display_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">Slug</Label>
                        <Input id="slug" v-model="form.slug" :placeholder="`VD: ${placeholders.slug}`" :disabled="!!category" />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Mô tả tóm tắt</Label>
                        <Textarea id="description" v-model="form.description"
                            placeholder="Mô tả ngắn gọn về danh mục này..." class="h-20 resize-none" />
                        <InputError :message="form.errors.description" />
                    </div>
                </div>

                <div class="grid gap-2 border-t pt-6">
                    <Label>Hình ảnh Banner</Label>
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
                            <p class="text-[10px] text-muted-foreground leading-tight">Khuyên dùng tỷ lệ 3:1 cho banner
                                Landing Page.</p>
                        </div>
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
                                <Label for="description">Mô tả SEO (Meta Description)</Label>
                                <Textarea id="description" v-model="form.metadata.description"
                                    placeholder="Nhập mô tả tóm tắt cho Google..." class="h-20 resize-none" />
                                <p class="text-[10px] text-muted-foreground">Khuyên dùng: Dưới 160 ký tự.</p>
                            </div>
                            <div class="grid gap-2 pt-2">
                                <Label>Đường dẫn Canonical (Tùy chọn)</Label>
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

                <div class="flex items-center justify-between rounded-lg border p-4 bg-muted/20">
                    <div class="space-y-0.5">
                        <Label class="text-base font-semibold">Kích hoạt danh mục</Label>
                        <p class="text-xs text-muted-foreground">Tắt để ẩn danh mục này khỏi website.</p>
                    </div>
                    <Switch id="is_active" v-model="form.is_active" @update:checked="form.is_active = $event" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="closeModal">Hủy</Button>
                    <Button type="submit" :disabled="form.processing" class="min-w-[120px]">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ category ? 'Lưu thay đổi' : 'Tạo danh mục' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
