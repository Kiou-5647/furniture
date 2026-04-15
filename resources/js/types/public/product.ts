import type { GroupedVariantCard } from "../product";

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
    variants: ProductVariant[];
    grouped_variants: GroupedVariantCard[];
}

export interface ProductVariant {
    id: string;
    sku: string;
    slug: string;
    price: string | number;
    name: string;
    in_stock: boolean;
    images: VariantGallery
}

export interface VariantGallery {
    primary: GalleryImage,
    hover: GalleryImage,
    dimension: GalleryImage,
    swatch: GalleryImage,
    gallery: GalleryImage[]

}

export interface GalleryImage {
    full: string
    thumb?: string;
    swatch?: string;
}
