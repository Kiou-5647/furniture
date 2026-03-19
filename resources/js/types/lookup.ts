export type LookupType = 'mau-sac' | 'phong' | 'phong-cach' | 'tinh-nang';
export type AttributeType = 'text' | 'number' | 'boolean' | 'color' | 'dimensions' | 'weight';
export type DimensionUnit = 'mm' | 'cm' | 'm' | 'inch' | 'ft';
export type WeightUnit = 'kg' | 'lb';

export interface Lookup {
    id: number;
    namespace: LookupType;
    key: string;
    display_name: string;
    metadata: Record<string, any> | null;
}

export interface LookupNamespace {
    namespace: LookupType;
    count: number;
    current_namespace?: string;
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
    namespace: LookupType;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
