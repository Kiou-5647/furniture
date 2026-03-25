<script setup lang="ts">
import type { Lookup } from '@/types/lookup';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Calendar,
    Clock,
    FileText,
    Globe,
    Hash,
    Image as ImageIcon,
    Info,
    Link as LinkIcon,
    Palette,
    Tag,
    X,
} from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    lookup: Lookup | null;
}>();

const emit = defineEmits(['close', 'edit', 'preview-image']);

function handlePreviewImage() {
    if (props.lookup?.image_path) {
        emit('preview-image', props.lookup.image_path);
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[700px] max-h-[90vh] overflow-y-auto ">
            <DialogHeader>
                <div class="flex items-start justify-between">
                    <div class="space-y-1 w-full">
                        <DialogTitle class="text-xl justify-self-start font-semibold">
                            Chi tiết tra cứu
                        </DialogTitle>
                    </div>
                    <Button variant="ghost" size="icon" @click="emit('close')" class="shrink-0">
                        <X class="h-5 w-5" />
                    </Button>
                </div>
                <div class="flex items-start justify-center">
                    <DialogDescription class="text-base uppercase font-bold">
                        {{ lookup?.namespace_label }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <div v-if="lookup" class="space-y-6">
                <!-- Header Section: Name & Status -->
                <div class="flex items-start justify-between gap-4 pb-4 border-b">
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-2">
                            <Tag class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-lg font-semibold">{{ lookup.display_name }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <Hash class="h-3.5 w-3.5 text-muted-foreground" />
                            <code class="text-sm bg-muted px-2 py-0.5 rounded">{{ lookup.slug }}</code>
                        </div>
                        <p v-if="lookup.description" class="text-sm text-muted-foreground">
                            {{ lookup.description }}
                        </p>
                    </div>
                    <Badge :variant="lookup.is_active ? 'default' : 'secondary'" class="shrink-0">
                        {{ lookup.is_active ? 'Đang hiện' : 'Đang ẩn' }}
                    </Badge>
                </div>

                <!-- Image Section -->
                <div v-if="lookup.image_path" class="space-y-2">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <ImageIcon class="h-4 w-4 text-muted-foreground" />
                        <span>Hình ảnh</span>
                    </div>
                    <div class="relative rounded-lg overflow-hidden border bg-muted">
                        <img :src="lookup.image_path" :alt="lookup.display_name"
                            class="w-full h-48 object-cover cursor-zoom-in hover:scale-105 transition-transform"
                            @click="handlePreviewImage" />
                        <Button variant="secondary" size="sm" class="absolute bottom-2 right-2"
                            @click="handlePreviewImage">
                            <ImageIcon class="h-4 w-4 mr-1" />
                            Xem ảnh lớn
                        </Button>
                    </div>
                </div>

                <!-- Color Preview (for Colors namespace) -->
                <div v-if="lookup.metadata?.hex_code" class="space-y-2">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <Palette class="h-4 w-4 text-muted-foreground" />
                        <span>Màu sắc</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-lg border bg-muted/50">
                        <div class="w-12 h-12 rounded-lg border shadow-sm"
                            :style="{ backgroundColor: lookup.metadata.hex_code }" />
                        <div class="space-y-0.5">
                            <code class="text-sm font-mono">{{ lookup.metadata.hex_code }}</code>
                            <p class="text-xs text-muted-foreground">Mã màu hex</p>
                        </div>
                    </div>
                </div>

                <!-- Metadata Section -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-sm font-medium pb-2 border-b">
                        <Info class="h-4 w-4 text-muted-foreground" />
                        <span>Metadata</span>
                    </div>

                    <div class="grid gap-3">
                        <!-- SEO Title -->
                        <div v-if="lookup.metadata?.title" class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Globe class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Tiêu đề SEO</p>
                                <p class="text-sm">{{ lookup.metadata.title }}</p>
                            </div>
                        </div>

                        <!-- SEO Description -->
                        <div v-if="lookup.metadata?.description"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <FileText class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Mô tả SEO</p>
                                <p class="text-sm line-clamp-2">{{ lookup.metadata.description }}</p>
                            </div>
                        </div>

                        <!-- Canonical URL -->
                        <div v-if="lookup.metadata?.canonical"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <LinkIcon class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Canonical URL</p>
                                <code class="text-xs break-all">{{ lookup.metadata.canonical }}</code>
                            </div>
                        </div>

                        <!-- Robots -->
                        <div v-if="lookup.metadata?.robots" class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Info class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Robots</p>
                                <Badge variant="outline">{{ lookup.metadata.robots }}</Badge>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!lookup.metadata?.title && !lookup.metadata?.description && !lookup.metadata?.canonical && !lookup.metadata?.robots"
                            class="text-center py-4 text-muted-foreground">
                            <p class="text-sm italic">Không có metadata</p>
                        </div>
                    </div>
                </div>

                <!-- Timestamps Section -->
                <div class="space-y-3 pt-4 border-t">
                    <div class="flex items-center gap-2 text-sm font-medium pb-2 border-b">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <span>Thời gian</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Calendar class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Ngày tạo</p>
                                <p class="text-sm font-medium">{{ lookup.created_at }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Clock class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Cập nhật</p>
                                <p class="text-sm font-medium">{{ lookup.updated_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions (slot for parent to customize) -->
            <slot name="footer">
                <!-- Default footer -->
                <div class="flex justify-end gap-2 pt-4 border-t">
                    <Button variant="outline" @click="emit('close')">
                        Đóng
                    </Button>
                    <Button @click="emit('edit', lookup)">
                        Chỉnh sửa
                    </Button>
                </div>
            </slot>
        </DialogContent>
    </Dialog>
</template>
