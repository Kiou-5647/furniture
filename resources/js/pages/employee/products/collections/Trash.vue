<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Trash2 } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/employee/collections';
import type { BreadcrumbItem } from '@/types';
import type { CollectionFilterData, CollectionPagination } from '@/types/collection';

defineProps<{
    collections?: CollectionPagination;
    filters: CollectionFilterData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sản phẩm', href: '#' },
    { title: 'Bộ sưu tập', href: index().url },
    { title: 'Thùng rác', href: '#' }
];
</script>

<template>
    <Head title="Thùng rác bộ sưu tập" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <Heading title="Thùng rác bộ sưu tập" description="Danh sách các bộ sưu tập đã bị xóa tạm thời" />
                <Button variant="outline" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Quay lại
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col items-center justify-center py-20 border rounded-lg bg-muted/10 border-dashed">
                <div class="p-4 bg-muted rounded-full mb-4">
                    <Trash2 class="h-10 w-10 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-semibold">Tính năng đang phát triển</h3>
                <p class="text-sm text-muted-foreground mt-2 max-w-md text-center">
                    Giao diện quản lý thùng rác cho bộ sưu tập hiện đang được hoàn thiện.
                    Bạn có thể quay lại danh sách chính để tiếp tục làm việc.
                </p>
                <Button class="mt-6" variant="secondary" as-child>
                    <Link :href="index().url">
                        Quay lại danh sách
                    </Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
