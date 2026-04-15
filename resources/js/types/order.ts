export interface Invoice {
    id: string;
    invoice_number: string;
    type: string;
    type_label: string;
    status: 'draft' | 'open' | 'paid' | 'overpaid' | 'cancelled';
    status_label: string;
    status_color: string;
    amount_due: string;
    amount_paid: string;
    remaining_balance: string;
    validated_by?: string;
    created_at: string;
    updated_at: string;
}

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
    status: 'pending' | 'processing' | 'completed' | 'rejected';
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

export interface Order {
    id: string;
    order_number: string;
    customer: {
        id: string;
        name: string;
        email: string;
    } | null;
    shipping_province_code: string | null;
    shipping_ward_code: string | null;
    shipping_province_name: string | null;
    shipping_ward_name: string | null;
    shipping_address_data: Record<string, unknown> | null;
    shipping_address_text: string;
    total_amount: string;
    status: string;
    status_label: string;
    status_color: string;
    items?: OrderItem[];
    accepted_by: string | null;
    guest_name: string | null;
    guest_phone: string | null;
    guest_email: string | null;
    notes: string | null;
    total_items: number;
    source: 'in_store' | 'online';
    payment_method: 'cash' | 'bank_transfer' | 'cod';
    payment_method_label: string;
    store_location: {
        id: string;
        name: string;
        code: string;
    } | null;
    shipping_method_id: string | null;
    paid_at: string | null;
    shipping_cost: string;
    shipping_method: {
        id: string;
        name: string;
        estimated_delivery_days: number | null;
    } | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    invoices?: Invoice[];
    shipments?: Shipment[];
    refunds?: Refund[];
}

export interface Shipment {
    id: string;
    shipment_number: string;
    order: {
        id: string;
        order_number: string;
        status: string;
        status_label: string;
        guest_name: string | null;
        guest_phone: string | null;
        guest_email: string | null;
        customer: {
            name: string;
            email: string;
        } | null;
        shipping_address_text: string;
        total_amount: string;
        shipping_cost: string;
        notes: string | null;
    } | null;
    origin_location: {
        id: string;
        name: string;
    } | null;
    status: string;
    status_label: string;
    status_color: string;
    can_ship: boolean;
    can_deliver: boolean;
    can_cancel: boolean;
    can_resend: boolean;
    handled_by: {
        full_name: string;
        phone: string | null;
    } | null;
    items: ShipmentItem[];
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ShipmentItem {
    id: string;
    order_item: {
        id: string;
        purchasable_name: string;
        purchasable_id: string | null;
    } | null;
    source_location: {
        id: string;
        name: string;
        code: string;
    } | null;
    quantity_shipped: number;
    status: string;
    status_label: string;
    status_color: string;
}

export interface OrderItem {
    id: string;
    purchasable_type: string;
    purchasable_id: string;
    purchasable_name: string;
    quantity: number;
    unit_price: string;
    subtotal: number;
    configuration: Record<string, unknown> | null;
    source_location: {
        id: string;
        name: string;
        code: string;
    } | null;
}

export interface OrderPagination {
    data: Order[];
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

export interface OrderFilterData {
    customer_id?: string | null;
    status?: string | null;
    source?: string | null;
    store_location_id?: string | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export interface ShipmentFilterData {
    status?: string | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export interface ShipmentPagination {
    data: Shipment[];
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
