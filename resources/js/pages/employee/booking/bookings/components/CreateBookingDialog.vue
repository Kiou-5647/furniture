<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Search, X, User, Clock } from '@lucide/vue';
import { ref, watch, computed } from 'vue';
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
import type { Booking } from '@/types/booking';

interface CustomerOption {
    id: string;
    name: string;
    email: string;
    phone?: string;
}

interface DesignerOption {
    value: string;
    label: string;
}

interface DesignerOption {
    value: string;
    label: string;
}

const props = defineProps<{
    open: boolean;
    designerOptions: DesignerOption[];
    serviceOptions: DesignerOption[];
    customerOptions: CustomerOption[];
}>();

const emit = defineEmits<{
    close: [];
    created: [];
}>();

const customerSearch = ref('');
const showCustomerDropdown = ref(false);
const designerAvailability = ref<number[][]>([]);
const loadingAvailability = ref(false);
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const form = useForm({
    customer_id: null as string | null,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    designer_id: null as string | null,
    service_id: null as string | null,
    date: '',
    start_hour: '9',
    end_hour: '17',
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

function selectCustomer(c: CustomerOption) {
    form.customer_id = c.id;
    form.customer_name = c.name;
    form.customer_email = c.email;
    form.customer_phone = c.phone || '';
    customerSearch.value = '';
    showCustomerDropdown.value = false;
}

function clearCustomer() {
    form.customer_id = null;
    form.customer_name = '';
    form.customer_email = '';
    form.customer_phone = '';
    customerSearch.value = '';
}

function closeCustomerDropdown() {
    setTimeout(() => {
        showCustomerDropdown.value = false;
    }, 150);
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
    } catch (e) {
        console.error('Failed to load availability:', e);
        designerAvailability.value = [];
    } finally {
        loadingAvailability.value = false;
    }
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            clearCustomer();
            form.start_hour = '9';
            form.end_hour = '17';
            form.date = new Date().toISOString().split('T')[0];
        }
    },
);

watch(
    () => form.designer_id,
    () => {
        loadDesignerAvailability();
    },
);

const todayDayOfWeek = computed(() => {
    return new Date().getDay();
});

const availableHoursForSelectedDate = computed(() => {
    if (
        !form.designer_id ||
        !form.date ||
        designerAvailability.value.length === 0
    ) {
        return [];
    }

    const dayOfWeek = new Date(form.date).getDay();
    const daySlots = designerAvailability.value[dayOfWeek] || [];

    const hours: number[] = [];
    for (let h = 0; h < 24; h++) {
        if (daySlots[h]) {
            hours.push(h);
        }
    }
    return hours;
});

const dayLabels = [
    'Chủ nhật',
    'Thứ hai',
    'Thứ ba',
    'Thứ tư',
    'Thứ năm',
    'Thứ sáu',
    'Thứ bảy',
];

function isSlotAvailable(day: number, hour: number): boolean {
    return designerAvailability.value[day]?.[hour] == 1;
}

