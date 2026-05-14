import type { Invoice } from "./invoice";
import type { Refund } from "./refund";
import type { Shipment } from "./shipment";

export interface Order {
    id: string;
    can_accept: boolean;
    can_mark_paid: boolean;
    can_bank: boolean;
    can_complete: boolean;
    can_cancel: boolean;
    can_create_shipment: boolean;
    can_delete: boolean;
    is_fully_paid: boolean;
    is_cod: boolean;
    order_number: string;
    customer: {
        id: string;
        name: string;
        email: string;
        phone: string;
    } | null;
    shipping_province_code: string | null;
    shipping_ward_code: string | null;
    shipping_province_name: string | null;
    shipping_ward_name: string | null;
    shipping_street: Record<string, unknown> | null;
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

export interface OrderItem {
    id: string;
    purchasable_type: string;
    purchasable_id: string;
    purchasable_name: string;
    quantity: number;
    unit_price: string;
    subtotal: number;
    configuration: Record<string, unknown> | null;
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
