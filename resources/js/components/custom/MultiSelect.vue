<script setup lang="ts">
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
import { Check, PlusCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Option {
    label: string;
    value: string | number;
    icon?: any;
}

const props = defineProps<{
    title?: string;
    options: Option[];
    modelValue: (string | number)[];
    placeholder?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const open = ref(false);

const selectedValues = computed({
    get: () => new Set(props.modelValue),
    set: (value) => emit('update:modelValue', Array.from(value)),
});

function toggleOption(value: string | number) {
    const next = new Set(selectedValues.value);
    if (next.has(value)) {
        next.delete(value);
    } else {
        next.add(value);
    }
    selectedValues.value = next;
}

function clearFilters() {
    selectedValues.value = new Set();
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="h-10 justify-start px-3 font-normal w-full">
                <PlusCircle class="mr-2 h-4 w-4" />
                {{ title }}
                <template v-if="selectedValues.size > 0">
                    <Separator orientation="vertical" class="mx-2 h-4" />
                    <Badge variant="secondary" class="rounded-sm px-1 font-normal lg:hidden">
                        {{ selectedValues.size }}
                    </Badge>
                    <div class="hidden space-x-1 lg:flex">
                        <Badge v-if="selectedValues.size > 2" variant="secondary" class="rounded-sm px-1 font-normal">
                            {{ selectedValues.size }} đã chọn
                        </Badge>
                        <template v-else>
                            <Badge v-for="value in Array.from(selectedValues)" :key="value" variant="secondary"
                                class="rounded-sm px-1 font-normal">
                                {{ options.find((opt) => opt.value === value)?.label }}
                            </Badge>
                        </template>
                    </div>
                </template>
                <span v-else class="text-muted-foreground ml-1">{{ placeholder }}</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-(--radix-popover-trigger-width) p-0" align="start">
            <Command>
                <CommandInput :placeholder="title" />
                <CommandList>
                    <CommandEmpty>Không tìm thấy kết quả.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem v-for="option in options" :key="option.value" :value="option.label"
                            @select="toggleOption(option.value)">
                            <div :class="cn(
                                'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                                selectedValues.has(option.value)
                                    ? 'bg-primary text-primary-foreground'
                                    : 'opacity-50 [&_svg]:invisible',
                            )">
                                <Check class="h-4 w-4" />
                            </div>
                            <component :is="option.icon" v-if="option.icon" class="mr-2 h-4 w-4 text-muted-foreground" />
                            <span>{{ option.label }}</span>
                        </CommandItem>
                    </CommandGroup>
                    <template v-if="selectedValues.size > 0">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem :value="{ label: 'Clear filters' }" class="justify-center text-center"
                                @select="clearFilters">
                                Xóa tất cả
                            </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