function submit() {
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            emit('created');
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="w-fit max-h-[90vh] overflow-y-auto sm:max-w-6xl">
            <DialogHeader>
                <DialogTitle>Tạo đặt lịch mới</DialogTitle>
                <DialogDescription>
                    Tạo đặt lịch thiết kế mới cho khách hàng.
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-6 md:flex-row">
                <div class="flex-1 space-y-4 md:max-w-lg">
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
                                    placeholder="Tìm theo tên hoặc SĐT..."
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
                                >
                                    <X class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                            <FieldError :errors="[form.errors.customer_id]" />
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

                    <Field>
                        <FieldLabel>Nhà thiết kế</FieldLabel>
                        <Select v-model="form.designer_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Chọn nhà thiết kế" />
                            </SelectTrigger>
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
                        <FieldError v-if="form.errors.designer_id">
                            {{ form.errors.designer_id }}
                        </FieldError>
                    </Field>

                    <Field>
                        <FieldLabel>Dịch vụ</FieldLabel>
                        <Select v-model="form.service_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Chọn dịch vụ" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in serviceOptions"
                                    :key="String(opt.value)"
                                    :value="String(opt.value)"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError v-if="form.errors.service_id">
                            {{ form.errors.service_id }}
                        </FieldError>
                    </Field>

                    <Field>
                        <FieldLabel>Ngày</FieldLabel>
                        <Input
                            v-model="form.date"
                            type="date"
                            required
                            :min="new Date().toISOString().split('T')[0]"
                        />
                        <FieldError v-if="form.errors.date">
                            {{ form.errors.date }}
                        </FieldError>
                    </Field>

                    <div class="grid grid-cols-2 gap-4">
                        <Field>
                            <FieldLabel>Giờ bắt đầu</FieldLabel>
                            <Select v-model="form.start_hour" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Giờ bắt đầu" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="h in availableHoursForSelectedDate"
                                        :key="String(h)"
                                        :value="String(h)"
                                    >
                                        {{ String(h).padStart(2, '0') }}:00
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError v-if="form.errors.start_hour">
                                {{ form.errors.start_hour }}
                            </FieldError>
                        </Field>

                        <Field>
                            <FieldLabel>Giờ kết thúc</FieldLabel>
                            <Select v-model="form.end_hour" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Giờ kết thúc" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="h in availableHoursForSelectedDate"
                                        :key="String(h)"
                                        :value="String(h)"
                                    >
                                        {{ String(h).padStart(2, '0') }}:00
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <FieldError v-if="form.errors.end_hour">
                                {{ form.errors.end_hour }}
                            </FieldError>
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel>Deadline (nếu cần)</FieldLabel>
                        <Input
                            v-model="form.deadline_at"
                            type="datetime-local"
                        />
                        <FieldError v-if="form.errors.deadline_at">
                            {{ form.errors.deadline_at }}
                        </FieldError>
                    </Field>

                    <Field>
                        <FieldLabel>Ghi chú</FieldLabel>
                        <FieldContent>
                            <textarea
                                v-model="form.notes"
                                class="min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                placeholder="Ghi chú thêm..."
                            />
                        </FieldContent>
                        <FieldError v-if="form.errors.notes">
                            {{ form.errors.notes }}
                        </FieldError>
                    </Field>
                </div>

                <div
                    v-if="form.designer_id"
                    class="w-full shrink-0 border-t pt-4 md:max-w-136 md:border-t-0 md:border-l md:pt-0 md:pl-6"
                >
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
                        class="space-y-2"
                    >
                        <div class="mb-2 text-xs text-muted-foreground">
                            Tuần này (nhấn để chọn ngày)
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <button
                                v-for="(day, dayIdx) in dayLabels"
                                :key="dayIdx"
                                class="rounded p-1 text-center text-xs"
                                :class="
                                    form.date &&
                                    new Date(form.date).getDay() === dayIdx
                                        ? 'bg-primary text-primary-foreground'
                                        : 'hover:bg-muted'
                                "
                                @click="form.date = getNextDateForDay(dayIdx)"
                            >
                                {{ day }}
                            </button>
                        </div>

                        <div class="mt-3">
                            <div class="mb-1 text-xs text-muted-foreground">
                                Giờ làm việc ngày
                                {{
                                    form.date
                                        ? dayLabels[
                                              new Date(form.date).getDay()
                                          ]
                                        : ''
                                }}
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="h in availableHoursForSelectedDate"
                                    :key="h"
                                    class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-800"
                                >
                                    {{ String(h).padStart(2, '0') }}:00
                                </span>
                                <span
                                    v-if="
                                        availableHoursForSelectedDate.length ===
                                        0
                                    "
                                    class="text-xs text-muted-foreground"
                                >
                                    Không có slot nào
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="mb-1 text-xs text-muted-foreground">
                                Lịch tuần
                            </div>
                            <div class="max-h-48 space-y-1 overflow-y-auto">
                                <div
                                    v-for="(day, dayIdx) in dayLabels"
                                    :key="dayIdx"
                                    class="flex items-center gap-2 text-xs"
                                >
                                    <span
                                        class="w-16 shrink-0 text-muted-foreground"
                                        >{{ day }}</span
                                    >
                                    <div class="flex flex-wrap gap-0.5">
                                        <span
                                            v-for="h in 24"
                                            :key="h"
                                            class="h-3 w-4 rounded-sm"
                                            :class="
                                                isSlotAvailable(dayIdx, h - 1)
                                                    ? 'bg-green-400'
                                                    : 'bg-gray-200'
                                            "
                                            :title="`${h - 1}:00`"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-xs">
                                <span
                                    class="inline-block h-3 w-3 rounded-sm bg-green-400"
                                />
                                <span class="text-muted-foreground"
                                    >Available</span
                                >
                                <span
                                    class="ml-2 inline-block h-3 w-3 rounded-sm bg-gray-200"
                                />
                                <span class="text-muted-foreground">Không</span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else-if="!loadingAvailability"
                        class="text-sm text-muted-foreground"
                    >
                        Chưa có lịch làm việc
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
    if (daysToAdd < 0) {
        daysToAdd += 7;
    }

    const nextDate = new Date(y, m, d + daysToAdd);
    const year = nextDate.getFullYear();
    const month = String(nextDate.getMonth() + 1).padStart(2, '0');
    const day = String(nextDate.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}
</script>
