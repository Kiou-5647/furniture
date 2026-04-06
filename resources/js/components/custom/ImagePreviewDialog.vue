<script setup lang="ts">
import {
    X,
    ZoomIn,
    ZoomOut,
    Maximize2,
    ChevronLeft,
    ChevronRight,
    Download,
    RotateCcw,
} from '@lucide/vue';
import { VisuallyHidden } from 'reka-ui';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
    src: string | null;
    alt?: string;
    images?: string[];
    currentIndex?: number;
}>();

const emit = defineEmits(['update:open', 'close', 'update:currentIndex']);

const zoomLevel = ref(1);
const rotation = ref(0);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });
const panOffset = ref({ x: 0, y: 0 });
const isLoaded = ref(true);
const imageDimensions = ref({ width: 0, height: 0 });

const currentImageIndex = computed(() => props.currentIndex ?? 0);
const hasMultipleImages = computed(() => (props.images?.length ?? 0) > 1);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            zoomLevel.value = 1;
            rotation.value = 0;
            panOffset.value = { x: 0, y: 0 };
        }
    },
);

watch(
    () => props.src,
    () => {
        zoomLevel.value = 1;
        rotation.value = 0;
        panOffset.value = { x: 0, y: 0 };
        isLoaded.value = false;
    },
);

function handleClose() {
    emit('update:open', false);
    emit('close');
}

function handleZoomIn() {
    zoomLevel.value = Math.min(zoomLevel.value + 0.25, 5);
}

function handleZoomOut() {
    zoomLevel.value = Math.max(zoomLevel.value - 0.25, 0.25);
}

function handleReset() {
    zoomLevel.value = 1;
    rotation.value = 0;
    panOffset.value = { x: 0, y: 0 };
}

function handleRotate() {
    rotation.value = (rotation.value + 90) % 360;
}

function handleWheel(event: WheelEvent) {
    event.preventDefault();
    const delta = event.deltaY > 0 ? -0.15 : 0.15;
    zoomLevel.value = Math.max(0.25, Math.min(5, zoomLevel.value + delta));
}

function handleMouseDown(event: MouseEvent) {
    if (zoomLevel.value > 1) {
        isDragging.value = true;
        dragStart.value = {
            x: event.clientX - panOffset.value.x,
            y: event.clientY - panOffset.value.y,
        };
    }
}

function handleMouseMove(event: MouseEvent) {
    if (isDragging.value) {
        panOffset.value = {
            x: event.clientX - dragStart.value.x,
            y: event.clientY - dragStart.value.y,
        };
    }
}

function handleMouseUp() {
    isDragging.value = false;
}

function handleImageLoad(event: Event) {
    const img = event.target as HTMLImageElement;
    imageDimensions.value = {
        width: img.naturalWidth,
        height: img.naturalHeight,
    };
    isLoaded.value = true;
}

function handleDownload() {
    if (!props.src) return;
    const link = document.createElement('a');
    link.href = props.src;
    link.download = props.alt || 'image';
    link.click();
}

function navigate(direction: 'prev' | 'next') {
    if (!hasMultipleImages.value || !props.images) return;
    const len = props.images.length;
    const newIndex =
        direction === 'prev'
            ? (currentImageIndex.value - 1 + len) % len
            : (currentImageIndex.value + 1) % len;
    emit('update:currentIndex', newIndex);
}

