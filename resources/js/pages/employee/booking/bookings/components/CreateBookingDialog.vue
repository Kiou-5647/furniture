<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Loader2, Search, X, User, Clock, CreditCard } from '@lucide/vue';
import { ref, watch, computed, onMounted } from 'vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store } from '@/routes/employee/booking';
import { availabilities } from '@/routes/employee/hr/designers';

interface CustomerOption {
    id: string;
    name: string;
    email: string;
    phone?: string;
    address?: {
        province_code: string;
        province_name: string;
        ward_code: string;
        ward_name: string;
        street: string;
    };
}

interface DesignerOption {
    value: string;
    label: string;
    hourly_rate: number;
}

const props = defineProps<{
    open: boolean;
    deposit_percentage: number;
    designerOptions: DesignerOption[];
    customerOptions: CustomerOption[];
}>();

const emit = defineEmits<{
    close: [];
    created: [];
}>();

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
const selectedSlot = ref<{ day: number; hour: number } | null>(null);

const customerSearch = ref('');
const showCustomerDropdown = ref(false);
const designerAvailability = ref<number[][]>([]);
const loadingAvailability = ref(false);
const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);

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

const form = useForm({
    customer_id: null as string | null,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    province_code: '',
    ward_code: '',
    province_name: '',
    ward_name: '',
    street: '',

    designer_id: null as string | null,
    date: '',
    start_time: '',
    duration: 1,
    deadline_at: '',
    notes: '',
});

const filteredCustomerOptions = computed(() => {
    if (customerSearch.value.length < 1)
        return props.customerOptions.slice(0, 10);
    const q = customerSearch.value.toLowerCase();
    return props.customerOptions.filter(
        (c) =>
            c.name.toLowerCase().includes(q) ||
            c.email.toLowerCase().includes(q) ||
            (c.phone && c.phone.includes(q)),
    );
});

const selectedCustomerLabel = computed(() => {
    if (!form.customer_id) return '';
    const c = props.customerOptions.find((c) => c.id === form.customer_id);
    if (!c) return '';
    return `${c.name} (${c.email})`;
});

const selectedDesigner = computed(() =>
    props.designerOptions.find((d) => d.value === form.designer_id),
);

const skipProvinceWatch = ref(false);

async function selectCustomer(c: CustomerOption) {
    // Identity Snapshot
    form.customer_id = c.id;
    form.customer_name = c.name;
    form.customer_email = c.email;
    form.customer_phone = c.phone || '';

    // Address Snapshot
    if (c.address) {
        if (provinces.value.length === 0) {
            await loadProvinces();
        }
        skipProvinceWatch.value = true;

        form.province_code = c.address.province_code || '';
        form.province_name = c.address.province_name || '';
        form.street = c.address.street || '';

        if (form.province_code) {
            await loadWards(form.province_code);
            form.ward_code = c.address.ward_code || '';
            form.ward_name = c.address.ward_name || '';
        }
        skipProvinceWatch.value = false;
    }

    customerSearch.value = '';
    showCustomerDropdown.value = false;
}

function clearCustomer() {
    form.customer_id = null;
    form.customer_name = '';
    form.customer_email = '';
    form.customer_phone = '';
    form.province_code = '';
    form.province_name = '';
    form.ward_code = '';
    form.ward_name = '';
    form.street = '';
    customerSearch.value = '';
}

function closeCustomerDropdown() {
    setTimeout(() => {
        showCustomerDropdown.value = false;
    }, 150);
}

function autoSelectFirstSlot() {
    if (form.start_time) return;
    if (!form.date || designerAvailability.value.length === 0) return;

    const dayOfWeek = new Date(form.date).getDay();
    const daySlots = designerAvailability.value[dayOfWeek] || [];

    // Find the first hour (0-23) that is true (available)
    const firstAvailableHour = daySlots.findIndex((slot) => slot === 1);

    if (firstAvailableHour !== -1) {
        // Format as "HH:00" to match the start_time field
        form.start_time = `${String(firstAvailableHour).padStart(2, '0')}:00`;
    } else {
        form.start_time = '';
    }
}

