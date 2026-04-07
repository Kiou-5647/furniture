import { useForm } from '@inertiajs/vue3';
import { Package, Settings, Layers, Warehouse } from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';
import type { Ref } from 'vue';
import { store, update } from '@/routes/employee/products/items';
import type { ProductStatus, SpecLookupOption, SpecItem } from '@/types';
import type { ProductSpecifications } from '@/types/lookup';
import type { Product, ProductVariant, OptionGroup } from '@/types/product';

export interface VariantStock {
    location_id: string;
    quantity: number;
    cost_per_unit: number | null;
}

export type LookupOptionItem = {
    id: string;
    slug: string;
    label: string;
    description?: string;
    image_url?: string | null;
    image_thumb_url?: string | null;
    metadata: Record<string, any>;
};

export type LookupOptionGroup = {
    label: string;
    namespace: string;
    options: LookupOptionItem[];
};

export type StepState = 'complete' | 'incomplete' | 'untouched';

const steps = [
    { title: 'Thông tin', description: 'Cơ bản & Nội dung', icon: Package },
    { title: 'Tùy chọn', description: 'Nhóm thuộc tính', icon: Settings },
    { title: 'Biến thể', description: 'SKU & Giá', icon: Layers },
    { title: 'Tồn kho', description: 'Số lượng & Vị trí', icon: Warehouse },
];

const statusOptions: { label: string; value: ProductStatus }[] = [
    { label: 'Bản nháp', value: 'draft' },
    { label: 'Chờ duyệt', value: 'pending_review' },
    { label: 'Đã xuất bản', value: 'published' },
    { label: 'Đang ẩn', value: 'hidden' },
    { label: 'Lưu trữ', value: 'archived' },
];

const difficultyOptions: { label: string; value: string }[] = [
    { label: 'Dễ', value: 'easy' },
    { label: 'Trung bình', value: 'medium' },
    { label: 'Khó', value: 'hard' },
];

export interface ProductFormContext {
    form: ReturnType<typeof useForm>;
    currentStep: number;
    stepStates: StepState[];
    steps: { title: string; description: string; icon: any }[];
    expandedVariants: Set<number>;
    newVariantCombo: Record<string, string>;
    selectedOptionNamespace: string;
    selectedOptionValues: string[];
    customOptionGroupName: string;
    newArrivalMonths: number | null;
    newFeature: SpecItem;
    newCareInstruction: string;
    specGroupName: string;
    specGroupNamespace: string;
    specGroupIsFilterable: boolean;
    specGroupItems: SpecItem[];
    specSelectedValues: string[];
    specValueDescriptions: Record<string, string>;
    specLookupOptions: SpecLookupOption[];
    specLookupOptionsMap: Record<string, SpecLookupOption[]>;
    statusOptions: { label: string; value: ProductStatus }[];
    difficultyOptions: { label: string; value: string }[];
    selectedVariantOptions: LookupOptionItem[];
    featureOptions: LookupOptionItem[];
    availableCombinations: any[][];
    validationErrors: string[];
    isValid: boolean;
    submit: () => void;
    handleFinalSubmit: () => void;
    closeModal: () => void;
    nextStep: () => void;
    prevStep: () => void;
    toggleVariantExpand: (index: number) => void;
    getVariantOptionLabels: (variant: Partial<ProductVariant>) => string[];
    generateAllVariants: () => void;
    addManualVariant: () => void;
    addOptionGroup: (variantOptions: LookupOptionGroup[]) => void;
    removeOptionGroup: (index: number) => void;
    removeVariant: (index: number) => void;
    bulkCopyPrice: () => void;
    bulkCopySkuPattern: () => void;
    addFeature: () => void;
    removeFeature: (index: number) => void;
    addCareInstruction: () => void;
    removeCareInstruction: (index: number) => void;
    addSpecGroup: () => void;
    removeSpecGroup: (groupName: string) => void;
    addSpecItemFromLookup: () => void;
    addSpecFreeItem: () => void;
    removeSpecItem: (groupName: string, itemIndex: number) => void;
    setSpecGroupNamespace: (ns: string, label: string) => void;
    updateVariantOptions: (options: LookupOptionGroup[]) => void;
    updateFeatureOptions: (options: LookupOptionItem[]) => void;
    updateSpecLookupOptionsMap: (
        map: Record<string, SpecLookupOption[]>,
    ) => void;
    addVariantPrimaryImage: (variantIndex: number, file: File) => void;
    removeVariantPrimaryImage: (variantIndex: number) => void;
    addVariantHoverImage: (variantIndex: number, file: File) => void;
    removeVariantHoverImage: (variantIndex: number) => void;
    addVariantGalleryImage: (variantIndex: number, files: File[]) => void;
    removeVariantGalleryImage: (
        variantIndex: number,
        index: number,
        isExisting: boolean,
    ) => void;
    setVariantDimensionImage: (variantIndex: number, file: File | null) => void;
    removeVariantDimensionImage: (variantIndex: number) => void;
    setVariantSwatchImage: (variantIndex: number, file: File | null) => void;
    removeVariantSwatchImage: (variantIndex: number) => void;
    addStockEntry: (variantIndex: number) => void;
    removeStockEntry: (variantIndex: number, locationId: string) => void;
}

