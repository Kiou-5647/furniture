export interface Discount {
    id: string;
    name: string;
    type: 'percentage' | 'fixed_amount';
    value: number;
    start_at: string | null;
    end_at: string | null;
    is_active: boolean;
    discountable_type: string;
    discountable_id: string | null;
    discountable_type_label: string;
    discountable_name: string;
    created_at: string;
    updated_at: string;
}

export interface DiscountFilterData {
    search?: string;
    type?: string;
    discountable_type?: string;
    discountable_id?: string;
    is_active?: boolean | null;
    start_after?: string;
    end_before?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc';
    per_page?: number;
}

export interface DiscountPagination {
    data: Discount[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    links: any[];
}
