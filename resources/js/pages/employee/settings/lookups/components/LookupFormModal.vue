<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Type, Hash, Tag, Palette } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { slugify } from '@/lib/utils';
import { store, update } from '@/routes/employee/settings/lookups';
import type { Lookup } from '@/types/lookup';

const props = defineProps<{
    open: boolean;
    namespace_id: string;
    display_namespace: string;
    lookup: Lookup | null;
    namespaces: { id: string | null; slug: string; label: string }[];
    categories: { id: string; display_name: string }[];
}>();

const emit = defineEmits(['close', 'delete']);

const form = useForm({
    namespace_id: props.namespace_id || '_null',
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    image: null as File | null,
    image_url: '',
    metadata: {
        hex_code: '',
        category_id: '',
    },
});

const skipImageUrl = ref(false);

watch(
    () => form.display_name,
    (newName) => {
        if (!props.lookup) {
            form.slug = slugify(newName);
        }
    },
);

watch(
    () => props.lookup,
    (newLookup) => {
        if (newLookup && props.open) {
            form.namespace_id = newLookup.namespace_id ?? '_null';
            form.slug = newLookup.slug;
            form.display_name = newLookup.display_name;
            form.description = newLookup.description ?? '';
            form.is_active = newLookup.is_active;
            form.image = null;
            if (skipImageUrl.value) {
                form.image_url = newLookup.image_url ?? '';
                skipImageUrl.value = true;
            }
            form.metadata = {
                hex_code: newLookup.metadata?.hex_code ?? '',
                category_id: newLookup.metadata?.category_id ?? '',
            };
        } else if (!newLookup && props.open) {
            form.reset();
            form.namespace_id = props.namespace_id || '_null';
            form.is_active = true;
        }
    },
    { immediate: true },
);

const previewUrl = computed(() => {
    if (form.image) return URL.createObjectURL(form.image);
    return props.lookup?.image_url ?? null;
});

