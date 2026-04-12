<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Field,
    FieldContent,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store, update } from '@/routes/employee/fulfillment/shipping-methods';
import type { ShippingMethod } from '@/types/shipping-method';

const props = defineProps<{
    open: boolean;
    method: ShippingMethod | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const isEditing = computed(() => !!props.method);

const form = useForm({
    code: '',
    name: '',
    price: '',
    estimated_delivery_days: '',
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.method) {
            form.code = props.method.code;
            form.name = props.method.name;
            form.price = String(props.method.price);
            form.estimated_delivery_days = props.method.estimated_delivery_days
                ? String(props.method.estimated_delivery_days)
                : '';
            form.is_active = props.method.is_active;
        } else if (isOpen) {
            form.reset();
            form.is_active = true;
        }
    },
);

function submit() {
    const url = isEditing.value
        ? update(props.method!).url
        : store().url;
    const method = isEditing.value ? 'put' : 'post';

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
}

function closeModal() {
    form.reset();
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="sm:max-w-[480px]">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Sửa phương thức' : 'Thêm phương thức mới' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        isEditing
                            ? 'Cập nhật thông tin phương thức vận chuyển'
                            : 'Tạo phương thức vận chuyển mới'
                    }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <Field>
                    <FieldLabel>Mã phương thức</FieldLabel>
                    <FieldContent>
                        <Input v-model="form.code" placeholder="VD: STANDARD" />
                        <FieldError :errors="[form.errors.code]" />
                    </FieldContent>
                </Field>

                <Field>
                    <FieldLabel>Tên phương thức</FieldLabel>
                    <FieldContent>
                        <Input v-model="form.name" placeholder="VD: Giao hàng tiêu chuẩn" />
                        <FieldError :errors="[form.errors.name]" />
                    </FieldContent>
                </Field>

                <div class="grid grid-cols-2 gap-3">
                    <Field>
                        <FieldLabel>Giá (VNĐ)</FieldLabel>
                        <FieldContent>
                            <Input v-model="form.price" type="number" placeholder="0" />
                            <FieldError :errors="[form.errors.price]" />
                        </FieldContent>
                    </Field>

                    <Field>
                        <FieldLabel>Số ngày dự kiến</FieldLabel>
                        <FieldContent>
                            <Input
                                v-model="form.estimated_delivery_days"
                                type="number"
                                placeholder="—"
                            />
                            <FieldError :errors="[form.errors.estimated_delivery_days]" />
                        </FieldContent>
                    </Field>
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        :id="method ? `active-${method.id}` : 'active-new'"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label
                        :for="method ? `active-${method.id}` : 'active-new'"
                        class="text-sm font-normal"
                    >
                        Phương thức đang hoạt động
                    </Label>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button
                    type="button"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ isEditing ? 'Cập nhật' : 'Thêm mới' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
