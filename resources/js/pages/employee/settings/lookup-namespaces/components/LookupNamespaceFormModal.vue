<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { watch } from 'vue';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { slugify } from '@/lib/utils';
import type { LookupNamespaceFull } from '@/types';

const props = defineProps<{
    open: boolean;
    namespace: LookupNamespaceFull | null;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    slug: '',
    display_name: '',
    description: '',
    for_variants: false,
    is_active: true,
});

watch(
    () => props.namespace,
    (newNamespace) => {
        if (newNamespace && props.open) {
            form.slug = newNamespace.slug;
            form.display_name = newNamespace.display_name;
            form.description = newNamespace.description ?? '';
            form.for_variants = newNamespace.for_variants;
            form.is_active = newNamespace.is_active;
        } else if (!newNamespace && props.open) {
            form.reset();
            form.is_active = true;
        }
    },
    { immediate: true },
);

watch(
    () => form.display_name,
    (newName) => {
        if (!props.namespace) {
            form.slug = slugify(newName);
        }
    },
);

function submit() {
    if (props.namespace) {
        form.put(`/nhan-vien/cau-hinh/danh-muc-tra-cuu/${props.namespace.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/nhan-vien/cau-hinh/danh-muc-tra-cuu', {
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
        <DialogContent class="sm:max-w-125">
            <DialogHeader>
                <DialogTitle
                    >{{ namespace ? 'Chỉnh sửa' : 'Thêm' }} Danh mục Tra
                    cứu</DialogTitle
                >
                <DialogDescription>
                    Quản lý thông tin danh mục cho tra cứu và biến thể sản phẩm
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4 py-4">
                <Field>
                    <FieldLabel>
                        Tên hiển thị
                        <span class="text-destructive">*</span>
                    </FieldLabel>
                    <FieldContent>
                        <Input
                            v-model="form.display_name"
                            placeholder="VD: Màu sắc"
                            required
                            :disabled="namespace?.is_system"
                        />
                        <FieldError :errors="[form.errors.display_name]" />
                    </FieldContent>
                </Field>

                <Field>
                    <FieldLabel>Khóa (Slug)</FieldLabel>
                    <FieldContent>
                        <Input
                            v-model="form.slug"
                            placeholder="VD: mau-sac"
                            :disabled="namespace?.is_system"
                        />
                        <FieldError :errors="[form.errors.slug]" />
                    </FieldContent>
                </Field>

                <Field>
                    <FieldLabel>Mô tả</FieldLabel>
                    <FieldContent>
                        <Textarea
                            v-model="form.description"
                            placeholder="Mô tả ngắn gọn về danh mục này..."
                            class="h-20 resize-none"
                            :disabled="namespace?.is_system"
                        />
                    </FieldContent>
                </Field>

                <div
                    class="flex items-center justify-between rounded-lg border p-4"
                >
                    <div class="space-y-0.5">
                        <Label class="text-base">Dùng cho biến thể</Label>
                        <p class="text-sm text-muted-foreground">
                            Cho phép sử dụng trong tạo biến thể sản phẩm
                        </p>
                    </div>
                    <Switch
                        v-model="form.for_variants"
                        :disabled="namespace?.is_system"
                    />
                </div>

                <div
                    class="flex items-center justify-between rounded-lg border p-4"
                >
                    <div class="space-y-0.5">
                        <Label class="text-base">Kích hoạt</Label>
                        <p class="text-sm text-muted-foreground">
                            Hiển thị và cho phép sử dụng
                        </p>
                    </div>
                    <Switch v-model="form.is_active" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="closeModal">
                        Hủy
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ namespace ? 'Lưu thay đổi' : 'Tạo mới' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
