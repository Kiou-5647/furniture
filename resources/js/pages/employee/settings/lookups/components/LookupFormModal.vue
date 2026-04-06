<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Type, Hash, Tag, Palette } from '@lucide/vue';
import { computed, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import StatusToggle from '@/components/custom/StatusToggle.vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
}>();

const emit = defineEmits(['close', 'delete']);

const form = useForm({
    namespace_id: props.namespace_id || '_null',
    slug: '',
    display_name: '',
    description: '',
    is_active: true,
    image: null as File | null,
    metadata: {
        hex_code: '',
    },
});

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
            form.metadata = {
                hex_code: newLookup.metadata?.hex_code ?? '',
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

const currentNamespace = computed(() => {
    const ns = props.namespaces.find(
        (n) =>
            n.id === form.namespace_id ||
            (n.id === null && form.namespace_id === ''),
    );
    return ns?.slug ?? '';
});

const placeholders = computed(() => {
    const configs: Record<string, { slug: string; name: string }> = {
        'mau-sac': { slug: 'do-do', name: 'Đỏ Đô' },
        phong: { slug: 'phong-khach', name: 'Phòng Khách' },
        'phong-cach': { slug: 'hien-dai', name: 'Hiện Đại' },
    };
    return (
        configs[currentNamespace.value] ?? {
            slug: 'ma-dinh-danh',
            name: 'Tên hiển thị',
        }
    );
});

const selectedNsLabel = computed(() => {
    const ns = props.namespaces.find(
        (n) =>
            n.id === form.namespace_id ||
            (n.id === null && form.namespace_id === ''),
    );
    return ns?.label;
});

const isColorNamespace = computed(() => currentNamespace.value === 'mau-sac');
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
                        <Label class="text-sm font-medium">Hình ảnh</Label>
                        <ImageUploader
                            v-model="form.image"
                            :preview-url="previewUrl"
                            aspect-ratio="square"
                        />
                    </div>

                    <!-- Right: Fields -->
                    <div class="space-y-4">
                        <!-- Namespace + Hex Code (if color) -->
                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                            v-if="isColorNamespace"
                        >
                            <div class="space-y-1.5">
                                <Label
                                    class="flex items-center gap-1.5 text-sm"
                                >
                                    <Tag
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Danh mục
                                </Label>
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
                                <InputError
                                    :message="form.errors.namespace_id"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <Label
                                    class="flex items-center gap-1.5 text-sm"
                                >
                                    <Palette
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Mã màu HEX
                                </Label>
                                <div class="flex gap-2">
                                    <Input
                                        v-model="form.metadata.hex_code"
                                        placeholder="#FFFFFF"
                                        class="flex-1 font-mono text-sm"
                                    />
                                    <div
                                        class="h-10 w-10 shrink-0 rounded-lg border"
                                        :style="{
                                            backgroundColor:
                                                form.metadata.hex_code ||
                                                '#eee',
                                        }"
                                    />
                                </div>
                                <InputError
                                    :message="form.errors['metadata.hex_code']"
                                />
                            </div>
                        </div>

                        <!-- Namespace (non-color, full width) -->
                        <div v-else class="space-y-1.5">
                            <Label class="flex items-center gap-1.5 text-sm">
                                <Tag
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Danh mục
                            </Label>
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
                            <InputError :message="form.errors.namespace_id" />
                        </div>

                        <!-- Name + Slug -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    class="flex items-center gap-1.5 text-sm"
                                >
                                    <Type
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Tên hiển thị
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    v-model="form.display_name"
                                    :placeholder="`VD: ${placeholders.name}`"
                                    required
                                    class="text-sm"
                                />
                                <InputError
                                    :message="form.errors.display_name"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <Label
                                    class="flex items-center gap-1.5 text-sm"
                                >
                                    <Hash
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Slug
                                </Label>
                                <Input
                                    v-model="form.slug"
                                    :placeholder="`VD: ${placeholders.slug}`"
                                    class="font-mono text-sm"
                                />
                                <InputError :message="form.errors.slug" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <Label class="text-sm">Mô tả</Label>
                            <Textarea
                                v-model="form.description"
                                placeholder="Mô tả ngắn gọn..."
                                class="min-h-[60px] resize-y text-sm"
                                rows="2"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="mb-4 sm:hidden">
                            <Label class="text-sm font-medium">Hình ảnh</Label>
                            <ImageUploader
                                v-model="form.image"
                                :preview-url="previewUrl"
                                aspect-ratio="square"
                                class="mt-2 w-60 justify-self-center"
                            />
                        </div>

                        <div class="flex gap-3">
                            <StatusToggle
                                v-model="form.is_active"
                                label="Kích hoạt"
                                description="Hiển thị trên web"
                                id="is_active"
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
