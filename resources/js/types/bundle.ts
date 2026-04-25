export interface Bundle {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    discount_type: 'percentage' | 'fixed_amount' | 'fixed_price';
    discount_value: number;
    is_available: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    images: {
        primary: string | null;
        hover: string | null;
        gallery: string[];
    };
    contents: BundleContent[];
}

export interface BundleContent {
    id: string;
    quantity: number;
    product_card: {
        id: string;
        name: string;
        product: {
            id: string;
            name: string;
        };
        variants: ProductCardVariant[];
    };
}

export interface ProductCardVariant {
    id: string;
    sku: string;
    name: string;
    price: number;
    sale_price: number | null;
    primary_image: string | null;
    hover_image: string | null;
    swatch_image: string | null;
}

export interface BundleFilterData {
    search?: string;
    is_active?: boolean | null;
    order_by?: string;
    order_direction?: 'asc' | 'desc';
    created_from?: string;
    created_to?: string;
    per_page?: number;
}

export interface BundlePagination {
    data: Bundle[];
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
        prev: string;
        next: string;
    };
}
