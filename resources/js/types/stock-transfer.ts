export type StockTransferStatus =
    | 'draft'
    | 'in_transit'
    | 'completed'
    | 'cancelled';

export interface StockTransferItem {
    id: string;
    variant: {
        id: string;
        sku: string;
        name: string;
        product_name: string | null;
        image_url?: string | null;
        full_image_url?: string | null;
        option_values?: Record<string, string> | null;
        price?: string | number | null;
    };
    quantity_shipped: number;
    quantity_received: number;
}

export interface StockTransfer {
    id: string;
    transfer_number: string;
    status: StockTransferStatus;
    status_label: string;
    status_color: string;
    from_location: {
        id: string;
        code: string;
        name: string;
        type?: string;
    };
    to_location: {
        id: string;
        code: string;
        name: string;
        type?: string;
    };
    requested_by: {
        id: string;
        full_name: string;
    } | null;
    received_by: {
        id: string;
        full_name: string;
    } | null;
    items: StockTransferItem[];
    items_count: number;
    notes: string | null;
    received_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface StockTransferPagination {
    data: StockTransfer[];
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

export interface StockTransferFilterData {
    status?: string | null;
    from_location_id?: string | null;
    to_location_id?: string | null;
    search?: string;
    date_from?: string | null;
    date_to?: string | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export interface LocationOption {
    id: string;
    label: string;
    type: string;
}

export interface VariantOption {
    id: string;
    sku: string;
    name: string;
    product_name: string | null;
    available_quantity: number;
    option_values?: Record<string, string> | null;
    price?: string | number | null;
    image_url?: string | null;
    full_image_url?: string | null;
}
