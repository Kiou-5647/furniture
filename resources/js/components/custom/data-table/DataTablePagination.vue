<script setup lang="ts">
import {
    Pagination,
    PaginationContent,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Props {
    total: number;
    pageSize: number;
    currentPage: number;
    lastPage: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:page', 'update:pageSize']);

function onPageChange(page: number) {
    emit('update:page', page);
}

function onPageSizeChange(value: string) {
    emit('update:pageSize', parseInt(value));
}
</script>

<template>
    <div class="flex items-center justify-between px-2">
        <!-- 1. Left side: Rows per page selection -->
        <div class="flex items-center space-x-2">
            <p class="text-sm font-medium">Số hàng mỗi trang</p>
            <Select
                :model-value="`${pageSize}`"
                @update:model-value="onPageSizeChange!"
            >
                <SelectTrigger class="h-8 w-[70px]">
                    <SelectValue :placeholder="`${pageSize}`" />
                </SelectTrigger>
                <SelectContent side="top">
                    <SelectItem
                        v-for="size in [10, 15, 20, 25]"
                        :key="size"
                        :value="`${size}`"
                    >
                        {{ size }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- 2. Middle: Page Info -->
        <div
            class="flex w-[100px] items-center justify-center text-sm font-medium"
        >
            Trang {{ currentPage }} / {{ lastPage }}
        </div>

        <!-- 3. Right side: Navigation -->
        <div class="flex items-center space-x-2">
            <Pagination
                :total="total"
                :sibling-count="3"
                :items-per-page="pageSize"
                :default-page="currentPage"
                @update:page="onPageChange"
            >
                <PaginationContent>
                    <PaginationFirst />
                    <PaginationPrevious />

                    <!-- Optional: Show specific page numbers for large datasets -->
                    <template v-for="index in lastPage" :key="index">
                        <PaginationItem
                            :value="index"
                            :is-active="index === currentPage"
                        >
                            {{ index }}
                        </PaginationItem>
                    </template>

                    <PaginationNext />
                    <PaginationLast />
                </PaginationContent>
            </Pagination>
        </div>
    </div>
</template>
