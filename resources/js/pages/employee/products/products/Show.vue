<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Package,
    Tag,
    Layers,
    Calendar,
    Star,
    ShieldCheck,
    Settings,
    TrendingUp,
    Eye,
    MessageSquare,
    Info,
    Database,
    LayoutDashboard,
    ShoppingBag,
} from '@lucide/vue';
import { ref } from 'vue';
import ProductCard from '@/components/custom/product/ProductCard.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/employee/products';
import {
    AssemblyDifficultyLabels,
    StatusLabels,
    type BreadcrumbItem,
} from '@/types';
import type { Product } from '@/types/product';

const props = defineProps<{
    product: Product;
}>();

const activeTab = ref('general');

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

function statusLabel(status: string): string {
    const map: Record<string, string> = {
        draft: 'Nháp',
        published: 'Đã xuất bản',
        archived: 'Lưu trữ',
        active: 'Hoạt động',
        inactive: 'Ngưng hoạt động',
    };
    return map[status] ?? status;
}

function difficultyLabel(level: string): string {
    const map: Record<string, string> = {
        easy: 'Dễ',
        medium: 'Trung bình',
        hard: 'Khó',
    };
    return map[level?.toLowerCase()] ?? level;
}

function getOptionLabel(
    product: Product,
    namespace: string,
    value: string,
): string {
    const group = product.option_groups?.find((g) => g.namespace === namespace);
    const option = group?.options.find((o) => o.value === value);
    return option ? option.label : value;
}

function formatPrice(value: string | number): string {
    return Number(value).toLocaleString('vi-VN');
}

function formatPercentage(value: number | string): string {
    return `${Number(value).toFixed(2)}%`;
}
</script>