function submit() {
    if (props.lookup) {
        form.put(update(props.lookup).url, {
            onSuccess: () => closeModal(),
            onError: (errors) => () =>
                toast.error('Có lỗi xảy ra trong quá trình cập nhật!'),
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

function handleEmitError(message: string) {
    toast.error(message);
}

const currentNamespace = computed(() => {
    const ns = props.namespaces.find(
        (n) =>
            n.id === form.namespace_id ||
            (n.id === null && form.namespace_id === ''),
    );
    return ns?.slug ?? '';
});

const selectedNsLabel = computed(() => {
    const ns = props.namespaces.find(
        (n) =>
            n.id === form.namespace_id ||
            (n.id === null && form.namespace_id === ''),
    );
    return ns?.label;
});

const isColor = computed(() => currentNamespace.value === 'mau-sac');
const isSubCategories = computed(
    () => currentNamespace.value === 'danh-muc-phu',
);
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
                        <DialogTitle class="text-lg sm:text-xl">
                            {{ lookup ? 'Chỉnh sửa' : 'Thêm' }} tra cứu
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{
                                lookup
                                    ? 'Cập nhật thông tin tra cứu'
                                    : 'Tạo giá trị tra cứu mới'
                            }}
                        </DialogDescription>
                    </div>
                    <Badge
                        v-if="selectedNsLabel"
                        variant="outline"
                        class="shrink-0"
                    >
                        {{ selectedNsLabel }}
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
                            @error="handleEmitError"
                            @remove-image="form.image_url = ''"
                        />
                        <FieldError :errors="[form.errors.image]" />
                    </div>

                    <div class="space-y-4">
                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                            v-if="isColor"
                        >
                            <Field>
                                <FieldLabel>
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Nhóm
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.namespace_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                :placeholder="
                                                    selectedNsLabel ||
                                                    'Chọn danh mục...'
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            position="popper"
                                            :side-offset="4"
                                        >
                                            <SelectItem
                                                v-for="ns in namespaces"
                                                :key="ns.id ?? '_null'"
                                                :value="ns.id ?? '_null'"
                                            >
                                                {{ ns.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[form.errors.namespace_id]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <Palette
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Mã màu HEX
                                </FieldLabel>
                                <FieldContent>
                                    <div class="flex gap-2">
                                        <Input
                                            v-model="form.metadata.hex_code"
                                            placeholder="#FFFFFF"
                                            class="flex-1 font-mono text-sm"
                                        />
                                        <div
                                            class="h-9 w-9 shrink-0 rounded-lg border"
                                            :style="{
                                                backgroundColor:
                                                    form.metadata.hex_code ||
                                                    '#EEEEEE',
                                            }"
                                        />
                                    </div>
                                    <FieldError
                                        :errors="[
                                            form.errors['metadata.hex_code'],
                                        ]"
                                    />
                                </FieldContent>
                            </Field>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                            v-else-if="isSubCategories"
                        >
                            <Field>
                                <FieldLabel>
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Nhóm
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.namespace_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                :placeholder="
                                                    selectedNsLabel ||
                                                    'Chọn danh mục...'
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            position="popper"
                                            :side-offset="4"
                                        >
                                            <SelectItem
                                                v-for="ns in namespaces"
                                                :key="ns.id ?? '_null'"
                                                :value="ns.id ?? '_null'"
                                            >
                                                {{ ns.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[form.errors.namespace_id]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <Palette
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Danh mục
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.metadata.category_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                :placeholder="
                                                    selectedNsLabel ||
                                                    'Chọn danh mục...'
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            position="popper"
                                            :side-offset="4"
                                        >
                                            <SelectItem
                                                v-for="c in categories"
                                                :key="c.id ?? '_null'"
                                                :value="c.id ?? '_null'"
                                            >
                                                {{ c.display_name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[
                                            form.errors['metadata.category_id'],
                                        ]"
                                    />
                                </FieldContent>
                            </Field>
                        </div>

                        <div v-else class="grid grid-cols-2 gap-2">
                            <Field>
                                <FieldLabel>
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Nhóm
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.namespace_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                :placeholder="
                                                    selectedNsLabel ||
                                                    'Chọn danh mục...'
                                                "
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            position="popper"
                                            :side-offset="4"
                                        >
                                            <SelectItem
                                                v-for="ns in namespaces"
                                                :key="ns.id ?? '_null'"
                                                :value="ns.id ?? '_null'"
                                            >
                                                {{ ns.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[form.errors.namespace_id]"
                                    />
                                </FieldContent>
                            </Field>
                            <div class="hidden flex-col gap-3 sm:flex">
                                <FieldLabel>
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Kích hoạt
                                </FieldLabel>
                                <StatusToggle
                                    v-model="form.is_active"
                                    label=""
                                    description="Hiển thị trên web"
                                    id="is_active"
                                    class="h-9"
                                />
                            </div>
                        </div>

                        <!-- Name + Slug -->
                        <div class="flex flex-col gap-3">
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
                                        placeholder="Tên hiển thị..."
                                        required
                                        class="text-sm"
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
                                        placeholder="Slug..."
                                        class="font-mono text-xs"
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
                                    placeholder="Mô tả ngắn gọn..."
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
                                class="mt-2 w-60 justify-self-center"
                                @error="handleEmitError"
                                @remove-image="form.image_url = ''"
                            />
                            <FieldError :errors="[form.errors.image]" />
                        </div>

                        <div
                            :class="['flex gap-3', isColor! ? '' : 'sm:hidden']"
                        >
                            <StatusToggle
                                v-model="form.is_active"
                                label="Kích hoạt"
                                description="Hiển thị trên web"
                                id="is_active"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <DialogFooter class="mt-6 border-t pt-4">
                    <div class="flex w-full gap-2 sm:justify-between">
                        <Button
                            v-if="lookup"
                            type="button"
                            variant="outline"
                            class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            @click="emit('delete', lookup)"
                        >
                            Xóa tra cứu
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
                                {{ lookup ? 'Lưu thay đổi' : 'Tạo mục mới' }}
                            </Button>
                        </div>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
