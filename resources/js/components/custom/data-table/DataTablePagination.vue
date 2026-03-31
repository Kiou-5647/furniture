<script setup lang="ts">
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
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
import { ref, watch } from 'vue';

interface Props {
    total: number;
    pageSize: number;
    currentPage: number;
    lastPage: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:page', 'update:pageSize']);

const selectedPageSize = ref(String(props.pageSize || 15));

watch(() => props.pageSize, (newVal) => {
    selectedPageSize.value = String(newVal);
}, { immediate: true });

function onPageChange(page: number) {
    emit('update:page', page);
}

function onPageSizeChange(value: string) {
    selectedPageSize.value = value;
    emit('update:pageSize', parseInt(value));
}
</script>

<template>
    <div class="flex items-center justify-between px-2">
        <!-- 1. Left side: Rows per page selection -->
        <div class="flex items-center space-x-2">
            <p class="hidden lg:block text-sm font-medium">Số hàng mỗi trang</p>
            <Select :model-value="selectedPageSize" @update:model-value="onPageSizeChange!">
                <SelectTrigger class="h-8 w-17.5">
                    <SelectValue :placeholder="selectedPageSize" />
                </SelectTrigger>
                <SelectContent side="top">
                    <SelectItem v-for="size in [10, 15, 20, 25]" :key="size" :value="`${size}`">
                        {{ size }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- 2. Middle: Page Info -->
        <div class="flex w-25 items-center justify-center text-sm font-medium">
            Trang {{ currentPage }} / {{ lastPage }}
        </div>

        <!-- 3. Right side: Navigation -->
        <div class="flex items-center space-x-2">
            <Pagination v-slot="{ page }" show-edges :sibling-count="1" :total="total" :items-per-page="pageSize"
                :default-page="currentPage" @update:page="onPageChange">
                <PaginationContent v-slot="{ items }">
                    <PaginationFirst />
                    <PaginationPrevious />

                    <!-- Optional: Show specific page numbers for large datasets -->
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === page">
                            {{ item.value }}
                        </PaginationItem>
                        <PaginationEllipsis v-else :key="item.type" :index="index" />
                    </template>


                    <PaginationNext />
                    <PaginationLast />
                </PaginationContent>
            </Pagination>
        </div>
    </div>
</template>
