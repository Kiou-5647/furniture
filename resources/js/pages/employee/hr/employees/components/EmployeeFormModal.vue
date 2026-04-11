<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { parseDate } from '@internationalized/date';
import { CalendarIcon, Loader2, Mail, MapPin, Phone, User, Users } from '@lucide/vue';
import { ref, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Field, FieldContent, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';
import { store, update } from '@/routes/employee/hr/employees';

const props = defineProps<{
    open: boolean;
    employee: any | null;
    departmentOptions: { id: string; label: string }[];
    locationOptions: { id: string; label: string }[];
    roleOptions: { id: string; label: string }[];
    permissionOptions: { id: string; label: string }[];
    rolePermissions: Record<string, string[]>;
}>();

const emit = defineEmits(['close', 'refresh']);

const showRoleSection = ref(false);
const selectedRoles = ref<string[]>([]);
const selectedPermissions = ref<string[]>([]);
const initialRoles = ref<string[]>([]);
const initialPermissions = ref<string[]>([]);

const form = useForm({
    name: '',
    email: '',
    full_name: '',
    phone: undefined as string | undefined,
    department_id: undefined as string | undefined,
    location_id: undefined as string | undefined,
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
            form.location_id = newEmp.location_id ?? undefined;
            form.hire_date = parseHireDate(newEmp.hire_date)?.toISOString().split('T')[0] ?? '';
            form.is_active = newEmp.user?.is_active ?? true;
            selectedRoles.value = newEmp.user?.roles ?? [];
            selectedPermissions.value = newEmp.user?.permissions ?? [];
            initialRoles.value = [...selectedRoles.value];
            initialPermissions.value = [...selectedPermissions.value];
            avatarPreview.value = newEmp.avatar_url ?? null;
            form.avatar = null;
        } else if (!newEmp && props.open) {
            form.reset();
            form.is_active = true;
            form.hire_date = new Date().toISOString().split('T')[0];
            form.avatar = null;
            selectedRoles.value = [];
            selectedPermissions.value = [];
            initialRoles.value = [];
            initialPermissions.value = [];
            avatarPreview.value = null;
        }
    },
    { immediate: true },
);

function submit() {
    form.roles = selectedRoles.value;
    form.permissions = selectedPermissions.value;

    if (props.employee) {
        form.put(update(props.employee).url, {
            preserveScroll: true,
            onSuccess: () => { emit('refresh'); closeModal(); },
        });
    } else {
        form.post(store().url, {
            preserveScroll: true,
            onSuccess: () => { emit('refresh'); closeModal(); },
        });
    }
}

function closeModal() {
    form.reset();
    form.clearErrors();
    form.roles = [];
    form.permissions = [];
    form.avatar = null;
    showRoleSection.value = false;
    avatarPreview.value = null;
    emit('close');
}

function toggleRole(roleId: string) {
    const idx = selectedRoles.value.indexOf(roleId);
    const perms = props.rolePermissions[roleId] ?? [];

    if (idx === -1) {
        selectedRoles.value.push(roleId);
        perms.forEach((p) => {
            if (!selectedPermissions.value.includes(p)) {
                selectedPermissions.value.push(p);
            }
        });
    } else {
        selectedRoles.value.splice(idx, 1);
        const remaining = new Set<string>();
        selectedRoles.value.forEach((role) => {
            (props.rolePermissions[role] ?? []).forEach((p) => remaining.add(p));
        });
        const manualPerms = selectedPermissions.value.filter((p) => !perms.includes(p));
        selectedPermissions.value = [...new Set([...remaining, ...manualPerms])];
    }
}

function togglePermission(permId: string) {
    const idx = selectedPermissions.value.indexOf(permId);
    if (idx === -1) {
        selectedPermissions.value.push(permId);
    } else {
        selectedPermissions.value.splice(idx, 1);
    }
}

function resetPermissions() {
    selectedPermissions.value = [...initialPermissions.value];
}

