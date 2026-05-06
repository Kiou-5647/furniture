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
import { computed, ref } from 'vue';
import DataTableGroup from '@/components/custom/data-table/DataTableGroup.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, deactivate } from '@/routes/employee/customers';
import { getCustomerOrderColumns, type CustomerOrder } from './types/customer-orders-columns';
import type { BreadcrumbItem } from '@/types';
import type { Customer } from '@/types/customer';

interface OrderPagination {
    data: CustomerOrder[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    customer: Customer;
    orders: OrderPagination;
}>();

const search = ref('');
const isActuallyLoading = ref(false);
const totalOrders = computed(() => {
    if (!props.orders) return 0;
    return typeof props.orders === 'number' ? props.orders : props.orders.total ?? 0;
});
const currentPage = computed(() => props.orders?.current_page ?? 1);
const lastPage = computed(() => props.orders?.last_page ?? 1);
const perPage = computed(() => props.orders?.per_page ?? 15);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Khách hàng', href: index().url },
    { title: props.customer?.full_name ?? 'Chi tiết khách hàng', href: '#' },
]);

function handlePageUpdate(page: number) {
    router.get(index().url, {
        customer_id: props.customer.id,
        page
    }, { preserveState: true });
}

function handlePageSizeUpdate(size: number) {
    router.get(index().url, {
        customer_id: props.customer.id,
        per_page: size
    }, { preserveState: true });
}

function handleSearchUpdate(value: string) {
    search.value = value;
    router.get(index().url, {
        customer_id: props.customer.id,
        search: value,
        page: 1
    }, { preserveState: true });
}

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
console.info(props.orders)
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
                            {{ props.orders?.total ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <DataTableGroup
                :search="search"
                @update:search="handleSearchUpdate"
                :is-actually-loading="isActuallyLoading"
                :columns="getCustomerOrderColumns()"
                :data="orders.data ?? []"
                :has-active-filters="!!search"
                :total="totalOrders"
                :page-size="perPage"
                :current-page="currentPage"
                :last-page="lastPage"
                @update:page="handlePageUpdate"
                @update:page-size="handlePageSizeUpdate"
            />
        </div>
    </AppLayout>
</template>
