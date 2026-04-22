
<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import { onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { useCartStore } from '@/stores/cart';

const { state, fetchCart, updateItemQuantity, removeItem } = useCartStore();

onMounted(() => {
    fetchCart();
});

function proceedToCheckout() {
    // Logic for checkout (e.g., navigate to /checkout)
    console.log('Proceeding to checkout...');
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-8">Giỏ hàng của bạn</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Item List -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-if="state.items.length === 0" class="text-center py-20">
                        <p class="text-muted-foreground">Giỏ hàng trống.</p>
                        <Link :href="'/san-pham'" class="text-primary underline mt-2 block">Tiếp tục mua sắm</Link>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="item in state.items" :key="item.id" class="flex gap-4 p-4 border rounded-xl bg-white">
                            <img :src="item.image_url" class="w-24 h-24 object-cover rounded-lg" />
                            <div class="flex-1 space-y-2">
                                <div class="flex justify-between">
                                    <p class="font-semibold">{{ item.name }}</p>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="removeItem(item.id)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center border rounded-md bg-zinc-50">
                                        <Button variant="ghost" size="icon" class="h-7 w-7" @click="updateItemQuantity(item.id, item.quantity - 1)">-</Button>
                                        <span class="px-3 text-sm w-10 text-center">{{ item.quantity }}</span>
                                        <Button variant="ghost" size="icon" class="h-7 w-7" @click="updateItemQuantity(item.id, item.quantity + 1)">+</Button>
                                    </div>
                                    <p class="font-bold">{{ (item.unit_price * item.quantity).toLocaleString('vi-VN') }} VND</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="border rounded-2xl p-6 bg-zinc-50 sticky top-24">
                        <h2 class="text-xl font-bold mb-6">Chi tiết thanh toán</h2>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-muted-foreground">
                                <span>Tạm tính</span>
                                <span>{{ state.totals.subtotal.toLocaleString('vi-VN') }} VND</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <span>Giảm giá</span>
                                <span class="text-green-600">- {{ (state.totals.discount || 0).toLocaleString('vi-VN') }} VND</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <span>Vận chuyển</span>
                                <span>Miễn phí</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between text-lg font-bold text-zinc-900">
                                <span>Tổng cộng</span>
                                <span>{{ state.totals.total.toLocaleString('vi-VN') }} VND</span>
                            </div>
                        </div>

                        <Button class="w-full mt-8 py-6 text-lg" @click="proceedToCheckout">
                            Tiến hành thanh toán
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
