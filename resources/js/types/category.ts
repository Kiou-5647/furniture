import type { Lookup } from "./lookup";
import type { ProductType } from ".";


export interface Category {
    id: number;
    group_id: number;
    group?: Lookup;

    room_ids: number[];
    rooms?: Lookup[];

    product_type: ProductType;
    product_type_label: string;

    display_name: string;
    slug: string;
    description: string | null;
    image_path: string | null;

    is_active: boolean;
    metadata: Record<string, any> | null;

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
        current_page: number,
        from: number,
        last_page: number,
        path: string,
        per_page: number,
        to: number,
        total: number
    };
    links: {
        first: string,
        last: string,
        prev: string,
        next: string
    };
}

export interface CategoryFilterData {
    group_id?: number | null;
    product_type?: ProductType | null;
    search?: string;
    is_active?: boolean | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
