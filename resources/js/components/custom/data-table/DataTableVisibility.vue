<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" class=" flex h-8">
                Hiển thị cột
                <ChevronDown class="ml-2 h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuCheckboxItem
                v-for="column in table
                    .getAllColumns()
                    .filter((c) => c.getCanHide())"
                :model-value="column.getIsVisible()"
                @update:model-value="(val) => column.toggleVisibility(!!val)"
                :key="column.id"
            >
                {{ column.columnDef.header }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ChevronDown } from 'lucide-vue-next';
import type { Table } from '@tanstack/vue-table';

defineProps<{
    table: Table<any>;
}>();
</script>
