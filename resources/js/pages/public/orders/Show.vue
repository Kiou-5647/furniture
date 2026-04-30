<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, Truck } from '@lucide/vue';
import { ref } from 'vue';
import ReviewForm from '@/components/custom/product/ReviewForm.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/settings/CustomerLayout.vue';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatDateTime } from '@/lib/date-utils';
import { formatPrice } from '@/lib/utils';
import { show } from '@/routes/products';

const props = defineProps<{
    order: any;
}>();

function resolveShipmentStatus(status: string) {
    switch (status) {
        case 'pending':
            return 'Chờ xử lý';
        case 'shipped':
            return 'Đang giao';
        case 'delivered':
            return 'Đã giao';
        case 'returned':
            return 'Đã trả hàng';
        case 'cancelled':
            return 'Đã hủy';

        default:
            return 'Chờ xử lý';
    }
}

function canReview(status: string, review: any = null) {
    return (
        props.order.status === 'completed' &&
        status === 'delivered' &&
        (!review || !review.is_published)
    );
}

const isReviewDialogOpen = ref(false);
const selectedVariantId = ref<string | null>(null);
const selectedReview = ref<any>(null);

function openReview(variantId: string, item: any) {
    selectedVariantId.value = variantId;

    // Determine if the item is a standalone variant or part of a bundle
    let reviewData = null;

    // If it's a direct variant
    if (item.purchasable?.review) {
        reviewData = item.purchasable.review;
    }
    // If it's a bundle, we need to find the specific component variant's review
    else if (item.selected_variants) {
        const component = item.selected_variants.find(
            (v: any) => v.id === variantId,
        );
        reviewData = component?.review || null;
    }

    selectedReview.value = reviewData;
    isReviewDialogOpen.value = true;
}
</script>

