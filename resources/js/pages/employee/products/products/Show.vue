<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Package, Tag, Layers, Calendar, Star, ShieldCheck, Settings } from '@lucide/vue';
import ProductCard from '@/components/product/ProductCard.vue';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/employee/products/items';
import type { BreadcrumbItem } from '@/types';
import type { Product } from '@/types/product';

const props = defineProps<{
    product: Product;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sản phẩm', href: index().url },
    { title: props.product.name, href: '#' },
];

function statusClass(status: string): string {
    const map: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        archived: 'bg-zinc-100 text-zinc-800',
    };
    return map[status] ?? 'bg-gray-100 text-gray-800';
}

function formatPrice(value: string | number): string {
    return Number(value).toLocaleString('vi-VN');
}
</script>

<template>
    <Head :title="product.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="index().url" class="text-muted-foreground hover:text-foreground">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-semibold">{{ product.name }}</h1>
                        <Badge :class="statusClass(product.status)">
                            {{ product.status_label }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Cập nhật: {{ product.updated_at }}
                    </p>
                </div>
            </div>

            <!-- General Info -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-lg border p-5">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold">
                        <Package class="h-4 w-4 text-muted-foreground" />
                        Thông tin chung
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Danh mục</dt>
                            <dd class="font-medium">{{ product.category?.display_name ?? '—' }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Bộ sưu tập</dt>
                            <dd class="font-medium">{{ product.collection?.display_name ?? '—' }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Nhà cung cấp</dt>
                            <dd class="font-medium">{{ product.vendor?.name ?? '—' }}</dd>
                        </div>
                        <Separator />
                    </dl>
                </div>

                <div class="rounded-lg border p-5">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold">
                        <Tag class="h-4 w-4 text-muted-foreground" />
                        Giá & Đánh giá
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Khoảng giá</dt>
                            <dd class="font-medium">{{ formatPrice(product.min_price) }}đ — {{ formatPrice(product.max_price) }}đ</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Đánh giá trung bình</dt>
                            <dd class="font-medium flex items-center gap-1">
                                <Star v-if="product.average_rating" class="h-3 w-3 fill-amber-400 text-amber-400" />
                                {{ product.average_rating ? `${product.average_rating} (${product.review_count})` : 'Chưa có' }}
                            </dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Lượt xem</dt>
                            <dd class="font-medium">{{ product.view_count }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Số lượng biến thể</dt>
                            <dd class="font-medium">{{ product.variants_count ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg border p-5">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        Thời gian
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Ngày tạo</dt>
                            <dd class="font-medium">{{ product.created_at }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Cập nhật cuối</dt>
                            <dd class="font-medium">{{ product.updated_at }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Đăng ngày</dt>
                            <dd class="font-medium">{{ product.published_date ?? '—' }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Mới đến hết</dt>
                            <dd class="font-medium">{{ product.new_arrival_until ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg border p-5">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold">
                        <ShieldCheck class="h-4 w-4 text-muted-foreground" />
                        Bảo hành & Lắp đặt
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Bảo hành</dt>
                            <dd class="font-medium">{{ product.warranty_months ? `${product.warranty_months} tháng` : '—' }}</dd>
                        </div>
                        <Separator />
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Cần lắp đặt</dt>
                            <dd class="font-medium">{{ product.assembly_info?.required ? 'Có' : 'Không' }}</dd>
                        </div>
                        <template v-if="product.assembly_info?.required">
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Độ khó</dt>
                                <dd class="font-medium capitalize">{{ product.assembly_info.difficulty_level ?? '—' }}</dd>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Thời gian ước tính</dt>
                                <dd class="font-medium">{{ product.assembly_info.estimated_minutes ? `${product.assembly_info.estimated_minutes} phút` : '—' }}</dd>
                            </div>
                        </template>
                    </dl>
                </div>
            </div>

            <!-- Features -->
            <div v-if="product.features && product.features.length > 0" class="rounded-lg border p-5">
                <h3 class="mb-4 flex items-center gap-2 font-semibold">
                    <Layers class="h-4 w-4 text-muted-foreground" />
                    Tính năng nổi bật
                </h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, i) in product.features"
                        :key="i"
                        class="rounded-md p-3 text-sm"
                    >
                        <p class="font-medium">{{ feature.display_name }}</p>
                        <p v-if="feature.description" class="mt-1 text-muted-foreground text-xs">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Option Groups -->
            <div v-if="product.option_groups && product.option_groups.length > 0" class="rounded-lg border p-5">
                <h3 class="mb-4 flex items-center gap-2 font-semibold">
                    <Settings class="h-4 w-4 text-muted-foreground" />
                    Nhóm tùy chọn biến thể
                </h3>
                <div class="space-y-4">
                    <div v-for="(group, gi) in product.option_groups" :key="gi">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-semibold">{{ group.name }}</span>
                            <Badge v-if="group.is_swatches" variant="secondary" class="text-xs">Swatches</Badge>
                            <Badge v-else variant="outline" class="text-xs">Chọn</Badge>
                            <span class="text-xs text-muted-foreground">{{ group.namespace }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <Badge
                                v-for="opt in group.options"
                                :key="opt.value"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ opt.label }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storefront Preview -->
            <div class="rounded-lg border p-5">
                <h3 class="mb-2 font-semibold">Hiển thị trên cửa hàng</h3>
                <p class="text-sm text-muted-foreground mb-6">
                    Cách sản phẩm và các biến thể sẽ hiển thị cho khách hàng
                </p>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Show cards if they exist -->
                    <template v-if="product.grouped_variants && product.grouped_variants.length > 0">
                        <ProductCard
                            v-for="(card, ci) in product.grouped_variants"
                            :key="ci"
                            :product="product"
                            :card-index="ci"
                        />
                    </template>
                    <!-- Fallback: single card without variant grouping -->
                    <ProductCard v-else :product="product" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
