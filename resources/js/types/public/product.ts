import type { OptionGroup } from "../product";

export interface ProductPage {
    id: string;
    name: string;
    slug: string;
    option_groups: OptionGroup[];
    navigation_map: Record<string, Record<string, string>>;
    category: {
        name: string;
        slug: string;
        product_type: {
            name: string;
            slug: string;
        };
    };
    collection: {
        name: string;
        slug: string;
    };
    featured_highlights: Feature[];
    plain_features: Feature[];
    specifications: ProductSpecification;
    care_information: string[];
    assembly_information: {
        required: boolean;
        estimated_minutes: number | null;
        difficulty_level: string | null;
        additional_info: string | null;
        manual_url: string | null;
    };
    active_variant: ProductVariantDetail;
    variants: ProductVariantSummary[];
}

export interface ProductVariantDetail {
    id: string;
    sku: string;
    slug: string;
    name: string;
    description: string | null;
    swatch_label: string;
    price: string | number;
    sale_price: string | number;
    in_stock: boolean;
    option_values: Record<string, string>;
    reviews_count: number;
    sales_count: number;
    average_rating: number;
    images: VariantGallery;
}

export interface ProductVariantSummary {
    id: string;
    sku: string;
    slug: string;
    name: string;
    swatch_label: string;
    option_values: Record<string, string>;
    images: {
        swatch: GalleryImage;
    };
}

export interface VariantGallery {
    primary: GalleryImage,
    hover?: GalleryImage,
    dimension?: GalleryImage,
    swatch?: GalleryImage,
    gallery?: GalleryImage[]

}

export interface GalleryImage {
    full: string
    thumb?: string;
    swatch?: string;
}

export interface Feature {
    image?: string;
    name: string;
    description?: string
}

export interface ProductSpecification {
    [categoryName: string]: {
        items: {
            display_name: string;
            description: string | null;
            lookup_slug: string | null;
        }[];
        is_filterable: boolean;
        lookup_namespace: string | null;
    };
}

export interface ProductCard {
    id: string;
    metrics: {
        views_count: number;
        sales_count: number;
        reviews_count: number;
        average_rating: number;
    };
    product: {
        id: string;
        name: string;
        is_new_arrival: boolean;
    };
    swatches: ProductCardVariant[];
    option_values: Record<string, string>;
}

export interface ProductCardVariant {
    id: string;
    sku: string;
    slug: string;
    name: string;
    label: string;
    price: string | number;
    sale_price: string | number;
    in_stock: boolean;
    primary_image_url: string | null;
    hover_image_url: string | null;
    swatch_image_url: string | null;
    option_values: Record<string, string>;
}

export interface ProductCardPagination {
    data: ProductCard[];
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