export function useProductForm(
    product: Product | null,
    open: boolean,
    variantOptions: LookupOptionGroup[],
    featureOptions: LookupOptionItem[],
    allSpecLookupOptions: Record<string, SpecLookupOption[]>,
    emit: (event: 'close') => void,
): ProductFormContext {
    const variantOptionsRef = ref(variantOptions) as Ref<typeof variantOptions>;
    const featureOptionsRef = ref(featureOptions) as Ref<typeof featureOptions>;
    const specLookupOptionsMap =
        ref<Record<string, SpecLookupOption[]>>(allSpecLookupOptions);
    const form = useForm({
        vendor_id: null as string | null,
        category_id: null as string | null,
        collection_id: null as string | null,
        status: 'draft' as ProductStatus,
        name: '',
        features: [] as SpecItem[],
        care_instructions: [] as string[],
        specifications: {} as ProductSpecifications,
        option_groups: [] as OptionGroup[],
        assembly_info: {
            required: false,
            estimated_minutes: null as number | null,
            price: null as number | null,
            instructions_url: '',
            difficulty_level: 'easy',
            additional_information: '',
        },
        warranty_months: 12 as number | null,
        is_featured: false,
        is_dropship: false,
        is_new_arrival: true,
        is_custom_made: false,
        published_date: '',
        new_arrival_until: '',
        variants: [] as Partial<ProductVariant>[],
        _force_update_price: false,
    });

    const currentStep = ref(1);
    const selectedOptionNamespace = ref('');
    const selectedOptionValues = ref<string[]>([]);
    const customOptionGroupName = ref('');
    const newArrivalMonths = ref<number | null>(null);
    const newFeature = ref<SpecItem>({
        lookup_slug: null,
        display_name: '',
        description: '',
    });
    const newCareInstruction = ref('');
    const specGroupName = ref('');
    const specGroupNamespace = ref('');
    const specGroupIsFilterable = ref(true);
    const specGroupItems = ref<SpecItem[]>([]);
    const specSelectedValues = ref<string[]>([]);
    const specValueDescriptions = ref<Record<string, string>>({});
    const specLookupOptions = ref<SpecLookupOption[]>([]);

    watch(
        () => product,
        (newProduct) => {
            if (newProduct && open) {
                form.vendor_id = newProduct.vendor_id;
                form.category_id = newProduct.category_id;
                form.collection_id = newProduct.collection_id;
                form.status = newProduct.status;
                form.name = newProduct.name;
                form.features = newProduct.features ?? [];
                form.care_instructions = newProduct.care_instructions ?? [];
                form.specifications = Object.fromEntries(
                    Object.entries(newProduct.specifications ?? {}).map(
                        ([key, group]: [string, any]) => [
                            key,
                            {
                                lookup_namespace:
                                    group.lookup_namespace === '_null'
                                        ? null
                                        : group.lookup_namespace,
                                is_filterable:
                                    group.is_filterable === '0'
                                        ? false
                                        : group.is_filterable === '1'
                                          ? true
                                          : (group.is_filterable ?? false),
                                items: group.items ?? [],
                            },
                        ],
                    ),
                ) as ProductSpecifications;
                form.option_groups = newProduct.option_groups ?? [];
                form.assembly_info = {
                    required: newProduct.assembly_info?.required ?? false,
                    estimated_minutes:
                        newProduct.assembly_info?.estimated_minutes ?? null,
                    price: newProduct.assembly_info?.price ?? null,
                    instructions_url:
                        newProduct.assembly_info?.instructions_url ?? '',
                    difficulty_level:
                        newProduct.assembly_info?.difficulty_level ?? 'easy',
                    additional_information:
                        newProduct.assembly_info?.additional_information ?? '',
                };
                form.warranty_months = newProduct.warranty_months;
                form.is_featured = newProduct.is_featured;
                form.is_dropship = newProduct.is_dropship;
                form.is_new_arrival = newProduct.is_new_arrival;
                form.is_custom_made = newProduct.is_custom_made;
                form.published_date = newProduct.published_date ?? '';
                form.new_arrival_until = newProduct.new_arrival_until ?? '';
                const optionGroups = newProduct.option_groups ?? [];
                form.variants = (newProduct.variants ?? []).map((v: any) => {
                    const normalizedOptionValues: Record<string, string> = {};
                    for (const group of optionGroups) {
                        const ns = group.namespace;
                        const fallbackKey = group.name.toLowerCase();
                        const val =
                            v.option_values?.[ns] ??
                            v.option_values?.[fallbackKey];
                        if (val && ns) {
                            normalizedOptionValues[ns] = val;
                        }
                    }
                    return {
                        id: v.id,
                        product_id: v.product_id,
                        sku: v.sku,
                        name: v.name,
                        slug: v.slug,
                        description: v.description,
                        price: v.price,
                        profit_margin_value: v.profit_margin_value ?? null,
                        profit_margin_unit: v.profit_margin_unit ?? 'fixed',
                        weight: v.weight ?? {},
                        dimensions: v.dimensions ?? {},
                        option_values: normalizedOptionValues,
                        features: v.features ?? [],
                        specifications: v.specifications ?? {},
                        care_instructions: v.care_instructions ?? [],
                        status: v.status,
                        metadata: v.metadata ?? {},
                        primary_image_url: v.primary_image_url ?? null,
                        hover_image_url: v.hover_image_url ?? null,
                        gallery_urls: v.gallery_urls ?? [],
                        dimension_image_url: v.dimension_image_url ?? null,
                        swatch_image_url: v.swatch_image_url ?? null,
                        swatch_image_thumb_url:
                            v.swatch_image_thumb_url ?? null,
                        primary_image_file: null,
                        hover_image_file: null,
                        gallery_files: [],
                        dimension_image_file: null,
                        swatch_image_file: null,
                        removed_gallery_ids: [],
                        stock: v.stock ?? [],
                        created_at: v.created_at,
                        updated_at: v.updated_at,
                    };
                });
            } else if (!newProduct && open) {
                form.reset();
                form.status = 'draft';
                form.assembly_info = {
                    required: false,
                    estimated_minutes: null,
                    price: null,
                    instructions_url: '',
                    difficulty_level: 'easy',
                    additional_information: '',
                };
                newArrivalMonths.value = null;
            }
        },
        { immediate: true },
    );

    watch(
        () => form.is_dropship,
        (val) => {
            if (val && !form.vendor_id) form.is_dropship = false;
        },
    );

    watch(
        () => form.vendor_id,
        (val) => {
            if (!val && form.is_dropship) form.is_dropship = false;
        },
    );

    watch(
        () => featureOptionsRef.value,
        (opts) => {
            const validLabels = new Set(opts.map((o) => o.label));
            form.features = form.features.filter(
                (f: SpecItem) =>
                    !f.lookup_slug || validLabels.has(f.display_name),
            );
        },
        { deep: true },
    );

    watch(
        () => form.name,
        () => {
            // No auto-slugify since slug is removed
        },
    );

    watch(
        () => form.status,
        (newStatus) => {
            if (newStatus === 'published' && !form.published_date) {
                form.published_date = new Date().toISOString().split('T')[0];
            }
        },
    );

    watch(
        () => newArrivalMonths.value,
        (months) => {
            if (months && form.status === 'published') {
                const date = new Date();
                date.setMonth(date.getMonth() + months);
                form.new_arrival_until = date.toISOString().split('T')[0];
            } else if (!months) {
                form.new_arrival_until = '';
            }
        },
        { immediate: true },
    );

    watch(
        () => form.status,
        (status) => {
            if (status === 'published' && newArrivalMonths.value) {
                const date = new Date();
                date.setMonth(date.getMonth() + newArrivalMonths.value);
                form.new_arrival_until = date.toISOString().split('T')[0];
            } else if (status !== 'published') {
                form.new_arrival_until = '';
            }
        },
    );

    const selectedVariantOptions = computed(() => {
        return (
            variantOptionsRef.value.find(
                (ns) => ns.namespace === selectedOptionNamespace.value,
            )?.options ?? []
        );
    });

    const availableCombinations = computed(() => {
        const groups = form.option_groups.filter((g) => g.options.length > 0);
        if (groups.length === 0) return [];

        const cartesian = (arrays: any[][]): any[][] =>
            arrays.reduce(
                (acc, arr) => acc.flatMap((c) => arr.map((v) => [...c, v])),
                [[]],
            );

        return cartesian(
            groups.map((g) =>
                g.options.map((o) => ({
                    group: g.namespace,
                    value: o.value,
                    label: o.label,
                })),
            ),
        );
    });

    const stepStates = computed<StepState[]>(() => {
        const hasName = !!form.name.trim();
        const hasCategory = !!form.category_id;
        const hasCollection = !!form.collection_id;
        const hasStatus = !!form.status;

        const step1Complete =
            hasName && hasCategory && hasCollection && hasStatus;
        const step1Touched =
            hasName || hasCategory || hasCollection || hasStatus;
        const hasOptionGroups = form.option_groups.length > 0;
        const hasVariants =
            form.variants.length > 0 &&
            form.variants.some((v) => v.price && v.sku);
        const hasStock =
            form.variants.length > 0 &&
            form.variants.some(
                (v) => v.stock && v.stock.length > 0 && v.stock[0].quantity > 0,
            );

        return [
            step1Complete
                ? 'complete'
                : step1Touched
                  ? 'incomplete'
                  : 'untouched',
            hasOptionGroups ? 'complete' : 'untouched',
            hasVariants
                ? 'complete'
                : hasOptionGroups
                  ? 'incomplete'
                  : 'untouched',
            hasStock ? 'complete' : hasVariants ? 'incomplete' : 'untouched',
        ];
    });

    const validationErrors = computed(() => {
        const errors: string[] = [];
        if (!form.name.trim()) errors.push('Tên sản phẩm là bắt buộc');
        if (!form.category_id) errors.push('Danh mục là bắt buộc');
        if (!form.collection_id) errors.push('Bộ sưu tập là bắt buộc');
        if (!form.status) errors.push('Trạng thái là bắt buộc');
        if (form.variants.length === 0) {
            errors.push('Cần ít nhất một biến thể');
        } else {
            const invalid = form.variants.filter((v) => !v.price || !v.sku);
            if (invalid.length > 0) {
                errors.push(`${invalid.length} biến thể thiếu SKU hoặc giá`);
            }
        }
        return errors;
    });

    const isValid = computed(() => validationErrors.value.length === 0);

    function toggleVariantExpand(index: number) {
        const set = new Set(expandedVariants);
        if (set.has(index)) set.delete(index);
        else set.add(index);
        expandedVariants = set;
    }

    function getVariantOptionLabels(
        variant: Partial<ProductVariant>,
    ): string[] {
        const labels: string[] = [];
        for (const group of form.option_groups) {
            const key = group.namespace;
            const fallbackKey = group.name.toLowerCase();
            const val =
                variant.option_values?.[key] ??
                variant.option_values?.[fallbackKey];
            if (val) {
                const opt = group.options.find((o) => o.value === val);
                labels.push(opt?.label ?? val);
            }
        }
        return labels;
    }

    function generateAllVariants() {
        const combos = availableCombinations.value;
        if (combos.length === 0) return;

        form.variants = combos.map((combo) => {
            const optionValues: Record<string, string> = {};
            for (const item of combo) {
                optionValues[item.group] = item.value;
            }
            return {
                sku: Math.random().toString(36).substring(2, 10).toUpperCase(),
                name: null,
                slug: null,
                description: null,
                price: '',
                profit_margin_value: null,
                profit_margin_unit: 'fixed' as const,
                weight: {},
                dimensions: {},
                option_values: optionValues,
                specifications: {},
                care_instructions: [],
                status: 'active' as const,
                primary_image_file: null,
                hover_image_file: null,
                gallery_files: [],
                removed_gallery_ids: [],
                dimension_image_file: null,
                swatch_image_file: null,
                stock: [],
            };
        });
        expandedVariants = new Set([0]);
    }

    function addManualVariant() {
        const combo = { ...newVariantCombo };
        if (Object.values(combo).some((v) => !v)) return;

        const exists = form.variants.some((v) =>
            form.option_groups.every(
                (g) => v.option_values?.[g.namespace] === combo[g.namespace],
            ),
        );
        if (exists) return;

        form.variants.push({
            sku: Math.random().toString(36).substring(2, 10).toUpperCase(),
            name: null,
            slug: null,
            description: null,
            price: '',
            profit_margin_value: null,
            profit_margin_unit: 'fixed' as const,
            weight: {},
            dimensions: {},
            option_values: combo,
            specifications: {},
            care_instructions: [],
            status: 'active' as const,
            primary_image_file: null,
            hover_image_file: null,
            gallery_files: [],
            removed_gallery_ids: [],
            dimension_image_file: null,
            swatch_image_file: null,
            stock: [],
        });
        expandedVariants = new Set([form.variants.length - 1]);
        newVariantCombo = {};
    }

    function addOptionGroup(variantOpts: LookupOptionGroup[]) {
        if (
            !selectedOptionNamespace.value ||
            selectedOptionValues.value.length === 0
        )
            return;

        const ns = variantOpts.find(
            (n) => n.namespace === selectedOptionNamespace.value,
        );
        if (!ns) return;

        const values = selectedOptionValues.value
            .map((slug) => ns.options.find((o) => o.slug === slug))
            .filter(Boolean)
            .map((o) => ({ value: o!.slug, label: o!.label, ...o!.metadata }));
        if (values.length === 0) return;

        const groupName = customOptionGroupName.value.trim() || ns.label;
        const namespace = ns.namespace;
        form.option_groups.push({
            name: groupName,
            namespace,
            is_swatches: false,
            options: values,
        });
        selectedOptionValues.value = [];
        selectedOptionNamespace.value = '';
        customOptionGroupName.value = '';
    }

    function removeOptionGroup(index: number) {
        form.option_groups.splice(index, 1);
    }

    function removeVariant(index: number) {
        form.variants.splice(index, 1);
    }

    function bulkCopyPrice() {
        const price = prompt('Nhập giá để áp dụng cho tất cả biến thể:');
        if (price !== null && price.trim()) {
            form.variants.forEach((v) => {
                v.price = price.trim();
            });
        }
    }

    function bulkCopySkuPattern() {
        const pattern = prompt('Nhập mẫu SKU (dùng {i} cho số thứ tự):');
        if (pattern !== null && pattern.trim()) {
            form.variants.forEach((v, i) => {
                v.sku = pattern.trim().replace('{i}', String(i + 1));
            });
        }
    }

    function addFeature() {
        if (newFeature.value.display_name.trim()) {
            form.features.push({ ...newFeature.value });
            newFeature.value = {
                lookup_slug: null,
                display_name: '',
                description: '',
            };
        }
    }

    function removeFeature(index: number) {
        form.features.splice(index, 1);
    }

    function addCareInstruction() {
        if (newCareInstruction.value.trim()) {
            form.care_instructions.push(newCareInstruction.value.trim());
            newCareInstruction.value = '';
        }
    }

    function removeCareInstruction(index: number) {
        form.care_instructions.splice(index, 1);
    }

    function addSpecGroup() {
        if (!specGroupName.value.trim()) return;

        const items: SpecItem[] = [];

        for (const slug of specSelectedValues.value) {
            const opt = specLookupOptions.value.find((o) => o.slug === slug);
            if (!opt) continue;

            items.push({
                lookup_slug: opt.slug,
                display_name: opt.label,
                description: specValueDescriptions.value[slug] ?? '',
            });
        }

        for (const item of specGroupItems.value) {
            if (item.display_name.trim()) {
                items.push({
                    lookup_slug: item.lookup_slug ?? null,
                    display_name: item.display_name,
                    description: item.description,
                });
            }
        }

        if (items.length === 0) return;

        const groupName = specGroupName.value.trim();
        if (!form.specifications) {
            form.specifications = {} as ProductSpecifications;
        }
        form.specifications[groupName] = {
            lookup_namespace: specGroupNamespace.value || null,
            is_filterable: specGroupIsFilterable.value,
            items,
        };

        specGroupName.value = '';
        specGroupNamespace.value = '';
        specGroupIsFilterable.value = false;
        specGroupItems.value = [];
        specSelectedValues.value = [];
        specValueDescriptions.value = {};
        specLookupOptions.value = [];
    }

    function removeSpecGroup(groupName: string) {
        if (form.specifications && form.specifications[groupName]) {
            delete form.specifications[groupName];
        }
    }

    function addSpecItemFromLookup() {
        if (!specGroupNamespace.value) return;
    }

    function addSpecFreeItem() {
        specGroupItems.value.push({
            lookup_slug: null,
            display_name: '',
            description: '',
        });
    }

    function removeSpecItem(groupName: string, itemIndex: number) {
        if (form.specifications && form.specifications[groupName]) {
            const group = form.specifications[groupName];
            if (group && group.items[itemIndex]) {
                group.items.splice(itemIndex, 1);
            }
        }
    }

    function addVariantPrimaryImage(variantIndex: number, file: File) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            primary_image_file: file,
            primary_image_url: null,
        };
    }

    function removeVariantPrimaryImage(variantIndex: number) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            primary_image_file: null,
            primary_image_url: null,
        };
    }

    function addVariantHoverImage(variantIndex: number, file: File) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            hover_image_file: file,
            hover_image_url: null,
        };
    }

    function removeVariantHoverImage(variantIndex: number) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            hover_image_file: null,
            hover_image_url: null,
        };
    }

    function addVariantGalleryImage(variantIndex: number, files: File[]) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        if (!variant.gallery_files) variant.gallery_files = [];
        const remaining =
            10 -
            (variant.gallery_urls?.length ?? 0) -
            variant.gallery_files.length;
        const toAdd = files.slice(0, Math.max(0, remaining));
        variant.gallery_files.push(...toAdd);
    }

    function removeVariantGalleryImage(
        variantIndex: number,
        index: number,
        isExisting: boolean,
    ) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        if (isExisting) {
            if (!variant.removed_gallery_ids) variant.removed_gallery_ids = [];
            const mediaItem = variant.gallery_urls?.[index];
            if (mediaItem) {
                variant.removed_gallery_ids.push(mediaItem.id);
                variant.gallery_urls?.splice(index, 1);
            }
        } else {
            variant.gallery_files?.splice(index, 1);
        }
    }

    function setVariantDimensionImage(variantIndex: number, file: File | null) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            dimension_image_file: file,
            dimension_image_url: null,
        };
    }

    function removeVariantDimensionImage(variantIndex: number) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            dimension_image_file: null,
            dimension_image_url: null,
        };
    }

    function setVariantSwatchImage(variantIndex: number, file: File | null) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            swatch_image_file: file,
            swatch_image_url: null,
        };
    }

    function removeVariantSwatchImage(variantIndex: number) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        form.variants[variantIndex] = {
            ...variant,
            swatch_image_file: null,
            swatch_image_url: null,
        };
    }

    function addStockEntry(variantIndex: number, locationId?: string) {
        const variant = form.variants[variantIndex];
        if (!variant) return;
        if (!variant.stock) variant.stock = [];
        if (locationId) {
            variant.stock.push({
                location_id: locationId,
                quantity: 1,
                cost_per_unit: null,
            });
        }
    }

    function removeStockEntry(variantIndex: number, locationId: string) {
        const variant = form.variants[variantIndex];
        if (!variant || !variant.stock) return;
        const idx = variant.stock.findIndex(
            (s) => s.location_id === locationId,
        );
        if (idx !== -1) variant.stock.splice(idx, 1);
    }

    function setSpecGroupNamespace(ns: string, label?: string) {
        const actualNs = ns === '_null' ? '' : ns;
        specGroupNamespace.value = actualNs;
        if (label) {
            specGroupName.value = label;
        }
        specSelectedValues.value = [];
        specValueDescriptions.value = {};
        const opts = specLookupOptionsMap.value[actualNs] ?? [];
        specLookupOptions.value = opts;
    }

    function updateSpecLookupOptionsMap(
        map: Record<string, SpecLookupOption[]>,
    ) {
        specLookupOptionsMap.value = map;
    }

    function updateVariantOptions(options: LookupOptionGroup[]) {
        variantOptionsRef.value = options;
    }

    function updateFeatureOptions(options: LookupOptionItem[]) {
        featureOptionsRef.value = options;
    }

    function submit() {
        if (!isValid.value) return;

        const url = product ? update(product).url : store().url;
        const method = product ? 'put' : 'post';
        form[method](url, { onSuccess: () => closeModal() });
    }

    function handleFinalSubmit() {
        submit();
    }

    let expandedVariants = new Set<number>();
    let newVariantCombo: Record<string, string> = {};

    function closeModal() {
        form.reset();
        form.clearErrors();
        form.status = 'draft';
        form.warranty_months = 12;
        form.is_new_arrival = true;
        form.is_custom_made = false;
        form.assembly_info = {
            required: false,
            estimated_minutes: null,
            price: null,
            instructions_url: '',
            difficulty_level: 'easy',
            additional_information: '',
        };
        newArrivalMonths.value = null;
        currentStep.value = 1;
        expandedVariants = new Set();
        newVariantCombo = {};
        selectedOptionNamespace.value = '';
        selectedOptionValues.value = [];
        specGroupName.value = '';
        specGroupNamespace.value = '';
        specGroupIsFilterable.value = true;
        specGroupItems.value = [];
        specSelectedValues.value = [];
        specValueDescriptions.value = {};
        emit('close');
    }

    function nextStep() {
        if (currentStep.value < steps.length) currentStep.value++;
    }

    function prevStep() {
        if (currentStep.value > 1) currentStep.value--;
    }

    return reactive({
        form,
        get currentStep() {
            return currentStep.value;
        },
        set currentStep(v: number) {
            currentStep.value = v;
        },
        get stepStates() {
            return stepStates.value;
        },
        steps,
        get expandedVariants() {
            return expandedVariants;
        },
        set expandedVariants(v: Set<number>) {
            expandedVariants = v;
        },
        get newVariantCombo() {
            return newVariantCombo;
        },
        set newVariantCombo(v: Record<string, string>) {
            newVariantCombo = v;
        },
        get selectedOptionNamespace() {
            return selectedOptionNamespace.value;
        },
        set selectedOptionNamespace(v: string) {
            selectedOptionNamespace.value = v;
        },
        get selectedOptionValues() {
            return selectedOptionValues.value;
        },
        set selectedOptionValues(v: string[]) {
            selectedOptionValues.value = v;
        },
        get customOptionGroupName() {
            return customOptionGroupName.value;
        },
        set customOptionGroupName(v: string) {
            customOptionGroupName.value = v;
        },
        get newArrivalMonths() {
            return newArrivalMonths.value;
        },
        set newArrivalMonths(v: number | null) {
            newArrivalMonths.value = v;
        },
        get newFeature() {
            return newFeature.value;
        },
        set newFeature(v: SpecItem) {
            newFeature.value = v;
        },
        get newCareInstruction() {
            return newCareInstruction.value;
        },
        set newCareInstruction(v: string) {
            newCareInstruction.value = v;
        },
        get specGroupName() {
            return specGroupName.value;
        },
        set specGroupName(v: string) {
            specGroupName.value = v;
        },
        get specGroupNamespace() {
            return specGroupNamespace.value;
        },
        set specGroupNamespace(v: string) {
            specGroupNamespace.value = v;
        },
        get specGroupIsFilterable() {
            return specGroupIsFilterable.value;
        },
        set specGroupIsFilterable(v: boolean) {
            specGroupIsFilterable.value = v;
        },
        get specGroupItems() {
            return specGroupItems.value;
        },
        set specGroupItems(v: SpecItem[]) {
            specGroupItems.value = v;
        },
        get specSelectedValues() {
            return specSelectedValues.value;
        },
        set specSelectedValues(v: string[]) {
            specSelectedValues.value = v;
        },
        get specValueDescriptions() {
            return specValueDescriptions.value;
        },
        set specValueDescriptions(v: Record<string, string>) {
            specValueDescriptions.value = v;
        },
        get specLookupOptions() {
            return specLookupOptions.value;
        },
        set specLookupOptions(v: SpecLookupOption[]) {
            specLookupOptions.value = v;
        },
        get specLookupOptionsMap() {
            return specLookupOptionsMap.value;
        },
        statusOptions,
        difficultyOptions,
        get selectedVariantOptions() {
            return selectedVariantOptions.value;
        },
        get featureOptions() {
            return featureOptionsRef.value;
        },
        get availableCombinations() {
            return availableCombinations.value;
        },
        get validationErrors() {
            return validationErrors.value;
        },
        get isValid() {
            return isValid.value;
        },
        submit,
        handleFinalSubmit,
        closeModal,
        nextStep,
        prevStep,
        toggleVariantExpand,
        getVariantOptionLabels,
        generateAllVariants,
        addManualVariant,
        addOptionGroup,
        removeOptionGroup,
        removeVariant,
        bulkCopyPrice,
        bulkCopySkuPattern,
        addFeature,
        removeFeature,
        addCareInstruction,
        removeCareInstruction,
        addSpecGroup,
        removeSpecGroup,
        addSpecItemFromLookup,
        addSpecFreeItem,
        removeSpecItem,
        setSpecGroupNamespace,
        updateVariantOptions,
        updateFeatureOptions,
        updateSpecLookupOptionsMap,
        addVariantPrimaryImage,
        removeVariantPrimaryImage,
        addVariantHoverImage,
        removeVariantHoverImage,
        addVariantGalleryImage,
        removeVariantGalleryImage,
        setVariantDimensionImage,
        removeVariantDimensionImage,
        setVariantSwatchImage,
        removeVariantSwatchImage,
        addStockEntry,
        removeStockEntry,
    });
}
