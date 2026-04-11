<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Users } from '@lucide/vue';
import { watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Field, FieldContent, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { store, update } from '@/routes/employee/hr/departments';
import type { Department } from '@/types/department';

const props = defineProps<{
    open: boolean;
    department: Department | null;
    managerOptions: { id: string; label: string }[];
}>();

const emit = defineEmits(['close']);

console.info(props.managerOptions);

const form = useForm({
    name: '',
    code: '',
    description: undefined as string | undefined,
    manager_id: undefined as string | undefined,
    is_active: true,
});

function generateCode(name: string) {
    if (!name) return '';
    return name
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .replace(/[^a-zA-Z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .toUpperCase()
        .trim()
        .slice(0, 50);
}

watch(
    () => form.name,
    (newName) => {
        if (!props.department) {
            form.code = generateCode(newName);
        }
    },
);

watch(
    () => props.department,
    (newDept) => {
        if (newDept && props.open) {
            form.name = newDept.name;
            form.code = newDept.code;
            form.description = newDept.description ?? undefined;
            form.manager_id = newDept.manager?.id ?? undefined;
            form.is_active = newDept.is_active;
        } else if (!newDept && props.open) {
            form.reset();
            form.is_active = true;
        }
    },
    { immediate: true },
);

function submit() {
    if (props.department) {
        form.put(update(props.department).url, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(store().url, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

function closeModal() {
    form.reset();
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-[500px]"
        >
            <DialogHeader class="px-4 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
                <div class="min-w-0">
                    <DialogTitle class="text-left text-lg sm:text-xl">
                        {{ department ? 'Chỉnh sửa' : 'Thêm' }} phòng ban
                    </DialogTitle>
                    <DialogDescription class="mt-1">
                        {{
                            department
                                ? 'Cập nhật thông tin phòng ban'
                                : 'Tạo phòng ban mới'
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                Tên phòng ban
                                <span class="text-destructive">*</span>
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.name"
                                    placeholder="Ví dụ: Phòng Kỹ thuật"
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.name]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>
                                Mã phòng ban
                                <span class="text-destructive">*</span>
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.code"
                                    placeholder="PHONG-KY-THUAT"
                                    class="w-full font-mono text-xs uppercase"
                                />
                                <FieldError :errors="[form.errors.code]" />
                            </FieldContent>
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel>Mô tả</FieldLabel>
                        <FieldContent>
                            <Input
                                v-model="form.description"
                                placeholder="Mô tả chức năng phòng ban"
                                class="w-full"
                            />
                            <FieldError :errors="[form.errors.description]" />
                        </FieldContent>
                    </Field>

                    <Field>
                        <FieldLabel>
                            <Users
                                class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                            />
                            Trưởng phòng
                        </FieldLabel>
                        <FieldContent>
                            <Select v-model="form.manager_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Chọn người quản lý..."
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="m in managerOptions"
                                        :key="m.id"
                                        :value="m.id"
                                        @click.stop="console.info(m, form.manager_id)"
                                    >
                                        {{ m.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError :errors="[form.errors.manager_id]" />
                        </FieldContent>
                    </Field>
                </div>

                <div
                    class="mt-6 flex items-center justify-between rounded-lg border p-4"
                >
                    <div class="space-y-0.5">
                        <Label class="text-base">Kích hoạt</Label>
                        <p class="text-sm text-muted-foreground">
                            Hiển thị và cho phép sử dụng
                        </p>
                    </div>
                    <Switch v-model="form.is_active" />
                </div>

                <DialogFooter class="mt-6 gap-2 sm:mt-8">
                    <Button type="button" variant="outline" @click="closeModal">
                        Hủy
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ department ? 'Lưu thay đổi' : 'Tạo mới' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
