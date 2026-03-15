<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    user: {
        name: string;
        email: string;
        roles: string[];
    };
    vendor?: Record<string, unknown>;
}>();
</script>

<template>
    <Head title="Bảng điều khiển nhà cung cấp" />
    <AppLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold">
                Bảng điều khiển - {{ user.name }}
            </h1>
            <p class="mt-2 text-muted-foreground">
                Vai trò: {{ user.roles.join(', ') }}
            </p>
            <!-- Dashboard content will go here -->
        </div>
    </AppLayout>
</template>