function clearAll() {
    selectedRoles.value = [];
    selectedPermissions.value = [];
}
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
                                    <User class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
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
                                    <FieldError :errors="[form.errors.full_name]" />
                                </FieldContent>
                            </Field>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Field>
                                <FieldLabel>
                                    <Mail class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
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
                                    <Phone class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
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
                                    <Users class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                    Phòng ban
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.department_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Chọn phòng ban..." />
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
                                    <FieldError :errors="[form.errors.department_id]" />
                                </FieldContent>
                            </Field>

                            <Field>
                                <FieldLabel>
                                    <MapPin class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                    Cửa hàng
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.location_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Chọn cửa hàng..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="loc in locationOptions"
                                                :key="loc.id"
                                                :value="loc.id"
                                            >
                                                {{ loc.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <FieldError :errors="[form.errors.location_id]" />
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
                                                    :class="cn(
                                                        'w-full justify-start text-left font-normal',
                                                        !form.hire_date && 'text-muted-foreground',
                                                    )"
                                                >
                                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                                    {{ form.hire_date || 'Chọn ngày' }}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent class="w-auto p-0" align="start">
                                                <Calendar
                                                    :model-value="form.hire_date ? parseDate(form.hire_date) : undefined"
                                                    @update:model-value="(date) => {
                                                        form.hire_date = date ? date.toString() : '';
                                                        close();
                                                    }"
                                                    :default-placeholder="undefined"
                                                    layout="month-and-year"
                                                />
                                            </PopoverContent>
                                        </template>
                                    </Popover>
                                    <FieldError :errors="[form.errors.hire_date]" />
                                </FieldContent>
                            </Field>
                        </div>

                        <div
                            class="mt-4 flex cursor-pointer items-center justify-between rounded-lg border p-4 hover:bg-muted/50"
                            @click="showRoleSection = !showRoleSection"
                        >
                            <div class="space-y-0.5">
                                <Label class="text-base">Vai trò & Quyền hạn</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        showRoleSection
                                            ? 'Ẩn phần cấu hình quyền'
                                            : 'Mở để cấu hình vai trò và quyền hạn'
                                    }}
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                            >
                                {{ showRoleSection ? 'Ẩn' : 'Mở' }}
                            </Button>
                        </div>

                        <div
                            v-if="showRoleSection"
                            class="space-y-4 rounded-lg border p-4"
                        >
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium">Vai trò</h4>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-7 text-xs"
                                        @click="clearAll"
                                    >
                                        Xóa tất cả
                                    </Button>
                                </div>
                                <p class="mb-2 text-xs text-muted-foreground">Chọn vai trò làm gợi ý, quyền hạn phải cấp thủ công</p>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        v-for="role in roleOptions"
                                        :key="role.id"
                                        type="button"
                                        :variant="selectedRoles.includes(role.id) ? 'default' : 'outline'"
                                        size="sm"
                                        @click="toggleRole(role.id)"
                                    >
                                        {{ role.label }}
                                    </Button>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium">Quyền hạn</h4>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-7 text-xs"
                                        @click="resetPermissions"
                                    >
                                        Đặt lại
                                    </Button>
                                </div>
                                <p class="mb-2 text-xs text-muted-foreground">Cấp quyền thủ công, vai trò chỉ để tham khảo</p>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        v-for="perm in permissionOptions"
                                        :key="perm.id"
                                        type="button"
                                        :variant="selectedPermissions.includes(perm.id) ? 'default' : 'outline'"
                                        class="text-xs"
                                        @click="togglePermission(perm.id)"
                                    >
                                        {{ perm.label }}
                                    </Button>
                                </div>
                            </div>

                            <div class="rounded-md bg-muted px-3 py-2 text-xs text-muted-foreground">
                                Vai trò: <span class="font-medium text-foreground">{{ selectedRoles.length }}</span>
                                · Quyền: <span class="font-medium text-foreground">{{ selectedPermissions.length }}</span>
                            </div>
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