<template>
    <ShopLayout>
        <CustomerLayout>
            <Head :title="`Đơn hàng ${order.order_number}`" />
            <div class="space-y-8 p-6">
                <!-- Header Section -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Chi tiết đơn hàng
                        </h1>
                        <p class="text-muted-foreground">
                            Mã đơn: {{ order.order_number }}
                        </p>
                    </div>
                    <Badge
                        :variant="
                            order.status === 'completed' ? 'default' : 'outline'
                        "
                    >
                        {{ order.status_label }}
                    </Badge>
                </div>

                <div class="grid gap-6 lg:grid-cols-12">
                    <div class="space-y-6 lg:col-span-4">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-base"
                                >
                                    <Truck class="h-4 w-4" /> Thông tin giao
                                    hàng
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Người nhận</span
                                    >
                                    <span class="text-sm font-medium">{{
                                        order.recipient.name
                                    }}</span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Email</span
                                    >
                                    <span class="text-sm font-medium">{{
                                        order.recipient.email
                                    }}</span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Số điện thoại</span
                                    >
                                    <span class="text-sm">{{
                                        order.recipient.phone
                                    }}</span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Địa chỉ giao hàng</span
                                    >
                                    <span class="text-sm leading-relaxed">{{
                                        order.recipient.address
                                    }}</span>
                                </div>
                            </CardContent>
                        </Card>
                        <!-- Payment Card -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base"
                                    >Thông tin thanh toán</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground"
                                        >Tổng tiền</span
                                    >
                                    <span class="font-bold">{{
                                        formatPrice(order.total_amount)
                                    }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground"
                                        >Trạng thái</span
                                    >
                                    <Badge
                                        v-if="order.paid_at"
                                        variant="default"
                                        class="bg-green-500"
                                        >Đã thanh toán</Badge
                                    >
                                    <Badge v-else variant="outline"
                                        >Chưa thanh toán</Badge
                                    >
                                </div>
                                <div
                                    v-if="order.paid_at"
                                    class="text-right text-xs text-muted-foreground"
                                >
                                    Đã xác nhận lúc:
                                    {{ formatDateTime(order.paid_at) }}
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Invoices -->
                        <Card v-if="order.invoices?.length">
                            <CardHeader>
                                <CardTitle class="text-base">Hóa đơn</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div
                                    v-for="inv in order.invoices"
                                    :key="inv.id"
                                >
                                    <div
                                        v-if="inv.amount_paid < inv.amount_due"
                                        class="flex justify-between rounded-lg bg-muted/50 p-2 text-sm"
                                    >
                                        <span class="font-medium">{{
                                            inv.invoice_number
                                        }}</span>
                                        <span class="text-red-500"
                                            >Cần thanh toán:
                                            {{
                                                formatPrice(
                                                    inv.amount_due -
                                                        inv.amount_paid,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        v-else
                                        class="flex justify-between rounded-lg bg-muted/50 p-2 text-sm"
                                    >
                                        <span class="font-medium">{{
                                            inv.invoice_number
                                        }}</span>
                                        <span class="text-green-500"
                                            >Đã thanh toán:
                                            {{
                                                formatPrice(inv.amount_paid)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Refunds -->
                        <Card v-if="order.refunds?.length">
                            <CardHeader>
                                <CardTitle class="text-base"
                                    >Hoàn tiền</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div
                                    v-for="refd in order.refunds"
                                    :key="refd.id"
                                    class="flex justify-between rounded-lg border border-red-100 bg-red-50 p-2 text-sm"
                                >
                                    <span class="font-medium text-red-600">{{
                                        refd.reason
                                    }}</span>
                                    <span class="font-bold text-red-600"
                                        >-{{ formatPrice(refd.amount) }}</span
                                    >
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div class="space-y-4 lg:col-span-8">
                        <h2
                            class="flex items-center gap-2 text-lg font-semibold"
                        >
                            <Package class="h-5 w-5" /> Sản phẩm
                        </h2>

                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="space-y-3"
                        >
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex gap-4">
                                        <!-- Product Image Link -->
                                        <Link
                                            :href="
                                                show({
                                                    sku: item.purchasable.sku,
                                                    variant_slug:
                                                        item.purchasable.slug,
                                                }).url
                                            "
                                            class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border bg-muted"
                                        >
                                            <img
                                                v-if="
                                                    item.purchasable.image_url
                                                "
                                                :src="
                                                    item.purchasable.image_url
                                                "
                                                class="h-full w-full object-cover"
                                            />
                                        </Link>

                                        <div
                                            class="flex flex-1 flex-col justify-center"
                                        >
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <Link
                                                    :href="
                                                        show({
                                                            sku: item
                                                                .purchasable
                                                                .sku,
                                                            variant_slug:
                                                                item.purchasable
                                                                    .slug,
                                                        }).url
                                                    "
                                                    class="text-sm leading-tight font-bold hover:underline"
                                                >
                                                    {{ item.purchasable.name }}
                                                </Link>
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <!-- Per-item Shipment Status -->
                                                    <Badge
                                                        variant="outline"
                                                        class="text-[10px]"
                                                    >
                                                        {{
                                                            resolveShipmentStatus(
                                                                item.shipment_status,
                                                            )
                                                        }}
                                                    </Badge>
                                                    <Button
                                                        v-if="
                                                            canReview(
                                                                item.shipment_status,
                                                                item.purchasable?.review,
                                                            )
                                                        "
                                                        @click="
                                                            openReview(
                                                                item.purchasable
                                                                    .id,
                                                                item,
                                                            )
                                                        "
                                                        variant="secondary"
                                                        size="sm"
                                                    >
                                                        Đánh giá
                                                    </Button>
                                                    <span
                                                        v-else-if="
                                                            item.shipment_status === 'delivered' &&
                                                            item.purchasable?.review?.is_published
                                                        "
                                                        class="text-xs text-muted-foreground italic"
                                                    >
                                                        Đã đánh giá
                                                    </span>
                                                </div>
                                            </div>
                                            <div
                                                class="mt-1 flex items-center justify-between"
                                            >
                                                <span
                                                    class="text-sm font-bold text-orange-500"
                                                >
                                                    {{
                                                        formatPrice(
                                                            item.unit_price,
                                                        )
                                                    }}
                                                    x {{ item.quantity }}
                                                </span>
                                                <span class="text-sm font-bold">
                                                    {{
                                                        formatPrice(
                                                            item.unit_price *
                                                                item.quantity,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bundle Components Accordion -->
                                    <Accordion
                                        v-if="
                                            item.selected_variants &&
                                            item.selected_variants.length > 0
                                        "
                                        type="single"
                                        collapsible
                                        class="mt-4"
                                    >
                                        <AccordionItem
                                            value="details"
                                            class="border-none"
                                        >
                                            <AccordionTrigger
                                                class="py-2 text-xs font-medium text-muted-foreground hover:no-underline"
                                            >
                                                Chi tiết gói sản phẩm
                                            </AccordionTrigger>
                                            <AccordionContent
                                                class="space-y-3 pt-2"
                                            >
                                                <div
                                                    v-for="v in item.selected_variants"
                                                    :key="v.id"
                                                    class="flex items-center justify-between border-t py-2 first:border-t-0"
                                                >
                                                    <div
                                                        class="flex items-center gap-3"
                                                    >
                                                        <Link
                                                            :href="
                                                                show({
                                                                    sku: v.sku,
                                                                    variant_slug:
                                                                        v.slug,
                                                                }).url
                                                            "
                                                            class="h-10 w-10 shrink-0 overflow-hidden rounded border bg-muted"
                                                        >
                                                            <img
                                                                v-if="
                                                                    v.image_url
                                                                "
                                                                :src="
                                                                    v.image_url
                                                                "
                                                                class="h-full w-full object-cover"
                                                            />
                                                        </Link>
                                                        <div
                                                            class="flex flex-col"
                                                        >
                                                            <Link
                                                                :href="
                                                                    show({
                                                                        sku: v.sku,
                                                                        variant_slug:
                                                                            v.slug,
                                                                    }).url
                                                                "
                                                                class="text-xs font-medium hover:underline"
                                                            >
                                                                {{ v.name }}
                                                            </Link>
                                                            <span
                                                                class="text-[10px] text-muted-foreground"
                                                                >{{ v.sku }} x{{
                                                                    v.quantity
                                                                }}</span
                                                            >
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex items-center gap-3"
                                                    >
                                                        <Badge
                                                            variant="outline"
                                                            class="text-[10px]"
                                                        >
                                                            {{
                                                                v.shipment_status ||
                                                                'Chờ giao'
                                                            }}
                                                        </Badge>
                                                        <Button
                                                            v-if="
                                                                canReview(
                                                                    v.shipment_status,
                                                                    v.review,
                                                                )
                                                            "
                                                            variant="secondary"
                                                            size="sm"
                                                            class="h-6 px-2 text-[10px]"
                                                        >
                                                            Đánh giá
                                                        </Button>
                                                        <span
                                                            v-else-if="
                                                                v.shipment_status === 'delivered' &&
                                                                v.review?.is_published
                                                            "
                                                            class="text-xs text-muted-foreground italic"
                                                        >
                                                            Đã đánh giá
                                                        </span>
                                                        <span
                                                            class="text-xs font-medium"
                                                            >{{
                                                                formatPrice(
                                                                    v.price *
                                                                        v.quantity,
                                                                )
                                                            }}</span
                                                        >
                                                    </div>
                                                </div>
                                            </AccordionContent>
                                        </AccordionItem>
                                    </Accordion>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </CustomerLayout>
    </ShopLayout>
    <ReviewForm
        :model-value="isReviewDialogOpen"
        @update:model-value="isReviewDialogOpen = $event"
        :variant-id="selectedVariantId || ''"
        :initial-review="selectedReview"
    />
</template>
