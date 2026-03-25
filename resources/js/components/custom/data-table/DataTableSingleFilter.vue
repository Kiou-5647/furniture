<script setup lang="ts">
import type { Component } from 'vue';
import { Check, PlusCircle } from 'lucide-vue-next';
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
import { cn } from '@/lib/utils';

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
}>(), {
    searchable: true,
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
        <PopoverContent class="w-[200px] p-0" align="start">
            <Command>
                <CommandInput v-if="searchable" :placeholder="title" />
                <CommandList>
                    <CommandEmpty>Không tìm thấy kết quả.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem v-for="option in options" :key="String(option.value)" :value="option"
                            @select="selectOption(option)" class="min-h-12">
                            <div :class="cn(
                                'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                modelValue === option.value
                                    ? 'bg-primary text-primary-foreground'
                                    : 'opacity-50 [&_svg]:invisible',
                            )
                                ">
                                <Check class="h-4 w-4" />
                            </div>
                            <component :is="option.icon" v-if="option.icon"
                                class="mr-2 h-4 w-4 text-muted-foreground" />
                            <span>{{ option.label }}</span>
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
