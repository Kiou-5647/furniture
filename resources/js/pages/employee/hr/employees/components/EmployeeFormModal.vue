<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { parseDate } from '@internationalized/date';
import {
    CalendarIcon,
    Loader2,
    Mail,
    MapPin,
    Phone,
    User,
    Users,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
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
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import SearchableSelect from '@/components/ui/SearchableSelect.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib';
import { store, update } from '@/routes/employee/hr/employees';

const props = defineProps<{
    open: boolean;
    employee: any | null;
    departmentOptions: { id: string; label: string }[];
    storeLocationOptions: { id: string; label: string, address: string }[];
    warehouseLocationOptions: { id: string; label: string, address: string }[];
    roleOptions: { id: string; label: string }[];
    permissionOptions: { id: string; label: string }[];
    rolePermissions: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'refresh']);

const form = useForm({
    name: '',
    email: '',
    full_name: '',
    phone: undefined as string | undefined,
    department_id: undefined as string | undefined,
    store_location_id: null as string | null,
    warehouse_location_id: null as string | null,
    hire_date: '',
    is_active: true,
    roles: [] as string[],
    permissions: [] as string[],
    avatar: null as File | null,
});

function parseHireDate(raw: string | null): Date | undefined {
    if (!raw) return undefined;
    const parts = raw.split('/');
    if (parts.length === 3) {
        return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
    }
    return new Date(raw);
}

const avatarPreview = ref<string | null>(null);

watch(
    () => props.employee,
    (newEmp) => {
        if (newEmp && props.open) {
            form.name = newEmp.user?.name ?? '';
            form.email = newEmp.user?.email ?? '';
            form.full_name = newEmp.full_name;
            form.phone = newEmp.phone ?? undefined;
            form.department_id = newEmp.department?.id ?? undefined;
            form.store_location_id = newEmp.store_location_id ?? null;
            form.warehouse_location_id = newEmp.warehouse_location_id ?? null;
            form.hire_date =
                parseHireDate(newEmp.hire_date)?.toISOString().split('T')[0] ??
                '';
            form.is_active = newEmp.user?.is_active ?? true;
            avatarPreview.value = newEmp.avatar_url ?? null;
            form.avatar = null;
        } else if (!newEmp && props.open) {
            form.reset();
            form.is_active = true;
            form.hire_date = new Date().toISOString().split('T')[0];
            form.avatar = null;
            avatarPreview.value = null;
        }
    },
    { immediate: true },
);

function submit() {
    if (props.employee) {
        form.put(update(props.employee).url, {
            preserveScroll: true,
            onSuccess: () => {
                emit('refresh');
                closeModal();
            },
        });
    } else {
        form.post(store().url, {
            preserveScroll: true,
            onSuccess: () => {
                emit('refresh');
                closeModal();
            },
        });
    }
}

function closeModal() {
    form.reset();
    form.clearErrors();
    form.avatar = null;
    avatarPreview.value = null;
    emit('close');
}

