<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { LogOut, AlertCircle } from '@lucide/vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';

const handleLogout = () => {
    router.flushAll();
};
</script>
<template>
    <AuthLayout title="Tài khoản của bạn đang chờ xác minh"
        description="Tổ chức nhà cung cấp của bạn cần được xác minh bởi quản trị viên trước khi bạn có thể truy cập trang tổng quan.">

        <Head title="Chờ xác minh" />
        <div class="space-y-6">
            <Alert variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>Chờ xác minh</AlertTitle>
                <AlertDescription>
                    Vui lòng liên hệ quản trị viên để xác minh tổ chức của bạn trước khi truy cập các chức năng dành cho
                    nhà cung cấp.
                </AlertDescription>
            </Alert>
            <Button variant="outline" class="w-full" as-child>
                <Link class="block w-full cursor-pointer" :href="logout()" @click="handleLogout" as="button"
                    data-test="logout-button">
                    <LogOut class="mr-2 h-4 w-4" />
                    Đăng xuất
                </Link>
            </Button>
        </div>
    </AuthLayout>
</template>
