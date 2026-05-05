<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Mail,
    MapPin,
    Phone,
    User,
    UserX,
    DollarSign,
    Package,
} from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, deactivate } from '@/routes/employee/customers';
import type { BreadcrumbItem } from '@/types';
import type { Customer } from '@/types/customer';

interface Order {
    id: string;
    order_number: string;
    total_amount: string;
    status: string;
    created_at: string;
}

const props = defineProps<{
    customer: Customer;
    recentOrders: Order[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Khách hàng', href: index().url },
    { title: props.customer?.full_name ?? 'Chi tiết khách hàng', href: '#' },
]);

function goBack() {
    router.visit(index().url);
}

function confirmDeactivate() {
    if (!confirm(`Bạn có chắc chắn muốn vô hiệu hóa tài khoản của khách hàng ${props.customer.full_name}? Họ sẽ không thể đăng nhập.`)) {
        return;
    }
    router.post(deactivate({ customer: props.customer.id }).url, {}, {
        preserveScroll: true,
    });
}
console.info(props.customer.user)
</script>

<template>
    <Head :title="customer.full_name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" @click="goBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <Heading
                            :title="customer.full_name"
                            :description="`Mã khách hàng: ${customer.id}`"
                        />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge
                        :class="[
                            'text-xs',
                            customer.user?.is_active
                                ? 'text-green-600 bg-green-50 border-green-200'
                                : 'text-red-600 bg-red-50 border-red-200',
                        ]"
                        variant="outline"
                    >
                        {{ customer.user?.is_active ? 'Hoạt động' : 'Đã vô hiệu hóa' }}
                    </Badge>
                    <Button
                        v-if="customer.user?.is_active"
                        variant="outline"
                        class="text-destructive"
                        @click="confirmDeactivate"
                    >
                        <UserX class="mr-2 h-4 w-4" /> Vô hiệu hóa tài khoản
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Profile Card -->
                <div class="rounded-lg border p-6 bg-card">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="relative">
                            <img
                                v-if="customer.avatar_url"
                                :src="customer.avatar_url"
                                class="h-24 w-24 rounded-full object-cover ring-4 ring-muted"
                            />
                            <div
                                v-else
                                class="flex h-24 w-24 items-center justify-center rounded-full bg-muted text-2xl font-bold text-muted-foreground ring-4 ring-muted"
                            >
                                {{ customer.full_name }}
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">{{ customer.full_name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ customer.user?.email }}</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3 border-t pt-6">
                        <div class="flex items-center gap-3 text-sm">
                            <Phone class="h-4 w-4 text-muted-foreground" />
                            <span>{{ customer.phone || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <Mail class="h-4 w-4 text-muted-foreground" />
                            <span>{{ customer.user?.email || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                            <span>{{ customer.province_name }} {{ customer.ward_name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-lg border p-6 bg-card flex flex-col justify-center items-center text-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mb-3">
                            <DollarSign class="h-6 w-6" />
                        </div>
                        <span class="text-sm text-muted-foreground">Tổng chi tiêu</span>
                        <span class="text-3xl font-bold tabular-nums">
                            {{ Number(customer.total_spent).toLocaleString('vi-VN') }}đ
                        </span>
                    </div>
                    <div class="rounded-lg border p-6 bg-card flex flex-col justify-center items-center text-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mb-3">
                            <Package class="h-6 w-6" />
                        </div>
                        <span class="text-sm text-muted-foreground">Số đơn hàng</span>
                        <span class="text-3xl font-bold tabular-nums">
                            {{ recentOrders.length }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="rounded-lg border bg-card">
                <div class="border-b px-4 py-3 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-medium">
                        <Calendar class="h-4 w-4" /> Đơn hàng gần đây
                    </h3>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50 text-xs text-muted-foreground">
                            <th class="px-4 py-2 text-left">Mã đơn hàng</th>
                            <th class="px-4 py-2 text-center">Ngày tạo</th>
                            <th class="px-4 py-2 text-center">Trạng thái</th>
                            <th class="px-4 py-2 text-right">Tổng tiền</th>
                            <th class="px-4 py-2 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in recentOrders" :key="order.id" class="border-b text-sm">
                            <td class="px-4 py-3 font-mono text-xs">{{ order.order_number }}</td>
                            <td class="px-4 py-3 text-center text-muted-foreground">{{ order.created_at }}</td>
                            <td class="px-4 py-3 text-center">
                                <Badge variant="outline" class="text-xs">{{ order.status }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums">
                                {{ Number(order.total_amount).toLocaleString('vi-VN') }}đ
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs"
                                    @click="router.get(`/nhan-vien/ban-hang/don-hang/${order.id}`)"
                                >
                                    Xem
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="recentOrders.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">
                                Không có đơn hàng nào
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
