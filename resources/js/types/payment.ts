export interface PaymentCustomer {
    id: string;
    name: string;
    email: string;
}

export interface Payment {
    id: string;
    customer: PaymentCustomer | null;
    gateway: string | null;
    transaction_id: string | null;
    amount: string;
    total_allocated: number;
    created_at: string;
    updated_at: string;
}

export interface PaymentFilterData {
    customer_id?: string | null;
    gateway?: string | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc';
    per_page?: number;
}

export interface PaymentPagination {
    data: Payment[];
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
