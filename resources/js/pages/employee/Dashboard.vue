<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    user: {
        name: string;
        email: string;
        roles: string[];
        permissions: string[];
    };
    employee?: Record<string, unknown>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Bảng điều khiển', href: '#' }];
</script>
<template>
    <Head title="Bảng điều khiển nhân viên" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Xin chào, {{ user.name }}</h1>
            <p class="mt-2 text-muted-foreground">
                Vai trò: {{ user.roles.join(', ') }}
            </p>
            <!-- Dashboard content will go here -->
        </div>
    </AppLayout>
</template>
