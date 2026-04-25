<script setup lang="ts">
import { ShoppingCart, Minus, Plus } from '@lucide/vue';
import { computed, ref, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useCartStore } from '@/stores/cart';
import type { ProductCard, ProductCardVariant } from '@/types/public/product';

const props = defineProps<{
    open: boolean;
    productCard: ProductCard;
}>();

const emit = defineEmits(['update:open']);

const { addToCart, state } = useCartStore();

const selectedVariantId = ref<string | null>(null);
const quantity = ref(1);

// Match the logic from ProductCard for initial selection
const initializeSelection = () => {
    if (!props.productCard.swatches || props.productCard.swatches.length === 0) {
        selectedVariantId.value = null;
        return;
    }
    selectedVariantId.value = props.productCard.swatches[0].id;
};

onMounted(initializeSelection);

const currentVariant = computed<ProductCardVariant | null>(() => {
    return props.productCard.swatches.find(s => s.id === selectedVariantId.value)
           || props.productCard.swatches[0] || null;
});

const price = computed(() => currentVariant.value?.sale_price ?? currentVariant.value?.price ?? 0);

function updateQuantity(delta: number) {
    quantity.value = Math.max(1, quantity.value + delta);
}

async function handleConfirmAdd() {
    if (!selectedVariantId.value) {
        toast.error('Vui lòng chọn một phiên bản sản phẩm');
        return;
    }

    const result = await addToCart({
        purchasable_id: selectedVariantId.value,
        purchasable_type: 'App\\Models\\Product\\ProductVariant',
        quantity: quantity.value,
    });

    if (result.success) {
        toast.success(`Đã thêm ${quantity.value} sản phẩm vào giỏ hàng`);
        emit('update:open', false);
    } else {
        toast.error('Có lỗi xảy ra, vui lòng thử lại');
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="val => emit('update:open', val)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-xl">
                    {{ productCard.product.name }}
                </DialogTitle>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <!-- Image Preview -->
                <div class="flex items-center gap-4">
                    <div class="relative h-24 w-24 overflow-hidden rounded-lg border bg-muted">
                        <img
                            v-if="currentVariant?.primary_image_url"
                            :src="currentVariant.primary_image_url"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-muted-foreground">
                            No Image
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-lg font-bold text-orange-500">
                            {{ price.toLocaleString('vi-VN') }}đ
                        </span>
                        <span v-if="currentVariant?.sale_price" class="text-sm line-through text-muted-foreground">
                            {{ (currentVariant.price).toLocaleString('vi-VN') }}đ
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ productCard.product.name + ' ' + currentVariant?.name || 'Phiên bản mặc định' }}
                        </span>
                    </div>
                </div>

                <!-- Variant Selection (Swatches) -->
                <div class="space-y-3">
                    <Label class="text-sm font-medium">Chọn phiên bản</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="swatch in productCard.swatches"
                            :key="swatch.id"
                            @click="selectedVariantId = swatch.id"
                            class="group relative h-10 w-10  rounded-md border-2 transition-all"
                            :class="selectedVariantId === swatch.id ? 'border-primary ring-2 ring-primary/20' : 'border-transparent hover:border-zinc-300'"
                        >
                            <img v-if="swatch.swatch_image_url" :src="swatch.swatch_image_url" class="h-full w-full overflow-hidden object-cover" />
                            <div v-else class="h-full w-full bg-zinc-200" />
                            <span class="absolute w-25 top-12 left-1/2 -translate-x-1/2 scale-0 rounded bg-zinc-800 px-2 py-1 text-sm text-white transition-all group-hover:scale-100">
                                {{ swatch.label || swatch.name}}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="space-y-3">
                    <Label class="text-sm font-medium">Số lượng</Label>
                    <div class="flex items-center gap-3">
                        <Button variant="outline" size="icon" class="h-8 w-8" @click="updateQuantity(-1)">
                            <Minus class="h-4 w-4" />
                        </Button>
                        <span class="w-8 text-center font-medium">{{ quantity }}</span>
                        <Button variant="outline" size="icon" class="h-8 w-8" @click="updateQuantity(1)">
                            <Plus class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button
                    class="w-full gap-2"
                    :disabled="state.isLoading"
                    @click="handleConfirmAdd"
                >
                    <ShoppingCart class="h-4 w-4" />
                    {{ state.isLoading ? 'Đang thêm...' : 'Thêm vào giỏ hàng' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
