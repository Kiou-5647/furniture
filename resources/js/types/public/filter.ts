export interface FilterOption {
    slug: string;
    label: string;
    count: number;
}

export interface FilterNamespace {
    namespace: string;
    label: string;
    options: FilterOption[];
}

export interface ProductFilters {
    type: string | null;
    limit: number;
    min_price: number;
    max_price: number;
    filters: Record<string, string | string[]>;
}
