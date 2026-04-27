<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Loader2,
    LayoutGrid,
    Package,
    Sparkles,
    Lamp,
    Tag,
    Hash,
    Type,
    Sofa,
    Home,
} from '@lucide/vue';
import { computed, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import MultiSelect from '@/components/custom/MultiSelect.vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { slugify } from '@/lib/utils';
import { store, update } from '@/routes/employee/categories';
import type { ProductType } from '@/types';
import type { Category } from '@/types/category';

const props = defineProps<{
    open: boolean;
    categoryGroups: any[];
    roomOptions: any[];
    specOptions: any[];
    category: Category | null;
}>();

const emit = defineEmits(['close', 'delete']);

const form = useForm({
    group_id: null as string | null,
    room_ids: null as string[] | null,
    filterable_specs: null as string[] | null,
    product_type: 'noi-that' as ProductType,
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    image: null as File | null,
});

const typeOptions = [
    { label: 'Nội thất', value: 'noi-that', icon: Sofa },
    { label: 'Phụ kiện', value: 'phu-kien', icon: Package },
    { label: 'Trang trí', value: 'trang-tri', icon: Sparkles },
    { label: 'Thắp sáng', value: 'thap-sang', icon: Lamp },
];

watch(
    () => props.category,
    (newCategory) => {
        if (newCategory && props.open) {
            form.group_id = newCategory.group_id;
            form.room_ids = newCategory.rooms?.map((r) => r.id) ?? [];
            form.filterable_specs =
                newCategory.filterable_specs?.map((s) => s.id) ?? [];
            form.product_type = newCategory.product_type;
            form.slug = newCategory.slug;
            form.display_name = newCategory.display_name;
            form.description = newCategory.description ?? '';
            form.is_active = newCategory.is_active;
            form.image = null;
        } else if (!newCategory && props.open) {
            form.reset();
            form.is_active = true;
        }
    },
    { immediate: true },
);

watch(
    () => form.display_name,
    (newName) => {
        if (!props.category) {
            form.slug = slugify(newName);
        }
    },
);

const previewUrl = computed(() => {
    if (form.image) return URL.createObjectURL(form.image);
    return props.category?.image_url ?? null;
});

function submit() {
    if (props.category) {
        form.put(update(props.category).url, {
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
    const configs: Record<string, { slug: string; name: string }> = {
        'noi-that': { slug: 'sofa-da', name: 'Sofa da cao cấp' },
        'phu-kien': { slug: 'goi-tua-lung', name: 'Gối tựa lưng' },
        'trang-tri': { slug: 'den-trang-tri', name: 'Đèn trang trí' },
        'thap-sang': { slug: 'den-ban', name: 'Đèn bàn' },
    };
    return (
        configs[props.category?.product_type || form.product_type] ?? {
            slug: 'ten-san-pham',
            name: 'Tên sản phẩm',
        }
    );
});

const selectedType = computed(() =>
    typeOptions.find((t) => t.value === form.product_type),
);

const selectedGroupLabel = computed(() => {
    const g = props.categoryGroups.find((g) => g.id === form.group_id);
    return g?.label;
});
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
                            {{ category ? 'Chỉnh sửa' : 'Thêm' }} danh mục
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{
                                category
                                    ? 'Cập nhật thông tin danh mục'
                                    : 'Tạo danh mục sản phẩm mới'
                            }}
                        </DialogDescription>
                    </div>
                    <Badge
                        v-if="selectedType"
                        variant="secondary"
                        class="shrink-0 gap-1.5 sm:inline"
                    >
                        <component
                            :is="selectedType.icon"
                            class="inline h-3.5 w-3.5"
                        />
                        <span class="ml-1 hidden sm:inline">{{
                            selectedType.label
                        }}</span>
                    </Badge>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-[180px_1fr]">
                    <!-- Left: Image (desktop only) -->
                    <div class="hidden space-y-3 sm:block">
                        <FieldLabel class="text-sm font-medium"
                            >Hình ảnh</FieldLabel
                        >
                        <ImageUploader
                            v-model="form.image"
                            :preview-url="previewUrl"
                            aspect-ratio="square"
                        />
                    </div>

                    <!-- Right: Fields -->
                    <div class="space-y-4">
                        <!-- Group + Type -->
                        <div class="grid grid-cols-2 gap-3">
                            <Field>
                                <FieldLabel>
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Nhóm danh mục
                                    <span class="text-destructive">*</span>
                                </FieldLabel>
                                <FieldContent>
                                    <Select
                                        v-model="form.group_id"
                                        @update:model-value="
                                            (val) =>
                                                (form.group_id = String(val))
                                        "
                                    >
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                :placeholder="
                                                    selectedGroupLabel ||
                                                    'Chọn nhóm...'
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            position="popper"
                                            :side-offset="4"
                                        >
                                            <SelectItem
                                                v-for="g in categoryGroups"
                                                :key="g.id"
                                                :value="g.id"
                                            >
                                                {{ g.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[form.errors.group_id]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <LayoutGrid
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Loại sản phẩm
                                    <span class="text-destructive">*</span>
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.product_type">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                placeholder="Chọn loại..."
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            position="popper"
                                            :side-offset="4"
                                        >
                                            <SelectItem
                                                v-for="t in typeOptions"
                                                :key="t.value"
                                                :value="t.value"
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <component
                                                        :is="t.icon"
                                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                                    />
                                                    {{ t.label }}
                                                </div>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[form.errors.product_type]"
                                    />
                                </FieldContent>
                            </Field>
                        </div>

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
                                        :placeholder="`VD: ${placeholders.name}`"
                                        class="text-sm"
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
                                        :placeholder="`VD: ${placeholders.slug}`"
                                        class="font-mono text-sm"
                                    />
                                    <FieldError :errors="[form.errors.slug]" />
                                </FieldContent>
                            </Field>
                        </div>

                        <!-- Room + Status -->
                        <div class="grid grid-cols-2 gap-3">
                            <Field>
                                <FieldLabel>
                                    <Home
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Phòng
                                </FieldLabel>
                                <FieldContent>
                                    <MultiSelect
                                        title="Phòng"
                                        :options="roomOptions"
                                        v-model="form.room_ids!"
                                        placeholder="Thêm phòng..."
                                    />
                                    <FieldError
                                        :errors="[form.errors.room_ids]"
                                    />
                                </FieldContent>
                            </Field>
                            <Field>
                                <FieldLabel>
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Thông số lọc
                                </FieldLabel>
                                <FieldContent>
                                    <MultiSelect
                                        title="Thông số lọc"
                                        :options="specOptions"
                                        v-model="form.filterable_specs!"
                                        placeholder="Thêm thông số..."
                                    />
                                    <FieldError
                                        :errors="[form.errors.filterable_specs]"
                                    />
                                </FieldContent>
                            </Field>
                        </div>

                        <!-- Description -->
                        <Field>
                            <FieldLabel>Mô tả</FieldLabel>
                            <FieldContent>
                                <Textarea
                                    v-model="form.description"
                                    placeholder="Mô tả ngắn gọn về danh mục này..."
                                    class="min-h-[60px] resize-y text-sm"
                                    rows="2"
                                />
                                <FieldError
                                    :errors="[form.errors.description]"
                                />
                            </FieldContent>
                        </Field>

                        <div class="mb-4 sm:hidden">
                            <FieldLabel class="text-sm font-medium"
                                >Hình ảnh</FieldLabel
                            >
                            <ImageUploader
                                v-model="form.image"
                                :preview-url="previewUrl"
                                aspect-ratio="square"
                                hint="4:3 hoặc 1:1 · Max 2MB"
                                class="mt-2 w-60 justify-self-center"
                            />
                        </div>

                        <!-- Status -->
                        <StatusToggle
                            v-model="form.is_active"
                            label="Kích hoạt"
                            description="Ẩn danh mục khỏi website khi tắt"
                            id="is_active"
                        />
                    </div>
                </div>

                <!-- Footer -->
                <DialogFooter class="mt-6 border-t pt-4">
                    <div class="flex w-full gap-2 sm:justify-between">
                        <Button
                            v-if="category"
                            type="button"
                            variant="outline"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            @click="emit('delete', category)"
                        >
                            Xóa danh mục
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
                                {{ category ? 'Lưu thay đổi' : 'Tạo danh mục' }}
                            </Button>
                        </div>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
