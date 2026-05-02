import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { Updater } from '@tanstack/vue-table';
import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Ref } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function valueUpdater<T extends Updater<any>>(updaterOrValue: T, ref: Ref) {
    ref.value
        = typeof updaterOrValue === 'function'
            ? updaterOrValue(ref.value)
            : updaterOrValue
}

export function slugify(str: string): string {
    if (!str) return '';

    let slug = str.toLowerCase();

    slug = slug.replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a');
    slug = slug.replace(/[éèẻẽẹêếềểễệ]/g, 'e');
    slug = slug.replace(/[iíìỉĩị]/g, 'i');
    slug = slug.replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o');
    slug = slug.replace(/[úùủũụưứừửữự]/g, 'u');
    slug = slug.replace(/[ýỳỷỹỵ]/g, 'y');
    slug = slug.replace(/đ/g, 'd');
    slug = slug.replace(/&/g, 'va');

    return slug
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

export function getCookie(name: string): string | null {
    if (typeof document === 'undefined') return null;
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop()?.split(';').shift() ?? null;
    return null;
}

export function setCookie(name: string, value: string | number, days: number = 30) {
    if (typeof document === 'undefined') return;
    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value}; path=/; max-age=${maxAge}; SameSite=Lax`;
}

export function cleanQuery(query: Record<string, any>) {
    return Object.fromEntries(
        Object.entries(query).filter(([key, value]) => {
            if (value === undefined ||
                value === null ||
                value === '' ||
                (key === 'per_page' && value === getCookie('per_page'))
               ) {
                return false;
            }

            if (key === 'page' && Number(value) === 1) {
                return false;
            }

            if (key === 'limit' && Number(value) === 0) {
                return false;
            }

            return true;
        })
    );
}

export function formatPrice(value: string | number): string {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
}

export function formatNumber(value: number | string) {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(num)) return '';
    return new Intl.NumberFormat('vi-VN').format(Number(num));
}

export function stripNumber(value: string) {
    return value.replace(/\D/g, '');
}

export function handleNumericInput(event: Event, key: string, form: any) {
    const input = event.target as HTMLInputElement;

    // 1. Strip illegal characters using our helper
    const rawValue = stripNumber(input.value);
    const numValue = Number(rawValue);

    // 2. Update the form state (works with Inertia useForm)
    form[key] = numValue;

    // 3. Force the input field to display the formatted version
    // This removes the illegal characters from the UI immediately
    input.value = formatNumber(numValue);
}
