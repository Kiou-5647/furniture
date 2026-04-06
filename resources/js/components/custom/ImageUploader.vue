<script setup lang="ts">
import { ImageIcon, X } from 'lucide-vue-next';
import { computed, watch, ref, useTemplateRef, onBeforeUnmount } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        previewUrl?: string | null;
        aspectRatio?: 'square' | 'wide' | 'banner';
        label?: string;
        hint?: string;
        removable?: boolean;
        maxSizeMb?: number;
    }>(),
    {
        aspectRatio: 'square',
        label: 'Chọn ảnh',
        hint: '',
        removable: true,
        maxSizeMb: 2,
    },
);

const emit = defineEmits<{
    'update:modelValue': [file: File | null];
    error: [message: string];
}>();

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');
const removedByUser = ref(false);
const objectUrl = ref<string | null>(null);

const aspectClasses = computed(
    () =>
        ({
            square: 'aspect-square',
            wide: 'aspect-[4/3]',
            banner: 'aspect-[3/1]',
        })[props.aspectRatio],
);

const defaultHints = computed(
    () =>
        ({
            square: '1:1 hoặc 4:3',
            wide: '4:3 hoặc 16:9',
            banner: '3:1',
        })[props.aspectRatio],
);

const displayHint = computed(
    () => props.hint || `${defaultHints.value} · Max ${props.maxSizeMb}MB`,
);

const effectivePreviewUrl = computed(() => {
    if (removedByUser.value) return null;
    if (props.modelValue) {
        if (objectUrl.value) return objectUrl.value;
        return URL.createObjectURL(props.modelValue);
    }
    return props.previewUrl ?? null;
});

watch(
    () => props.modelValue,
    (newFile, oldFile) => {
        if (newFile) {
            removedByUser.value = false;
            if (oldFile && objectUrl.value) {
                URL.revokeObjectURL(objectUrl.value);
            }
            objectUrl.value = URL.createObjectURL(newFile);
        } else if (oldFile && objectUrl.value) {
            URL.revokeObjectURL(objectUrl.value);
            objectUrl.value = null;
        }
    },
);

watch(effectivePreviewUrl, (newUrl) => {
    if (props.modelValue && newUrl) {
        objectUrl.value = newUrl;
    }
});

onBeforeUnmount(() => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
    }
});

function onFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        emit('error', 'Chỉ chấp nhận file hình ảnh');
        return;
    }

    const maxSize = props.maxSizeMb * 1024 * 1024;
    if (file.size > maxSize) {
        emit('error', `File quá lớn (tối đa ${props.maxSizeMb}MB)`);
        return;
    }

    emit('update:modelValue', file);
    target.value = '';
}

function removeImage() {
    removedByUser.value = true;
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = null;
    }
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    emit('update:modelValue', null);
}
</script>

<template>
    <div>
        <div
            :class="[
                'group relative w-full cursor-pointer overflow-hidden rounded-lg border-2 border-dashed bg-muted transition-colors hover:border-primary/50',
                aspectClasses,
            ]"
            @click="fileInput?.click()"
        >
            <img
                v-if="effectivePreviewUrl"
                :src="effectivePreviewUrl"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full flex-col items-center justify-center gap-2 text-muted-foreground transition-colors hover:text-primary/70"
            >
                <ImageIcon class="h-8 w-8" />
                <span class="text-xs">{{ label }}</span>
            </div>
            <button
                v-if="effectivePreviewUrl && removable"
                @click.stop="removeImage"
                type="button"
                class="absolute top-2 right-2 rounded-full bg-black/60 p-1.5 text-white opacity-0 backdrop-blur-sm transition-opacity group-hover:opacity-100 hover:bg-black/80 dark:bg-white/20 dark:hover:bg-white/30"
            >
                <X class="h-3.5 w-3.5" />
            </button>
        </div>
        <input
            type="file"
            ref="fileInput"
            class="hidden"
            @change="onFileSelect"
            accept="image/*"
        />
        <p
            v-if="displayHint"
            class="mt-1 text-center text-xs text-muted-foreground"
        >
            {{ displayHint }}
        </p>
    </div>
</template>
