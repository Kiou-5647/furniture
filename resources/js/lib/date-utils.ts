/**
 * Vietnamese date formatting utilities.
 *
 * Day abbreviations: CN T2 T3 T4 T5 T6 T7
 */

export const DAYS_SHORT = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'] as const;

export function parseDate(dateStr: string): Date | null {
    if (!dateStr) return null;

    // Handle dd/mm/yyyy or dd/mm/yyyy HH:mm
    if (dateStr.includes('/')) {
        const [datePart, timePart] = dateStr.split(/[\s-]/);
        const [dd, mm, yyyy] = datePart.split('/');
        if (dd && mm && yyyy) {
            return new Date(`${yyyy}-${mm}-${dd}${timePart ? 'T' + timePart : ''}`);
        }
    }

    // Handle ISO or yyyy-mm-dd
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? null : d;
}

/**
 * Normalize any date string to YYYY-MM-DD.
 */
export function toISODate(dateStr: string): string {
    const d = parseDate(dateStr);
    if (!d) return '';
    return d.toISOString().split('T')[0];
}

export function formatSessionDate(
    dateStr: string,
    startHour: number,
    endHour: number,
): string {
    const d = parseDate(dateStr);
    if (!d) return '—';

    const dayName = DAYS_SHORT[d.getDay()] ?? '';
    const dd = d.getDate().toString().padStart(2, '0');
    const mm = (d.getMonth() + 1).toString().padStart(2, '0');
    const start = `${startHour.toString().padStart(2, '0')}:00`;
    const end = `${endHour.toString().padStart(2, '0')}:00`;
    return `${dayName} · ${dd}/${mm} · ${start}–${end}`;
}

/**
 * Format a datetime string into "T4 15/04/2026 · 14:00".
 */
export function formatDateTime(datetimeStr: string): string {
    const d = parseDate(datetimeStr);
    if (!d) return datetimeStr || '—';

    const dayName = DAYS_SHORT[d.getDay()] ?? '';
    const dd = d.getDate().toString().padStart(2, '0');
    const mm = (d.getMonth() + 1).toString().padStart(2, '0');
    const yyyy = d.getFullYear();
    const hh = d.getHours().toString().padStart(2, '0');
    const min = d.getMinutes().toString().padStart(2, '0');

    return `${dayName} · ${dd}/${mm}/${yyyy} · ${hh}:${min}`;
}

/**
 * Format a date string into "T4 15/04/2026".
 */
export function formatDateOnly(dateStr: string): string {
    const d = parseDate(dateStr);
    if (!d) return '—';

    const dayName = DAYS_SHORT[d.getDay()] ?? '';
    const dd = d.getDate().toString().padStart(2, '0');
    const mm = (d.getMonth() + 1).toString().padStart(2, '0');
    const yyyy = d.getFullYear();

    return `${dayName} · ${dd}/${mm}/${yyyy}`;
}