<template>
    <Head :title="product.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header Section -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="index().url"
                        class="rounded-full p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        title="Quay lại danh sách"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight">
                                {{ product.name }}
                            </h1>
                            <Badge
                                :class="statusClass(product.status)"
                                class="px-3 py-1 text-xs font-semibold"
                            >
                                {{ product.status_label }}
                            </Badge>
                        </div>
                        <p
                            class="flex items-center gap-2 text-sm text-muted-foreground"
                        >
                            <Calendar class="h-3 w-3" />
                            Cập nhật lần cuối: {{ product.updated_at }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Actions would go here, e.g., Edit Button -->
                    <Link
                        :href="`/employee/products/${product.id}/edit`"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Chỉnh sửa sản phẩm
                    </Link>
                </div>
            </div>

            <!-- Quick Stats Bar -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-5">
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-full bg-blue-100 p-2 text-blue-600">
                            <Eye class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Lượt xem
                            </p>
                            <p class="text-lg font-bold">
                                {{ product.views_count }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div
                            class="rounded-full bg-green-100 p-2 text-green-600"
                        >
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Đã bán
                            </p>
                            <p class="text-lg font-bold">
                                {{ product.sales_count }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div
                            class="rounded-full bg-purple-100 p-2 text-purple-600"
                        >
                            <MessageSquare class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Đánh giá
                            </p>
                            <p class="text-lg font-bold">
                                {{ product.reviews_count }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div
                            class="rounded-full bg-orange-100 p-2 text-orange-600"
                        >
                            <Star class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Điểm TB
                            </p>
                            <p class="text-lg font-bold">
                                {{ product.average_rating || '0.0' }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="hidden lg:block">
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-full bg-zinc-100 p-2 text-zinc-600">
                            <Layers class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground uppercase"
                            >
                                Biến thể
                            </p>
                            <p class="text-lg font-bold">
                                {{ product.variants_count ?? 0 }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full max-w-2xl grid-cols-5">
                    <TabsTrigger
                        value="general"
                        class="flex items-center gap-2"
                    >
                        <Info class="h-4 w-4" /> Chung
                    </TabsTrigger>
                    <TabsTrigger
                        value="variants"
                        class="flex items-center gap-2"
                    >
                        <Database class="h-4 w-4" /> Biến thể
                    </TabsTrigger>
                    <TabsTrigger value="specs" class="flex items-center gap-2">
                        <Settings class="h-4 w-4" /> Kỹ thuật
                    </TabsTrigger>
                    <TabsTrigger
                        value="preview"
                        class="flex items-center gap-2"
                    >
                        <LayoutDashboard class="h-4 w-4" /> Xem trước
                    </TabsTrigger>
                    <TabsTrigger value="seo" class="flex items-center gap-2">
                        <Tag class="h-4 w-4" /> SEO/Tags
                    </TabsTrigger>
                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="mt-6 space-y-6">
                    <div class="grid gap-6 md:grid-cols-3">
                        <Card class="md:col-span-2">
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-lg"
                                >
                                    <Package
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    Thông tin cơ bản
                                </CardTitle>
                                <CardDescription
                                    >Chi tiết phân loại và định danh sản
                                    phẩm</CardDescription
                                >
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <p
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            Danh mục
                                        </p>
                                        <p class="text-base font-semibold">
                                            {{
                                                product.category
                                                    ?.display_name ?? '—'
                                            }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <p
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            Bộ sưu tập
                                        </p>
                                        <p class="text-base font-semibold">
                                            {{
                                                product.collection
                                                    ?.display_name ?? '—'
                                            }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <p
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            Nhà cung cấp
                                        </p>
                                        <p class="text-base font-semibold">
                                            {{ product.vendor?.name ?? '—' }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <p
                                            class="text-sm font-medium text-muted-foreground"
                                        >
                                            Khoảng giá
                                        </p>
                                        <p class="text-base font-semibold">
                                            {{
                                                formatPrice(product.min_price)
                                            }}đ —
                                            {{
                                                formatPrice(product.max_price)
                                            }}đ
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-lg"
                                >
                                    <ShieldCheck
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    Hậu cần & Bảo hành
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground"
                                        >Bảo hành</span
                                    >
                                    <span class="text-sm font-semibold">{{
                                        product.warranty_months
                                            ? `${product.warranty_months} tháng`
                                            : '—'
                                    }}</span>
                                </div>
                                <Separator />
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground"
                                        >Lắp đặt</span
                                    >
                                    <Badge
                                        :variant="
                                            product.assembly_info?.required
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        class="text-[10px]"
                                    >
                                        {{
                                            product.assembly_info?.required
                                                ? 'Yêu cầu'
                                                : 'Không cần'
                                        }}
                                    </Badge>
                                </div>
                                <div
                                    v-if="product.assembly_info?.required"
                                    class="space-y-2 pt-2"
                                >
                                    <div class="flex justify-between text-xs">
                                        <span class="text-muted-foreground"
                                            >Độ khó:</span
                                        >
                                        <span class="font-medium capitalize">{{
                                            AssemblyDifficultyLabels[
                                                product.assembly_info
                                                    .difficulty_level as keyof typeof AssemblyDifficultyLabels
                                            ]
                                        }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-muted-foreground"
                                            >Thời gian:</span
                                        >
                                        <span class="font-medium"
                                            >{{
                                                product.assembly_info
                                                    .estimated_minutes
                                            }}
                                            phút</span
                                        >
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <Layers class="h-5 w-5 text-muted-foreground" />
                                Đặc điểm nổi bật
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="
                                    product.features &&
                                    product.features.length > 0
                                "
                                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <div
                                    v-for="(feature, i) in product.features"
                                    :key="i"
                                    class="flex gap-3 rounded-lg border bg-muted/30 p-3"
                                >
                                    <div
                                        class="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary"
                                    />
                                    <div>
                                        <p class="text-sm font-bold">
                                            {{ feature.display_name }}
                                        </p>
                                        <p
                                            class="text-xs leading-relaxed text-muted-foreground"
                                        >
                                            {{ feature.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="py-6 text-center text-sm text-muted-foreground"
                            >
                                Chưa có thông tin đặc điểm nổi bật.
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Variants Tab -->
                <TabsContent value="variants" class="mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg"
                                >Quản lý Biến thể</CardTitle
                            >
                            <CardDescription
                                >Chi tiết SKU, Giá bán, Biên lợi nhuận và Tình
                                trạng kho</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-hidden rounded-md border">
                                <Table>
                                    <TableHeader class="bg-muted/50">
                                        <TableRow>
                                            <TableHead class="font-bold"
                                                >SKU</TableHead
                                            >
                                            <TableHead class="font-bold"
                                                >Tên Biến Thể</TableHead
                                            >
                                            <TableHead
                                                class="text-right font-bold"
                                                >Giá Bán</TableHead
                                            >
                                            <TableHead
                                                class="text-right font-bold"
                                                >Lợi Nhuận Tối Thiểu</TableHead
                                            >
                                            <TableHead
                                                class="text-center font-bold"
                                                >Trạng Thái</TableHead
                                            >
                                            <TableHead
                                                class="text-right font-bold"
                                                >Doanh Số</TableHead
                                            >
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="variant in product.variants"
                                            :key="variant.id"
                                        >
                                            <TableCell
                                                class="font-mono text-xs font-bold text-primary"
                                                >{{ variant.sku }}</TableCell
                                            >
                                            <TableCell>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-sm font-medium"
                                                        >{{
                                                            variant.name ||
                                                            variant.swatch_label
                                                        }}</span
                                                    >
                                                    <div
                                                        class="mt-1 flex flex-wrap gap-1"
                                                    >
                                                        <Badge
                                                            v-for="(
                                                                val, ns
                                                            ) in variant.option_values"
                                                            :key="ns"
                                                            variant="outline"
                                                            class="h-4 px-1 py-0 text-[9px]"
                                                        >
                                                            {{
                                                                getOptionLabel(
                                                                    product,
                                                                    ns,
                                                                    val,
                                                                )
                                                            }}
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell
                                                class="text-right font-semibold"
                                                >{{
                                                    formatPrice(variant.price)
                                                }}đ</TableCell
                                            >
                                            <TableCell
                                                class="text-right font-medium text-green-600"
                                            >
                                                {{
                                                    formatPrice(
                                                        Number(
                                                            variant.profit_margin_value,
                                                        ),
                                                    )
                                                }}
                                                <span
                                                    class="text-[10px] opacity-70"
                                                    >{{
                                                        variant.profit_margin_unit ===
                                                        'fixed'
                                                            ? 'cố định'
                                                            : '%'
                                                    }}</span
                                                >
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Badge
                                                    :variant="
                                                        variant.status ===
                                                        'active'
                                                            ? 'default'
                                                            : 'secondary'
                                                    "
                                                    class="text-[10px]"
                                                >
                                                    {{
                                                        StatusLabels[
                                                            variant.status as keyof typeof StatusLabels
                                                        ]
                                                    }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">{{
                                                variant.sales_count
                                            }}</TableCell>
                                        </TableRow>
                                        <TableRow
                                            v-if="
                                                !product.variants ||
                                                product.variants.length === 0
                                            "
                                        >
                                            <TableCell
                                                colspan="6"
                                                class="py-10 text-center text-muted-foreground"
                                            >
                                                Không có biến thể nào cho sản
                                                phẩm này.
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Technical Specs Tab -->
                <TabsContent value="specs" class="mt-6 space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-lg"
                                >
                                    <Settings
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    Thông số kỹ thuật
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-6">
                                    <div
                                        v-for="(
                                            details, group
                                        ) in product.specifications"
                                        :key="group"
                                        class="space-y-3"
                                    >
                                        <div
                                            class="border-b pb-1 text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >
                                            {{ group }}
                                        </div>
                                        <div class="grid gap-2">
                                            <div
                                                v-for="(
                                                    item, idx
                                                ) in details.items"
                                                :key="idx"
                                                class="flex justify-between py-1 text-sm"
                                            >
                                                <span class="text-medium">{{
                                                    item.display_name
                                                }}</span>
                                                <span class="font-medium">{{
                                                    item.description
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-if="
                                            !product.specifications ||
                                            Object.keys(product.specifications)
                                                .length === 0
                                        "
                                        class="py-6 text-center text-sm text-muted-foreground"
                                    >
                                        Chưa có thông số kỹ thuật.
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-lg"
                                >
                                    <Layers
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    Cấu trúc tùy chọn
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div
                                    v-for="(group, gi) in product.option_groups"
                                    :key="gi"
                                    class="space-y-3 rounded-lg border bg-muted/20 p-3"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span class="text-sm font-bold">{{
                                            group.name
                                        }}</span>
                                        <Badge
                                            variant="outline"
                                            class="text-[10px]"
                                            >{{ group.namespace }}</Badge
                                        >
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="opt in group.options"
                                            :key="opt.value"
                                            variant="secondary"
                                            class="text-[10px]"
                                        >
                                            {{ opt.label }}
                                        </Badge>
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        !product.option_groups ||
                                        product.option_groups.length === 0
                                    "
                                    class="py-6 text-center text-sm text-muted-foreground"
                                >
                                    Không có nhóm tùy chọn.
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <!-- Storefront Preview Tab -->
                <TabsContent value="preview" class="mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <ShoppingBag
                                    class="h-5 w-5 text-muted-foreground"
                                />
                                Xem trước giao diện Storefront
                            </CardTitle>
                            <CardDescription
                                >Hình ảnh thực tế khách hàng sẽ nhìn thấy trên
                                website</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <template
                                    v-if="
                                        product.product_cards &&
                                        product.product_cards.length > 0
                                    "
                                >
                                    <ProductCard
                                        v-for="card in product.product_cards"
                                        :key="card.id"
                                        :product-card="card"
                                    />
                                </template>
                                <template v-else>
                                    <div
                                        class="col-span-full rounded-xl border-2 border-dashed py-20 text-center text-muted-foreground"
                                    >
                                        Hiện tại không có thẻ sản phẩm (Product
                                        Card) để hiển thị.
                                    </div>
                                </template>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- SEO / Analytics Tab -->
                <TabsContent value="seo" class="mt-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-lg"
                                >
                                    <Tag
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    SEO & Định danh
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground"
                                        >Trạng thái hiển thị</span
                                    >
                                    <Badge
                                        :variant="
                                            product.status === 'published'
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        class="text-[10px]"
                                    >
                                        {{ statusLabel(product.status) }}
                                    </Badge>
                                </div>
                                <Separator />
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground"
                                        >Ngày xuất bản</span
                                    >
                                    <span class="text-sm font-medium">{{
                                        product.published_date ||
                                        'Chưa xuất bản'
                                    }}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-lg"
                                >
                                    <TrendingUp
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                    Chỉ số hiệu quả
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="grid grid-cols-2 gap-4">
                                <div
                                    class="rounded-lg border bg-muted/20 p-3 text-center"
                                >
                                    <p
                                        class="text-xs font-semibold text-muted-foreground uppercase"
                                    >
                                        Tỷ lệ chuyển đổi (ước)
                                    </p>
                                    <p class="text-xl font-bold">
                                        {{
                                            product.views_count > 0
                                                ? (
                                                      (product.sales_count /
                                                          product.views_count) *
                                                      100
                                                  ).toFixed(2)
                                                : '0.00'
                                        }}%
                                    </p>
                                </div>
                                <div
                                    class="rounded-lg border bg-muted/20 p-3 text-center"
                                >
                                    <p
                                        class="text-xs font-semibold text-muted-foreground uppercase"
                                    >
                                        Trung bình lượt xem/sale
                                    </p>
                                    <p class="text-xl font-bold">
                                        {{
                                            product.sales_count > 0
                                                ? (
                                                      product.views_count /
                                                      product.sales_count
                                                  ).toFixed(0)
                                                : '—'
                                        }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
