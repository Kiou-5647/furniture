<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import {
    User,
    Loader2,
    Calendar,
    Info,
    ExternalLink,
    ChevronLeft,
} from '@lucide/vue';
import { ref, computed, onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import { availabilities } from '@/actions/App/Http/Controllers/Customer/BookingController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldContent, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { store } from '@/routes/customer/bookings';

interface Designer {
    id: string;
    full_name: string;
    bio: string;
    hourly_rate: number;
    avatar_url: string;
    phone: string;
    portfolio_url: string;
}

const props = defineProps<{
    customer: {
        id: string;
        name: string;
        email: string;
        phone: string;
        province_code: string;
        province_name: string;
        ward_code: string;
        ward_name: string;
        street: string | null;
    };
    designers: Designer[];
    deposit_percentage: number;
}>();

// --- FORM STATE ---
const form = useForm({
    customer_id: props.customer.id,
    customer_name: props.customer.name,
    customer_email: props.customer.email,
    customer_phone: props.customer.phone,
    province_code: props.customer.province_code,
    province_name: props.customer.province_name,
    ward_code: props.customer.ward_code,
    ward_name: props.customer.ward_name,
    street: props.customer.street || '',
    designer_id: null as string | null,
    date: '',
    start_time: '',
    duration: 1,
    notes: '',
});

// --- UI STATE ---
const isDesignerDialogOpen = ref(false);
const designerAvailability = ref<number[][]>([]);
const loadingAvailability = ref(false);
const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);

const DAYS = [
    { key: 1, label: 'T2' },
    { key: 2, label: 'T3' },
    { key: 3, label: 'T4' },
    { key: 4, label: 'T5' },
    { key: 5, label: 'T6' },
    { key: 6, label: 'T7' },
    { key: 0, label: 'CN' },
];
const HOURS = Array.from({ length: 19 }, (_, i) => i + 5);

// --- COMPUTED ---
const selectedDesigner = computed(() =>
    props.designers.find((d) => d.id === form.designer_id),
);

const totalCost = computed(() => {
    if (!selectedDesigner.value) return 0;
    return selectedDesigner.value.hourly_rate * form.duration;
});

const depositCost = computed(() => {
    return (totalCost.value * props.deposit_percentage) / 100;
});

const wardDisplayLabel = computed(() => {
    if (form.ward_name) return form.ward_name;
    if (!form.province_code) return 'Chọn Tỉnh trước';
    if (loadingWards.value) return 'Đang tải...';
    return 'Chọn Quận/Huyện';
});

// --- METHODS ---
async function loadProvinces() {
    loadingProvinces.value = true;
    try {
        const response = await fetch('/api/geodata/provinces');
        provinces.value = await response.json();
    } catch {
        toast.error('Không thể lấy thông tin tỉnh/thành');
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
        toast.error('Không thể lấy thông tin phường/xã');
    } finally {
        loadingWards.value = false;
    }
}

async function loadDesignerAvailability() {
    if (!form.designer_id) return;
    loadingAvailability.value = true;
    try {
        const response = await fetch(
            availabilities({ designer: form.designer_id }).url,
        );
        const data = await response.json();
        designerAvailability.value = data.weekly || [];
    } catch {
        toast.error('Không thể tải lịch làm việc của nhà thiết kế.');
        designerAvailability.value = [];
    } finally {
        loadingAvailability.value = false;
    }
}

async function handleSlotClick(day: number, hour: number) {
    if (designerAvailability.value[day]?.[hour] != 1) {
        toast.error('Khung giờ này không khả dụng. Vui lòng chọn giờ khác.');
        return;
    }

    const selectedDate = form.date ? new Date(form.date) : null;
    if (!selectedDate || selectedDate.getDay() != day) {
        form.date = getNextDateForDay(day);
    }

    try {
        const response = await fetch(
            `/dat-lich/designers/${form.designer_id}/available-slots?date=${form.date}`,
        );
        const data = await response.json();
        console.info(data);
        if (!data.slots || !data.slots[hour]) {
            toast.error(
                'Rất tiếc, khung giờ này vừa mới bị đặt. Vui lòng chọn giờ khác.',
            );
            return;
        }
    } catch {
        toast.error('Có lỗi xảy ra!');
    }

    form.start_time = `${String(hour).padStart(2, '0')}:00`;
}

