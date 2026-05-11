
import { availableSlots } from "@/routes/designers";
import { addDays, toISODate } from "./date-utils";


export async function loadWeeklyActualSlots(
    designerId: string,
    currentWeekStart: Date,
    DAYS: { key: number; label: string }[],
    weeklySlots: Record<string, Record<number, number>>
): Promise<Record<string, Record<number, number>>> {
    const updatedSlots = { ...weeklySlots };
    const promises = DAYS.map(async (day) => {
        const date = toISODate(addDays(currentWeekStart, day.key === 0 ? 6 : day.key - 1));
        try {
            const res = await fetch(`${availableSlots(designerId).url}?date=${date}`);
            const data = await res.json();
            updatedSlots[date] = data.slots;
        } catch (e) {
            console.error(`Error loading slots for ${date}`, e);
        }
    });
    await Promise.all(promises);
    return updatedSlots;
}

export function prevWeek(currentWeekStart: Date) {
    const newStart = addDays(currentWeekStart, -7);
    return {
        newStart,
        newDate: toISODate(newStart),
    };
}

export function nextWeek(currentWeekStart: Date) {
    const newStart = addDays(currentWeekStart, 7);
    return {
        newStart,
        newDate: toISODate(newStart),
    };
}

export function getSlotColor(
    day: number,
    hour: number,
    currentWeekStart: Date,
    designerAvailability: number[][],
    weeklySlots: Record<string, Record<number, number>>
): string {
    const date = addDays(currentWeekStart, day === 0 ? 6 : day - 1);
    const dateString = toISODate(date);

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diffDays = Math.ceil((date.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 3) return 'bg-gray-200';

    // 1. Kiểm tra lịch làm việc chung (General Availability)
    if (designerAvailability[day]?.[hour] != 1) {
        return 'bg-gray-200';
    }

    // 2. Kiểm tra slot thực tế từ weeklySlots
    if (weeklySlots[dateString] && weeklySlots[dateString][hour] == 0) {
        return 'bg-red-500';
    }

    // 3. Còn lại là trống
    return 'bg-green-600';
}

export function handleSlotClick(
    day: number,
    hour: number,
    currentWeekStart: Date,
    designerAvailability: number[][],
    weeklySlots: Record<string, Record<number, number>>
): { isValid: boolean; error?: string; date?: string; startTime?: string } {
    const selectedDate = addDays(currentWeekStart, day === 0 ? 6 : day - 1);
    const dateString = toISODate(selectedDate);

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diffDays = Math.ceil((selectedDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 3) {
        return { isValid: false, error: 'Khung giờ này không khả dụng.' };
    }

    // 1. Check lịch làm việc chung (General)
    if (designerAvailability[day]?.[hour] != 1) {
        return { isValid: false, error: 'Khung giờ này không khả dụng.' };
    }

    // 2. Check slot thực tế từ dữ liệu đã load cho cả tuần (weeklySlots)
    if (weeklySlots[dateString] && weeklySlots[dateString][hour] == 0) {
        return { isValid: false, error: 'Rất tiếc, khung giờ này vừa mới bị đặt.' };
    }

    return {
        isValid: true,
        date: dateString,
        startTime: `${String(hour).padStart(2, '0')}:00`
    };
}

