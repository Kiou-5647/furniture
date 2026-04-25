<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ImageOff, Minus, Plus } from '@lucide/vue';
import { Trash2, Loader2 } from 'lucide-vue-next';
import { VisuallyHidden } from 'reka-ui';
import { onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetFooter,
    SheetDescription,
} from '@/components/ui/sheet';
import { formatPrice } from '@/lib/utils';
import { index } from '@/routes/cart';
import { useCartStore } from '@/stores/cart';

const { state, fetchCart, updateItemQuantity, removeItem, closeDrawer } =
    useCartStore();

onMounted(() => {
    fetchCart();
});
</script>

<template>
    <Sheet :open="state.isOpen" @update:open="closeDrawer">
        <SheetContent side="right" class="flex w-full flex-col sm:max-w-md">
            <SheetHeader>
                <SheetTitle>Shopping Cart</SheetTitle>
                <VisuallyHidden><SheetDescription /></VisuallyHidden>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto py-6">
                <div v-if="state.isLoading" class="flex justify-center py-10">
                    <Loader2
                        class="h-6 w-6 animate-spin text-muted-foreground"
                    />
                </div>

                <div
                    v-else-if="state.items.length === 0"
                    class="py-10 text-center"
                >
                    <p class="text-muted-foreground">Your cart is empty.</p>
                    <Button variant="outline" class="mt-4" @click="closeDrawer">
                        Continue Shopping
                    </Button>
                </div>

                <div class="flex h-full flex-col gap-6 overflow-y-auto p-4">
                    <div
                        v-for="item in state.items"
                        :key="item.id"
                        class="flex flex-col gap-3"
                    >
                        <!-- ITEM HEADER: Used for both Product and Bundle -->
                        <div class="flex items-center gap-3">
                            <!-- Image -->
                            <div
                                class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border bg-muted"
                            >
                                <img
                                    v-if="item.image_url"
                                    :src="item.image_url"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-muted-foreground"
                                >
                                    <ImageOff class="h-6 w-6" />
                                </div>
                            </div>

                            <!-- Primary Info -->
                            <div class="flex flex-1 flex-col justify-center">
                                <div class="flex items-start justify-between">
                                    <span
                                        class="text-sm leading-tight font-bold"
                                        >{{ item.name }}</span
                                    >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-6 w-6 p-0 text-destructive"
                                        @click="removeItem(item.id)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <div
                                    class="mt-1 flex items-center justify-between"
                                >
                                    <span
                                        class="text-sm font-bold text-orange-500"
                                    >
                                        {{ formatPrice(item.unit_price) }}
                                    </span>

                                    <!-- Quantity Controls -->
                                    <div
                                        class="flex items-center gap-2 rounded-md bg-zinc-100 px-1 py-0.5"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-5 w-5 p-0"
                                            @click="
                                                updateItemQuantity(
                                                    item.id,
                                                    item.quantity - 1,
                                                )
                                            "
                                            :disabled="item.quantity <= 1"
                                        >
                                            <Minus class="h-3 w-3" />
                                        </Button>
                                        <span
                                            class="w-4 text-center text-xs font-medium"
                                            >{{ item.quantity }}</span
                                        >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-5 w-5 p-0"
                                            @click="
                                                updateItemQuantity(
                                                    item.id,
                                                    item.quantity + 1,
                                                )
                                            "
                                        >
                                            <Plus class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BUNDLE BREAKDOWN: Only rendered if item is a Bundle -->
                        <div
                            v-if="
                                item.purchasable_type ===
                                    'App\\Models\\Product\\Bundle' &&
                                item.selected_variants
                            "
                            class="ml-3 space-y-2 border-l-2 border-zinc-200 py-2 pl-4"
                        >
                            <p
                                class="mb-2 text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Thành phần gói:
                            </p>

                            <div
                                v-for="variant in item.selected_variants"
                                :key="variant.id"
                                class="flex items-center justify-between py-1"
                            >
                                <div class="flex items-center gap-2">
                                    <img
                                        v-if="variant.image_url"
                                        :src="variant.image_url"
                                        class="h-16 w-16 rounded-full border object-cover"
                                    />
                                    <div class="flex flex-col">
                                        <span
                                            class="mr-3 text-xs leading-none font-medium"
                                            >{{ variant.name }}</span
                                        >
                                        <span
                                            class="text-sm font-bold text-primary"
                                        >
                                            x{{ variant.quantity }}
                                        </span>
                                        <span
                                            class="font-mono text-xs text-muted-foreground"
                                            >{{ variant.sku }}</span
                                        >
                                    </div>
                                </div>
                                <span class="text-xs font-medium">
                                    {{
                                        formatPrice(
                                            variant.sale_price ?? variant.price,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="border-b border-zinc-100 pb-4" />
                    </div>
                </div>
            </div>

            <SheetFooter class="space-y-4 border-t pt-6">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span>{{ formatPrice(state.totals.total) }} VND</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <Button variant="outline" @click="closeDrawer"
                        >Continue</Button
                    >
                    <Link :href="index()">
                        <Button class="w-full">Review Cart</Button>
                    </Link>
                </div>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