function getNextDateForDay(dayOfWeek: number): string {
    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth();
    const d = now.getDate();
    const currentDay = now.getDay();

    let daysToAdd = dayOfWeek - currentDay;
    if (daysToAdd <= 0) {
        daysToAdd += 7;
    }

    const nextDate = new Date(y, m, d + daysToAdd);
    const year = nextDate.getFullYear();
    const month = String(nextDate.getMonth() + 1).padStart(2, '0');
    const day = String(nextDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

async function submit() {
    if (!form.designer_id || !form.date || !form.start_time) {
        toast.error('Vui lòng điền đầy đủ thông tin bắt buộc.');
        return;
    }

    form.processing = true;
    console.log(form.data());
    router.post(store(), form.data(), {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            toast.error(errors.error);
        },
        onFinish: () => {
            form.processing = false;
        },
    });
}

// --- WATCHERS ---
watch(
    () => form.province_code,
    async (newCode) => {
        if (!newCode) return;
        const province = provinces.value.find((p) => p.value === newCode);
        form.province_name = province?.label ?? '';
        form.ward_code = '';
        form.ward_name = '';
        wards.value = [];
        await loadWards(newCode);
    },
);

watch(
    () => form.ward_code,
    (newCode) => {
        const ward = wards.value.find((w) => w.value === newCode);
        form.ward_name = ward?.label ?? '';
    },
);

watch(
    () => form.designer_id,
    () => {
        loadDesignerAvailability();
    },
);

onMounted(() => {
    loadProvinces();
});

function handleReturn() {
    window.history.back()
}
</script>

<template>
    <ShopLayout>
        <div class="mx-auto max-w-[1400px] px-4 py-10">
            <div class="mb-8 flex items-center gap-3">
                <div>
                    <Button variant="ghost" class="h-10 w-10"
                    @click="handleReturn">
                        <ChevronLeft />
                    </Button>
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Đặt lịch tư vấn
                    </h1>
                    <p class="text-muted-foreground">
                        Hãy cung cấp thông tin của bạn và chọn nhà thiết kế phù
                        hợp.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- LEFT COLUMN: Customer Info -->
                <div class="space-y-8 lg:col-span-5">
                    <div class="rounded-2xl border bg-card p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-2 font-semibold">
                            <User class="h-5 w-5 text-primary" />
                            <span>Thông tin cá nhân</span>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <Field>
                                    <FieldLabel>Họ tên</FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.customer_name"
                                            placeholder="Nhập họ tên"
                                        />
                                    </FieldContent>
                                </Field>
                                <Field>
                                    <FieldLabel>Số điện thoại</FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.customer_phone"
                                            placeholder="Nhập SĐT"
                                        />
                                    </FieldContent>
                                </Field>
                            </div>

                            <Field>
                                <FieldLabel>Email</FieldLabel>
                                <FieldContent>
                                    <Input
                                        v-model="form.customer_email"
                                        type="email"
                                        placeholder="email@example.com"
                                    />
                                </FieldContent>
                            </Field>

                            <div class="space-y-4 pt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <Field>
                                        <FieldLabel>Tỉnh/Thành phố</FieldLabel>
                                        <FieldContent>
                                            <Select
                                                v-model="form.province_code"
                                            >
                                                <SelectTrigger class="w-full">
                                                    <SelectValue
                                                        :placeholder="
                                                            loadingProvinces
                                                                ? 'Đang tải...'
                                                                : 'Chọn tỉnh/thành'
                                                        "
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="p in provinces"
                                                        :key="p.value"
                                                        :value="p.value"
                                                    >
                                                        {{ p.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </FieldContent>
                                    </Field>
                                    <Field>
                                        <FieldLabel>Quận/Huyện</FieldLabel>
                                        <FieldContent>
                                            <Select
                                                v-model="form.ward_code"
                                                :disabled="
                                                    !form.province_code ||
                                                    loadingWards
                                                "
                                            >
                                                <SelectTrigger class="w-full">
                                                    <SelectValue
                                                        :placeholder="
                                                            wardDisplayLabel
                                                        "
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="w in wards"
                                                        :key="w.value"
                                                        :value="w.value"
                                                    >
                                                        {{ w.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </FieldContent>
                                    </Field>
                                </div>
                                <Field>
                                    <FieldLabel>Địa chỉ chi tiết</FieldLabel>
                                    <FieldContent>
                                        <Input
                                            v-model="form.street"
                                            placeholder="Số nhà, tên đường..."
                                        />
                                    </FieldContent>
                                </Field>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-card p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-2 font-semibold">
                            <Info class="h-5 w-5 text-primary" />
                            <span>Yêu cầu tư vấn</span>
                        </div>
                        <Field>
                            <FieldLabel>Ghi chú thêm</FieldLabel>
                            <FieldContent>
                                <Textarea
                                    v-model="form.notes"
                                    class="min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary"
                                    placeholder="Mô tả chi tiết yêu cầu của bạn..."
                                />
                            </FieldContent>
                        </Field>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Designer & Schedule -->
                <div class="lg:col-span-7">
                    <div
                        v-if="!form.designer_id"
                        class="flex h-full flex-col items-center justify-center rounded-2xl border-2 border-dashed p-12 text-center"
                    >
                        <div class="mb-4 rounded-full bg-primary/10 p-4">
                            <User class="h-8 w-8 text-primary" />
                        </div>
                        <h3 class="text-lg font-semibold">Chọn nhà thiết kế</h3>
                        <p class="mb-6 text-muted-foreground">
                            Vui lòng chọn một chuyên gia để bắt đầu đặt lịch tư
                            vấn.
                        </p>
                        <Button
                            @click="isDesignerDialogOpen = true"
                            size="lg"
                            class="rounded-full px-8"
                        >
                            Xem danh sách nhà thiết kế
                        </Button>
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Designer Profile Header -->
                        <div class="rounded-2xl border bg-card p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div
                                    class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-muted ring-2 ring-primary/20"
                                >
                                    <img
                                        :src="
                                            selectedDesigner?.avatar_url ||
                                            `https://ui-avatars.com/api/?name=${selectedDesigner?.full_name}&background=random`
                                        "
                                        class="h-full w-full object-cover"
                                        @error="
                                            (e: any) =>
                                                (e.target.src =
                                                    'https://ui-avatars.com/api/?name=NTK')
                                        "
                                    />
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h3 class="text-xl font-bold">
                                            {{ selectedDesigner?.full_name }}
                                        </h3>
                                        <span
                                            class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary"
                                        >
                                            {{
                                                selectedDesigner?.hourly_rate.toLocaleString()
                                            }}đ/giờ
                                        </span>
                                    </div>
                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        {{
                                            selectedDesigner?.bio ||
                                            'Chuyên gia tư vấn thiết kế nội thất.'
                                        }}
                                    </p>
                                    <div
                                        class="mt-3 flex flex-wrap gap-3 text-xs"
                                    >
                                        <div
                                            class="flex items-center gap-1 text-muted-foreground"
                                        >
                                            <Info class="h-3 w-3" />
                                            {{
                                                selectedDesigner?.phone ||
                                                'Liên hệ qua hệ thống'
                                            }}
                                        </div>
                                        <a
                                            v-if="
                                                selectedDesigner?.portfolio_url
                                            "
                                            :href="
                                                selectedDesigner?.portfolio_url
                                            "
                                            target="_blank"
                                            class="flex items-center gap-1 text-primary hover:underline"
                                        >
                                            <ExternalLink class="h-3 w-3" />
                                            Portfolio
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability Grid -->
                        <div class="rounded-2xl border bg-card p-6 shadow-sm">
                            <div class="mb-6 flex items-center justify-between">
                                <div
                                    class="flex items-center gap-2 font-semibold"
                                >
                                    <Calendar class="h-5 w-5 text-primary" />
                                    <span>Chọn khung giờ</span>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="form.designer_id = null"
                                    class="text-xs"
                                >
                                    Thay đổi NTK
                                </Button>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <Field>
                                        <FieldLabel>Ngày</FieldLabel>
                                        <FieldContent>
                                            <Input
                                                v-model="form.date"
                                                :min="
                                                    new Date()
                                                        .toISOString()
                                                        .split('T')[0]
                                                "
                                                type="date"
                                                required
                                            />
                                        </FieldContent>
                                    </Field>
                                    <Field>
                                        <FieldLabel>Giờ bắt đầu</FieldLabel>
                                        <FieldContent>
                                            <Input
                                                v-model="form.start_time"
                                                type="time"
                                                required
                                            />
                                        </FieldContent>
                                    </Field>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <Field>
                                        <FieldLabel
                                            >Thời lượng (Giờ)</FieldLabel
                                        >
                                        <FieldContent>
                                            <Input
                                                v-model="form.duration"
                                                type="number"
                                                min="1"
                                                max="12"
                                                required
                                            />
                                        </FieldContent>
                                    </Field>
                                    <div class="flex items-end pb-2">
                                        <div
                                            class="w-full rounded-lg border border-primary/10 bg-primary/5 p-3"
                                        >
                                            <div
                                                class="mb-1 flex justify-between text-xs text-muted-foreground"
                                            >
                                                <span
                                                    >Đặt cọc ({{
                                                        deposit_percentage
                                                    }}%)</span
                                                >
                                                <span
                                                    >Tổng:
                                                    {{
                                                        totalCost.toLocaleString()
                                                    }}đ</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <span
                                                    class="text-xs font-medium"
                                                    >Số tiền cần thanh
                                                    toán:</span
                                                >
                                                <span
                                                    class="text-lg font-bold text-primary"
                                                    >{{
                                                        depositCost.toLocaleString()
                                                    }}đ</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visual Grid -->
                                <div class="overflow-x-auto">
                                    <div
                                        v-if="loadingAvailability"
                                        class="flex h-40 items-center justify-center"
                                    >
                                        <Loader2
                                            class="h-6 w-6 animate-spin text-primary"
                                        />
                                    </div>
                                    <div v-else class="min-w-[600px] space-y-2">
                                        <div
                                            class="grid grid-cols-[40px_repeat(7,minmax(35px,1fr))] gap-px overflow-hidden rounded-md border bg-border"
                                        >
                                            <div
                                                class="bg-muted p-1 text-right text-[10px] font-medium text-muted-foreground"
                                            >
                                                Hour
                                            </div>
                                            <div
                                                v-for="day in DAYS"
                                                :key="day.key"
                                                class="bg-muted p-1 text-center text-[10px] font-medium"
                                            >
                                                {{ day.label }}
                                            </div>

                                            <template
                                                v-for="hour in HOURS"
                                                :key="hour"
                                            >
                                                <div
                                                    class="bg-muted p-1 text-right text-[10px] font-medium text-muted-foreground"
                                                >
                                                    {{
                                                        String(hour).padStart(
                                                            2,
                                                            '0',
                                                        )
                                                    }}:00
                                                </div>
                                                <div
                                                    v-for="day in DAYS"
                                                    :key="`${day.key}-${hour}`"
                                                    class="cursor-pointer bg-background p-0.5 transition-colors hover:bg-muted/50"
                                                    @click="
                                                        handleSlotClick(
                                                            day.key,
                                                            hour,
                                                        )
                                                    "
                                                >
                                                    <div
                                                        class="h-4 w-full rounded-sm transition-all"
                                                        :class="[
                                                            designerAvailability[
                                                                day.key
                                                            ]?.[hour] == 1
                                                                ? 'bg-green-600'
                                                                : 'bg-gray-200',
                                                            form.date &&
                                                            new Date(
                                                                form.date,
                                                            ).getDay() ===
                                                                day.key &&
                                                            form.start_time ===
                                                                `${String(hour).padStart(2, '0')}:00`
                                                                ? 'ring-2 ring-primary ring-offset-1'
                                                                : '',
                                                        ]"
                                                    ></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-center">
                <Button
                    @click="submit"
                    :disabled="form.processing"
                    size="lg"
                    class="w-full max-w-md rounded-full py-6 text-lg font-bold shadow-xl"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-5 w-5 animate-spin"
                    />
                    Xác nhận đặt lịch tư vấn
                </Button>
            </div>
        </div>

        <!-- Designer Selection Dialog -->
        <Dialog
            :open="isDesignerDialogOpen"
            @update:open="isDesignerDialogOpen = $event"
        >
            <DialogContent class="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>Chọn Nhà Thiết Kế</DialogTitle>
                    <DialogDescription>
                        Hãy chọn một chuyên gia phù hợp nhất với phong cách của
                        bạn.
                    </DialogDescription>
                </DialogHeader>
                <div
                    class="grid grid-cols-1 gap-4 py-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="d in designers"
                        :key="d.id"
                        @click="
                            form.designer_id = d.id;
                            isDesignerDialogOpen = false;
                        "
                        class="group cursor-pointer rounded-xl border p-4 transition-all hover:border-primary hover:shadow-md"
                        :class="
                            form.designer_id === d.id
                                ? 'border-primary ring-1 ring-primary'
                                : ''
                        "
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="h-12 w-12 overflow-hidden rounded-full bg-muted ring-2 ring-transparent transition-all group-hover:ring-primary/30"
                            >
                                <img
                                    :src="
                                        d.avatar_url ||
                                        `https://ui-avatars.com/api/?name=${d.full_name}&background=random`
                                    "
                                    class="h-full w-full object-cover transition-transform group-hover:scale-110"
                                    @error="
                                        (e: any) =>
                                            (e.target.src =
                                                'https://ui-avatars.com/api/?name=NTK')
                                    "
                                />
                            </div>
                            <div class="overflow-hidden">
                                <p class="truncate text-sm font-bold">
                                    {{ d.full_name }}
                                </p>
                                <p class="text-xs font-medium text-primary">
                                    {{ d.hourly_rate.toLocaleString() }}đ/giờ
                                </p>
                            </div>
                        </div>
                        <p
                            class="mt-3 line-clamp-2 text-xs text-muted-foreground"
                        >
                            {{
                                d.bio || 'Nhà thiết kế nội thất chuyên nghiệp.'
                            }}
                        </p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </ShopLayout>
</template>
