export interface BundleVariantImages {
    swatch: string;
    primary: {
        full: string;
        thumb: string;
    };
    hover: {
        full: string;
        thumb: string;
    };
    dimension: {
        full: string;
        thumb: string;
    };
    gallery: {
        full: string;
        thumb: string;
    };
}

export interface BundleVariant {
    id: string;
    sku: string;
    slug: string;
    name: string;
    swatch_label: string | null;
    price: number;
    sale_price: number | null;
    in_stock: boolean;
    images: BundleVariantImages;
}

export interface BundleItem {
    id: string;
    quantity: number;
    product_card_id: string;
    product_name: string;
    variants: BundleVariant[];
}

export interface Bundle {
    id: string;
    name: string;
    slug: string;
    description: string;
    discount_type: 'percentage' | 'fixed_amount' | 'fixed_price' | null;
    discount_value: number;
    starting_price: number;
    images: {
        primary: string;
        hover: string;
    };
    items: BundleItem[];
}
