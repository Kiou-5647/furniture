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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/employee/services';
import type { DesignService } from '@/types/design-service';

const props = defineProps<{
    open: boolean;
    service: DesignService | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const isEditing = computed(() => !!props.service);

const form = useForm({
    name: '',
    type: 'consultation',
    base_price: '',
    deposit_percentage: '0',
    estimated_minutes: '',
    is_schedule_blocking: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.service) {
            form.name = props.service.name;
            form.type = props.service.type;
            form.base_price = String(props.service.base_price);
            form.deposit_percentage = String(props.service.deposit_percentage);
            form.estimated_minutes = props.service.estimated_minutes
                ? String(props.service.estimated_minutes)
                : '';
            form.is_schedule_blocking = props.service.is_schedule_blocking;
        } else if (isOpen) {
            form.reset();
            form.is_schedule_blocking = true;
            form.deposit_percentage = '0';
            form.type = 'consultation';
        }
    },
);

function submit() {
    const url = isEditing.value
        ? update(props.service!).url
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
        <DialogContent class="sm:max-w-[520px]">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Sửa dịch vụ' : 'Thêm dịch vụ mới' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        isEditing
                            ? 'Cập nhật thông tin dịch vụ thiết kế'
                            : 'Tạo dịch vụ thiết kế mới'
                    }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <Field>
                    <FieldLabel>Tên dịch vụ</FieldLabel>
                    <FieldContent>
                        <Input v-model="form.name" placeholder="VD: Tư vấn thiết kế nội thất" />
                        <FieldError :errors="[form.errors.name]" />
                    </FieldContent>
                </Field>

                <Field>
                    <FieldLabel>Loại dịch vụ</FieldLabel>
                    <FieldContent>
                        <Select v-model="form.type">
                            <SelectTrigger>
                                <SelectValue placeholder="Chọn loại dịch vụ" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="consultation">
                                    Tư vấn
                                </SelectItem>
                                <SelectItem value="custom_build">
                                    Thiết kế theo yêu cầu
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError :errors="[form.errors.type]" />
                    </FieldContent>
                </Field>

                <div class="grid grid-cols-2 gap-3">
                    <Field>
                        <FieldLabel>Giá cơ bản (VNĐ)</FieldLabel>
                        <FieldContent>
                            <Input v-model="form.base_price" type="number" placeholder="0" />
                            <FieldError :errors="[form.errors.base_price]" />
                        </FieldContent>
                    </Field>

                    <Field>
                        <FieldLabel>Đặt cọc (%)</FieldLabel>
                        <FieldContent>
                            <Input v-model="form.deposit_percentage" type="number" min="0" max="100" placeholder="0" />
                            <FieldError :errors="[form.errors.deposit_percentage]" />
                        </FieldContent>
                    </Field>
                </div>

                <Field>
                    <FieldLabel>Thời gian dự kiến (phút)</FieldLabel>
                    <FieldContent>
                        <Input v-model="form.estimated_minutes" type="number" placeholder="VD: 90" />
                        <FieldError :errors="[form.errors.estimated_minutes]" />
                    </FieldContent>
                </Field>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        :id="service ? `blocking-${service.id}` : 'blocking-new'"
                        :model-value="form.is_schedule_blocking"
                        @update:model-value="form.is_schedule_blocking = $event as boolean"
                    />
                    <Label
                        :for="service ? `blocking-${service.id}` : 'blocking-new'"
                        class="text-sm font-normal"
                    >
                        Chặn lịch khi đặt dịch vụ này
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
