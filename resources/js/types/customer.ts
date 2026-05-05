export interface Customer {
    id: string;
    user: {
        id: string;
        name: string;
        email: string;
        is_active: boolean;
    };
    full_name: string;
    phone: string;
    street: string | null;
    province_name: string;
    ward_name: string;
    address: string;
    total_spent: string;
    avatar_url: string | null;
    created_at: string;
    updated_at: string;
}

export interface CustomerFilterData {
    is_active?: boolean;
    search?: string;
    order_by: string;
    order_direction: string;
    per_page: number;
}

export interface CustomerPagination {
    data: Customer[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        total: number;
    };
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}