async function loadDesignerAvailability() {
    if (!form.designer_id) {
        designerAvailability.value = [];
        return;
    }

    loadingAvailability.value = true;
    try {
        const response = await fetch(
            availabilities({ designer: form.designer_id }).url,
        );
        const data = await response.json();
        designerAvailability.value = data.weekly || [];

        // After loading the grid, try to set a valid start time for the current date
        autoSelectFirstSlot();
    } catch (e) {
        console.error('Failed to load availability:', e);
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
    if (!selectedDate || selectedDate.getDay() !== day) {
        form.date = getNextDateForDay(day);
    }

    try {
        const response = await fetch(
            `/api/designers/${form.designer_id}/available-slots?date=${form.date}`,
        );
        const data = await response.json();
        if (!data.slots || !data.slots[hour]) {
            toast.error(
                'Rất tiếc, khung giờ này vừa mới bị đặt. Vui lòng chọn giờ khác.',
            );
            return;
        }
    } catch (e) {
        console.error('Availability check failed', e);
    }

    form.start_time = `${String(hour).padStart(2, '0')}:00`;
}

watch(
    () => form.date,
    () => {
        autoSelectFirstSlot();
    },
);

watch(
    () => form.designer_id,
    () => {
        loadDesignerAvailability();
    },
);

async function submit() {
    // Double check required fields
    if (!form.designer_id || !form.date || !form.start_time || !form.duration) {
        toast.error('Vui lòng điền đầy đủ thông tin bắt buộc.');
        return;
    }

    form.processing = true;

    try {
        await router.post(
            store().url,
            {
                ...form.data(),
            },
            {
                preserveScroll: true,
                preserveState: true,
                onError: (errors) => {
                    console.error(`Lỗi: ${errors}`)
                },
                onFinish: () => {
                    form.processing = false;
                },
            },
        );
    } catch (e) {
        toast.error('Không thể tạo đặt lịch. Vui lòng thử lại.');
    } finally {
        form.processing = false;
    }
}

watch(
    () => form.province_code,
    async (newCode) => {
        if (skipProvinceWatch.value) return;
        if (newCode) {
            const province = provinces.value.find((p) => p.value === newCode);
            form.province_name = province?.label ?? '';
            form.ward_code = '';
            form.ward_name = '';
            wards.value = [];
            await loadWards(newCode);
        }
    },
);

watch(
    () => form.ward_code,
    (newCode) => {
        if (newCode) {
            const ward = wards.value.find((w) => w.value === newCode);
            form.ward_name = ward?.label ?? '';
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent
            class="max-h-[90vh] w-fit overflow-y-auto sm:max-w-[75vw]"
        >
            <DialogHeader>
                <DialogTitle>Tạo đặt lịch mới</DialogTitle>
                <DialogDescription>
                    Thiết lập thời gian và thông tin khách hàng cho buổi tư vấn.
                </DialogDescription>
            </DialogHeader>
            <div class="flex flex-col gap-3 md:flex-row">
                <!-- LEFT COLUMN: Form Inputs -->
                <div class="flex-1 space-y-6 md:w-[40vw]">
                    <!-- Customer Section -->
                    <div class="space-y-3">
                        <Field>
                            <FieldLabel>
                                <User class="mr-1 h-3.5 w-3.5" />
                                Khách hàng
                            </FieldLabel>
                            <FieldContent>
                                <div
                                    v-if="!form.customer_id"
                                    class="relative"
                                    @click.stop
                                >
                                    <Search
                                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="customerSearch"
                                        placeholder="Tìm khách hàng hoặc nhập mới..."
                                        class="w-full pl-9 text-sm"
                                        @focus="showCustomerDropdown = true"
                                        @blur="closeCustomerDropdown"
                                    />
                                    <div
                                        v-if="
                                            showCustomerDropdown &&
                                            filteredCustomerOptions.length > 0
                                        "
                                        class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-md border bg-popover shadow-md"
                                    >
                                        <div
                                            v-for="c in filteredCustomerOptions"
                                            :key="c.id"
                                            class="cursor-pointer px-3 py-2 text-sm hover:bg-muted"
                                            @click="selectCustomer(c)"
                                        >
                                            <span class="font-medium">{{
                                                c.name
                                            }}</span>
                                            <span
                                                v-if="c.phone"
                                                class="ml-2 text-xs text-muted-foreground"
                                                >{{ c.phone }}</span
                                            >
                                            <span
                                                class="ml-1 text-xs text-muted-foreground"
                                                >({{ c.email }})</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex items-center justify-between rounded-md border bg-muted px-3 py-2"
                                >
                                    <span class="text-sm font-medium">{{
                                        selectedCustomerLabel
                                    }}</span>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-6 w-6"
                                        @click="clearCustomer()"
                                        ><X class="h-3.5 w-3.5"
                                    /></Button>
                                </div>
                                <FieldError
                                    :errors="[form.errors.customer_id]"
                                />
                            </FieldContent>
                        </Field>

                        <div class="grid grid-cols-2 gap-2">
                            <Input
                                v-model="form.customer_name"
                                placeholder="Họ tên"
                                class="text-sm"
                            />
                            <Input
                                v-model="form.customer_phone"
                                placeholder="SĐT"
                                class="text-sm"
                            />
                        </div>
                        <Input
                            v-model="form.customer_email"
                            placeholder="Email"
                            class="text-sm"
                        />
                    </div>

                    <!-- Address Section -->
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <Field>
                                <FieldLabel>Tỉnh/TP</FieldLabel>
                                <FieldContent>
                                    <Select v-model="form.province_code">
                                        <SelectTrigger class="w-full text-sm">
                                            <SelectValue
                                                :placeholder="
                                                    loadingProvinces
                                                        ? 'Đang tải...'
                                                        : 'Chọn Tỉnh/TP'
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
                                </FieldContent>
                            </Field>
                            <Field>
                                <FieldLabel>Quận/Huyện</FieldLabel>
                                <FieldContent>
                                    <Select
                                        v-model="form.ward_code"
                                        :disabled="
                                            !form.province_code || loadingWards
                                        "
                                    >
                                        <SelectTrigger class="w-full text-sm">
                                            <SelectValue
                                                :placeholder="wardDisplayLabel"
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
                                </FieldContent>
                            </Field>
                        </div>

                        <Field>
                            <FieldLabel>Địa chỉ chi tiết</FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.street"
                                    placeholder="Số nhà, tên đường..."
                                    class="text-sm"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Designer & Time Section -->
                    <div class="space-y-4">
                        <Field>
                            <FieldLabel>Nhà thiết kế</FieldLabel>
                            <Select v-model="form.designer_id" required>
                                <SelectTrigger
                                    ><SelectValue
                                        placeholder="Chọn nhà thiết kế"
                                /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="opt in designerOptions"
                                        :key="String(opt.value)"
                                        :value="String(opt.value)"
                                    >
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError v-if="form.errors.designer_id">{{
                                form.errors.designer_id
                            }}</FieldError>
                        </Field>

                        <div class="grid grid-cols-2 gap-4">
                            <Field>
                                <FieldLabel>Ngày</FieldLabel>
                                <Input
                                    v-model="form.date"
                                    type="date"
                                    required
                                />
                            </Field>
                            <Field>
                                <FieldLabel>Giờ bắt đầu</FieldLabel>
                                <Input
                                    v-model="form.start_time"
                                    type="time"
                                    required
                                    class="text-sm"
                                />
                            </Field>
                        </div>

                        <Field>
                            <FieldLabel>Thời lượng (Giờ)</FieldLabel>
                            <Input
                                v-model="form.duration"
                                type="number"
                                min="1"
                                max="12"
                                required
                                class="text-sm"
                            />
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel>Ghi chú</FieldLabel>
                        <FieldContent>
                            <textarea
                                v-model="form.notes"
                                class="min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            />
                        </FieldContent>
                    </Field>
                </div>

                <!-- RIGHT COLUMN: Availability Grid & Summary -->
                <div
                    v-if="form.designer_id"
                    class="w-full shrink-0 border-t pt-4 md:max-w-136 md:border-t-0 md:border-l md:pt-0 md:pl-6"
                >
                    <!-- Price Preview Card (New) -->
                    <div
                        class="mb-6 space-y-3 rounded-xl border border-primary/20 bg-primary/5 p-4"
                    >
                        <div
                            class="flex items-center gap-2 font-semibold text-primary"
                        >
                            <CreditCard class="h-4 w-4" />
                            <span>Chi phí ước tính</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground"
                                >Tổng cộng:</span
                            >
                            <span class="font-bold"
                                >{{
                                    (
                                        selectedDesigner!.hourly_rate *
                                        form.duration
                                    ).toLocaleString()
                                }}đ</span
                            >
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground"
                                >Đặt cọc ({{ deposit_percentage }}%):</span
                            >
                            <span class="font-bold text-primary">
                                {{
                                    (
                                        (selectedDesigner!.hourly_rate *
                                            form.duration *
                                            deposit_percentage) /
                                        100
                                    ).toLocaleString()
                                }}đ
                            </span>
                        </div>
                    </div>

                    <!-- Availability Grid (Existing logic) -->
                    <div class="mb-3 flex items-center gap-2">
                        <Clock class="h-4 w-4" />
                        <span class="text-sm font-medium">Lịch làm việc</span>
                        <Loader2
                            v-if="loadingAvailability"
                            class="ml-auto h-3 w-3 animate-spin"
                        />
                    </div>

                    <div
                        v-if="
                            !loadingAvailability &&
                            designerAvailability.length > 0
                        "
                        class="space-y-4"
                    >
                        <!-- The Availability Grid -->
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

                            <template v-for="hour in HOURS" :key="hour">
                                <div
                                    class="bg-muted p-1 text-right text-[10px] font-medium text-muted-foreground"
                                >
                                    {{ String(hour).padStart(2, '0') }}:00
                                </div>
                                <div
                                    v-for="day in DAYS"
                                    :key="`${day.key}-${hour}`"
                                    class="cursor-pointer bg-background p-0.5 transition-colors hover:bg-muted/50"
                                    @click="handleSlotClick(day.key, hour)"
                                >
                                    <div
                                        class="h-4 w-full rounded-sm transition-all"
                                        :class="[
                                            designerAvailability[day.key]?.[
                                                hour
                                            ] == 1
                                                ? 'bg-green-600'
                                                : 'bg-gray-200',
                                            form.date &&
                                            new Date(form.date).getDay() ===
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

            <DialogFooter>
                <Button type="button" variant="outline" @click="emit('close')">
                    Hủy
                </Button>
                <Button
                    type="submit"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Tạo đặt lịch
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script lang="ts">
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
</script>
