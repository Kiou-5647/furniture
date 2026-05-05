<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    VisXYContainer,
    VisArea,
    VisGroupedBar,
    VisAxis,
    VisCrosshair,
    VisTooltip,
} from '@unovis/vue';

import {
    TrendingUp,
    ShoppingCart,
    Users,
    DollarSign,
    ArrowUpRight,
    AlertTriangle,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    user: {
        name: string;
        email: string;
        roles: string[];
        permissions: string[];
    };
    employee?: Record<string, unknown>;
    tables: {
        recent_orders: Array<{
            id: string;
            number: string;
            customer: string;
            total: number;
            status: string;
            created_at: string;
        }>;
        recent_bookings: Array<{
            id: string;
            customer: string;
            designer: string;
            start_at: string;
            status: string;
        }>;
        low_stock: Array<{
            variant: string;
            product: string;
            quantity: number;
            location: string;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Bảng điều khiển', href: '#' }];

// State for independent periods
const summaryPeriod = ref('today');
const ordersPeriod = ref('month');
const financePeriod = ref('month');

// State for async data
const summary = ref<any>(null);
const ordersTrend = ref<any[]>([]);
const financialAnalysis = ref<any[]>([]);

const loadingSummary = ref(false);
const loadingOrders = ref(false);
const loadingFinance = ref(false);

const fetchSummary = async () => {
    loadingSummary.value = true;
    try {
        const res = await axios.get(
            `/nhan-vien/dashboard/summary?period=${summaryPeriod.value}`,
        );
        summary.value = res.data;
    } finally {
        loadingSummary.value = false;
    }
};

const fetchOrdersTrend = async () => {
    loadingOrders.value = true;
    try {
        const res = await axios.get(
            `/nhan-vien/dashboard/orders-trend?period=${ordersPeriod.value}`,
        );
        ordersTrend.value = res.data;
    } finally {
        loadingOrders.value = false;
    }
};

const fetchFinancialAnalysis = async () => {
    loadingFinance.value = true;
    try {
        const res = await axios.get(
            `/nhan-vien/dashboard/financial-analysis?period=${financePeriod.value}`,
        );
        financialAnalysis.value = res.data;
    } finally {
        loadingFinance.value = false;
    }
};

onMounted(() => {
    fetchSummary();
    fetchOrdersTrend();
    fetchFinancialAnalysis();
});

const getTrendClass = (trend: number) => {
    return trend >= 0 ? 'text-green-500' : 'text-red-500';
};

const tickPriceFormat = (value: any) => {
    const num = typeof value === 'string' ? Number(value) : value;
    return new Intl.NumberFormat('vi-VN', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

const getStatusBadge = (status: string) => {
    const map: Record<string, { label: string; variant: any }> = {
        pending: { label: 'Chờ xử lý', variant: 'outline' },
        processing: { label: 'Đang xử lý', variant: 'secondary' },
        completed: { label: 'Hoàn thành', variant: 'default' },
        cancelled: { label: 'Đã hủy', variant: 'destructive' },
        confirmed: { label: 'Đã xác nhận', variant: 'default' },
        pending_deposit: { label: 'Chờ đặt cọc', variant: 'outline' },
    };
    return map[status] || { label: status, variant: 'outline' };
};
</script>

<template>
    <Head title="Bảng điều khiển nhân viên" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Xin chào, {{ user.name }} 👋
                    </h1>
                    <p class="text-muted-foreground">
                        Theo dõi hiệu suất kinh doanh thời gian thực.
                    </p>
                </div>
            </div>

            <!-- Top Section: KPIs and Order Trend -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- KPI Cards Group -->
                <div class="space-y-4 lg:col-span-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Chỉ số chính</h2>
                        <div
                            class="flex items-center gap-2 rounded-lg bg-muted p-1"
                        >
                            <button
                                v-for="p in [
                                    'today',
                                    'week',
                                    'month',
                                    'quarter',
                                    'year',
                                ]"
                                :key="p"
                                @click="
                                    summaryPeriod = p;
                                    fetchSummary();
                                "
                                :class="[
                                    'rounded-md px-2 py-1 text-[10px] font-medium transition-all',
                                    summaryPeriod === p
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                            >
                                {{
                                    p === 'today'
                                        ? 'Hôm nay'
                                        : p === 'week'
                                          ? 'Tuần'
                                          : p === 'month'
                                            ? 'Tháng'
                                            : p === 'quarter'
                                              ? 'Quý'
                                              : 'Năm'
                                }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4" v-if="summary">
                        <Card>
                            <CardHeader
                                class="flex flex-row items-center justify-between space-y-0 pb-2"
                            >
                                <CardTitle class="text-xs font-medium"
                                    >Khách hàng</CardTitle
                                >
                                <Users class="h-3 w-3 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ summary.customers.value }}
                                </div>
                                <div
                                    :class="[
                                        'flex items-center gap-1 text-[10px]',
                                        getTrendClass(summary.customers.trend),
                                    ]"
                                >
                                    <TrendingUp
                                        :class="[
                                            'h-2 w-2',
                                            summary.customers.trend < 0
                                                ? 'rotate-180'
                                                : '',
                                        ]"
                                    />
                                    {{ summary.customers.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader
                                class="flex flex-row items-center justify-between space-y-0 pb-2"
                            >
                                <CardTitle class="text-xs font-medium"
                                    >Đơn hàng</CardTitle
                                >
                                <ShoppingCart
                                    class="h-3 w-3 text-muted-foreground"
                                />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ summary.orders.value }}
                                </div>
                                <div
                                    :class="[
                                        'flex items-center gap-1 text-[10px]',
                                        getTrendClass(summary.orders.trend),
                                    ]"
                                >
                                    <TrendingUp
                                        :class="[
                                            'h-2 w-2',
                                            summary.orders.trend < 0
                                                ? 'rotate-180'
                                                : '',
                                        ]"
                                    />
                                    {{ summary.orders.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader
                                class="flex flex-row items-center justify-between space-y-0 pb-2"
                            >
                                <CardTitle class="text-xs font-medium"
                                    >Doanh thu</CardTitle
                                >
                                <DollarSign
                                    class="h-3 w-3 text-muted-foreground"
                                />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ formatPrice(summary.revenue.value) }}
                                </div>
                                <div
                                    :class="[
                                        'flex items-center gap-1 text-[10px]',
                                        getTrendClass(summary.revenue.trend),
                                    ]"
                                >
                                    <TrendingUp
                                        :class="[
                                            'h-2 w-2',
                                            summary.revenue.trend < 0
                                                ? 'rotate-180'
                                                : '',
                                        ]"
                                    />
                                    {{ summary.revenue.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader
                                class="flex flex-row items-center justify-between space-y-0 pb-2"
                            >
                                <CardTitle class="text-xs font-medium"
                                    >Lợi nhuận</CardTitle
                                >
                                <TrendingUp class="h-3 w-3 text-green-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold text-green-600">
                                    {{ formatPrice(summary.profit.value) }}
                                </div>
                                <div
                                    :class="[
                                        'flex items-center gap-1 text-[10px]',
                                        getTrendClass(summary.profit.trend),
                                    ]"
                                >
                                    <TrendingUp
                                        :class="[
                                            'h-2 w-2',
                                            summary.profit.trend < 0
                                                ? 'rotate-180'
                                                : '',
                                        ]"
                                    />
                                    {{ summary.profit.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-4 opacity-50">
                        <Card
                            v-for="i in 4"
                            :key="i"
                            class="h-24 animate-pulse bg-muted/50"
                        ></Card>
                    </div>
                </div>

                <!-- Order Trend Chart Group -->
                <div class="space-y-4 lg:col-span-7">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Số lượng đơn hàng</h2>
                        <div
                            class="flex items-center gap-2 rounded-lg bg-muted p-1"
                        >
                            <button
                                v-for="p in [
                                    'week',
                                    'month',
                                    'quarter',
                                    'year',
                                ]"
                                :key="p"
                                @click="
                                    ordersPeriod = p;
                                    fetchOrdersTrend();
                                "
                                :class="[
                                    'rounded-md px-2 py-1 text-[10px] font-medium transition-all',
                                    ordersPeriod === p
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                            >
                                {{
                                    p === 'week'
                                        ? 'Tuần'
                                        : p === 'month'
                                          ? 'Tháng'
                                          : p === 'quarter'
                                            ? 'Quý'
                                            : 'Năm'
                                }}
                            </button>
                        </div>
                    </div>
                    <Card>
                        <CardContent
                            class="flex h-[320px] items-center justify-center p-4"
                        >
                            <div
                                v-if="loadingOrders"
                                class="flex h-full items-center justify-center"
                            >
                                Đang tải...
                            </div>
                            <VisXYContainer v-else :data="ordersTrend">
                                <VisAxis
                                    type="x"
                                    :tickFormat="
                                        (v: string | number) =>
                                            ordersTrend[v as any]?.label || v
                                    "
                                />
                                <VisAxis type="y" />
                                <VisGroupedBar
                                    :x="(d: { index: any }) => d.index"
                                    :y="(d: { count: any }) => d.count"
                                    color="#6366f1"
                                />
                                <VisTooltip />
                                <VisCrosshair
                                    :template="
                                        (d: any) =>
                                            `${d.label}\\nSố đơn: ${d.count}`
                                    "
                                />
                            </VisXYContainer>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Financial Analysis - Full Width -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Phân tích Tài chính</h2>
                    <div
                        class="flex items-center gap-2 rounded-lg bg-muted p-1"
                    >
                        <button
                            v-for="p in ['week', 'month', 'quarter', 'year']"
                            :key="p"
                            @click="
                                financePeriod = p;
                                fetchFinancialAnalysis();
                            "
                            :class="[
                                'rounded-md px-2 py-1 text-[10px] font-medium transition-all',
                                financePeriod === p
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground',
                            ]"
                        >
                            {{
                                p === 'week'
                                    ? 'Tuần'
                                    : p === 'month'
                                      ? 'Tháng'
                                      : p === 'quarter'
                                        ? 'Quý'
                                        : 'Năm'
                            }}
                        </button>
                    </div>
                </div>
                <Card>
                    <CardContent class="h-[500px] p-4">
                        <div
                            v-if="loadingFinance"
                            class="flex h-full items-center justify-center"
                        >
                            Đang tải...
                        </div>
                        <VisXYContainer
                            v-else
                            :height="400"
                            :padding="{ top: 20, bottom: 0 }"
                            :data="financialAnalysis"
                        >
                            <VisAxis
                                type="x"
                                :tickFormat="
                                    (v: string | number) =>
                                        financialAnalysis[v as any]?.label || v
                                "
                            />
                            <VisArea
                                :x="(d: { index: any }) => d.index"
                                :y="(d: { revenue: any }) => d.revenue"
                                color="#3b82f6"
                                :opacity="0.2"
                                :strokeWidth="2"
                                :interpolateMissingData="true"
                            />
                            <VisArea
                                :x="(d: { index: any }) => d.index"
                                :y="(d: { profit: any }) => d.profit"
                                color="#10b981"
                                :opacity="0.4"
                                :strokeWidth="2"
                                :interpolateMissingData="true"
                            />
                            <VisAxis type="y" :tickFormat="tickPriceFormat" />
                            <VisTooltip />
                            <VisCrosshair
                                :template="
                                    (d: any) =>
                                        `${d.label}\\n Doanh thu: ${formatPrice(d.revenue)}\\nLợi nhuận: ${formatPrice(d.profit)}`
                                "
                            />
                        </VisXYContainer>
                    </CardContent>
                </Card>
            </div>

            <!-- Utility Tables -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <div>
                            <CardTitle>Đơn hàng gần đây</CardTitle>
                            <CardDescription
                                >Theo dõi các giao dịch mới
                                nhất</CardDescription
                            >
                        </div>
                        <ArrowUpRight
                            class="h-4 w-4 cursor-pointer text-muted-foreground transition-colors hover:text-foreground"
                        />
                    </CardHeader>
                    <CardContent class="max-h-[300px] overflow-y-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Mã đơn</TableHead>
                                    <TableHead>Khách hàng</TableHead>
                                    <TableHead>Tổng tiền</TableHead>
                                    <TableHead>Trạng thái</TableHead>
                                    <TableHead>Ngày tạo</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="order in tables.recent_orders"
                                    :key="order.id"
                                >
                                    <TableCell class="font-medium">{{
                                        order.number
                                    }}</TableCell>
                                    <TableCell>{{ order.customer }}</TableCell>
                                    <TableCell>{{
                                        formatPrice(order.total)
                                    }}</TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                getStatusBadge(order.status)
                                                    .variant
                                            "
                                        >
                                            {{
                                                getStatusBadge(order.status)
                                                    .label
                                            }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        class="text-xs text-muted-foreground"
                                        >{{ order.created_at }}</TableCell
                                    >
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlertTriangle class="h-5 w-5 text-destructive" />
                            Cảnh báo tồn kho
                        </CardTitle>
                        <CardDescription
                            >Sản phẩm sắp hết hàng ( dưới 5)</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="max-h-[300px] overflow-y-auto pr-2">
                            <Table>
                                <TableHeader
                                    class="sticky top-0 z-10 bg-background"
                                >
                                    <TableRow>
                                        <TableHead>Sản phẩm</TableHead>
                                        <TableHead class="text-center"
                                            >Số lượng</TableHead
                                        >
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="item in tables.low_stock"
                                        :key="item.variant"
                                    >
                                        <TableCell class="flex flex-col font-medium">
                                            <span>
                                                {{
                                                    item.product +
                                                    ' ' +
                                                    item.variant
                                                }}
                                            </span>
                                            <span class="text-muted-foreground">
                                                {{ item.location }}
                                            </span></TableCell
                                        >
                                        <TableCell class="text-center">
                                            <Badge variant="destructive">{{
                                                item.quantity
                                            }}</Badge>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow
                                        v-if="tables.low_stock.length === 0"
                                    >
                                        <TableCell
                                            colspan="2"
                                            class="py-4 text-center text-muted-foreground"
                                        >
                                            Không có sản phẩm nào sắp hết hàng.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.unovis-crosshair) {
    white-space: pre-wrap;
    line-height: 1.4;
    padding: 8px;
}
</style>
