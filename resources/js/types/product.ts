import type { ProductSpecifications, SpecItem } from './lookup';
import type { ProductCard } from './public/product';
import type { AssemblyDifficulty, ProductStatus, Status } from '.';

export interface Product {
    id: string;
    vendor_id: string | null;
    vendor?: {
        id: string;
        name: string;
    };
    category_id: string | null;
    category?: {
        id: string;
        display_name: string;
    };
    collection_id: string | null;
    collection?: {
        id: string;
        display_name: string;
    };
    status: ProductStatus;
    status_label: string;
    status_color: string;
    name: string;
    features: SpecItem[];
    care_instructions: string[];
    specifications: ProductSpecifications;
    option_groups: OptionGroup[];
    filterable_options: Record<string, string[]>;
    assembly_info: {
        required?: boolean;
        estimated_minutes?: number;
        difficulty_level?: AssemblyDifficulty;
        additional_information?: string;
        manual_url: string;
        manual_file: File | null;
    };
    warranty_months: number | null;
    views_count: number;
    reviews_count: number;
    average_rating: string | null;
    sales_count: number;
    min_price: string;
    max_price: string;
    is_new_arrival: boolean;
    published_date: string | null;
    new_arrival_until: string | null;
    variants_count?: number;
    variants?: ProductVariant[];
    product_cards?: ProductCard[];
    created_at: string;
    updated_at: string;
}

export interface ProductPagination {
    data: Product[];
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

export interface ProductFilterData {
    vendor_id?: string | null;
    category_id?: string | null;
    collection_id?: string | null;
    status?: ProductStatus | null;
    is_new_arrival?: boolean | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export interface OptionGroup {
    name: string;
    namespace: string;
    is_swatches: boolean;
    sort_order?: number;
    options: OptionItem[];
}

export interface OptionItem {
    value: string;
    label: string;
    image_url?: string | null;
    image_thumb_url?: string | null;
    metadata?: Record<string, any>;
}

export interface SwatchOption {
    value: string;
    label: string;
    variant_id: string;
    sku: string;
    name: string;
    slug: string;
    price: string | number;
    sale_price: string | number;
    in_stock: boolean;
    primary_image_url: string | null;
    swatch_image_url: string | null;
}

export interface MediaItem {
    id: number;
    url: string;
    thumb_url: string;
}

export interface ProductVariant {
    id: string;
    product_id: string;
    sku: string;
    name: string | null;
    swatch_label: string | null;
    slug: string | null;
    description: string | null;
    price: string | number;
    sale_price: string | number;
    profit_margin_value: string | number | null;
    profit_margin_unit: 'fixed' | 'percentage';
    option_values: Record<string, any>;
    features: SpecItem[];
    specifications: ProductSpecifications;
    care_instructions: string[];
    status: Status;
    primary_image_url?: string | null;
    primary_image_file?: File | null;
    hover_image_url?: string | null;
    hover_image_file?: File | null;
    gallery_urls?: MediaItem[];
    gallery_files?: File[];
    dimension_image_url?: string | null;
    dimension_image_file?: File | null;
    swatch_image_url?: string | null;
    swatch_image_thumb_url?: string | null;
    swatch_image_file?: File | null;
    removed_gallery_ids?: number[];
    sales_count: number;
    in_stock: boolean;
    stock?: VariantStock[];
    created_at: string;
    updated_at: string;
}

export interface VariantStock {
    location_id: string;
    quantity: number;
    cost_per_unit: number | null;
    movement_type?: string;
    movement_notes?: string;
}


