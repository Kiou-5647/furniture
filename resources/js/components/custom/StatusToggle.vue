<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

defineProps<{
    modelValue: boolean;
    label: string;
    description?: string;
    id?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();
</script>

<template>
    <div
        class="flex items-center gap-2 rounded-md border bg-muted/30 px-3 py-2.5"
    >
        <Switch
            :id="id"
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
        />
        <div>
            <Label class="text-sm font-medium">{{ label }}</Label>
            <p
                v-if="description"
                class="text-[11px] leading-tight text-muted-foreground"
            >
                {{ description }}
            </p>
        </div>
    </div>
</template>
