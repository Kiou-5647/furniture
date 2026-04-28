<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Trash2, ImageOff, Minus, Plus, AlertCircle } from 'lucide-vue-next';
import { onMounted } from 'vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatPrice } from '@/lib/utils';
import { index } from '@/routes/customer/checkout';
import { show } from '@/routes/products';
import { useCartStore } from '@/stores/cart';

const { state, fetchCart, updateItemQuantity, removeItem } = useCartStore();

onMounted(() => {
    fetchCart();
});

function proceedToCheckout() {
    router.visit(index().url);
}
</script>

<template>
    <ShopLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="mb-8 text-3xl font-bold">Giỏ hàng của bạn</h1>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
                <!-- Item List -->
                <div class="space-y-6 lg:col-span-2">
                    <div
                        v-if="state.isLoading"
                        class="flex justify-center py-20"
                    >
                        <div
                            class="h-8 w-8 animate-spin rounded-full border-b-2 border-primary"
                        ></div>
                    </div>

                    <div
                        v-else-if="state.items.length === 0"
                        class="rounded-3xl border-2 border-dashed py-20 text-center"
                    >
                        <p class="text-muted-foreground">Giỏ hàng trống.</p>
                        <Link
                            :href="'/san-pham'"
                            class="mt-2 block font-bold text-primary underline"
                            >Tiếp tục mua sắm</Link
                        >
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="item in state.items"
                            :key="item.id"
                            class="flex flex-col gap-4 rounded-2xl border bg-white p-5 transition-all"
                            :class="{
                                'opacity-60 grayscale': !item.is_available,
                            }"
                        >
                            <!-- MAIN ITEM ROW -->
                            <div class="flex items-center gap-4">
                                <!-- Image -->
                                <Link
                                    :href="
                                        show({
                                            sku: item.sku,
                                            variant_slug: item.slug,
                                        }).url
                                    "
                                    class="h-24 w-24 shrink-0 overflow-hidden rounded-xl border bg-muted transition-transform hover:scale-105"
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
                                        <ImageOff class="h-8 w-8" />
                                    </div>
                                </Link>

                                <!-- Info -->
                                <div class="flex-1 space-y-1">
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex flex-col">
                                            <Link
                                                :href="
                                                    show({
                                                        sku: item.sku,
                                                        variant_slug: item.slug,
                                                    }).url
                                                "
                                                class="text-lg leading-tight font-bold transition-colors hover:text-primary"
                                            >
                                                {{ item.name }}
                                            </Link>
                                            <Badge
                                                v-if="!item.is_available"
                                                variant="destructive"
                                                class="mt-1 w-fit text-[10px]"
                                            >
                                                Hết hàng
                                            </Badge>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-destructive hover:bg-destructive/10"
                                            @click="removeItem(item.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>

                                    <!-- Pricing -->
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="
                                                item.original_unit_price >
                                                item.unit_price
                                            "
                                            class="text-sm text-muted-foreground line-through"
                                        >
                                            {{
                                                formatPrice(
                                                    item.original_unit_price,
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="text-lg font-bold text-orange-500"
                                        >
                                            {{ formatPrice(item.unit_price) }}
                                        </span>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div
                                        class="mt-3 flex items-center justify-between"
                                    >
                                        <div
                                            class="flex items-center gap-2 rounded-lg bg-zinc-100 p-1"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7"
                                                @click="
                                                    updateItemQuantity(
                                                        item.id,
                                                        item.quantity - 1,
                                                    )
                                                "
                                                :disabled="
                                                    item.quantity <= 1 ||
                                                    !item.is_available
                                                "
                                            >
                                                <Minus class="h-3 w-3" />
                                            </Button>
                                            <span
                                                class="w-8 text-center text-sm font-medium"
                                                >{{ item.quantity }}</span
                                            >
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7"
                                                @click="
                                                    updateItemQuantity(
                                                        item.id,
                                                        item.quantity + 1,
                                                    )
                                                "
                                                :disabled="
                                                    item.quantity >=
                                                        item.available_stock ||
                                                    !item.is_available
                                                "
                                            >
                                                <Plus class="h-3 w-3" />
                                            </Button>
                                        </div>
                                        <p class="text-lg font-bold">
                                            {{
                                                formatPrice(
                                                    item.unit_price *
                                                        item.quantity,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- BUNDLE ACCORDION -->
                            <Accordion
                                v-if="
                                    item.purchasable_type ==
                                    'App\\Models\\Product\\Bundle'
                                "
                                type="single"
                                collapsible
                                class="w-full"
                            >
                                <AccordionItem
                                    value="contents"
                                    class="border-none"
                                >
                                    <AccordionTrigger
                                        class="px-0 py-2 text-xs font-semibold text-muted-foreground hover:no-underline"
                                    >
                                        Xem nội dung gói
                                    </AccordionTrigger>
                                    <AccordionContent class="space-y-3 pl-4">
                                        <div
                                            v-for="variant in item.selected_variants"
                                            :key="variant.id"
                                            class="flex items-center justify-between border-b border-zinc-100 py-2 last:border-0"
                                            :class="{
                                                'opacity-50':
                                                    variant.available_stock <=
                                                    0,
                                            }"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <Link
                                                    :href="
                                                        show({
                                                            sku: variant.sku,
                                                            variant_slug:
                                                                variant.slug,
                                                        }).url
                                                    "
                                                    class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border bg-muted"
                                                >
                                                    <img
                                                        v-if="variant.image_url"
                                                        :src="variant.image_url"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div
                                                        v-else
                                                        class="flex h-full w-full items-center justify-center text-muted-foreground"
                                                    >
                                                        <ImageOff
                                                            class="h-4 w-4"
                                                        />
                                                    </div>
                                                </Link>
                                                <div class="flex flex-col">
                                                    <Link
                                                        :href="
                                                            show({
                                                                sku: variant.sku,
                                                                variant_slug:
                                                                    variant.slug,
                                                            }).url
                                                        "
                                                        class="text-xs font-medium transition-colors hover:text-primary"
                                                    >
                                                        {{ variant.name }}
                                                    </Link>
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <span
                                                            class="font-mono text-[10px] text-muted-foreground"
                                                            >{{
                                                                variant.sku
                                                            }}</span
                                                        >
                                                        <span
                                                            v-if="
                                                                variant.available_stock <=
                                                                0
                                                            "
                                                            class="flex items-center gap-1 text-[10px] font-bold text-destructive"
                                                        >
                                                            <AlertCircle
                                                                class="h-3 w-3"
                                                            />
                                                            Hết hàng
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-bold">
                                                    x{{ variant.quantity }}
                                                </p>

                                                <span
                                                    v-if="
                                                        variant.sale_price &&
                                                        variant.sale_price <
                                                            variant.price
                                                    "
                                                    class="text-xs text-muted-foreground line-through"
                                                >
                                                    {{
                                                        formatPrice(
                                                            variant.sale_price
                                                        )
                                                    }}
                                                </span>
                                                &nbsp;
                                                <span
                                                    class="text-xs font-bold text-orange-500"
                                                >
                                                    {{
                                                        formatPrice(
                                                            variant.price,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                    </div>
                </div>
                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div
                        class="sticky top-24 rounded-3xl border bg-zinc-50 p-8 shadow-sm"
                    >
                        <h2 class="mb-6 text-xl font-bold">
                            Chi tiết thanh toán
                        </h2>

                        <div class="space-y-4 text-sm">
                            <div
                                class="flex justify-between text-muted-foreground"
                            >
                                <span>Tạm tính</span>
                                <span>{{
                                    formatPrice(state.totals.subtotal)
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between text-muted-foreground"
                            >
                                <span>Tiết kiệm được</span>
                                <span class="font-bold text-green-600">
                                    -
                                    {{
                                        formatPrice(state.totals.discount || 0)
                                    }}
                                </span>
                            </div>
                            <div
                                class="flex justify-between border-t pt-4 text-xl font-bold text-zinc-900"
                            >
                                <span>Tổng cộng</span>
                                <span>{{
                                    formatPrice(state.totals.total)
                                }}</span>
                            </div>
                        </div>

                        <Button
                            class="mt-8 w-full py-7 text-lg font-bold shadow-lg transition-transform hover:scale-[1.02] active:scale-[0.98]"
                            @click="proceedToCheckout"
                        >
                            Tiến hành thanh toán
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
