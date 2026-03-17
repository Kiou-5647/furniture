<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, LookupPagination } from '@/types';
import { Head } from '@inertiajs/vue3';
import { index } from '@/routes/employee/lookups';

const props = defineProps <{
    'namespaces': [];
}>();

const breadcrumbs: BreadcrumbItem[] = [{title: "Tra cứu", href: index()}];

const fetchLookups = async () => {
    const response = await index();
}

console.info(props.namespaces);
</script>

<template>
    <Head title="Tra cứu" />
    <AppLayout :breadcrumbs="breadcrumbs">

    </AppLayout>
</template>
