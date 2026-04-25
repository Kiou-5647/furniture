<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronRight,
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
import {
    store,
    update,
    updateAvailabilitySlots,
} from '@/routes/employee/hr/designers';
import type { WeeklySlots } from '@/types/designer';

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
const weeklySlots = ref<WeeklySlots>({});

const DAYS = [
    { key: 1, label: 'T2' },
    { key: 2, label: 'T3' },
    { key: 3, label: 'T4' },
    { key: 4, label: 'T5' },
    { key: 5, label: 'T6' },
    { key: 6, label: 'T7' },
    { key: 0, label: 'CN' },
];

const HOURS = Array.from({ length: 24 }, (_, i) => i);

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
});

const showEmployeeInfo = ref(false);

const activeDaysCount = computed(() => {
    let count = 0;
    for (const day of DAYS) {
        if (weeklySlots.value[day.key]?.some(Boolean)) {
            count++;
        }
    }
    return count;
});

function formatHour(hour: number): string {
    return `${hour.toString().padStart(2, '0')}:00`;
}

function getFirstHour(day: number): number | null {
    const slots = weeklySlots.value[day];
    if (!slots) return null;
    for (let h = 0; h < 24; h++) {
        if (slots[h]) return h;
    }
    return null;
}

function getLastHour(day: number): number | null {
    const slots = weeklySlots.value[day];
    if (!slots) return null;
    for (let h = 23; h >= 0; h--) {
        if (slots[h]) return h + 1;
    }
    return null;
}

watch(
    () => props.designer,
    async (newDes) => {
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

            if (newDes.id) {
                try {
                    const response = await fetch(
                        `/nhan-vien/quan-ly-nhan-su/nha-thiet-ke/${newDes.id}/availabilities`,
                        {
                            headers: {
                                'X-CSRF-TOKEN':
                                    document
                                        .querySelector(
                                            'meta[name="csrf-token"]',
                                        )
                                        ?.getAttribute('content') || '',
                            },
                        },
                    );
                    const data = await response.json();
                    weeklySlots.value = data.weekly || {};
                } catch (e) {
                    weeklySlots.value = {};
                }
            }
        } else if (!newDes && props.open) {
            resetForm();
        }
    },
    { immediate: true },
);

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
    designerType.value = 'freelancer';
    selectedEmployeeId.value = undefined;
    avatarPreview.value = null;
    weeklySlots.value = {};
}

function toggleHour(day: number, hour: number) {
    if (!weeklySlots.value[day]) {
        weeklySlots.value[day] = Array(24).fill(false);
    }
    weeklySlots.value[day][hour] = !weeklySlots.value[day][hour];
}

function selectAllDay(day: number, available: boolean) {
    if (!weeklySlots.value[day]) {
        weeklySlots.value[day] = Array(24).fill(available);
    } else {
        weeklySlots.value[day] = weeklySlots.value[day].map(() => available);
    }
}

