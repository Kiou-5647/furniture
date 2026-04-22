<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Trash2, Loader2 } from 'lucide-vue-next'
import { VisuallyHidden } from 'reka-ui'
import { onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter, SheetDescription } from '@/components/ui/sheet'
import { index } from '@/routes/cart'
import { useCartStore } from '@/stores/cart'

const { state, fetchCart, updateItemQuantity, removeItem, closeDrawer } = useCartStore()

onMounted(() => {
    fetchCart()
})
</script>

<template>
    <Sheet :open="state.isOpen" @update:open="closeDrawer">
        <SheetContent side="right" class="flex flex-col w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Shopping Cart</SheetTitle>
                <VisuallyHidden><SheetDescription /></VisuallyHidden>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto py-6">
                <div v-if="state.isLoading" class="flex justify-center py-10">
                    <Loader2 class="w-6 h-6 animate-spin text-muted-foreground" />
                </div>

                <div v-else-if="state.items.length === 0" class="text-center py-10">
                    <p class="text-muted-foreground">Your cart is empty.</p>
                    <Button variant="outline" class="mt-4" @click="closeDrawer">
                        Continue Shopping
                    </Button>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="item in state.items" :key="item.id" class="flex gap-4 p-2 border rounded-lg">
                        <img :src="item.image_url" class="w-20 h-20 object-cover rounded-md" />
                        <div class="flex-1 space-y-1">
                            <p class="font-medium text-sm leading-tight">{{ item.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ item.unit_price.toLocaleString('vi-VN') }} VND</p>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center border rounded-md">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="updateItemQuantity(item.id, item.quantity - 1)">-</Button>
                                    <span class="px-2 text-xs w-8 text-center">{{ item.quantity }}</span>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="updateItemQuantity(item.id, item.quantity + 1)">+</Button>
                                </div>
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="removeItem(item.id)">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <SheetFooter class="border-t pt-6 space-y-4">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span>{{ state.totals.total.toLocaleString('vi-VN') }} VND</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <Button variant="outline" @click="closeDrawer">Continue</Button>
                    <Link :href="index()">
                        <Button class="w-full">Review Cart</Button>
                    </Link>
                </div>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
