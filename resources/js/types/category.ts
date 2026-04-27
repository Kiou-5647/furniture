import type { Lookup } from './lookup';
import type { ProductType } from '.';

export interface Category {
    id: string;
    group_id: string;
    group?: Lookup;

    rooms: Lookup[];
    filterable_specs: {
        id: string;
        display_name: string;
    }[];

    product_type: ProductType;
    product_type_label: string;

    display_name: string;
    slug: string;
    description: string | null;
    image_url?: string;
    image_thumb_url?: string;

    is_active: boolean;

    created_at: string;
    updated_at: string;
}

export interface PublicCategory {
    category_group?: string;
    category_type: string;

    display_name: string;
    slug: string;
    description: string | null;
    image_path: string | null;

    meta: {
        title: string;
        description: string;
        canonical: string | null;
        robots: string;
    };
}

export interface CategoryPagination {
    data: Category[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
    links: {
        first: string;
        last: string;
        prev: string;
        next: string;
    };
}

export interface CategoryFilterData {
    group_id?: string | null;
    room_ids?: string[] | null;
    namespace_ids?: string[] | null;
    product_type?: ProductType | null;
    search?: string;
    is_active?: boolean | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
