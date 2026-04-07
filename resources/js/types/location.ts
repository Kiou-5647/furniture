export interface Location {
    id: string;
    code: string;
    name: string;
    type: 'warehouse' | 'retail' | 'vendor';
    type_label: string;
    type_color: string;
    building: string | null;
    address_number: string | null;
    province_code: string | null;
    province_name: string | null;
    ward_code: string | null;
    ward_name: string | null;
    full_address: string;
    phone: string | null;
    manager_id: string | null;
    manager: {
        id: string;
        full_name: string;
    } | null;
    is_active: boolean;
    inventories_count: number;
    created_at: string;
    updated_at: string;
}

export interface LocationPagination {
    data: Location[];
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
        prev: string | null;
        next: string | null;
    };
}

export interface LocationFilterData {
    type?: string | null;
    is_active?: boolean | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
