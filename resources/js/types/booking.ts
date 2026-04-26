export interface Booking {
    address_line: any;
    ward_name: any;
    province_name: any;
    service: any;
    deadline_at: any;
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
    start_at?: string;
    end_at?: string;
    total_price: number;
    status: string;
    status_label: string;
    status_color: string;
    accepted_by?: string;
    address: {
        province_code: string;
        province_name: string;
        ward_code: string;
        ward_name: string;
        street: string;
        full_address: string;
    };
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
    status?: string;
    customer_id?: string;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
