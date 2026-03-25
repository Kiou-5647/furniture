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
    X,
} from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    category: Category | null;
}>();

const emit = defineEmits(['close', 'edit', 'preview-image']);

function handlePreviewImage() {
    if (props.category?.image_path) {
        emit('preview-image', props.category.image_path);
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
        <DialogContent class="sm:max-w-[700px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <div class="flex items-start justify-between">
                    <div class="space-y-1 w-full">
                        <DialogTitle class="text-xl justify-self-start font-semibold">
                            Chi tiết danh mục
                        </DialogTitle>
                    </div>
                    <Button variant="ghost" size="icon" @click="emit('close')" class="shrink-0">
                        <X class="h-5 w-5" />
                    </Button>
                </div>
                <div class="flex items-start justify-center">
                    <DialogDescription class="text-base uppercase font-bold">
                        {{ category?.group?.display_name || 'Không nhóm' }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <div v-if="category" class="space-y-6">
                <!-- Header Section: Name & Status -->
                <div class="flex items-start justify-between gap-4 pb-4 border-b">
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-2">
                            <Tag class="h-4 w-4 text-muted-foreground" />
                            <h3 class="text-lg font-semibold">{{ category.display_name }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <Hash class="h-3.5 w-3.5 text-muted-foreground" />
                            <code class="text-sm bg-muted px-2 py-0.5 rounded">{{ category.slug }}</code>
                        </div>
                        <div class="flex items-center gap-2">
                            <component :is="getProductTypeIcon(category.product_type)"
                                class="h-3.5 w-3.5 text-muted-foreground" />
                            <Badge variant="outline" class="font-medium">
                                {{ getProductTypeLabel(category.product_type) }}
                            </Badge>
                        </div>
                        <p v-if="category.description" class="text-sm text-muted-foreground">
                            {{ category.description }}
                        </p>
                    </div>
                    <Badge :variant="category.is_active ? 'default' : 'secondary'" class="shrink-0">
                        {{ category.is_active ? 'Đang hiện' : 'Đang ẩn' }}
                    </Badge>
                </div>

                <!-- Rooms Section -->
                <div v-if="category.rooms && category.rooms.length > 0" class="space-y-2">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <LayoutGrid class="h-4 w-4 text-muted-foreground" />
                        <span>Phòng phù hợp</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Badge v-for="room in category.rooms" :key="room.id" variant="secondary">
                            {{ room.display_name }}
                        </Badge>
                    </div>
                </div>

                <!-- Image Section -->
                <div v-if="category.image_path" class="space-y-2">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <ImageIcon class="h-4 w-4 text-muted-foreground" />
                        <span>Hình ảnh</span>
                    </div>
                    <div class="relative rounded-lg overflow-hidden border bg-muted">
                        <img :src="category.image_path" :alt="category.display_name"
                            class="w-full h-48 object-cover cursor-zoom-in hover:scale-105 transition-transform"
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
                        <span>Metadata</span>
                    </div>

                    <div class="grid gap-3">
                        <!-- SEO Title -->
                        <div v-if="category.metadata?.title" class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Globe class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Tiêu đề SEO</p>
                                <p class="text-sm">{{ category.metadata.title }}</p>
                            </div>
                        </div>

                        <!-- SEO Description -->
                        <div v-if="category.metadata?.description"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <FileText class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Mô tả SEO</p>
                                <p class="text-sm line-clamp-2">{{ category.metadata.description }}</p>
                            </div>
                        </div>

                        <!-- Canonical URL -->
                        <div v-if="category.metadata?.canonical"
                            class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <LinkIcon class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Canonical URL</p>
                                <code class="text-xs break-all">{{ category.metadata.canonical }}</code>
                            </div>
                        </div>

                        <!-- Robots -->
                        <div v-if="category.metadata?.robots" class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Info class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="flex-1 space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Robots</p>
                                <Badge variant="outline">{{ category.metadata.robots }}</Badge>
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
                                <p class="text-sm font-medium">{{ category.created_at }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/50">
                            <Clock class="h-4 w-4 text-muted-foreground mt-0.5" />
                            <div class="space-y-1">
                                <p class="text-xs font-medium text-muted-foreground">Cập nhật</p>
                                <p class="text-sm font-medium">{{ category.updated_at }}</p>
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
                    <Button @click="emit('edit', category)">
                        Chỉnh sửa
                    </Button>
                </div>
            </slot>
        </DialogContent>
    </Dialog>
</template>
