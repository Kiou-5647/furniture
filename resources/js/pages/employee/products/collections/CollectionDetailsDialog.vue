<script setup lang="ts">
import type { Collection } from '@/types/collection';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    Star,
    Tag,
    X,
} from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    collection: Collection | null;
}>();

const emit = defineEmits(['close', 'edit', 'preview-image']);

function handlePreviewImage() {
    if (props.collection?.image_path) {
        emit('preview-image', props.collection.image_path);
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[700px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <div class="flex items-start justify-between">
                    <div class="space-y-1 w-full">
                        <DialogTitle class="text-xl justify-self-start font-semibold">
                            Chi tiết bộ sưu tập
                        </DialogTitle>
                    </div>
                    <Button variant="ghost" size="icon" @click="emit('close')" class="shrink-0">
                        <X class="h-5 w-5" />
                    </Button>
                </div>
                <div class="flex items-start justify-center">
                    <DialogDescription class="text-base uppercase font-bold">
                        Thông tin chi tiết bộ sưu tập
                    </DialogDescription>
                </div>
            </DialogHeader>

            <div v-if="collection" class="space-y-6">
                <!-- Header Section: Name & Status -->
                <div class="flex items-start justify-between gap-4 pb-4 border-b">
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-2">
                            <Tag class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-lg font-semibold">{{ collection.display_name }}</h3>
                            <Badge v-if="collection.is_featured" variant="outline"
                                class="bg-yellow-50 text-yellow-700 border-yellow-200 gap-1 py-0 px-2">
                                <Star class="h-3 w-3 fill-yellow-500 text-yellow-500" />
                                Nổi bật
                            </Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <Hash class="h-3.5 w-3.5 text-muted-foreground" />
                            <code class="text-sm bg-muted px-2 py-0.5 rounded">{{ collection.slug }}</code>
                        </div>
                        <p v-if="collection.description" class="text-sm text-muted-foreground">
                            {{ collection.description }}
                        </p>
                    </div>
                    <Badge :variant="collection.is_active ? 'default' : 'secondary'" class="shrink-0">
                        {{ collection.is_active ? 'Đang hiện' : 'Đang ẩn' }}
                    </Badge>
                </div>

                <!-- Image Section -->
                <div v-if="collection.image_path" class="space-y-2">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <ImageIcon class="h-4 w-4 text-muted-foreground" />
                        <span>Hình ảnh đại diện</span>
                    </div>
                    <div class="relative rounded-lg overflow-hidden border bg-muted">
                        <img :src="collection.image_path" :alt="collection.display_name"
                            class="w-full h-64 object-cover cursor-zoom-in hover:scale-105 transition-transform"
                            @click="handlePreviewImage" />
                        <Button variant="secondary" size="sm" class="absolute bottom-2 right-2"
                            @click="handlePreviewImage">
                            <ImageIcon class="h-4 w-4 mr-1" />
                            Xem ảnh lớn
                        </Button>
                    </div>
                </div>

                <!-- Metadata Section -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-sm font-medium pb-2 border-b">
                        <Info class="h-4 w-4 text-muted-foreground" />
                        <span>Metadata & SEO</span>
                    </div>

                    <div class="grid gap-3">
                        <!-- SEO Title -->
                        <div v-if="collection.metadata?.title"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Globe class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Tiêu đề SEO</p>
                                <p class="text-sm">{{ collection.metadata.title }}</p>
                            </div>
                        </div>

                        <!-- SEO Description -->
                        <div v-if="collection.metadata?.description"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <FileText class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Mô tả SEO</p>
                                <p class="text-sm line-clamp-3">{{ collection.metadata.description }}</p>
                            </div>
                        </div>

                        <!-- Canonical URL -->
                        <div v-if="collection.metadata?.canonical"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <LinkIcon class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Canonical URL</p>
                                <code class="text-xs break-all text-blue-600">{{ collection.metadata.canonical }}</code>
                            </div>
                        </div>

                        <!-- Robots -->
                        <div v-if="collection.metadata?.robots"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Info class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Robots</p>
                                <Badge variant="outline">{{ collection.metadata.robots }}</Badge>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!collection.metadata?.title && !collection.metadata?.description && !collection.metadata?.canonical && !collection.metadata?.robots"
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
                                <p class="text-sm font-medium">{{ collection.created_at }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Clock class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Cập nhật</p>
                                <p class="text-sm font-medium">{{ collection.updated_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <slot name="footer">
                <div class="flex justify-end gap-2 pt-4 border-t">
                    <Button variant="outline" @click="emit('close')">
                        Đóng
                    </Button>
                    <Button @click="emit('edit', collection)">
                        Chỉnh sửa
                    </Button>
                </div>
            </slot>
        </DialogContent>
    </Dialog>
</template>