function submit() {
    form.employee_id =
        designerType.value === 'employee'
            ? selectedEmployeeId.value
            : undefined;

    if (props.designer) {
        form.put(update({ designer: props.designer.id }).url, {
            preserveScroll: true,
            onSuccess: () => {
                saveAvailability();
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

async function saveAvailability() {
    if (!props.designer?.id) return;

    const slotsFlat: Array<{
        day_of_week: number;
        hour: number;
        is_available: boolean;
    }> = [];

    for (const day of DAYS) {
        for (const hour of HOURS) {
            slotsFlat.push({
                day_of_week: day.key,
                hour,
                is_available: weeklySlots.value[day.key]?.[hour] ?? false,
            });
        }
    }

    try {
        await router.put(
            updateAvailabilitySlots({ designer: props.designer.id }).url,
            { slots: slotsFlat },
            { preserveScroll: true },
        );
        emit('refresh');
        closeModal();
    } catch (e) {
        console.error('Save error:', e);
        alert('Lỗi khi lưu availability');
    }
}

function closeModal() {
    form.reset();
    form.clearErrors();
    form.avatar = null;
    avatarPreview.value = null;
    designerType.value = 'freelancer';
    selectedEmployeeId.value = undefined;
    weeklySlots.value = {};
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="flex h-[90vh] max-h-[90vh] flex-col gap-0 overflow-hidden p-0 sm:max-w-[1000px]"
        >
            <DialogHeader class="shrink-0 border-b px-4 py-3 sm:px-6 sm:py-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle
                            class="text-left text-base font-semibold sm:text-lg"
                        >
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
                    <Badge
                        v-if="props.designer?.employee"
                        variant="secondary"
                        class="text-xs"
                    >
                        <Users class="mr-1.5 h-3 w-3" />
                        {{ props.designer.employee.full_name }}
                    </Badge>
                </div>
            </DialogHeader>

            <div
                class="flex flex-1 flex-col overflow-y-auto sm:flex-row sm:overflow-hidden"
            >
                <div
                    class="w-full px-4 py-4 sm:min-h-0 sm:flex-1 sm:overflow-y-auto sm:border-r sm:px-5 sm:py-5"
                >
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

                    <div
                        v-if="designer && props.designer?.employee"
                        class="mb-4 flex items-center gap-3"
                    >
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
                            <ChevronRight v-else class="ml-1 h-3 w-3" />
                        </Button>
                        <p class="text-xs text-muted-foreground">
                            Nhà thiết kế này được liên kết với nhân viên
                        </p>
                    </div>

                    <div
                        v-if="showEmployeeInfo && props.designer?.employee"
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
                                <span>{{
                                    props.designer.employee.full_name
                                }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Mail
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                <span class="truncate">{{
                                    props.designer.employee.email ?? '—'
                                }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Phone
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                <span>{{
                                    props.designer.employee.phone ?? '—'
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!designer && designerType === 'employee'"
                        class="mb-4"
                    >
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
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Ảnh hồ sơ
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Upload ảnh vuông, tối đa 2MB
                                </p>
                            </div>
                        </div>

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
                                <FieldError :errors="[form.errors.phone]" />
                            </FieldContent>
                        </Field>

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

                        <div
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div class="space-y-0.5">
                                <Label class="text-sm font-medium"
                                    >Kích hoạt</Label
                                >
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
                                <Label class="text-sm font-medium"
                                    >Tự động xác nhận</Label
                                >
                                <p class="text-xs text-muted-foreground">
                                    Tự động duyệt lịch đặt mà không cần xác nhận
                                    thủ công
                                </p>
                            </div>
                            <Switch v-model="form.auto_confirm_bookings" />
                        </div>
                    </form>
                </div>

                <div
                    class="w-full border-t px-4 py-4 sm:w-[45%] sm:flex-shrink-0 sm:border-t-0 sm:px-5 sm:py-5"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold"
                                >Lịch làm việc</span
                            >
                            <Badge
                                v-if="activeDaysCount > 0"
                                variant="secondary"
                                class="text-xs"
                            >
                                {{ activeDaysCount }} ngày
                            </Badge>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="min-w-[400px]">
                            <div
                                class="grid grid-cols-[40px_repeat(7,minmax(35px,1fr))] gap-px bg-border"
                            >
                                <div
                                    class="bg-muted p-1 text-center text-[10px] text-muted-foreground"
                                >
                                    Giờ
                                </div>
                                <div
                                    v-for="day in DAYS"
                                    :key="day.key"
                                    class="bg-muted p-1 text-center text-[10px] font-medium"
                                >
                                    {{ day.label }}
                                </div>

                                <template v-for="hour in HOURS" :key="hour">
                                    <div
                                        class="bg-muted p-1 pr-1 text-right text-[10px] text-muted-foreground"
                                    >
                                        {{ formatHour(hour) }}
                                    </div>
                                    <div
                                        v-for="day in DAYS"
                                        :key="`${day.key}-${hour}`"
                                        class="cursor-pointer bg-background p-0.5 hover:bg-muted/50"
                                        @click="toggleHour(day.key, hour)"
                                    >
                                        <div
                                            class="h-5 w-full rounded-sm transition-colors"
                                            :class="
                                                weeklySlots[day.key]?.[hour]
                                                    ? 'bg-green-500'
                                                    : ''
                                            "
                                        />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded bg-green-500"></div>
                            <span>Đã chọn</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div
                                class="h-3 w-3 rounded border border-border bg-background"
                            ></div>
                            <span>Trống</span>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter
                class="shrink-0 flex-row justify-end gap-2 border-t px-4 py-2.5 sm:px-6"
            >
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="closeModal"
                >
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
