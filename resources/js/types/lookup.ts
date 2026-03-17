export interface Lookup {
    id: number;
    namespace: string;
    key: string;
    display_name: string;
    metadata: Record<string, any> | null;
}

export interface LookupNamespace {
    namespace: string;
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
