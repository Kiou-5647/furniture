export interface Lookup {
    id: number;
    namespace: string;
    namespace_label: string;
    slug: string;
    display_name: string;
    description: string | null;
    image_path: string | null;
    is_active: boolean;
    metadata: Record<string, any> | null;
    created_at: string;
    updated_at: string;
}

export interface LookupNamespace {
    namespace: string;
    label: string;
    count: number;
}

export interface LookupPagination {
    data: Lookup[];
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

export interface LookupFilterData {
    namespace: string;
    search?: string;
    is_active?: boolean;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
