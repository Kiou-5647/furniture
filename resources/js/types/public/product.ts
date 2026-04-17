import type { GroupedVariantCard, OptionGroup } from "../product";

export interface Product {
    id: string;
    name: string;
    slug: string;
    category?: {
        id: string;
        name: string;
        slug: string;
        product_type: {
            name: string,
            slug: string
        };
        room: {
            name: string,
            slug: string
        };
        group: {
            name: string,
            slug: string
        } | null
    };
    collection?: {
        id: string;
        name: string;
        slug: string;
    };
    option_groups: OptionGroup[];
    views_count: number;
    reviews_count: number;
    average_rating: number;
    variants: ProductVariant[];
    grouped_variants: GroupedVariantCard[];
}

export interface ProductVariant {
    id: string;
    sku: string;
    name: string;
    slug: string;
    swatch_label?: string;
    price: string | number;
    sale_price: string | number;
    in_stock: boolean;
    images: VariantGallery
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
