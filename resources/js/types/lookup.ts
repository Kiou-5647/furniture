export type AttributeType = 'text' | 'number' | 'boolean' | 'color' | 'dimensions' | 'weight';
export type DimensionUnit = 'mm' | 'cm' | 'm' | 'inch' | 'ft';
export type WeightUnit = 'kg' | 'lb';

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
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

export interface LookupFilterData {
    namespace: string;
    search?: string;
    is_active?: boolean;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
