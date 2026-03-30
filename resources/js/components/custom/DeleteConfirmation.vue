<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = withDefaults(defineProps<{
    open: boolean;
    title?: string;
    description: string;
    itemName?: string;
    confirmLabel?: string;
    cancelLabel?: string;
}>(), {
    title: 'Xác nhận xóa',
    confirmLabel: 'Xóa',
    cancelLabel: 'Hủy',
});

const emit = defineEmits(['update:open', 'confirm']);

function handleConfirm() {
    emit('confirm');
    emit('update:open', false);
}
</script>

<template>
    <AlertDialog :open="open" @update:open="(val) => emit('update:open', val)">
        <AlertDialogContent class="z-60">
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    <slot name="description">
                        {{ itemName ? description.replace('{name}', itemName) : description }}
                    </slot>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="emit('update:open', false)">
                    {{ cancelLabel }}
                </AlertDialogCancel>
                <AlertDialogAction
                    @click="handleConfirm"
                    class="bg-destructive text-white hover:bg-destructive/90"
                >
                    {{ confirmLabel }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
