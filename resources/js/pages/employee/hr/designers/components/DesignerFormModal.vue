<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronDown,
    ChevronRight,
    Clock,
    Loader2,
    Mail,
    Phone,
    User,
    Users,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import { Badge } from '@/components/ui/badge';
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
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { store, update } from '@/routes/employee/hr/designers';

const props = defineProps<{
    open: boolean;
    designer: any | null;
    employeeOptions: {
        id: string;
        full_name: string;
        phone: string | null;
        email: string | null;
    }[];
}>();

const emit = defineEmits(['close', 'refresh']);

const designerType = ref<'freelancer' | 'employee'>('freelancer');
const selectedEmployeeId = ref<string | undefined>(undefined);
const avatarPreview = ref<string | null>(null);
const availabilities = ref<
    Array<{
        day_of_week: number;
        start_time: string;
        end_time: string;
        active: boolean;
    }>
>([]);

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
    availabilities: [] as Array<{
        day_of_week: number;
        start_time: string;
        end_time: string;
    }>,
});

const selectedEmployee = computed(() => {
    if (!selectedEmployeeId.value) return null;
    return (
        props.employeeOptions.find((e) => e.id === selectedEmployeeId.value) ??
        null
    );
});

const linkedEmployee = computed(() => {
    if (!props.designer?.employee) return null;
    return props.designer.employee;
});

const showEmployeeInfo = ref(false);

