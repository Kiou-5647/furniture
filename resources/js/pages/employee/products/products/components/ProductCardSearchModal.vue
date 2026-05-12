<script setup lang="ts">
import { Search, Loader2, Package, Check } from '@lucide/vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import { formatPrice } from '@/lib';
import { search as searchCards } from '@/routes/employee/products/product-cards';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'selected', item: { id: any; product: { id: any; name: any; }; variants: any; }): void;
    (e: 'close'): void;
}>();

const search = ref('');
const results = ref<any[]>([]);
const isLoading = ref(false);
const activeVariantMap = ref<Record<string, string>>({});

const fetchCards = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(searchCards().url, {
            params: { q: search.value }, // Nếu search trống, backend sẽ trả về danh sách mặc định
        });
        results.value = response.data;

        // Initialize the first variant as active for each card
        results.value.forEach((card) => {
            if (card.variants?.length > 0 && !activeVariantMap.value[card.id]) {
                activeVariantMap.value[card.id] = card.variants[0].id;
            }
        });
    } catch (error) {
        console.error('Failed to fetch product cards:', error);
    } finally {
        isLoading.value = false;
    }
};

const performSearch = debounce(() => {
    fetchCards();
}, 300);

// Load initial data when modal opens
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        search.value = ''; // Reset search on open
        fetchCards();
    }
}, { immediate: true });

// Search as user types
watch(search, performSearch);

function handleSelect(card: any) {
    const normalizedCard = {
        id: card.id,
        product: {
            id: card.id,
            name: card.product_name,
        },
        variants: card.variants,
    };
    emit('selected', normalizedCard);
}

const getActiveVariant = (card: any) => {
    const variantId = activeVariantMap.value[card.id];
    return card.variants?.find((v: any) => v.id === variantId);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <DialogContent class="sm:max-w-[800px] p-0 overflow-hidden gap-0">
            <DialogHeader class="p-6 pb-2">
                <DialogTitle class="text-xl font-bold tracking-tight">Tìm kiếm sản phẩm</DialogTitle>
                <DialogDescription>
                    Chọn sản phẩm để thêm vào gói combo của bạn.
                </DialogDescription>
            </DialogHeader>

            <div class="px-6 pb-4">
                <div class="relative group">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    <Input v-model="search" placeholder="Tìm theo tên sản phẩm hoặc SKU..."
                        class="pl-9 h-11 rounded-xl border-muted-foreground/20 focus-visible:ring-primary" />
                    <div v-if="isLoading" class="absolute top-1/2 right-3 -translate-y-1/2">
                        <Loader2 class="h-4 w-4 animate-spin text-primary" />
                    </div>
                </div>
            </div>

            <ScrollArea class="h-[600px] px-6 pb-6">
                <!-- Empty State -->
                <div v-if="results.length === 0 && !isLoading"
                    class="flex flex-col items-center justify-center py-20 text-center text-muted-foreground">
                    <div class="p-4 rounded-full bg-muted mb-4">
                        <Package class="h-10 w-10 opacity-20" />
                    </div>
                    <p class="text-base font-medium">Không tìm thấy sản phẩm phù hợp</p>
                    <p class="text-sm opacity-60">Hãy thử từ khóa khác hoặc kiểm tra lại chính tả.</p>
                </div>

                <!-- Product List -->
                <div v-else class="grid gap-3 pb-4">
                    <div v-for="card in results" :key="card.id"
                        class="flex gap-4 rounded-2xl border bg-card p-3 transition-all hover:border-primary/50 hover:shadow-sm group">
                        <!-- Image Preview -->
                        <div
                            class="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-muted border border-border">
                            <img :src="getActiveVariant(card)?.primary_image"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110" />
                        </div>

                        <!-- Details & Swatches -->
                        <div class="flex-1 flex flex-col justify-between py-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-sm font-bold leading-none">
                                            {{ card.product_name }}
                                        </span>
                                        <span
                                            class="text-sm font-medium px-1.5 py-0.5 rounded-full bg-primary/10 text-primary">
                                            {{ getActiveVariant(card)?.name }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground flex items-center gap-2">
                                        <span class="font-mono opacity-70">{{ getActiveVariant(card)?.sku }}</span>
                                        <span class="w-1 h-1 rounded-full bg-muted-foreground/30" />
                                        <span class="font-semibold text-foreground">
                                            {{ formatPrice(getActiveVariant(card)?.sale_price ??
                                            getActiveVariant(card)?.price) }}
                                        </span>
                                    </p>
                                </div>
                                <Button variant="ghost" size="sm"
                                    class="h-8 px-3 rounded-full text-xs font-bold transition-all hover:bg-primary hover:text-primary-foreground"
                                    @click="handleSelect(card)">
                                    Chọn
                                    <Check class="ml-1 h-3 w-3" />
                                </Button>
                            </div>

                            <!-- Swatches Grid -->
                            <div class="flex items-center gap-2 mt-2">
                                <span
                                    class="text-[10px] font-medium text-muted-foreground uppercase tracking-tighter">Màu:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="variant in card.variants" :key="variant.id"
                                        @mouseenter="activeVariantMap[card.id] = variant.id"
                                        class="h-5 w-5 overflow-hidden rounded-full border-2 transition-all" :class="activeVariantMap[card.id] === variant.id
                                            ? 'border-primary ring-2 ring-primary/20 scale-110'
                                            : 'border-transparent hover:border-zinc-300'">
                                        <img :src="variant.swatch_image" class="h-full w-full object-cover" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <DialogFooter class="p-6 pt-2 border-t bg-muted/10">
                <Button variant="outline" @click="emit('close')" class="rounded-xl">Đóng</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
