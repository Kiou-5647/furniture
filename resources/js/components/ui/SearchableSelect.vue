<script setup lang="ts">
import { ref, computed } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { cn } from '@/lib/utils';
import { Check } from '@lucide/vue';
import type { HTMLAttributes } from 'vue';

interface Props<T> {
    modelValue: string | number | null;
    options: T[];
    valueKey: keyof T;
    labelKey: keyof T;
    placeholder?: string;
    disabled?: boolean;
    isLoading?: boolean;
    searchableKeys?: (keyof T)[];
    customFilter?: (option: T, search: string) => boolean;
    customLabel?: (option: T) => string;
    serverSearch?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props<any>>(), {
    placeholder: 'Chọn mục tiêu',
    searchableKeys: () => [],
});

const emit = defineEmits(['update:modelValue', 'search']);

const searchQuery = ref('');

const filteredOptions = computed(() => {
    if (props.serverSearch) return props.options;
    if (!searchQuery.value) return props.options;

    const search = searchQuery.value.toLowerCase();

    return props.options.filter(option => {
        if (props.customFilter) {
            return props.customFilter(option, search);
        }

        if (props.searchableKeys.length > 0) {
            return props.searchableKeys.some(key => {
                const value = option[key];
                return value && String(value).toLowerCase().includes(search);
            });
        }

        const label = option[props.labelKey];
        return label && String(label).toLowerCase().includes(search);
    });
});

const selectedLabel = computed(() => {
    const option = props.options.find(opt => opt[props.valueKey] === props.modelValue);
    if (!option) return props.placeholder;

    // If a custom slot 'item' is used, we can't easily extract text from it here.
    // But we can allow a custom label function via a new prop.
    if (props.customLabel) {
        return props.customLabel(option);
    }

    return String(option[props.labelKey]);
});

function selectOption(value: any) {
    emit('update:modelValue', value);
}

function handleInput(event: Event) {
    const value = (event.target as HTMLInputElement).value;
    searchQuery.value = value;
    if (props.serverSearch) {
        emit('search', value);
    }
}
</script>

<template>
    <Popover>
        <PopoverTrigger
            :disabled="disabled"
            :class="cn(
                'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-balance placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                props.class
            )"
        >
            <span :class="cn('truncate text-left', { 'text-muted-foreground': !modelValue })">
                {{ isLoading ? 'Đang tải...' : selectedLabel }}
            </span>
            <div class="flex shrink-0 items-center gap-1 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </div>
        </PopoverTrigger>
        <PopoverContent class="p-0 w-[--popover-trigger-width] min-w-[200px]">
            <Command :filter-fn="() => true">
                <CommandInput 
                    placeholder="Tìm kiếm..." 
                    @input="handleInput" 
                />
                <CommandList>
                    <CommandEmpty v-if="filteredOptions.length === 0">Không tìm thấy kết quả.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in filteredOptions"
                            :key="option[valueKey]"
                            :value="String(option[valueKey])"
                            @select="selectOption(option[valueKey])"
                            :class="cn(
                                'flex items-center justify-between',
                                modelValue === option[valueKey] && 'bg-accent text-accent-foreground'
                            )"
                        >
                            <slot name="item" :option="option">
                                <span>{{ option[labelKey] }}</span>
                            </slot>
                            <Check v-if="modelValue === option[valueKey]" class="size-4 ml-2" />
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
