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
import InputError from '@/components/InputError.vue';
import type { Lookup, LookupType } from '@/types/lookup';
import { store, update } from '@/routes/employee/lookups';
import { ImageIcon } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    namespace?: LookupType;
    lookup: Lookup | null;
    display_namespace: string; // Null for Create, Object for Edit
}>();

const emit = defineEmits(['close']);

const form = useForm({
    namespace: props.namespace,
    key: '',
    display_name: '',
    metadata: {
        hex_code: '',
        image: null as File | null
    },
});

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');

const placeholders = computed(() => {
    switch (props.namespace) {
        case 'mau-sac':
            return { key: 'tu-nhien', name: 'Tự nhiên' };
        case 'phong':
            return { key: 'phong-khach', name: 'Phòng khách' };
        case 'phong-cach':
            return { key: 'tan-co-dien', name: 'Tân cổ điển' };
        case 'tinh-nang':
            return { key: 'chong-nuoc', name: 'Chống nước' };
        default:
            return { key: 'ma-tra-cuu', name: 'Tên hiển thị' };
    }
});

const previewUrl = computed(() => {
    if (form.metadata.image instanceof File) {
        return URL.createObjectURL(form.metadata.image as any);
    }

    return props.lookup?.metadata?.image ?? null;
});

// Sync form when Modal opens or Lookup changes
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.lookup) {
                form.namespace = props.lookup.namespace;
                form.key = props.lookup.key;
                form.display_name = props.lookup.display_name;
                form.metadata = {
                    hex_code: props.lookup.metadata?.hex_code ?? '',
                    image: props.lookup?.metadata?.image ?? null
                };
            } else {
                form.reset();
                form.namespace = props.namespace;
            }
        }
    }
);

function triggerFileUpload() {
    fileInput.value?.click();
}

function onFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        form.metadata.image = target.files[0];
    }
}

function submit() {
    console.info(form.metadata.image)
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
</script>
<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ lookup ? 'Cập nhật ' + display_namespace : 'Thêm ' + display_namespace + ' mới' }}
                </DialogTitle>
                <DialogDescription>
                    Nhập thông tin cho {{ display_namespace }} bên dưới. Nhấn Lưu để hoàn tất.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="grid gap-4 py-4">
                <!-- Key Field (Slugs) -->
                <div class="grid gap-2">
                    <Label for="key">Mã (Key)</Label>
                    <Input id="key" v-model="form.key" :placeholder="`VD: ${placeholders.key}`" :disabled="!!lookup" />
                    <InputError :message="form.errors.key" />
                </div>

                <!-- Display Name Field -->
                <div class="grid gap-2">
                    <Label for="display_name">Tên hiển thị</Label>
                    <Input id="display_name" v-model="form.display_name" :placeholder="`VD: ${placeholders.name}`" />
                    <InputError :message="form.errors.display_name" />
                </div>

                <!-- Dynamic Metadata Section (Colors Only) -->
                <div v-if="namespace === 'mau-sac'" class="grid gap-2">
                    <Label for="hex_code">Mã màu (HEX)</Label>
                    <div class="flex gap-2">
                        <Input id="hex_code" v-model="form.metadata.hex_code" placeholder="#FFFFFF" class="flex-1" />
                        <div class="w-10 h-10 rounded-md border shadow-sm transition-colors"
                            :style="{ backgroundColor: form.metadata.hex_code || '#fff' }" />
                    </div>
                    <InputError :message="form.errors['metadata.hex_code']" />
                </div>

                <div class="grid gap-2">
                    <Label>Hình ảnh minh họa</Label>
                    <div class="flex items-center gap-4">
                        <!-- Image Preview -->
                        <div
                            class="w-16 h-16 rounded-lg border bg-muted flex items-center justify-center overflow-hidden">
                            <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                            <ImageIcon v-else class="w-6 h-6 text-muted-foreground" />
                        </div>
                        <input type="file" class="hidden" id="image_file" ref="fileInput" @change="onFileSelect"
                            accept="image/*" />
                        <Button type="button" variant="outline" @click="triggerFileUpload">
                            Chọn ảnh
                        </Button>
                    </div>
                    <InputError :message="form.errors['metadata.image']" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="closeModal">Hủy</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
