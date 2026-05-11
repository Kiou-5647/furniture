import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { Updater } from '@tanstack/vue-table';
import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Ref } from 'vue';
import { getCookie } from './cookie-utils';

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
