export interface Booking {
    id: string;
    customer: {
        id: string;
        name: string;
        email: string;
        phone?: string;
    };
    designer: {
        id: string;
        name: string;
        hourly_rate?: string;
        auto_confirm_bookings?: boolean;
    };
    service: {
        id: string;
        name: string;
        type: string;
        base_price: string;
        deposit_percentage: number;
        estimated_hours?: number;
        is_schedule_blocking: boolean;
    };
    sessions?: Array<{
        id: string;
        date: string;
        start_hour: number;
        end_hour: number;
    }>;
    start_at?: string;
    end_at?: string;
    deadline_at?: string;
    status: string;
    status_label: string;
    status_color: string;
    accepted_by?: string;
    deposit_invoice?: {
        id: string;
        invoice_number: string;
        amount_due: string;
        status: string;
    };
    final_invoice?: {
        id: string;
        invoice_number: string;
        amount_due: string;
        status: string;
    };
    can_confirm: boolean;
    can_cancel: boolean;
    can_pay_deposit: boolean;
    created_at: string;
    updated_at: string;
}

export interface BookingPagination {
    data: Booking[];
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

export interface BookingFilterData {
    designer_id?: string;
    service_id?: string;
    status?: string;
    customer_id?: string;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
