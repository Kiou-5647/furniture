export type StockMovementType =
    | 'receive'
    | 'sell'
    | 'return'
    | 'adjust'
    | 'transfer_in'
    | 'transfer_out'
    | 'damage'
    | 'restock';

export interface StockMovement {
    id: string;
    type: StockMovementType;
    type_label: string;
    type_color: string;
    variant: {
        id: string;
        sku: string;
        name: string;
        product_name: string | null;
    };
    location: {
        id: string;
        code: string;
        name: string;
    };
    quantity: number;
    quantity_before: number;
    quantity_after: number;
    cost_per_unit: string | null;
    cost_per_unit_before: string | null;
    performed_by: {
        id: string;
        full_name: string;
    } | null;
    reference_type: string | null;
    reference_id: string | null;
    notes: string | null;
    created_at: string;
}

export interface StockMovementPagination {
    data: StockMovement[];
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

export interface StockMovementFilterData {
    type?: string | null;
    location_id?: string | null;
    variant_id?: string | null;
    search?: string;
    date_from?: string | null;
    date_to?: string | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
