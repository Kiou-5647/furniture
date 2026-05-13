<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, User, Mail, Phone, MapPin } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
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
import { store, update } from '@/routes/employee/customers';
import type { Customer } from '@/types';

const props = defineProps<{
    open: boolean;
    customer: Customer | null;
}>();

const emit = defineEmits(['close']);

const isEditing = computed(() => !props.customer);

const canUpdate = computed(() => {
    if (!isEditing.value) return true;
    return props.customer?.can_update ?? false;
});

const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);
const skipProvinceWatch = ref(false);

const form = useForm({
    user_id: '',
    full_name: '',
    email: '',
    phone: '',
    street: '',
    province_code: undefined as string | undefined,
    province_name: undefined as string | undefined,
    ward_code: undefined as string | undefined,
    ward_name: undefined as string | undefined,
});

async function loadProvinces() {
    loadingProvinces.value = true;
    try {
        const response = await fetch('/api/geodata/provinces');
        provinces.value = await response.json();
    } catch {
        // Silently fail
    } finally {
        loadingProvinces.value = false;
    }
}

async function loadWards(provinceCode: string) {
    loadingWards.value = true;
    try {
        const response = await fetch(
            `/api/geodata/wards?province_code=${provinceCode}`,
        );
        wards.value = await response.json();
    } catch {
        // Silently fail
    } finally {
        loadingWards.value = false;
    }
}

onMounted(() => {
    loadProvinces();

});
const wardDisplayLabel = computed(() => {
    if (form.ward_name) return form.ward_name;
    if (!form.province_code) return 'Chọn Tỉnh trước';
    if (loadingWards.value) return 'Đang tải...';
    return 'Chọn Quận/Huyện';
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.customer) {
            form.user_id = props.customer.user?.id;
            form.full_name = props.customer.full_name;
            form.email = props.customer.user.email;
            form.phone = props.customer.phone;
            form.street = props.customer.street || '';
            if (provinces.value.length === 0) {
                loadProvinces();
            }

            skipProvinceWatch.value = true;
            form.province_code = props.customer.province_code || undefined;
            form.province_name = props.customer.province_name || undefined;
            if (form.province_code) {
                loadWards(form.province_code);
                form.ward_code = props.customer.ward_code || undefined;
                form.ward_name = props.customer.ward_name || undefined;
            }
            skipProvinceWatch.value = false;

        } else if (isOpen) {
            form.reset();
        }
    },
    { immediate: true }
);

watch(
    () => form.province_code,
    async (newCode) => {
        if (newCode) {
            const province = provinces.value.find((p) => p.value === newCode);
            form.province_name = province?.label ?? '';
            form.ward_code = '';
            form.ward_name = '';
            wards.value = [];
            await loadWards(newCode);
        } else {
            form.province_name = '';
            form.ward_code = '';
            form.ward_name = '';
            wards.value = [];
        }
    },
);

watch(
    () => form.ward_code,
    (newCode) => {
        if (newCode) {
            const ward = wards.value.find((w) => w.value === newCode);
            form.ward_name = ward?.label ?? '';
        } else {
            form.ward_name = '';
        }
    },
);

function submit() {
    if (props.customer) {
        form.put(update(props.customer).url, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        })
    } else {
        form.post(store().url, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
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
        <DialogContent :class="[
            'sm:max-w-[500px]',
            !canUpdate && isEditing ? 'pointer-events-none' : '',
        ]">
            <DialogHeader>
                <DialogTitle>Thêm khách hàng mới</DialogTitle>
                <DialogDescription>
                    Nhập thông tin chi tiết để tạo tài khoản khách hàng mới.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <Field>
                    <FieldLabel>
                        <User class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                        Họ và tên <span class="text-destructive">*</span>
                    </FieldLabel>
                    <FieldContent>
                        <Input v-model="form.full_name" placeholder="Nguyễn Văn A" />
                        <FieldError :errors="[form.errors.full_name]" />
                    </FieldContent>
                </Field>

                <div class="grid grid-cols-2 gap-3">
                    <Field>
                        <FieldLabel>
                            <Mail class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                            Email <span class="text-destructive">*</span>
                        </FieldLabel>
                        <FieldContent>
                            <Input v-model="form.email" type="email" placeholder="email@example.com" />
                            <FieldError :errors="[form.errors.email]" />
                        </FieldContent>
                    </Field>

                    <Field>
                        <FieldLabel>
                            <Phone class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                            Số điện thoại <span class="text-destructive">*</span>
                        </FieldLabel>
                        <FieldContent>
                            <Input v-model="form.phone" placeholder="090..." />
                            <FieldError :errors="[form.errors.phone]" />
                        </FieldContent>
                    </Field>
                </div>

                <div class="space-y-3 border-t pt-4">
                    <Label class="text-sm font-medium flex items-center gap-2">
                        <MapPin class="h-3.5 w-3.5" /> Địa chỉ liên hệ
                    </Label>

                    <div class="grid grid-cols-2 gap-3">
                        <Field>
                            <FieldLabel>Tỉnh/Thành phố</FieldLabel>
                            <FieldContent>
                                <Select v-model="form.province_code">
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue :placeholder="loadingProvinces ? 'Đang tải...' : 'Chọn Tỉnh/TP'" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="province in provinces" :key="province.value"
                                            :value="province.value">
                                            {{ province.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Quận/Huyện</FieldLabel>
                            <FieldContent>
                                <Select v-model="form.ward_code" :disabled="!form.province_code || loadingWards">
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue :placeholder="wardDisplayLabel" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="ward in wards" :key="ward.value" :value="ward.value">
                                            {{ ward.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </FieldContent>
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel>Địa chỉ chi tiết</FieldLabel>
                        <FieldContent>
                            <Input v-model="form.street" placeholder="Số nhà, tên đường..." />
                        </FieldContent>
                    </Field>
                </div>
            </div>

            <DialogFooter class="mt-6">
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button type="button" :disabled="form.processing" @click="submit">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Lưu khách hàng
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