function handleKeyDown(event: KeyboardEvent) {
    if (!props.open) return;
    switch (event.key) {
        case 'Escape':
            handleClose();
            break;
        case 'ArrowLeft':
            navigate('prev');
            break;
        case 'ArrowRight':
            navigate('next');
            break;
        case '+':
        case '=':
            handleZoomIn();
            break;
        case '-':
            handleZoomOut();
            break;
        case '0':
            handleReset();
            break;
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('mouseup', handleMouseUp);
    document.addEventListener('mousemove', handleMouseMove);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('mouseup', handleMouseUp);
    document.removeEventListener('mousemove', handleMouseMove);
});
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent
            class="z-9999 h-[95vh] max-h-[95vh] w-[95vw] max-w-[95vw] overflow-hidden border-none bg-black p-0 shadow-2xl"
        >
            <VisuallyHidden>
                <DialogTitle>Image Preview</DialogTitle>
                <DialogDescription
                    >Preview and interact with the image</DialogDescription
                >
            </VisuallyHidden>

            <!-- Top bar -->
            <div
                class="absolute inset-x-0 top-0 z-50 flex items-center justify-between bg-linear-to-b from-black/60 to-transparent p-4"
            >
                <div class="flex items-center gap-2">
                    <span
                        v-if="hasMultipleImages"
                        class="text-sm font-medium text-white/80"
                    >
                        {{ currentImageIndex + 1 }} / {{ images?.length }}
                    </span>
                    <span
                        v-if="imageDimensions.width"
                        class="text-xs text-white/50"
                    >
                        {{ imageDimensions.width }} ×
                        {{ imageDimensions.height }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-full text-white/70 hover:bg-white/20 hover:text-white"
                        @click="handleDownload"
                    >
                        <Download class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-full text-white/70 hover:bg-white/20 hover:text-white"
                        @click="handleClose"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Navigation arrows -->
            <template v-if="hasMultipleImages">
                <Button
                    variant="ghost"
                    size="icon"
                    class="absolute top-1/2 left-4 z-50 h-10 w-10 -translate-y-1/2 rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20"
                    @click="navigate('prev')"
                >
                    <ChevronLeft class="h-5 w-5" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    class="absolute top-1/2 right-4 z-50 h-10 w-10 -translate-y-1/2 rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20"
                    @click="navigate('next')"
                >
                    <ChevronRight class="h-5 w-5" />
                </Button>
            </template>

            <!-- Bottom controls -->
            <div
                class="absolute inset-x-0 bottom-0 z-50 flex items-center justify-center gap-2 bg-linear-to-t from-black/60 to-transparent p-4"
            >
                <div
                    class="flex items-center gap-1.5 rounded-full border border-white/10 bg-black/40 px-3 py-1.5 backdrop-blur-md"
                >
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 rounded-full text-white/70 hover:bg-white/20 hover:text-white disabled:opacity-30"
                        @click="handleZoomOut"
                        :disabled="zoomLevel <= 0.25"
                    >
                        <ZoomOut class="h-3.5 w-3.5" />
                    </Button>
                    <span
                        class="w-12 text-center font-mono text-xs text-white/80 tabular-nums"
                    >
                        {{ Math.round(zoomLevel * 100) }}%
                    </span>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 rounded-full text-white/70 hover:bg-white/20 hover:text-white disabled:opacity-30"
                        @click="handleZoomIn"
                        :disabled="zoomLevel >= 5"
                    >
                        <ZoomIn class="h-3.5 w-3.5" />
                    </Button>
                    <div class="mx-1 h-4 w-px bg-white/20" />
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 rounded-full text-white/70 hover:bg-white/20 hover:text-white disabled:opacity-30"
                        @click="handleRotate"
                        :disabled="zoomLevel < 1"
                    >
                        <RotateCcw class="h-3.5 w-3.5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 rounded-full text-white/70 hover:bg-white/20 hover:text-white disabled:opacity-30"
                        @click="handleReset"
                        :disabled="zoomLevel === 1 && rotation === 0"
                    >
                        <Maximize2 class="h-3.5 w-3.5" />
                    </Button>
                </div>
            </div>

            <!-- Thumbnails -->
            <div
                v-if="hasMultipleImages"
                class="absolute inset-x-0 bottom-16 z-50 flex justify-center gap-1.5 px-4"
            >
                <button
                    v-for="(img, i) in images"
                    :key="i"
                    class="h-12 w-12 shrink-0 overflow-hidden rounded-md border-2 transition-all"
                    :class="
                        i === currentImageIndex
                            ? 'scale-110 border-white/80'
                            : 'border-transparent opacity-50 hover:opacity-80'
                    "
                    @click="emit('update:currentIndex', i)"
                >
                    <img :src="img" class="h-full w-full object-cover" />
                </button>
            </div>

            <!-- Loading spinner -->
            <div
                v-if="!isLoaded"
                class="absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="h-8 w-8 animate-spin rounded-full border-2 border-white/30 border-t-white"
                />
            </div>

            <!-- Image container -->
            <div
                class="relative flex h-full w-full cursor-grab items-center justify-center overflow-hidden active:cursor-grabbing"
                @wheel="handleWheel"
                @mousedown="handleMouseDown"
            >
                <img
                    :src="src!"
                    :alt="alt || 'Image preview'"
                    class="max-h-full max-w-full object-contain select-none"
                    :class="{
                        'transition-transform duration-200 ease-out':
                            !isDragging,
                    }"
                    :style="{
                        transform: `scale(${zoomLevel}) rotate(${rotation}deg) translate(${panOffset.x / zoomLevel}px, ${panOffset.y / zoomLevel}px)`,
                    }"
                    @load="handleImageLoad"
                    @dragstart.prevent
                />
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
