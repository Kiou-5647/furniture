export interface InvoiceableOrder {
    id: string;
    type: 'Order';
    number: string;
}

export interface InvoiceableBooking {
    id: string;
    type: 'Booking';
    number: string;
}

export type Invoiceable = InvoiceableOrder | InvoiceableBooking;

export interface InvoiceAllocationPayment {
    id: string;
    transaction_id: string | null;
    gateway: string | null;
    amount: string;
    created_at: string;
}

export interface InvoiceAllocation {
    id: string;
    amount_applied: string;
    applied_at: string;
    payment: InvoiceAllocationPayment | null;
}

export interface Invoice {
    id: string;
    invoice_number: string;
    invoiceable_type: string | null;
    invoiceable_id: string | null;
    invoiceable: Invoiceable | null;
    type: 'deposit' | 'final_balance' | 'full';
    type_label: string;
    status: 'draft' | 'open' | 'paid' | 'overpaid' | 'cancelled';
    status_label: string;
    status_color: string;
    amount_due: string;
    amount_paid: string;
    remaining_balance: string;
    validated_by?: string;
    allocations?: InvoiceAllocation[];
    created_at: string;
    updated_at: string;
}

export interface InvoiceFilterData {
    status?: string | null;
    type?: string | null;
    invoiceable_type?: string | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export interface InvoicePagination {
    data: Invoice[];
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
