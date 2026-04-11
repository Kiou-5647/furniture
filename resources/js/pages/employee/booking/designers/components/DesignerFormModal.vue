<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, Clock, Loader2, Mail, Phone, User, Users } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Field, FieldContent, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { store, update } from '@/routes/employee/booking/designers';

const props = defineProps<{
    open: boolean;
    designer: any | null;
    employeeOptions: { id: string; full_name: string; phone: string | null; email: string | null }[];
}>();

const emit = defineEmits(['close', 'refresh']);

const designerType = ref<'freelancer' | 'employee'>('freelancer');
const selectedEmployeeId = ref<string | undefined>(undefined);
const avatarPreview = ref<string | null>(null);
const availabilities = ref<Array<{ day_of_week: number; start_time: string; end_time: string; active: boolean }>>([]);

const DAYS_OF_WEEK = [
    { value: 1, label: 'Thứ hai' },
    { value: 2, label: 'Thứ ba' },
    { value: 3, label: 'Thứ tư' },
    { value: 4, label: 'Thứ năm' },
    { value: 5, label: 'Thứ sáu' },
    { value: 6, label: 'Thứ bảy' },
    { value: 0, label: 'Chủ nhật' },
];

const form = useForm({
    full_name: '',
    email: '',
    phone: undefined as string | undefined,
    employee_id: undefined as string | undefined,
    bio: undefined as string | undefined,
    portfolio_url: undefined as string | undefined,
    hourly_rate: '',
    auto_confirm_bookings: false,
    is_active: true,
    avatar: null as File | null,
    availabilities: [] as Array<{ day_of_week: number; start_time: string; end_time: string }>,
});

const selectedEmployee = computed(() => {
    if (!selectedEmployeeId.value) return null;
    return props.employeeOptions.find((e) => e.id === selectedEmployeeId.value) ?? null;
});

watch(
    () => props.designer,
    (newDes) => {
        if (newDes && props.open) {
            form.full_name = newDes.full_name ?? '';
            form.email = newDes.user?.email ?? '';
            form.phone = newDes.phone ?? undefined;
            form.employee_id = newDes.employee?.id ?? undefined;
            form.bio = newDes.bio ?? undefined;
            form.portfolio_url = newDes.portfolio_url ?? undefined;
            form.hourly_rate = newDes.hourly_rate?.toString() ?? '';
            form.auto_confirm_bookings = newDes.auto_confirm_bookings;
            form.is_active = newDes.is_active;
            avatarPreview.value = newDes.avatar_url ?? null;
            form.avatar = null;

            if (newDes.employee) {
                designerType.value = 'employee';
                selectedEmployeeId.value = newDes.employee.id;
            } else {
                designerType.value = 'freelancer';
                selectedEmployeeId.value = undefined;
            }

            // Map availabilities
            const existingAvail: Record<number, any> = newDes.availabilities
                ? Object.values(newDes.availabilities).reduce((acc: Record<number, any>, a: any) => {
                      acc[a.day_of_week] = a;
                      return acc;
                  }, {} as Record<number, any>)
                : {};

            availabilities.value = DAYS_OF_WEEK.map((d) => {
                const slot = existingAvail[d.value];
                return {
                    day_of_week: d.value,
                    start_time: slot?.start_time?.substring(0, 5) ?? '09:00',
                    end_time: slot?.end_time?.substring(0, 5) ?? '18:00',
                    active: !!slot,
                };
            });
        } else if (!newDes && props.open) {
            resetForm();
        }
    },
    { immediate: true },
);

function resetForm() {
    form.reset();
    form.clearErrors();
    form.avatar = null;
    form.availabilities = [];
    designerType.value = 'freelancer';
    selectedEmployeeId.value = undefined;
    avatarPreview.value = null;
    availabilities.value = DAYS_OF_WEEK.map((d) => ({
        day_of_week: d.value,
        start_time: '09:00',
        end_time: '18:00',
        active: false,
    }));
}

