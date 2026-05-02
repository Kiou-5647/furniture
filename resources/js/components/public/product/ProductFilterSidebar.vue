<script setup lang="ts">
import { debounce } from 'lodash';
import { X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import {
    Accordion,
    AccordionItem,
    AccordionTrigger,
    AccordionContent,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import type { ProductFilters, FilterNamespace } from '@/types/public/filter';

interface Props {
    filters: ProductFilters;
    filterSummary: FilterNamespace[];
    totalItems: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['update-filter']);

const localMinPrice = ref(props.filters.min_price);
const localMaxPrice = ref(props.filters.max_price);

// Keep local state in sync if props change from outside (e.g. clear filters)
watch(
    () => props.filters.min_price,
    (val) => (localMinPrice.value = val),
);
watch(
    () => props.filters.max_price,
    (val) => (localMaxPrice.value = val),
);

const performServerUpdate = debounce((payload: ProductFilters) => {
    emit('update-filter', payload);
}, 500);

// Compute active tags within the sidebar
const activeTags = computed(() => {
    const tags: { namespace: string; value: string; label: string }[] = [];

    if (props.filters.type && props.filters.type !== 'popularity') {
        const sortLabels: Record<string, string> = {
            'high-low': 'Giá: Cao - Thấp',
            'low-high': 'Giá: Thấp - Cao',
            newest: 'Mới nhất',
        };
        tags.push({
            namespace: 'type',
            value: props.filters.type,
            label: sortLabels[props.filters.type] || props.filters.type,
        });
    }

    props.filterSummary.forEach((ns) => {
        const activeValues = props.filters.filters[ns.namespace] || [];
        const values = Array.isArray(activeValues)
            ? activeValues
            : [activeValues];

        values.forEach((val) => {
            const option = ns.options.find((o) => o.slug === val);
            tags.push({
                namespace: ns.namespace,
                value: val,
                label: option ? option.label : val,
            });
        });
    });

    return tags;
});

const updatePriceFilter = (key: 'min_price' | 'max_price', value: string) => {
    const numericValue = value === '' ? null : parseFloat(value);

    if (key === 'min_price') localMinPrice.value = Number(numericValue);
    if (key === 'max_price') localMaxPrice.value = Number(numericValue);

    performServerUpdate({
        ...props.filters,
        min_price: localMinPrice.value,
        max_price: localMaxPrice.value,
    });
};

const updateFilter = (namespace: string, value: any) => {
    const newFilters = { ...props.filters.filters };

    if (namespace === 'type') {
        emit('update-filter', { ...props.filters, type: value });
        return;
    }

    if (namespace === 'limit') {
        emit('update-filter', { ...props.filters, limit: value });
        return;
    }

    const currentValues = Array.isArray(newFilters[namespace])
        ? newFilters[namespace]
        : newFilters[namespace]
          ? [newFilters[namespace]]
          : [];

    if (currentValues.includes(value)) {
        const updated = currentValues.filter((v) => v !== value);
        newFilters[namespace] = updated.length > 0 ? updated : '';
    } else {
        newFilters[namespace] = [...currentValues, value];
    }

    emit('update-filter', { ...props.filters, filters: newFilters });
};

const removeTag = (namespace: string, value: string) => {
    const newFilters = { ...props.filters.filters };
    if (namespace === 'type') {
        emit('update-filter', { ...props.filters, type: 'popularity' });
        return;
    }
    const currentValues = Array.isArray(newFilters[namespace])
        ? newFilters[namespace]
        : newFilters[namespace]
          ? [newFilters[namespace]]
          : [];
    const updated = currentValues.filter((v) => v !== value);
    newFilters[namespace] = updated.length > 0 ? updated : '';
    emit('update-filter', { ...props.filters, filters: newFilters });
};

const clearAllFilters = () => {
    emit('update-filter', {
        ...props.filters,
        filters: {},
        type: 'popularity',
        min_price: null,
        max_price: null,
    });
};

const openAccordions = computed(() => {
    return Object.keys(props.filters.filters).filter((namespace) => {
        const value = props.filters.filters[namespace];
        return value !== null && value !== undefined && value !== '';
    });
});
</script>

<template>
    <aside class="w-64 flex-shrink-0 py-8 pr-8">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-medium text-slate-900">Bộ lọc</h2>
            <span class="text-xs font-light text-slate-400"
                >{{ totalItems }} sản phẩm</span
            >
        </div>

        <!-- Active Tags Area (Now inside the sidebar) -->
        <div v-if="activeTags.length > 0" class="mb-6 flex flex-wrap gap-2">
            <Badge
                v-for="tag in activeTags"
                :key="tag.value"
                variant="secondary"
                class="flex items-center gap-1 border-slate-200 bg-slate-100 py-0.5 pr-1 pl-2 text-[10px] font-normal"
            >
                {{ tag.label }}
                <button
                    @click="removeTag(tag.namespace, tag.value)"
                    class="rounded-full p-0.5 transition-colors hover:bg-slate-200"
                >
                    <X class="h-3 w-3" />
                </button>
            </Badge>
        </div>

        <!-- Clear All Button -->
        <Button
            v-if="
                Object.keys(filters.filters).length > 0 ||
                filters.min_price != null ||
                filters.max_price != null ||
                (filters.type && filters.type != 'popularity')
            "
            variant="ghost"
            size="sm"
            class="mb-6 h-auto justify-start p-0 text-xs text-slate-400 underline underline-offset-4 hover:text-red-500"
            @click="clearAllFilters"
        >
            Xóa tất cả bộ lọc
        </Button>

        <!-- Sort Section -->
        <div class="mb-8">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-900"
                    >Sắp xếp theo</span
                >
            </div>
            <RadioGroup
                :model-value="filters.type"
                @update:model-value="updateFilter('type', $event)"
                class="space-y-3"
            >
                <div
                    v-for="option in [
                        { slug: 'popularity', label: 'Phổ biến nhất' },
                        { slug: 'high-low', label: 'Giá: Cao đến Thấp' },
                        { slug: 'low-high', label: 'Giá: Thấp đến Cao' },
                        { slug: 'newest', label: 'Mới nhất' },
                    ]"
                    :key="option.slug"
                    class="group flex cursor-pointer items-center space-x-3"
                    @click="updateFilter('type', option.slug)"
                >
                    <RadioGroupItem
                        :value="option.slug"
                        class="text-slate-900"
                    />
                    <span
                        class="text-sm text-slate-600 transition-colors group-hover:text-slate-900"
                    >
                        {{ option.label }}
                    </span>
                </div>
            </RadioGroup>
        </div>

        <div class="mb-8">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-900">Khoảng giá</span>
            </div>

            <!-- Changed from 'flex' to 'flex flex-col' for vertical layout -->
            <div class="flex flex-col gap-3">
                <div class="relative">
                    <span class="absolute top-1/2 left-2 -translate-y-1/2 text-sm text-slate-400">₫</span>
                    <input
                        type="number"
                        placeholder="Từ"
                        max="999999999"
                        step="1000000"
                        class="w-full rounded-md border border-slate-200 py-1 pr-2 pl-5 text-sm focus:border-orange-400 focus:outline-none"
                        v-model="localMinPrice"
                        @input.stop="updatePriceFilter('min_price', ($event.target as HTMLInputElement).value)"
                    />
                </div>
                <div class="relative">
                    <span class="absolute top-1/2 left-2 -translate-y-1/2 text-sm text-slate-400">₫</span>
                    <input
                        type="number"
                        placeholder="Đến"
                        max="999999999"
                        step="1000000"
                        class="w-full rounded-md border border-slate-200 py-1 pr-2 pl-5 text-sm focus:border-orange-400 focus:outline-none"
                        v-model="localMaxPrice"
                        @input.stop="updatePriceFilter('max_price', ($event.target as HTMLInputElement).value)"
                    />
                </div>
            </div>
        </div>

        <div class="mb-8 h-px bg-slate-100"></div>

        <!-- Dynamic Filters Section -->
        <Accordion
            type="multiple"
            class="space-y-8"
            :default-value="openAccordions"
        >
            <AccordionItem
                v-for="ns in filterSummary"
                :key="ns.namespace"
                :value="ns.namespace"
                class="border-none"
            >
                <AccordionTrigger class="py-0 hover:no-underline">
                    <span
                        class="text-sm font-semibold text-slate-900 capitalize"
                        >{{ ns.label }}</span
                    >
                </AccordionTrigger>
                <AccordionContent class="space-y-3 pt-4 pb-0">
                    <div
                        v-for="opt in ns.options"
                        :key="opt.slug"
                        class="group flex cursor-pointer items-center space-x-3"
                        @click="updateFilter(ns.namespace, opt.slug)"
                    >
                        <Checkbox
                            :model-value="
                                filters.filters[ns.namespace]?.includes(
                                    opt.slug,
                                )
                            "
                            @update:model-value="
                                updateFilter(ns.namespace, opt.slug)
                            "
                            class="text-slate-900"
                        />
                        <span
                            class="flex w-full justify-between text-sm text-slate-600 transition-colors group-hover:text-slate-900"
                        >
                            {{ opt.label }}
                            <span class="font-light text-slate-400"
                                >({{ opt.count }})</span
                            >
                        </span>
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </aside>
</template>
