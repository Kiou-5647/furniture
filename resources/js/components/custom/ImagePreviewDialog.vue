<script setup lang="ts">
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { X, ZoomIn, ZoomOut, Maximize2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    src: string | null;
    alt?: string;
}>();

const emit = defineEmits(['update:open', 'close']);

const zoomLevel = ref(1);

// Reset zoom when dialog opens
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        zoomLevel.value = 1;
    }
});

function handleClose() {
    emit('update:open', false);
    emit('close');
}

function handleZoomIn() {
    zoomLevel.value = Math.min(zoomLevel.value + 0.25, 3);
}

function handleZoomOut() {
    zoomLevel.value = Math.max(zoomLevel.value - 0.25, 0.5);
}

function handleReset() {
    zoomLevel.value = 1;
}

function handleWheel(event: WheelEvent) {
    event.preventDefault();
    const delta = event.deltaY > 0 ? -0.1 : 0.1;
    zoomLevel.value = Math.max(0.5, Math.min(3, zoomLevel.value + delta));
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="w-[90vw] h-[90vh] max-w-[95vw] max-h-[95vh] border-none bg-black/95 p-0 shadow-2xl overflow-hidden z-9999">
            <!-- Close button -->
            <Button
                variant="secondary"
                size="icon"
                class="absolute top-8 right-8 z-50 w-10 h-10 rounded-full bg-gray-500/20 backdrop-blur-md border border-white/30 hover:bg-gray-500/30 text-white transition-all shadow-lg"
                @click="handleClose"
            >
                <X class="w-5 h-5" />
            </Button>

            <!-- Zoom controls -->
            <div class="absolute top-8 left-8 z-50 flex items-center gap-2">
                <Button
                    variant="secondary"
                    size="icon"
                    class="w-9 h-9 bg-white/20 backdrop-blur-md border border-gray-500/30 hover:bg-white/30 text-white shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="handleZoomOut"
                    :disabled="zoomLevel <= 0.5"
                >
                    <ZoomOut class="w-4 h-4" />
                </Button>
                <Button
                    variant="secondary"
                    size="icon"
                    class="w-9 h-9 bg-white/20 backdrop-blur-md border border-gray-500/30 hover:bg-white/30 text-white shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="handleZoomIn"
                    :disabled="zoomLevel >= 3"
                >
                    <ZoomIn class="w-4 h-4" />
                </Button>
                <Button
                    variant="secondary"
                    size="icon"
                    class="w-9 h-9 bg-white/20 backdrop-blur-md border border-gray-500/30 hover:bg-white/30 text-white shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="handleReset"
                    :disabled="zoomLevel === 1"
                >
                    <Maximize2 class="w-4 h-4" />
                </Button>
                <span class="text-white text-sm font-semibold bg-black/70 backdrop-blur-sm px-2.5 py-1 rounded-md border border-white/20 shadow-lg">
                    {{ Math.round(zoomLevel * 100) }}%
                </span>
            </div>

            <!-- Image container -->
            <div class="relative flex items-center justify-center h-full w-full overflow-hidden">
                <img
                    :src="src!"
                    :alt="alt || 'Image preview'"
                    class="max-h-full max-w-full object-contain rounded-lg transition-transform duration-200 ease-out"
                    :style="{ transform: `scale(${zoomLevel})` }"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
</style>
