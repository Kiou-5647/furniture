<script setup lang="ts">
import type { Category } from '@/types/category';
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
    CheckCircle2,
    Clock,
    FileText,
    Globe,
    Hash,
    Image as ImageIcon,
    Info,
    LayoutGrid,
    Link as LinkIcon,
    Package,
    Sparkles,
    Lamp,
    Tag,
    Trash2,
    X,
    PenIcon,
} from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    category: Category | null;
}>();

const emit = defineEmits(['close', 'edit', 'delete', 'preview-image']);

function handlePreviewImage() {
    if (props.category?.image_url) {
        emit('preview-image', props.category.image_url);
    }
}

function getProductTypeIcon(type: string) {
    const icons: Record<string, any> = {
        'noi-that': LayoutGrid,
        'phu-kien': Package,
        'trang-tri': Sparkles,
        'thap-sang': Lamp,
    };
    return icons[type] || LayoutGrid;
}

function getProductTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        'noi-that': 'Nội thất',
        'phu-kien': 'Phụ kiện',
        'trang-tri': 'Trang trí',
        'thap-sang': 'Thắp sáng',
    };
    return labels[type] || type;
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="max-w-[95vw] sm:max-w-175 max-h-[90vh] overflow-y-auto overflow-x-hidden p-4 sm:p-6">
            <DialogHeader>
                <div class="flex items-start justify-between">
                    <div class="space-y-1 w-full text-left min-w-0">
                        <DialogTitle class="text-xl font-semibold wrap-break-word">
                            Chi tiết danh mục
                        </DialogTitle>
                    </div>
                    <Button variant="ghost" size="icon" @click="emit('close')" class="shrink-0 ml-2">
                        <X class="h-5 w-5" />
                    </Button>
                </div>
                <div class="flex items-start justify-start sm:justify-center min-w-0">
                    <DialogDescription
                        class="text-sm sm:text-base uppercase font-bold wrap-break-word text-left sm:text-center">
                        {{ category?.group?.display_name || 'Không nhóm' }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <div v-if="category" class="space-y-6 min-w-0">
                <!-- Header Section: Name & Status -->
                <div class="flex flex-col sm:flex-row items-start justify-between gap-4 pb-4 border-b min-w-0">
                    <div class="flex-1 space-y-2 w-full min-w-0">
                        <div class="flex items-start gap-2">
                            <Tag class="h-4 w-4 text-muted-foreground shrink-0 mt-1" />
                            <h3 class="text-lg font-semibold wrap-break-word">Tên danh mục: {{ category.display_name }}
                            </h3>
                        </div>
                        <div class="flex items-start gap-2">
                            <Hash class="h-3.5 w-3.5 text-muted-foreground shrink-0 mt-1" />
                            <code
                                class="text-sm bg-muted px-2 py-0.5 rounded break-all whitespace-pre-wrap flex-1 min-w-0">Khóa: {{ category.slug }}</code>
                        </div>
                        <div class="flex items-center gap-2">
                            <component :is="getProductTypeIcon(category.product_type)"
                                class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <span class="text-sm text-muted-foreground">Loại:</span>
                            <Badge variant="outline" class="font-medium">
                                {{ getProductTypeLabel(category.product_type) }}
                            </Badge>
                        </div>
                        <div v-if="category.description" class="text-sm text-muted-foreground space-y-1 min-w-0">
                            <p class="font-medium text-foreground">Mô tả:</p>
                            <p class="wrap-break-word">{{ category.description }}</p>
                        </div>
                    </div>
                    <Badge :variant="category.is_active ? 'default' : 'secondary'" class="shrink-0">
                        {{ category.is_active ? 'Đang hiện' : 'Đang ẩn' }}
                    </Badge>
                </div>

                <!-- Rooms Section -->
                <div v-if="category.rooms && category.rooms.length > 0" class="space-y-2 min-w-0">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <LayoutGrid class="h-4 w-4 text-muted-foreground shrink-0" />
                        <span>Phòng phù hợp:</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Badge v-for="room in category.rooms" :key="room.id" variant="secondary"
                            class="wrap-break-word">
                            {{ room.display_name }}
                        </Badge>
                    </div>
                </div>

                <!-- Image Section -->
                <div v-if="category.image_url" class="space-y-2 min-w-0">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <ImageIcon class="h-4 w-4 text-muted-foreground shrink-0" />
                        <span>Hình ảnh</span>
                    </div>
                    <div class="relative rounded-lg overflow-hidden border bg-muted">
                        <img :src="category.image_url" :alt="category.display_name"
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
                        <div v-if="category.metadata?.title"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Globe class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Tiêu đề SEO</p>
                                <p class="text-sm wrap-break-word">{{ category.metadata.title }}</p>
                            </div>
                        </div>

                        <!-- SEO Description -->
                        <div v-if="category.metadata?.description"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <FileText class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Mô tả SEO</p>
                                <p class="text-sm line-clamp-3 wrap-break-word">{{ category.metadata.description }}</p>
                            </div>
                        </div>

                        <!-- Canonical URL -->
                        <div v-if="category.metadata?.canonical"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <LinkIcon class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Canonical URL</p>
                                <code
                                    class="text-xs break-all text-blue-600 block whitespace-pre-wrap">{{ category.metadata.canonical }}</code>
                            </div>
                        </div>

                        <!-- Robots -->
                        <div v-if="category.metadata?.robots"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Info class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="flex-1 space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Robots</p>
                                <Badge variant="outline" class="truncate max-w-full block text-center">{{
                                    category.metadata.robots }}</Badge>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!category.metadata?.title && !category.metadata?.description && !category.metadata?.canonical && !category.metadata?.robots"
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
                                <p class="text-sm font-medium wrap-break-word">{{ category.created_at }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50 min-w-0">
                            <Clock class="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                            <div class="space-y-1 min-w-0">
                                <p class="text-xs font-medium text-muted-foreground">Cập nhật lần cuối</p>
                                <p class="text-sm font-medium wrap-break-word">{{ category.updated_at }}</p>
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
                        <Button @click="emit('delete', category)"
                            class="flex-1 sm:flex-none text-white bg-destructive hover:bg-destructive/90">
                            <Trash2 class="h-4 w-4 mr-1" />
                            Xóa
                        </Button>
                        <Button @click="emit('edit', category)" class="flex-1 sm:flex-none">
                            <PenIcon class="h-4 w-4 mr-1" />
                            Chỉnh sửa
                        </Button>
                    </div>
                </div>
            </slot>
        </DialogContent>
    </Dialog>
</template>
