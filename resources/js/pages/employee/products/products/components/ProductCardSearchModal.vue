<script setup lang="ts">
import { Search, Loader2, Package } from '@lucide/vue';
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
import { search as searchCards } from '@/routes/employee/products/product-cards';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (
        e: 'selected',
        item: {
            id: any;
            product: {
                id: any;
                name: any;
            };
            variants: any;
        },
    ): void;
    (e: 'close'): void;
}>();

const search = ref('');
const results = ref<any[]>([]);
const isLoading = ref(false);
// Track the currently hovered variant for each card: { [cardId]: variantId }
const activeVariantMap = ref<Record<string, string>>({});

const performSearch = debounce(async () => {
    if (search.value.length < 2) {
        results.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(searchCards().url, {
            params: { q: search.value },
        });
        results.value = response.data;

        // Initialize the first variant as active for each card
        results.value.forEach((card) => {
            if (card.variants?.length > 0) {
                activeVariantMap.value[card.id] = card.variants[0].id;
            }
        });
    } catch (error) {
        console.error('Failed to search product cards:', error);
    } finally {
        isLoading.value = false;
    }
}, 300);

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
    console.info(JSON.stringify(card));
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>Tìm kiếm sản phẩm</DialogTitle>
                <DialogDescription>
                    Tìm kiếm theo tên sản phẩm hoặc mã SKU để thêm vào gói.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="relative">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Nhập tên hoặc SKU..." class="pl-9" />
                    <div v-if="isLoading" class="absolute top-1/2 right-3 -translate-y-1/2">
                        <Loader2 class="h-4 w-4 animate-spin text-muted-foreground" />
                    </div>
                </div>

                <ScrollArea class="h-[450px] rounded-md border">
                    <div v-if="results.length === 0 && search.length >= 2"
                        class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground">
                        <Package class="mb-2 h-8 w-8 opacity-20" />
                        <p>Không tìm thấy sản phẩm nào phù hợp.</p>
                    </div>

                    <div v-else-if="results.length === 0"
                        class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground">
                        <p>Nhập ít nhất 2 ký tự để tìm kiếm.</p>
                    </div>

                    <div class="grid gap-4 p-2">
                        <div v-for="card in results" :key="card.id"
                            class="flex gap-4 rounded-lg border bg-card p-3 transition-all hover:border-primary/50">
                            <!-- Image Preview (Dynamic based on active variant) -->
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-md bg-muted">
                                <img :src="card.variants.find(
                                    (v: { id: string }) =>
                                        v.id ===
                                        activeVariantMap[card.id],
                                )?.primary_image || '/placeholder.png'
                                    " class="h-full w-full object-cover transition-all duration-200" />
                            </div>

                            <!-- Details & Swatches -->
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <span class="text-sm leading-tight font-bold">
                                            {{ card.product_name }}
                                            {{
                                                card.variants.find(
                                                    (v: { id: string }) =>
                                                        v.id ===
                                                        activeVariantMap[
                                                        card.id
                                                        ],
                                                )?.name
                                            }}
                                        </span>
                                        <span class="text-xs text-muted-foreground">
                                            SKU:
                                            {{
                                                card.variants.find(
                                                    (v: { id: string }) =>
                                                        v.id ===
                                                        activeVariantMap[
                                                        card.id
                                                        ],
                                                )?.sku
                                            }}
                                            -
                                            <span class="font-medium text-foreground">
                                                {{
                                                    card.variants.find(
                                                        (v: { id: string }) =>
                                                            v.id ===
                                                            activeVariantMap[
                                                            card.id
                                                            ],
                                                    )?.sale_price ??
                                                    card.variants.find(
                                                        (v: { id: string, }) =>
                                                            v.id ===
                                                            activeVariantMap[
                                                            card.id
                                                            ],
                                                    )?.price
                                                }}đ
                                            </span>
                                        </span>
                                    </div>
                                    <Button variant="ghost" size="sm" class="h-8 px-2 text-xs"
                                        @click="handleSelect(card)">
                                        Chọn
                                    </Button>
                                </div>

                                <!-- Swatches Grid -->
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="variant in card.variants" :key="variant.id" @mouseenter="
                                        activeVariantMap[card.id] =
                                        variant.id
                                        " class="h-6 w-6 overflow-hidden rounded-full border-2 transition-all" :class="activeVariantMap[card.id] ===
                                                variant.id
                                                ? 'border-primary ring-1 ring-primary/30'
                                                : 'border-transparent hover:border-zinc-300'
                                            ">
                                        <img :src="variant.swatch_image" class="h-full w-full object-cover" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </ScrollArea>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Hủy</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
