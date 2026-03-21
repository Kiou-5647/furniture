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
    value: string | boolean | number;
    icon?: Component;
}

const props = defineProps<{
    title?: string;
    options: FilterOption[];
    modelValue: any[]; // Mảng các giá trị đang chọn
}>();

const emit = defineEmits(['update:modelValue']);

function toggleOption(value: any) {
    const newSelected = [...props.modelValue];
    const index = newSelected.indexOf(value);
    if (index > -1) {
        newSelected.splice(index, 1);
    } else {
        newSelected.push(value);
    }
    emit('update:modelValue', newSelected);
}
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="w-full h-8 border-dashed lg:w-auto">
                <PlusCircle class="mr-2 h-4 w-4" />
                {{ title }}
                <template v-if="modelValue.length > 0">
                    <Separator orientation="vertical" class="mx-2 h-4" />
                    <Badge
                        variant="secondary"
                        class="rounded-sm px-1 font-normal lg:hidden"
                    >
                        {{ modelValue.length }}
                    </Badge>
                    <div class="hidden space-x-1 lg:flex">
                        <Badge
                            v-if="modelValue.length > 2"
                            variant="secondary"
                            class="rounded-sm px-1 font-normal"
                        >
                            {{ modelValue.length }} đã chọn
                        </Badge>
                        <template v-else>
                            <Badge
                                v-for="val in modelValue.filter(v => options.some(opt => opt.value === v))"
                                :key="String(val)"
                                variant="secondary"
                                class="rounded-sm px-1 font-normal"
                            >
                                {{
                                    options.find((opt) => opt.value === val)
                                        ?.label
                                }}
                            </Badge>
                        </template>
                    </div>
                </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[200px] p-0" align="start">
            <Command>
                <CommandInput :placeholder="title" />
                <CommandList>
                    <CommandEmpty>Không tìm thấy kết quả.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="String(option.value)"
                            :value="option"
                            @select="toggleOption(option.value)"
                        >
                            <div
                                :class="
                                    cn(
                                        'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                        modelValue.includes(option.value)
                                            ? 'bg-primary text-primary-foreground'
                                            : 'opacity-50 [&_svg]:invisible',
                                    )
                                "
                            >
                                <Check class="h-4 w-4" />
                            </div>
                            <component
                                :is="option.icon"
                                v-if="option.icon"
                                class="mr-2 h-4 w-4 text-muted-foreground"
                            />
                            <span>{{ option.label }}</span>
                        </CommandItem>
                    </CommandGroup>
                    <template v-if="modelValue.filter(v => options.some(opt => opt.value === v)).length > 0">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem
                                :value="{ label: 'Xóa bộ lọc' }"
                                class="justify-center text-center"
                                @select="emit('update:modelValue', [])"
                            >
                                Xóa bộ lọc
                            </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
