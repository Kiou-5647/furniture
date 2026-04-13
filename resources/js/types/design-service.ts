export interface DesignService {
    id: string;
    name: string;
    type: string;
    type_label: string;
    is_schedule_blocking: boolean;
    base_price: string;
    deposit_percentage: number;
    estimated_minutes: number | null;
    created_at: string;
    updated_at: string;
}

export interface DesignServiceFilterData {
    search?: string;
    type?: string;
    is_schedule_blocking?: boolean | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc';
    per_page?: number;
}

export interface DesignServicePagination {
    data: DesignService[];
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
