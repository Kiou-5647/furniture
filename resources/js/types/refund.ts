export interface Refund {
    id: string;
    order_number: string;
    order: {
        id: string;
        order_number: string;
        total_amount: string;
    } | null;
    payment: {
        id: string;
        gateway: string;
        amount: string;
        status: string;
    } | null;
    amount: string;
    reason: string | null;
    status: string;
    status_label: string;
    status_color: string;
    requested_by: {
        full_name: string;
        phone: string | null;
    } | null;
    processed_by: {
        full_name: string;
    } | null;
    notes: string | null;
    processed_at: string | null;
    created_at: string;
}

export interface RefundFilterData {
    status?: string;
    order_id?: string;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc';
    per_page?: number;
}

export interface RefundPagination {
    data: Refund[];
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
