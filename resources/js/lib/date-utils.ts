/**
 * Vietnamese date formatting utilities.
 *
 * Day abbreviations: CN T2 T3 T4 T5 T6 T7
 */

export const DAYS_SHORT = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'] as const;

/**
 * Format a session date (YYYY-MM-DD + hour integers) into "T4 15/04 · 14:00–16:00".
 */
export function formatSessionDate(
    dateStr: string,
    startHour: number,
    endHour: number,
): string {
    const d = new Date(dateStr);
    const dayName = DAYS_SHORT[d.getDay()] ?? '';
    const dd = d.getDate().toString().padStart(2, '0');
    const mm = (d.getMonth() + 1).toString().padStart(2, '0');
    const start = `${startHour.toString().padStart(2, '0')}:00`;
    const end = `${endHour.toString().padStart(2, '0')}:00`;
    return `${dayName} ${dd}/${mm} · ${start}–${end}`;
}

/**
 * Format a datetime string from the API ("dd/mm/yyyy HH:MM" or "dd/mm/yyyy-HH:mm:ss")
 * into "T4 15/04/2026 · 14:00". Returns the original string if it can't be parsed.
 */
export function formatDateTime(datetimeStr: string): string {
    if (!datetimeStr) return '';
    const [datePart, timePart] = datetimeStr.split(/[\s-]/);
    if (!datePart || !timePart) return datetimeStr;
    const [dd, mm, yyyy] = datePart.split('/');
    if (!dd || !mm || !yyyy) return datetimeStr;
    const d = new Date(`${yyyy}-${mm}-${dd}T${timePart}`);
    const dayName = DAYS_SHORT[d.getDay()] ?? '';
    return `${dayName} ${datePart} · ${timePart}`;
}

/**
 * Format a date string ("dd/mm/yyyy" or "yyyy-mm-dd") into "T4 15/04/2026".
 */
export function formatDateOnly(dateStr: string): string {
    if (!dateStr) return '—';
    const parts = dateStr.split(/[\/\-]/);
    if (parts.length !== 3) return dateStr;

    // Determine order: if first part is 4 digits it's yyyy-mm-dd
    if (parts[0]!.length === 4) {
        const [yyyy, mm, dd] = parts;
        const d = new Date(`${yyyy}-${mm}-${dd}`);
        const dayName = DAYS_SHORT[d.getDay()] ?? '';
        return `${dayName} ${dd}/${mm}/${yyyy}`;
    }

    // Otherwise assume dd/mm/yyyy
    const [dd, mm, yyyy] = parts;
    const d = new Date(`${yyyy}-${mm}-${dd}`);
    const dayName = DAYS_SHORT[d.getDay()] ?? '';
    return `${dayName} ${dd}/${mm}/${yyyy}`;
}
