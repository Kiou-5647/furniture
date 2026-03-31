export interface Collection {
    id: string;
    display_name: string;
    slug: string;
    description: string | null;
    image_url?: string;
    image_thumb_url?: string;
    banner_url?: string;
    banner_thumb_url?: string;
    is_active: boolean;
    is_featured: boolean;
    metadata: Record<string, any> | null;
    created_at: string;
    updated_at: string;
}

export interface CollectionPagination {
    data: Collection[];
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

export interface CollectionFilterData {
    search?: string;
    is_active?: boolean | null;
    is_featured?: boolean | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
