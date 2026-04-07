<script setup lang="ts">
import { PlusCircle } from '@lucide/vue';
import type { Component } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';

interface FilterOption {
    label: string;
    value: string | boolean | number | null;
    icon?: Component;
}

const props = withDefaults(defineProps<{
    title?: string;
    options: FilterOption[];
    modelValue: any;
    searchable?: boolean;
    icon_location?: 'start' | 'end' | 'none';
}>(), {
    searchable: true,
    icon_location: 'none'
});

const emit = defineEmits(['update:modelValue']);

function selectOption(option: FilterOption) {
    emit('update:modelValue', option.value === props.modelValue ? null : option.value);
}
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="h-8 border-dashed">
                <PlusCircle class="mr-2 h-4 w-4" />
                {{ title }}
                <template v-if="modelValue !== null && modelValue !== undefined">
                    <Separator orientation="vertical" class="mx-2 h-4" />
                    <Badge variant="secondary" class="rounded-sm px-1 font-normal">
                        {{options.find(o => o.value === modelValue)?.label}}
                    </Badge>
                </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-fit p-0" align="start">
            <Command>
                <CommandInput v-if="searchable" :placeholder="title" />
                <CommandList>
                    <CommandEmpty>Không tìm thấy kết quả.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem v-for="option in options" :key="String(option.value)" :value="option"
                            @select="selectOption(option)" class="min-h-12 w-full items-center justify-normal pr-4">
                            <component :is="option.icon" v-if="option.icon && icon_location == 'start'"
                                class="mr-2 h-4 w-4 text-muted-foreground" />
                            <span class="mr-2" >{{ option.label }}</span>
                            <component :is="option.icon" v-if="option.icon && icon_location == 'end'"
                                class="ml-auto h-4 w-4 text-muted-foreground" />
                        </CommandItem>
                    </CommandGroup>
                    <template v-if="modelValue !== null && modelValue !== undefined">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem value="clear" class="justify-center text-center"
                                @select="emit('update:modelValue', null)">
                                Xóa bộ lọc
                            </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
