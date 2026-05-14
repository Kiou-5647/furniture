<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Calendar } from '@lucide/vue';
import {
    VisXYContainer,
    VisArea,
    VisGroupedBar,
    VisAxis,
    VisCrosshair,
    VisTooltip,
    VisBulletLegend,
    VisDonut,
    VisSingleContainer,
} from '@unovis/vue';
import axios from 'axios';

import {
    TrendingUp,
    ShoppingCart,
    Users,
    DollarSign,
    ArrowUpRight,
    AlertTriangle,
} from 'lucide-vue-next';
import { ref, onMounted, shallowRef, computed } from 'vue';
import { getBookingStatusDistribution, getFinancialAnalysis, getOrderStatusDistribution, getOrdersTrend, getRefundStatusDistribution, getSummary } from '@/actions/App/Http/Controllers/Employee/EmployeeDashboardController';
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
import { formatPrice } from '@/lib';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    user: {
        name: string;
        email: string;
        can: {
            order: boolean;
            booking: boolean;
            inventory: boolean;
            refund: boolean;
        };
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

const orderDistPeriod = ref('month');
const bookingDistPeriod = ref('month');
const refundDistPeriod = ref('month');

// State cho data
const orderDist = ref<any[]>([]);
const bookingDist = ref<any[]>([]);
const refundDist = ref<any[]>([]);
const loadingOrderDist = ref(false);
const loadingBookingDist = ref(false);
const loadingRefundDist = ref(false);

const fetchOrderDist = async () => {
    loadingOrderDist.value = true;
    try {
        const res = await axios.get(getOrderStatusDistribution().url + `?period=${orderDistPeriod.value}`);
        orderDist.value = res.data;
        console.log(orderDist.value)
    } finally {
        loadingOrderDist.value = false;
    }
};

const fetchBookingDist = async () => {
    loadingBookingDist.value = true;
    try {
        const res = await axios.get(getBookingStatusDistribution().url + `?period=${bookingDistPeriod.value}`);
        bookingDist.value = res.data;
    } finally {
        loadingBookingDist.value = false;
    }
};

const fetchRefundDist = async () => {
    loadingRefundDist.value = true
    try {
        const res = await axios.get(getRefundStatusDistribution().url + `?period=${refundDistPeriod.value}`);
        refundDist.value = res.data;
    } finally {
        loadingRefundDist.value = false;
    }
};

const fetchSummary = async () => {
    loadingSummary.value = true;
    try {
        const res = await axios.get(getSummary().url + `?period=${summaryPeriod.value}`);
        summary.value = res.data;
    } finally {
        loadingSummary.value = false;
    }
};

const fetchOrdersTrend = async () => {
    loadingOrders.value = true;
    try {
        const res = await axios.get(getOrdersTrend().url + `?period=${ordersPeriod.value}`);
        ordersTrend.value = res.data;
    } finally {
        loadingOrders.value = false;
    }
};

const fetchFinancialAnalysis = async () => {
    loadingFinance.value = true;
    try {
        const res = await axios.get(getFinancialAnalysis().url + `?period=${financePeriod.value}`);
        financialAnalysis.value = res.data;
        console.log(financialAnalysis.value)
    } finally {
        loadingFinance.value = false;
    }
};

onMounted(() => {
    fetchSummary();
    fetchOrdersTrend();
    fetchFinancialAnalysis();
    fetchOrderDist();
    fetchBookingDist();
    fetchRefundDist();
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

const getXIndex = (d: any) => d.index;
const getYCount = (d: any) => d.count;
const getYRevenue = (d: any) => d.revenue;
const getYProfit = (d: any) => d.profit;
const getYRefunds = (d: any) => d.refunds;

const financialItems = [
    { name: 'Doanh thu', color: 'blue' },
    { name: 'Lợi nhuận', color: 'green' },
    { name: 'Hoàn tiền', color: 'red' },
]

const donutValue = (d: any) => d.value;
const donutColor = (d: any) => d.color;

const orderLegendItems = computed(() => {
    return orderDist.value.map(item => ({
        name: item.key,
        color: item.color
    }));
});

const bookingLegendItems = computed(() => {
    return bookingDist.value.map(item => ({
        name: item.key,
        color: item.color
    }));
});
const refundLegendItems = computed(() => {
    return refundDist.value.map(item => ({
        name: item.key,
        color: item.color
    }));
});

</script>

<template>

    <Head title="Bảng điều khiển nhân viên" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
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
                        <div class="flex items-center gap-2 rounded-lg bg-muted p-1">
                            <button v-for="p in [
                                'today',
                                'week',
                                'month',
                                'quarter',
                                'year',
                            ]" :key="p" @click="
                                summaryPeriod = p;
                            fetchSummary();
                            " :class="[
                                'rounded-md px-2 py-1 text-[10px] font-medium transition-all',
                                summaryPeriod === p
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground',
                            ]">
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
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-xs font-medium">Khách hàng</CardTitle>
                                <Users class="h-3 w-3 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ summary.customers.value }}
                                </div>
                                <div :class="[
                                    'flex items-center gap-1 text-[10px]',
                                    getTrendClass(summary.customers.trend),
                                ]">
                                    <TrendingUp :class="[
                                        'h-2 w-2',
                                        summary.customers.trend < 0
                                            ? 'rotate-180'
                                            : '',
                                    ]" />
                                    {{ summary.customers.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-xs font-medium">Đơn hàng</CardTitle>
                                <ShoppingCart class="h-3 w-3 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ summary.orders.value }}
                                </div>
                                <div :class="[
                                    'flex items-center gap-1 text-[10px]',
                                    getTrendClass(summary.orders.trend),
                                ]">
                                    <TrendingUp :class="[
                                        'h-2 w-2',
                                        summary.orders.trend < 0
                                            ? 'rotate-180'
                                            : '',
                                    ]" />
                                    {{ summary.orders.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-xs font-medium">Lịch hẹn</CardTitle>
                                <Calendar class="h-3 w-3 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ summary.bookings.value }}
                                </div>
                                <div :class="[
                                    'flex items-center gap-1 text-[10px]',
                                    getTrendClass(summary.bookings.trend),
                                ]">
                                    <TrendingUp :class="[
                                        'h-2 w-2',
                                        summary.bookings.trend < 0 ? 'rotate-180' : '',
                                    ]" />
                                    {{ summary.bookings.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-xs font-medium">Doanh thu</CardTitle>
                                <DollarSign class="h-3 w-3 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold">
                                    {{ formatPrice(summary.revenue.value) }}
                                </div>
                                <div :class="[
                                    'flex items-center gap-1 text-[10px]',
                                    getTrendClass(summary.revenue.trend),
                                ]">
                                    <TrendingUp :class="[
                                        'h-2 w-2',
                                        summary.revenue.trend < 0
                                            ? 'rotate-180'
                                            : '',
                                    ]" />
                                    {{ summary.revenue.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-xs font-medium">Lợi nhuận</CardTitle>
                                <TrendingUp class="h-3 w-3 text-green-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold text-green-600">
                                    {{ formatPrice(summary.profit.value) }}
                                </div>
                                <div :class="[
                                    'flex items-center gap-1 text-[10px]',
                                    getTrendClass(summary.profit.trend),
                                ]">
                                    <TrendingUp :class="[
                                        'h-2 w-2',
                                        summary.profit.trend < 0
                                            ? 'rotate-180'
                                            : '',
                                    ]" />
                                    {{ summary.profit.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-xs font-medium">Hoàn tiền</CardTitle>
                                <ArrowUpRight class="h-3 w-3 text-destructive" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-xl font-bold text-destructive">
                                    {{ formatPrice(summary.refunds.value) }}
                                </div>
                                <div
                                    :class="['flex items-center gap-1 text-[10px]', getTrendClass(summary.refunds.trend)]">
                                    <TrendingUp :class="['h-2 w-2', summary.refunds.trend < 0 ? 'rotate-180' : '']" />
                                    {{ summary.refunds.trend.toFixed(1) }}%
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-4 opacity-50">
                        <Card v-for="i in 4" :key="i" class="h-24 animate-pulse bg-muted/50"></Card>
                    </div>
                </div>

                <!-- Order Trend Chart Group -->
                <div class="space-y-4 lg:col-span-7">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Số lượng đơn hàng</h2>
                        <div class="flex items-center gap-2 rounded-lg bg-muted p-1">
                            <button v-for="p in [
                                'week',
                                'month',
                                'quarter',
                                'year',
                            ]" :key="p" @click="
                                ordersPeriod = p;
                            fetchOrdersTrend();
                            " :class="[
                                'rounded-md px-2 py-1 text-[10px] font-medium transition-all',
                                ordersPeriod === p
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground',
                            ]">
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
                        <CardContent class="flex h-[320px] items-center justify-center p-4">
                            <div v-if="loadingOrders" class="flex h-full items-center justify-center">
                                Đang tải...
                            </div>
                            <VisXYContainer v-else :data="ordersTrend">
                                <VisAxis type="x" :tickFormat="(v: string | number) =>
                                    ordersTrend[v as any]?.label || v
                                    " />
                                <VisAxis type="y" />
                                <VisGroupedBar :x="getXIndex" :y="getYCount" color="#6366f1" />
                                <VisTooltip />
                                <VisCrosshair :template="(d: any) =>
                                    `${d.label}<br /> Số đơn: ${d.count}`
                                    " />
                            </VisXYContainer>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Financial Analysis - Full Width -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Phân tích Tài chính</h2>
                    <div class="flex items-center gap-2 rounded-lg bg-muted p-1">
                        <button v-for="p in ['week', 'month', 'quarter', 'year']" :key="p" @click="
                            financePeriod = p;
                        fetchFinancialAnalysis();
                        " :class="[
                            'rounded-md px-2 py-1 text-[10px] font-medium transition-all',
                            financePeriod === p
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]">
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
                    <CardContent class="flex flex-col h-[500px] items-center justify-center p-4">
                        <div v-if="loadingFinance" class="flex h-full items-center justify-center">
                            Đang tải...
                        </div>
                        <VisXYContainer v-else :height="400" :padding="{ top: 20, bottom: 0 }"
                            :data="financialAnalysis">
                            <VisAxis type="x" :tickFormat="(v: string | number) =>
                                financialAnalysis[v as any]?.label || v
                                " />
                            <VisArea :x="getXIndex" :y="getYRevenue" color="blue" :opacity="0.2" :strokeWidth="2"
                                :interpolateMissingData="true" />
                            <VisArea :x="getXIndex" :y="getYProfit" color="green" :opacity="0.4" :strokeWidth="2"
                                :interpolateMissingData="true" />
                            <VisArea :x="getXIndex" :y="getYRefunds" color="red" :opacity="0.4" :strokeWidth="2"
                                :interpolateMissingData="true" />
                            <VisAxis type="y" :tickFormat="tickPriceFormat" />
                            <VisTooltip />
                            <VisCrosshair :template="(d: any) => `${d.label}
                                <br /> Doanh thu: ${formatPrice(d.revenue)}
                                <br /> Lợi nhuận: ${formatPrice(d.profit)}
                                <br /> Hoàn tiền: ${formatPrice(d.refunds)}`" />
                        </VisXYContainer>
                        <VisBulletLegend :items="financialItems" :shape="'line'" />
                    </CardContent>
                </Card>
            </div>
            <!-- Distribution Charts Section -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Order Distribution -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-sm font-medium">Trạng thái Đơn hàng</CardTitle>
                        <div class="flex items-center gap-1 rounded-lg bg-muted p-1">
                            <button v-for="p in ['today', 'week', 'month', 'quarter', 'year']" :key="p"
                                @click="orderDistPeriod = p; fetchOrderDist()"
                                :class="['rounded-md px-2 py-1 text-[10px] font-medium', orderDistPeriod === p ? 'bg-background shadow-sm' : 'text-muted-foreground']">
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
                    </CardHeader>
                    <CardContent class="flex h-[400px] items-center justify-center">
                        <VisSingleContainer :height="300">
                            <VisDonut :data="orderDist" :value="donutValue" :color="donutColor"
                                :showEmptySegments="true" :padAngle="0.01" :arcWidth="100" :radius="100" />
                            <VisBulletLegend :items="orderLegendItems" />
                            <VisTooltip />
                        </VisSingleContainer>
                    </CardContent>
                </Card>

                <!-- Booking Distribution -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-sm font-medium">Trạng thái Lịch hẹn</CardTitle>
                        <div class="flex items-center gap-1 rounded-lg bg-muted p-1">
                            <button v-for="p in ['today', 'week', 'month', 'quarter', 'year']" :key="p"
                                @click="bookingDistPeriod = p; fetchBookingDist()"
                                :class="['rounded-md px-2 py-1 text-[10px] font-medium', bookingDistPeriod === p ? 'bg-background shadow-sm' : 'text-muted-foreground']">
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
                    </CardHeader>
                    <CardContent class="flex h-[400px] items-center justify-center">
                        <VisSingleContainer :height="300">
                            <VisDonut :data="bookingDist" :value="donutValue" :color="donutColor"
                                :showEmptySegments="true" :padAngle="0.01" :arcWidth="100" :radius="100" />
                            <VisBulletLegend :items="bookingLegendItems" />
                            <VisTooltip />
                        </VisSingleContainer>
                    </CardContent>
                </Card>

                <!-- Refund Distribution -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-sm font-medium">Trạng thái Hoàn tiền</CardTitle>
                        <div class="flex items-center gap-1 rounded-lg bg-muted p-1">
                            <button v-for="p in ['today', 'week', 'month', 'quarter', 'year']" :key="p"
                                @click="refundDistPeriod = p; fetchRefundDist()"
                                :class="['rounded-md px-2 py-1 text-[10px] font-medium', refundDistPeriod === p ? 'bg-background shadow-sm' : 'text-muted-foreground']">
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
                    </CardHeader>
                    <CardContent class="flex h-[400px] items-center justify-center">
                        <VisSingleContainer :height="300">
                            <VisBulletLegend :items="refundLegendItems" />
                            <VisDonut :data="refundDist" :value="donutValue" :color="donutColor"
                                :showEmptySegments="true" :padAngle="0.01" :arcWidth="100" :radius="100" />
                        </VisSingleContainer>
                    </CardContent>
                </Card>
            </div>
            <!-- Utility Tables -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Bảng Đơn hàng (Giữ nguyên nhưng đổi lg:col-span-2 thành không có span) -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Đơn hàng gần đây</CardTitle>
                            <CardDescription>Theo dõi các giao dịch mới nhất</CardDescription>
                        </div>
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
                                <TableRow v-for="order in tables.recent_orders" :key="order.id">
                                    <TableCell class="font-medium">{{ order.number }}</TableCell>
                                    <TableCell>{{ order.customer }}</TableCell>
                                    <TableCell>{{ formatPrice(order.total) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadge(order.status).variant">
                                            {{ getStatusBadge(order.status).label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-xs text-muted-foreground">{{ order.created_at }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Bảng Lịch hẹn mới thêm -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Lịch hẹn gần đây</CardTitle>
                            <CardDescription>Danh sách khách hẹn tư vấn mới nhất</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="max-h-[300px] overflow-y-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Khách hàng</TableHead>
                                    <TableHead>Thời gian</TableHead>
                                    <TableHead>Trạng thái</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="booking in tables.recent_bookings" :key="booking.id">
                                    <TableCell class="font-medium">{{ booking.customer }}</TableCell>
                                    <TableCell class="text-xs">{{ booking.start_at }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ booking.status }}</Badge>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="tables.recent_bookings.length === 0">
                                    <TableCell colspan="3" class="py-4 text-center text-muted-foreground">
                                        Không có lịch hẹn nào.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <!-- Bảng Cảnh báo tồn kho (Đặt riêng một hàng dưới cùng) -->
            <Card class="mt-6">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-destructive" />
                        Cảnh báo tồn kho
                    </CardTitle>
                    <CardDescription>Sản phẩm sắp hết hàng (dưới 5)</CardDescription>
                </CardHeader>
                <CardContent class="max-h-[400px] overflow-y-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Sản phẩm</TableHead>
                                <TableHead class="text-center">Số lượng</TableHead>
                                <TableHead>Vị trí</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in tables.low_stock" :key="item.variant">
                                <TableCell class="font-medium">{{ item.product }} {{ item.variant }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="destructive">{{ item.quantity }}</Badge>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ item.location }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.unovis-crosshair) {
    white-space: pre-line;
    line-height: 1.4;
    padding: 8px;
}

.unovis-crosshair-template {
    white-space: pre-line !important;
}
</style>