console.info(props.storeLocationOptions)
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-[900px]"
        >
            <DialogHeader class="px-4 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
                <div class="min-w-0">
                    <DialogTitle class="text-left text-lg sm:text-xl">
                        {{ employee ? 'Chỉnh sửa' : 'Thêm' }} nhân viên
                    </DialogTitle>
                    <DialogDescription class="mt-1">
                        {{
                            employee
                                ? 'Cập nhật thông tin nhân viên'
                                : 'Tạo tài khoản và hồ sơ nhân viên mới'
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-[200px_1fr]">
                    <!-- Left: Avatar -->
                    <div class="hidden sm:block">
                        <ImageUploader
                            v-model="form.avatar"
                            :preview-url="avatarPreview"
                            aspect-ratio="square"
                            label="Chọn ảnh"
                            hint="1:1 · Max 2MB"
                        />
                    </div>

                    <!-- Right: Fields -->
                    <div class="space-y-4">
                        <!-- Mobile Avatar -->
                        <div class="sm:hidden">
                            <ImageUploader
                                v-model="form.avatar"
                                :preview-url="avatarPreview"
                                aspect-ratio="square"
                                label="Chọn ảnh"
                                hint="1:1 · Max 2MB"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Field>
                                <FieldLabel>
                                    <User
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Tên đăng nhập
                                    <span class="text-destructive">*</span>
                                </FieldLabel>
                                <FieldContent>
                                    <Input
                                        v-model="form.name"
                                        placeholder="nguyen.van.a"
                                        class="w-full"
                                    />
                                    <FieldError :errors="[form.errors.name]" />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    Họ tên đầy đủ
                                    <span class="text-destructive">*</span>
                                </FieldLabel>
                                <FieldContent>
                                    <Input
                                        v-model="form.full_name"
                                        placeholder="Nguyễn Văn A"
                                        class="w-full"
                                    />
                                    <FieldError
                                        :errors="[form.errors.full_name]"
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
                                    <span class="text-destructive">*</span>
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
                                        placeholder="0123 456 789"
                                        class="w-full"
                                    />
                                    <FieldError :errors="[form.errors.phone]" />
                                </FieldContent>
                            </Field>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Field>
                                <FieldLabel>
                                    <Users
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Phòng ban
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.department_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                placeholder="Chọn phòng ban..."
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="d in departmentOptions"
                                                :key="d.id"
                                                :value="d.id"
                                            >
                                                {{ d.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        :errors="[form.errors.department_id]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <MapPin
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Cửa hàng
                                </FieldLabel>
                                <FieldContent>
                                    <SearchableSelect
                                        v-model="form.store_location_id"
                                        :options="storeLocationOptions"
                                        value-key="id"
                                        label-key="label"
                                        :searchable-keys="['label', 'address']"
                                        placeholder="Chọn cửa hàng..."
                                    >
                                        <template #item="{ option }">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ option.label }}</span>
                                                <span class="text-xs text-muted-foreground">{{ option.address || 'Không có địa chỉ' }}</span>
                                            </div>
                                        </template>
                                    </SearchableSelect>
                                    <FieldError
                                        :errors="[form.errors.store_location_id]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <MapPin
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    Kho hàng
                                </FieldLabel>
                                <FieldContent>
                                    <SearchableSelect
                                        v-model="form.warehouse_location_id"
                                        :options="warehouseLocationOptions"
                                        value-key="id"
                                        label-key="label"
                                        :searchable-keys="['label', 'address']"
                                        placeholder="Chọn kho hàng..."
                                    >
                                        <template #item="{ option }">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ option.label }}</span>
                                                <span class="text-xs text-muted-foreground">{{ option.address || 'Không có địa chỉ' }}</span>
                                            </div>
                                        </template>
                                    </SearchableSelect>
                                    <FieldError
                                        :errors="[form.errors.warehouse_location_id]"
                                    />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>Ngày vào làm</FieldLabel>
                                <FieldContent>
                                    <Popover>
                                        <template #default="{ close }">
                                            <PopoverTrigger as-child>
                                                <Button
                                                    variant="outline"
                                                    :class="
                                                        cn(
                                                            'w-full justify-start text-left font-normal',
                                                            !form.hire_date &&
                                                                'text-muted-foreground',
                                                        )
                                                    "
                                                >
                                                    <CalendarIcon
                                                        class="mr-2 h-4 w-4"
                                                    />
                                                    {{
                                                        form.hire_date ||
                                                        'Chọn ngày'
                                                    }}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent
                                                class="w-auto p-0"
                                                align="start"
                                            >
                                                <Calendar
                                                    :model-value="
                                                        form.hire_date
                                                            ? parseDate(
                                                                  form.hire_date,
                                                              )
                                                            : undefined
                                                    "
                                                    @update:model-value="
                                                        (date) => {
                                                            form.hire_date =
                                                                date
                                                                    ? date.toString()
                                                                    : '';
                                                            close();
                                                        }
                                                    "
                                                    :default-placeholder="
                                                        undefined
                                                    "
                                                    layout="month-and-year"
                                                />
                                            </PopoverContent>
                                        </template>
                                    </Popover>
                                    <FieldError
                                        :errors="[form.errors.hire_date]"
                                    />
                                </FieldContent>
                            </Field>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-0.5">
                                <Label class="text-base">Kích hoạt</Label>
                                <p class="text-sm text-muted-foreground">
                                    Cho phép đăng nhập và sử dụng hệ thống
                                </p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>
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
                        {{ employee ? 'Lưu thay đổi' : 'Tạo mới' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
