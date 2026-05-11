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
    can_delete: boolean;
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
    variant: {
        id: string;
        name: string;
        sku: string;
    }
    quantity_shipped: number;
    status: string;
    status_label: string;
    status_color: string;
    can_return: boolean;
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
