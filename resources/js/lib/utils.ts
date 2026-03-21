import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { Updater } from '@tanstack/vue-table';
import type { ClassValue } from 'clsx';
import type { Ref } from 'vue';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

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

    return slug
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

export function cleanQuery(query: Record<string, any>) {
    return Object.fromEntries(
        Object.entries(query).filter(([_, value]) =>
            value !== undefined &&
            value !== null &&
            value !== ''
        )
    );
}