function toggleDay(day: number) {
    const slot = availabilities.value.find((a) => a.day_of_week === day);
    if (slot) slot.active = !slot.active;
}

function submit() {
    const slots = availabilities.value
        .filter((a) => a.active)
        .map((a) => ({
            day_of_week: a.day_of_week,
            start_time: a.start_time,
            end_time: a.end_time,
        }));

    form.employee_id = designerType.value === 'employee' ? selectedEmployeeId.value : undefined;
    form.availabilities = slots;

    if (props.designer) {
        form.put(update(props.designer).url, {
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
    form.avatar = null;
    form.availabilities = [];
    avatarPreview.value = null;
    designerType.value = 'freelancer';
    selectedEmployeeId.value = undefined;
    availabilities.value = DAYS_OF_WEEK.map((d) => ({
        day_of_week: d.value,
        start_time: '09:00',
        end_time: '18:00',
        active: false,
    }));
    emit('close');
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
                        {{ designer ? 'Chỉnh sửa' : 'Thêm' }} nhà thiết kế
                    </DialogTitle>
                    <DialogDescription class="mt-1">
                        {{
                            designer
                                ? 'Cập nhật thông tin nhà thiết kế'
                                : 'Thêm nhà thiết kế mới (freelancer hoặc nhân viên)'
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <Tabs default-value="info">
                    <TabsList class="grid w-full grid-cols-3">
                        <TabsTrigger value="info">Thông tin</TabsTrigger>
                        <TabsTrigger value="availability">Lịch làm việc</TabsTrigger>
                        <TabsTrigger value="settings">Cấu hình</TabsTrigger>
                    </TabsList>

                    <!-- Tab: Thông tin -->
                    <TabsContent value="info" class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-[180px_1fr]">
                            <!-- Avatar -->
                            <div class="hidden sm:block">
                                <ImageUploader
                                    v-model="form.avatar"
                                    :preview-url="avatarPreview"
                                    aspect-ratio="square"
                                    label="Chọn ảnh"
                                    hint="1:1 · Max 2MB"
                                />
                            </div>

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

                                <!-- Type Selection -->
                                <div v-if="!designer" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <Button
                                        type="button"
                                        :variant="designerType === 'freelancer' ? 'default' : 'outline'"
                                        @click="designerType = 'freelancer'"
                                    >
                                        <User class="mr-2 h-4 w-4" /> Freelancer
                                    </Button>
                                    <Button
                                        type="button"
                                        :variant="designerType === 'employee' ? 'default' : 'outline'"
                                        @click="designerType = 'employee'"
                                    >
                                        <Users class="mr-2 h-4 w-4" /> Nhân viên
                                    </Button>
                                </div>

                                <!-- Employee Selection -->
                                <div v-if="designerType === 'employee'">
                                    <Field>
                                        <FieldLabel>
                                            Chọn nhân viên
                                            <span class="text-destructive">*</span>
                                        </FieldLabel>
                                        <FieldContent>
                                            <Select v-model="selectedEmployeeId">
                                                <SelectTrigger class="w-full">
                                                    <SelectValue placeholder="Chọn nhân viên..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="emp in employeeOptions"
                                                        :key="emp.id"
                                                        :value="emp.id"
                                                    >
                                                        {{ emp.full_name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <FieldError :errors="[form.errors.employee_id]" />
                                        </FieldContent>
                                    </Field>

                                    <!-- Employee Reference Info (read-only) -->
                                    <div v-if="selectedEmployee" class="mt-3 rounded-lg border bg-muted p-4 text-sm">
                                        <div class="mb-2 font-medium">Thông tin tham khảo</div>
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            <div class="flex items-center gap-2">
                                                <User class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                <span>{{ selectedEmployee.full_name }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Mail class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                <span>{{ selectedEmployee.email ?? '—' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <Phone class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                <span>{{ selectedEmployee.phone ?? '—' }}</span>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-muted-foreground">
                                            Thông tin này lấy từ hồ sơ nhân viên, không thể chỉnh sửa tại đây.
                                        </p>
                                    </div>
                                </div>

                                <!-- Freelancer Fields -->
                                <template v-if="designerType === 'freelancer'">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <Field>
                                            <FieldLabel>
                                                Họ tên
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
                                    </div>

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
                                </template>

                                <!-- Hourly Rate (always editable) -->
                                <Field>
                                    <FieldLabel>
                                        Giá/giờ
                                        <span class="text-destructive">*</span>
                                    </FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.hourly_rate"
                                            type="number"
                                            placeholder="500000"
                                            class="w-full"
                                        />
                                        <FieldError :errors="[form.errors.hourly_rate]" />
                                    </FieldContent>
                                </Field>

                                <!-- Bio -->
                                <Field>
                                    <FieldLabel>Tiểu sử</FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.bio"
                                            placeholder="Giới thiệu ngắn về nhà thiết kế"
                                            class="w-full"
                                        />
                                        <FieldError :errors="[form.errors.bio]" />
                                    </FieldContent>
                                </Field>

                                <!-- Portfolio URL -->
                                <Field>
                                    <FieldLabel>URL Portfolio</FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.portfolio_url"
                                            placeholder="https://example.com/portfolio"
                                            class="w-full"
                                        />
                                        <FieldError :errors="[form.errors.portfolio_url]" />
                                    </FieldContent>
                                </Field>
                            </div>
                        </div>
                    </TabsContent>

                    <!-- Tab: Lịch làm việc -->
                    <TabsContent value="availability" class="mt-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <Label class="text-base">Lịch làm việc hàng tuần</Label>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="availabilities.forEach((a) => (a.active = true))"
                                >
                                    <CheckCircle2 class="mr-1.5 h-3.5 w-3.5" /> Chọn tất cả
                                </Button>
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="day in availabilities"
                                    :key="day.day_of_week"
                                    class="flex items-center gap-3 rounded-lg border p-3"
                                >
                                    <Switch
                                        :model-value="day.active"
                                        @update:model-value="toggleDay(day.day_of_week)"
                                    />
                                    <span class="w-24 text-sm font-medium">
                                        {{ DAYS_OF_WEEK.find((d) => d.value === day.day_of_week)?.label }}
                                    </span>
                                    <template v-if="day.active">
                                        <Clock class="h-4 w-4 shrink-0 text-muted-foreground" />
                                        <Input
                                            v-model="day.start_time"
                                            type="time"
                                            class="w-32"
                                        />
                                        <span class="text-xs text-muted-foreground">đến</span>
                                        <Input
                                            v-model="day.end_time"
                                            type="time"
                                            class="w-32"
                                        />
                                    </template>
                                    <Badge v-else variant="secondary" class="ml-auto">
                                        Nghỉ
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </TabsContent>

                    <!-- Tab: Cấu hình -->
                    <TabsContent value="settings" class="mt-4 space-y-4">
                        <div
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-0.5">
                                <Label class="text-base">Kích hoạt</Label>
                                <p class="text-sm text-muted-foreground">
                                    Hiển thị và cho phép đặt lịch
                                </p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div class="space-y-0.5">
                                <Label class="text-base">Tự động xác nhận</Label>
                                <p class="text-sm text-muted-foreground">
                                    Tự động duyệt lịch đặt mà không cần xác nhận thủ công
                                </p>
                            </div>
                            <Switch v-model="form.auto_confirm_bookings" />
                        </div>
                    </TabsContent>
                </Tabs>
            </form>

            <DialogFooter class="mt-6 gap-2 sm:mt-8">
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button type="submit" :disabled="form.processing" @click="submit">
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ designer ? 'Lưu thay đổi' : 'Tạo mới' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
