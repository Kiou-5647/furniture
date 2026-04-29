<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Loader2,
    MapPin,
    Phone,
    Mail,
    Globe,
    CreditCard,
    User,
} from '@lucide/vue';
import { nextTick, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
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
import { Switch } from '@/components/ui/switch';
import { store, update } from '@/routes/employee/inventory/vendor';

const props = defineProps<{
    open: boolean;
    vendor: any | null;
}>();

const emit = defineEmits(['close']);

const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);

const form = useForm({
    name: '',
    contact_name: undefined as string | undefined,
    email: undefined as string | undefined,
    phone: undefined as string | undefined,
    website: undefined as string | undefined,
    province_code: undefined as string | undefined,
    ward_code: undefined as string | undefined,
    street: undefined as string | undefined,
    bank_name: undefined as string | undefined,
    bank_account_number: undefined as string | undefined,
    bank_account_holder: undefined as string | undefined,
    is_active: true as boolean,
});

async function loadProvinces() {
    loadingProvinces.value = true;
    try {
        const response = await fetch('/api/geodata/provinces');
        provinces.value = await response.json();
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
    } finally {
        loadingWards.value = false;
    }
}

onMounted(() => {
    loadProvinces();
});

watch(
    () => props.vendor,
    (newVendor) => {
        if (newVendor && props.open) {
            form.name = newVendor.name;
            form.contact_name = newVendor.contact_name;
            form.email = newVendor.email;
            form.phone = newVendor.phone;
            form.website = newVendor.website;
            form.province_code = newVendor.province_code;
            form.ward_code = newVendor.ward_code;
            form.street = newVendor.street;
            form.bank_name = newVendor.bank_name;
            form.bank_account_number = newVendor.bank_account_number;
            form.bank_account_holder = newVendor.bank_account_holder;
            form.is_active = newVendor.is_active;

            if (newVendor.province_code) {
                nextTick(() => {
                    setTimeout(() => {
                        loadWards(newVendor.province_code);
                    }, 100);
                });
            }
        } else if (!newVendor && props.open) {
            form.reset();
            form.is_active = true;
        }
    },
    { immediate: true },
);

watch(
    () => form.province_code,
    (newProvinceCode) => {
        form.ward_code = undefined;
        wards.value = [];
        if (newProvinceCode) {
            loadWards(newProvinceCode);
        }
    },
);

function submit() {
    if (props.vendor) {
        form.put(update(props.vendor).url, {
            onSuccess: () => closeModal(),
            onError: (errors) => toast.error(errors.error || 'Có lỗi xảy ra!'),
        });
    } else {
        form.post(store().url, {
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
            class="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-[600px]"
        >
            <DialogHeader class="px-4 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <DialogTitle class="text-left text-lg sm:text-xl">
                            {{ vendor ? 'Chỉnh sửa' : 'Thêm' }} nhà cung cấp
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{
                                vendor
                                    ? 'Cập nhật thông tin nhà cung cấp'
                                    : 'Tạo nhà cung cấp mới'
                            }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <div class="space-y-4">
                    <!-- General Info -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                <User
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Tên nhà cung cấp
                                <span class="text-destructive">*</span>
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.name"
                                    placeholder="Ví dụ: Công ty TNHH Gỗ Việt"
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.name]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Người liên hệ</FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.contact_name"
                                    placeholder="Nguyễn Văn A"
                                    class="w-full"
                                />
                                <FieldError
                                    :errors="[form.errors.contact_name]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                <Mail
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Email
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@example.com"
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.email]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>
                                <Phone
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Số điện thoại
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.phone"
                                    placeholder="090..."
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.phone]" />
                            </FieldContent>
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel>
                            <Globe
                                class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                            />
                            Website
                        </FieldLabel>
                        <FieldContent>
                            <Input
                                v-model="form.website"
                                type="url"
                                placeholder="https://..."
                                class="w-full"
                            />
                            <FieldError :errors="[form.errors.website]" />
                        </FieldContent>
                    </Field>

                    <!-- Address Section -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                <MapPin
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Tỉnh/Thành phố
                            </FieldLabel>
                            <FieldContent>
                                <Select v-model="form.province_code">
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            :placeholder="
                                                loadingProvinces
                                                    ? 'Đang tải...'
                                                    : 'Chọn Tỉnh/Thành'
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="province in provinces"
                                            :key="province.value"
                                            :value="province.value"
                                        >
                                            {{ province.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[form.errors.province_code]"
                                />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Phường/Xã</FieldLabel>
                            <FieldContent>
                                <Select
                                    v-model="form.ward_code"
                                    :disabled="
                                        !form.province_code || loadingWards
                                    "
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            :placeholder="
                                                loadingWards
                                                    ? 'Đang tải...'
                                                    : 'Chọn Phường/Xã'
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="ward in wards"
                                            :key="ward.value"
                                            :value="ward.value"
                                        >
                                            {{ ward.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError :errors="[form.errors.ward_code]" />
                            </FieldContent>
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel> Địa chỉ</FieldLabel>
                        <FieldContent>
                            <Input
                                v-model="form.street"
                                placeholder="123 Đường ABC"
                                class="w-full"
                            />
                        </FieldContent>
                    </Field>

                    <!-- Bank Info -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <Field>
                            <FieldLabel>
                                <CreditCard
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Ngân hàng
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.bank_name"
                                    placeholder="Vietcombank"
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.bank_name]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Số tài khoản</FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.bank_account_number"
                                    placeholder="1010..."
                                    class="w-full"
                                />
                                <FieldError
                                    :errors="[form.errors.bank_account_number]"
                                />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Chủ tài khoản</FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.bank_account_holder"
                                    placeholder="Tên chủ tài khoản"
                                    class="w-full"
                                />
                                <FieldError
                                    :errors="[form.errors.bank_account_holder]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Status Toggle -->
                    <div
                        class="mt-6 flex items-center justify-between rounded-lg border p-4"
                    >
                        <div class="space-y-0.5">
                            <Label class="text-base">Kích hoạt</Label>
                            <p class="text-sm text-muted-foreground">
                                Hiển thị và cho phép sử dụng
                            </p>
                        </div>
                        <Switch v-model="form.is_active"/>
                    </div>
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
                        {{ vendor ? 'Lưu thay đổi' : 'Lưu nhà cung cấp' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