const activeDaysCount = computed(() => {
    return availabilities.value.filter((a) => a.active).length;
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

            const existingAvail: Record<number, any> = newDes.availabilities
                ? Object.values(newDes.availabilities).reduce(
                      (acc: Record<number, any>, a: any) => {
                          acc[a.day_of_week] = a;
                          return acc;
                      },
                      {} as Record<number, any>,
                  )
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

// Auto-fill form fields when an employee is selected in create mode
watch(selectedEmployeeId, (empId) => {
    if (!empId || props.designer) return;
    const emp = props.employeeOptions.find((e) => e.id === empId);
    if (emp) {
        form.full_name = emp.full_name ?? '';
        form.email = emp.email ?? '';
        form.phone = emp.phone ?? undefined;
    }
});

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

    form.employee_id =
        designerType.value === 'employee'
            ? selectedEmployeeId.value
            : undefined;
    form.availabilities = slots;

    if (props.designer) {
        form.put(update({ designer: props.designer.id }).url, {
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
            class="flex h-[90vh] max-h-[90vh] flex-col gap-0 overflow-hidden p-0 sm:max-w-[1000px]"
        >
            <!-- Header -->
            <DialogHeader class="shrink-0 border-b px-4 py-3 sm:px-6 sm:py-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle class="text-left text-base font-semibold sm:text-lg">
                            {{ designer ? 'Chỉnh sửa' : 'Thêm' }} nhà thiết kế
                        </DialogTitle>
                        <DialogDescription class="mt-0.5 text-xs">
                            {{
                                designer
                                    ? 'Cập nhật thông tin nhà thiết kế'
                                    : 'Thêm nhà thiết kế mới (freelancer hoặc nhân viên)'
                            }}
                        </DialogDescription>
                    </div>
                    <!-- Badge for employee-linked designer -->
                    <Badge
                        v-if="linkedEmployee"
                        variant="secondary"
                        class="text-xs"
                    >
                        <Users class="mr-1.5 h-3 w-3" />
                        {{ linkedEmployee.full_name }}
                    </Badge>
                </div>
            </DialogHeader>

            <!-- Content: mobile = scroll all, desktop = left scrolls only -->
            <div class="flex flex-1 flex-col overflow-y-auto sm:flex-row sm:overflow-hidden">
                <!-- Left: Info (scrollable on desktop) -->
                <div class="w-full px-4 py-4 sm:min-h-0 sm:flex-1 sm:overflow-y-auto sm:border-r sm:px-5 sm:py-5">
                        <!-- Type selection (create mode only) -->
                        <div v-if="!designer" class="mb-4 flex gap-2">
                            <Button
                                type="button"
                                :variant="
                                    designerType === 'freelancer'
                                        ? 'default'
                                        : 'outline'
                                "
                                class="flex-1"
                                @click="designerType = 'freelancer'"
                            >
                                <User class="mr-2 h-4 w-4" /> Freelancer
                            </Button>
                            <Button
                                type="button"
                                :variant="
                                    designerType === 'employee'
                                        ? 'default'
                                        : 'outline'
                                "
                                class="flex-1"
                                @click="designerType = 'employee'"
                            >
                                <Users class="mr-2 h-4 w-4" /> Nhân viên
                            </Button>
                        </div>

                        <!-- Employee link badge (edit mode, employee-linked) -->
                        <div v-if="designer && linkedEmployee" class="mb-4 flex items-center gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="text-xs"
                                @click="showEmployeeInfo = !showEmployeeInfo"
                            >
                                <Users class="mr-1.5 h-3.5 w-3.5" />
                                Xem thông tin nhân viên
                                <ChevronDown
                                    v-if="showEmployeeInfo"
                                    class="ml-1 h-3 w-3"
                                />
                                <ChevronRight
                                    v-else
                                    class="ml-1 h-3 w-3"
                                />
                            </Button>
                            <p class="text-xs text-muted-foreground">
                                Nhà thiết kế này được liên kết với nhân viên
                            </p>
                        </div>

                        <!-- Expandable employee info -->
                        <div
                            v-if="showEmployeeInfo && linkedEmployee"
                            class="mb-4 rounded-lg border bg-muted/50 p-3 text-sm"
                        >
                            <div class="mb-2 font-medium text-muted-foreground">
                                Thông tin nhân viên liên kết
                            </div>
                            <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-2">
                                <div class="flex items-center gap-2">
                                    <User
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    <span>{{ linkedEmployee.full_name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Mail
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    <span class="truncate">{{
                                        linkedEmployee.email ?? '—'
                                    }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Phone
                                        class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                    />
                                    <span>{{
                                        linkedEmployee.phone ?? '—'
                                    }}</span>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-muted-foreground">
                                Thông tin này lấy từ hồ sơ nhân viên, không thể
                                chỉnh sửa tại đây.
                            </p>
                        </div>

                        <!-- Employee Selection (create mode, employee type) -->
                        <div v-if="!designer && designerType === 'employee'" class="mb-4">
                            <Field>
                                <FieldLabel>
                                    Chọn nhân viên
                                    <span class="text-destructive">*</span>
                                </FieldLabel>
                                <FieldContent>
                                    <Select v-model="selectedEmployeeId">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                placeholder="Chọn nhân viên..."
                                            />
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
                                    <FieldError
                                        :errors="[form.errors.employee_id]"
                                    />
                                </FieldContent>
                            </Field>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Avatar row -->
                            <div class="flex items-center gap-4">
                                <ImageUploader
                                    v-model="form.avatar"
                                    :preview-url="avatarPreview"
                                    aspect-ratio="square"
                                    label="Ảnh đại diện"
                                    hint="1:1 · Max 2MB"
                                    class="w-24 shrink-0"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-muted-foreground">
                                        Ảnh hồ sơ
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Upload ảnh vuông, tối đa 2MB
                                    </p>
                                </div>
                            </div>

                            <!-- All info fields (always visible) -->
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
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
                                        <FieldError
                                            :errors="[form.errors.full_name]"
                                        />
                                    </FieldContent>
                                </Field>

                                <Field>
                                    <FieldLabel>
                                        <Mail
                                            class="h-3.5 w-3.5 text-muted-foreground"
                                        />
                                        Email
                                        <span class="text-destructive"
                                            >*</span
                                        >
                                    </FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.email"
                                            type="email"
                                            placeholder="email@example.com"
                                            class="w-full"
                                        />
                                        <FieldError
                                            :errors="[form.errors.email]"
                                        />
                                    </FieldContent>
                                </Field>
                            </div>

                            <Field>
                                <FieldLabel>
                                    <Phone
                                        class="h-3.5 w-3.5 text-muted-foreground"
                                    />
                                    Số điện thoại
                                </FieldLabel>
                                <FieldContent>
                                    <Input
                                        v-model="form.phone"
                                        placeholder="0123 456 789"
                                        class="w-full"
                                    />
                                    <FieldError
                                        :errors="[form.errors.phone]"
                                    />
                                </FieldContent>
                            </Field>

                            <!-- Hourly Rate -->
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
                                    <FieldError
                                        :errors="[form.errors.hourly_rate]"
                                    />
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
                                    <FieldError
                                        :errors="[form.errors.portfolio_url]"
                                    />
                                </FieldContent>
                            </Field>

                            <Separator />

                            <!-- Settings -->
                            <div
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="space-y-0.5">
                                    <Label class="text-sm font-medium">Kích hoạt</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Hiển thị và cho phép đặt lịch
                                    </p>
                                </div>
                                <Switch v-model="form.is_active" />
                            </div>

                            <div
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="space-y-0.5">
                                    <Label class="text-sm font-medium">Tự động xác nhận</Label>
                                    <p class="text-xs text-muted-foreground">
                                        Tự động duyệt lịch đặt mà không cần xác nhận
                                        thủ công
                                    </p>
                                </div>
                                <Switch v-model="form.auto_confirm_bookings" />
                            </div>
                        </form>
                    </div>

                    <!-- Right: Schedule (static on desktop) -->
                    <div class="w-full border-t px-4 py-4 sm:w-[42%] sm:flex-shrink-0 sm:border-t-0 sm:px-5 sm:py-5 sm:overflow-hidden">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-semibold">Lịch làm việc</span>
                                <Badge
                                    v-if="activeDaysCount > 0"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    {{ activeDaysCount }} ngày
                                </Badge>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="
                                    availabilities.forEach(
                                        (a) => (a.active = !a.active),
                                    )
                                "
                            >
                                <CheckCircle2 class="mr-1 h-3.5 w-3.5" />
                                {{ activeDaysCount === 7 ? 'Bỏ tất cả' : 'Chọn tất cả' }}
                            </Button>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="day in availabilities"
                                :key="day.day_of_week"
                                class="flex items-center gap-2 rounded-lg border p-2.5 transition-colors"
                                :class="day.active ? 'bg-background' : 'bg-muted/40'"
                            >
                                <Switch
                                    :model-value="day.active"
                                    @update:model-value="
                                        toggleDay(day.day_of_week)
                                    "
                                />
                                <span class="w-20 text-sm font-medium">
                                    {{
                                        DAYS_OF_WEEK.find(
                                            (d) => d.value === day.day_of_week,
                                        )?.label
                                    }}
                                </span>
                                <template v-if="day.active">
                                    <Input
                                        v-model="day.start_time"
                                        type="time"
                                        class="h-8 w-28 text-sm"
                                    />
                                    <span class="text-xs text-muted-foreground">→</span>
                                    <Input
                                        v-model="day.end_time"
                                        type="time"
                                        class="h-8 w-28 text-sm"
                                    />
                                </template>
                                <Badge
                                    v-else
                                    variant="secondary"
                                    class="ml-auto text-xs"
                                >
                                    Nghỉ
                                </Badge>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- Footer -->
            <DialogFooter class="shrink-0 flex-row justify-end gap-2 border-t px-4 py-2.5 sm:px-6">
                <Button type="button" variant="outline" size="sm" @click="closeModal">
                    Hủy
                </Button>
                <Button
                    type="button"
                    size="sm"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-3.5 w-3.5 animate-spin"
                    />
                    {{ designer ? 'Lưu thay đổi' : 'Tạo mới' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
