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
    Trash2,
    X,
} from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    collection: Collection | null;
}>();

const emit = defineEmits(['close', 'edit', 'delete', 'preview-image']);

function handlePreviewImage() {
    if (props.collection?.image_path) {
        emit('preview-image', props.collection.image_path);
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="max-w-[95vw] sm:max-w-[700px] max-h-[90vh] overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            <DialogHeader>
                <div class="flex items-start justify-between">
                    <div class="space-y-1 w-full text-left min-w-0">
                        <DialogTitle class="text-xl font-semibold wrap-break-word">
                            Chi tiết bộ sưu tập
                        </DialogTitle>
                    </div>
                    <Button variant="ghost" size="icon" @click="emit('close')" class="shrink-0 ml-2">
                        <X class="h-5 w-5" />
                    </Button>
                </div>
                <div class="flex items-start justify-start sm:justify-center min-w-0">
                    <DialogDescription class="text-sm sm:text-base uppercase font-bold wrap-break-word text-left sm:text-center">
                        Thông tin chi tiết bộ sưu tập
                    </DialogDescription>
                </div>
            </DialogHeader>

            <div v-if="collection" class="space-y-6 min-w-0">
                <!-- Header Section: Name & Status -->
                <div class="flex flex-col sm:flex-row items-start justify-between gap-4 pb-4 border-b min-w-0">
                    <div class="flex-1 space-y-2 w-full min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <Tag class="h-4 w-4 text-muted-foreground shrink-0 mt-1" />
                            <h3 class="text-lg font-semibold wrap-break-word">Tên bộ sưu tập: {{ collection.display_name }}</h3>
                            <Badge v-if="collection.is_featured" variant="outline"
                                class="bg-yellow-50 text-yellow-700 border-yellow-200 gap-1 py-0 px-2">
                                <Star class="h-3 w-3 fill-yellow-500 text-yellow-500" />
                                Nổi bật
                            </Badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <Hash class="h-3.5 w-3.5 text-muted-foreground shrink-0 mt-1" />
                            <code class="text-sm bg-muted px-2 py-0.5 rounded break-all whitespace-pre-wrap flex-1 min-w-0">Đường dẫn: {{ collection.slug }}</code>
                        </div>
                        <div v-if="collection.description" class="text-sm text-muted-foreground space-y-1 min-w-0">
                            <p class="font-medium text-foreground">Mô tả:</p>
                            <p class="wrap-break-word">{{ collection.description }}</p>
                        </div>
                    </div>
                    <Badge :variant="collection.is_active ? 'default' : 'secondary'" class="shrink-0">
                        {{ collection.is_active ? 'Đang hiện' : 'Đang ẩn' }}
                    </Badge>
                </div>

                <!-- Image Section -->
                <div v-if="collection.image_path" class="space-y-2 min-w-0">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <ImageIcon class="h-4 w-4 text-muted-foreground shrink-0" />
                        <span>Hình ảnh đại diện</span>
                    </div>
                    <div class="relative rounded-lg overflow-hidden border bg-muted">
                        <img :src="collection.image_path" :alt="collection.display_name"
                            class="w-full h-48 sm:h-64 object-cover cursor-zoom-in hover:scale-105 transition-transform"
                            @click="handlePreviewImage" />
                        <Button variant="secondary" size="sm" class="absolute bottom-2 right-2"
                            @click="handlePreviewImage">
                            <ImageIcon class="h-4 w-4 mr-1 shrink-0" />
                            Phóng to
                        </Button>
                    </div>
                </div>

                <!-- Metadata Section -->
                <div class="space-y-3 min-w-0">
                    <div class="flex items-center gap-2 text-sm font-medium pb-2 border-b">
                        <Info class="h-4 w-4 text-muted-foreground shrink-0" />
                        <span>Metadata & SEO</span>
                    </div>

                    <div class="grid gap-3 min-w-0">
                        <!-- SEO Title -->
                        <div v-if="collection.metadata?.title"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Globe class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Tiêu đề SEO</p>
                                <p class="text-sm wrap-break-word">{{ collection.metadata.title }}</p>
                            </div>
                        </div>

                        <!-- SEO Description -->
                        <div v-if="collection.metadata?.description"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <FileText class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Mô tả SEO</p>
                                <p class="text-sm line-clamp-3 wrap-break-word">{{ collection.metadata.description }}</p>
                            </div>
                        </div>

                        <!-- Canonical URL -->
                        <div v-if="collection.metadata?.canonical"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <LinkIcon class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Canonical URL</p>
                                <code class="text-xs break-all text-blue-600 block whitespace-pre-wrap">{{ collection.metadata.canonical }}</code>
                            </div>
                        </div>

                        <!-- Robots -->
                        <div v-if="collection.metadata?.robots"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Info class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Robots</p>
                                <Badge variant="outline" class="truncate max-w-full block text-center">{{ collection.metadata.robots }}</Badge>
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
                <div class="space-y-3 pt-4 border-t min-w-0">
                    <div class="flex items-center gap-2 text-sm font-medium pb-2 border-b">
                        <Clock class="h-4 w-4 text-muted-foreground shrink-0" />
                        <span>Thời gian</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Calendar class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Ngày tạo</p>
                                <p class="text-sm font-medium wrap-break-word">{{ collection.created_at }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Clock class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Cập nhật lần cuối</p>
                                <p class="text-sm font-medium wrap-break-word">{{ collection.updated_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <slot name="footer">
                <div class="flex flex-col sm:flex-row justify-between gap-2 pt-4 border-t mt-6">
                    <Button variant="outline" @click="emit('close')" class="w-full sm:w-auto order-3 sm:order-1">
                        Đóng
                    </Button>
                    <div class="flex gap-2 w-full sm:w-auto order-2 sm:order-3">
                        <Button @click="emit('delete', collection)" class="flex-1 sm:flex-none text-white bg-destructive hover:bg-destructive/90">
                            <Trash2 class="h-4 w-4 mr-1" />
                            Xóa
                        </Button>
                        <Button @click="emit('edit', collection)" class="flex-1 sm:flex-none">
                            Chỉnh sửa
                        </Button>
                    </div>
                </div>
            </slot>
        </DialogContent>
    </Dialog>
</template>
