<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { updateAvailabilitySlots } from '@/routes/employee/hr/designers';
import type { WeeklySlots } from '@/types/designer';

const props = defineProps<{
    designerId: string;
    designerName: string;
    initialWeekly: WeeklySlots;
}>();

const HOURS = Array.from({ length: 24 }, (_, i) => i);
const DAYS = [
    { key: 1, label: 'T2' },
    { key: 2, label: 'T3' },
    { key: 3, label: 'T4' },
    { key: 4, label: 'T5' },
    { key: 5, label: 'T6' },
    { key: 6, label: 'T7' },
    { key: 0, label: 'CN' },
];

const weeklySlots = ref<WeeklySlots>(structuredClone(props.initialWeekly));
const isSaving = ref(false);
const hasChanges = ref(false);

const slotsFlat = computed(() => {
    const flat: Array<{
        day_of_week: number;
        hour: number;
        is_available: boolean;
    }> = [];
    for (const day of DAYS) {
        for (const hour of HOURS) {
            flat.push({
                day_of_week: day.key,
                hour,
                is_available: weeklySlots.value[day.key]?.[hour] ?? false,
            });
        }
    }
    return flat;
});

watch(
    weeklySlots,
    () => {
        hasChanges.value = true;
    },
    { deep: true },
);

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
    hasChanges.value = true;
}

function selectWorkHours(day: number) {
    const workHours = [8, 9, 10, 11, 13, 14, 15, 16, 17, 18, 19];
    if (!weeklySlots.value[day]) {
        weeklySlots.value[day] = Array(24).fill(false);
    }
    for (const hour of workHours) {
        weeklySlots.value[day][hour] = true;
    }
    hasChanges.value = true;
}

function clearDay(day: number) {
    if (!weeklySlots.value[day]) {
        weeklySlots.value[day] = Array(24).fill(false);
    } else {
        weeklySlots.value[day] = weeklySlots.value[day].map(() => false);
    }
    hasChanges.value = true;
}

async function saveSlots() {
    isSaving.value = true;
    try {
        const url = updateAvailabilitySlots({ designer: props.designerId }).url;
        await fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slots: slotsFlat.value }),
        });
        hasChanges.value = false;
    } catch (e) {
        console.error(e);
    } finally {
        isSaving.value = false;
    }
}

function formatHour(hour: number): string {
    return `${hour.toString().padStart(2, '0')}:00`;
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Lịch làm việc hàng tuần</h2>
                <p class="text-sm text-muted-foreground">{{ designerName }}</p>
            </div>
            <Button :disabled="!hasChanges || isSaving" @click="saveSlots">
                {{ isSaving ? 'Đang lưu...' : 'Lưu lịch' }}
            </Button>
        </div>

        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                <div class="grid grid-cols-[60px_repeat(7,1fr)] gap-1">
                    <div class="p-2 text-center text-sm font-medium">Giờ</div>
                    <div
                        v-for="day in DAYS"
                        :key="day.key"
                        class="p-2 text-center text-sm font-medium"
                    >
                        <div>{{ day.label }}</div>
                        <div class="mt-1 flex justify-center gap-1">
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-6 px-1 text-xs"
                                @click="selectAllDay(day.key, true)"
                            >
                                All
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-6 px-1 text-xs"
                                @click="selectWorkHours(day.key)"
                            >
                                Job
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-6 px-1 text-xs"
                                @click="clearDay(day.key)"
                            >
                                X
                            </Button>
                        </div>
                    </div>

                    <template v-for="hour in HOURS" :key="hour">
                        <div
                            class="p-1 pr-2 text-right text-xs text-muted-foreground"
                        >
                            {{ formatHour(hour) }}
                        </div>
                        <div
                            v-for="day in DAYS"
                            :key="`${day.key}-${hour}`"
                            class="flex justify-center p-1"
                        >
                            <button
                                class="h-6 w-6 rounded border transition-colors"
                                :class="
                                    weeklySlots[day.key]?.[hour]
                                        ? 'border-green-500 bg-green-500'
                                        : 'border-border bg-background hover:bg-muted'
                                "
                                @click="toggleHour(day.key, hour)"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="h-4 w-4 rounded bg-green-500"></div>
                <span>Đã chọn</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="h-4 w-4 rounded border border-border bg-background"
                ></div>
                <span>Trống</span>
            </div>
        </div>
    </div>
</template>
