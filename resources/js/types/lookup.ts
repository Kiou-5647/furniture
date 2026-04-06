export interface Lookup {
    id: string;
    namespace_id: string | null;
    namespace: string | null;
    namespace_label: string | null;
    slug: string;
    display_name: string;
    description: string | null;
    image_url?: string;
    image_thumb_url?: string;
    is_active: boolean;
    metadata: Record<string, any> | null;
    created_at: string;
    updated_at: string;
}

export interface LookupNamespace {
    id: string | null;
    slug: string;
    label: string;
    for_variants: boolean;
    is_system: any;
    count: number;
}

export interface LookupNamespaceFull {
    id: string;
    slug: string;
    display_name: string;
    description: string | null;
    for_variants: boolean;
    is_active: boolean;
    is_system: boolean;
    created_at: string;
    updated_at: string;
}

export interface LookupNamespacePagination {
    data: LookupNamespaceFull[];
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

export interface LookupNamespaceFilterData {
    search?: string;
    is_active?: boolean;
    for_variants?: boolean;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export interface LookupPagination {
    data: Lookup[];
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

export interface LookupFilterData {
    namespace: string | null;
    search?: string;
    is_active?: boolean;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export type SpecNamespace = {
    id: string;
    namespace: string;
    label: string;
    for_variants: boolean;
};

export type SpecLookupOption = {
    id: string;
    slug: string;
    label: string;
    description: string | null;
    metadata: Record<string, any>;
    image_url: string | null;
    image_thumb_url: string | null;
};

export type SpecItem = {
    lookup_slug: string | null;
    display_name: string;
    description: string;
};

export type SpecGroup = {
    lookup_namespace: string | null;
    is_filterable: boolean;
    items: SpecItem[];
};

export type ProductSpecifications = Record<string, SpecGroup>;
