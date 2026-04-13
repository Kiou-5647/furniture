<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { availabilities, index } from '@/routes/employee/hr/designers';
import type { Designer, WeeklySlots, DateAvailability } from '@/types/designer';

const props = defineProps<{
    designers: Designer[];
}>();

const breadcrumbs = [
    { title: 'Quản lý nhân sự', href: index().url },
    { title: 'Nhà thiết kế', href: index().url },
    { title: 'Cài đặt lịch làm việc', href: '#' },
];

const selectedDesignerId = ref<string | null>(null);
const weeklySlots = ref<WeeklySlots>({});
const dateAvailabilities = ref<DateAvailability[]>([]);
const isLoading = ref(false);

const selectedDesignerName = computed(() => {
    const designer = props.designers.find(
        (d) => d.id === selectedDesignerId.value,
    );
    return designer?.display_name ?? '';
});

watch(selectedDesignerId, async (newId) => {
    if (!newId) {
        weeklySlots.value = {};
        dateAvailabilities.value = [];
        return;
    }
    isLoading.value = true;
    try {
        const response = await fetch(availabilities({ designer: newId }).url);
        const data = await response.json();
        weeklySlots.value = data.weekly;
        dateAvailabilities.value = data.date_availabilities;
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <Head title="Cài đặt lịch làm việc" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Cài đặt lịch làm việc</h1>
                <p class="text-muted-foreground">
                    Quản lý lịch làm việc của nhà thiết kế
                </p>
            </div>

            <div class="flex items-center gap-4">
                <label class="text-sm font-medium">Chọn nhà thiết kế:</label>
                <select
                    v-model="selectedDesignerId"
                    class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                >
                    <option :value="null">-- Chọn nhà thiết kế --</option>
                    <option
                        v-for="designer in designers"
                        :key="designer.id"
                        :value="designer.id"
                    >
                        {{ designer.display_name }}
                    </option>
                </select>
            </div>

            <div
                v-if="!selectedDesignerId"
                class="py-12 text-center text-muted-foreground"
            >
                Vui lòng chọn nhà thiết kế để quản lý lịch làm việc
            </div>

            <DesignerAvailabilityGrid
                v-else-if="selectedDesignerId && !isLoading"
                :designer-id="selectedDesignerId"
                :designer-name="selectedDesignerName"
                :initial-weekly="weeklySlots"
            />

            <div
                v-if="isLoading"
                class="py-12 text-center text-muted-foreground"
            >
                Đang tải...
            </div>
        </div>
    </AppLayout>
</template>
