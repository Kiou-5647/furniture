export interface ShippingMethod {
    id: string;
    code: string;
    name: string;
    price: string;
    estimated_delivery_days: number | null;
    is_active: boolean;
    shipments_count?: number;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
}

export interface ShippingMethodFilterData {
    is_active?: boolean | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc';
    per_page?: number;
}

export interface ShippingMethodPagination {
    data: ShippingMethod[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
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
