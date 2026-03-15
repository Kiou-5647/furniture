<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Cài đặt giao diện',
        href: edit(),
    },
];

const headTitle = 'Cài đặt giao diện';
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="headTitle" />

        <h1 class="sr-only">{{ headTitle }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Cài đặt giao diện"
                    description="Cập nhật cài đặt giao diện của tài khoản"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
