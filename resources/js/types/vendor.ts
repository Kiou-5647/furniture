export interface Vendor {
    id: string;
    name: string;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    province_code: string | null;
    ward_code: string | null;
    street: string | null;
    bank_name: string | null;
    bank_account_number: string | null;
    bank_account_holder: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface VendorFilterData {
    search?: string;
    is_active?: boolean | null;
    order_by: string;
    order_direction: string;
    per_page: number;
}

export interface VendorPagination {
    data: Vendor[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    links: {
        prev: string | null;
        next: string | null;
    };
}
