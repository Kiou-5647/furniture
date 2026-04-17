<script setup lang="ts">
import {
    Camera,
    ChevronDown,
    ChevronRight,
    Clipboard,
    ClipboardCheck,
    ImagePlus,
    Palette,
    Pencil,
    Plus,
    Ruler,
    Settings,
    Tag,
    Trash2,
    Truck,
    X,
    Zap,
} from '@lucide/vue';
import { computed, inject, ref } from 'vue';
import ImagePreviewDialog from '@/components/custom/ImagePreviewDialog.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Separator from '@/components/ui/separator/Separator.vue';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import type { ProductFormContext } from '@/composables/useProductForm';
import { slugify } from '@/lib/utils';
import type { SpecNamespace } from '@/types';

const createObjectURL = URL.createObjectURL.bind(URL);

const ctx = inject<ProductFormContext>('productForm')!;

const previewImageUrl = ref<string | null>(null);

function openPreview(url: string) {
    previewImageUrl.value = url;
}

const copiedVariantData = ref<any>(null);
const justCopied = ref(false);
const variantContentAccordion = ref<string | undefined>(undefined);
const expandedVariantSpecGroups = ref<Set<string>>(new Set());

// Variant spec form state
const variantSpecEditing = ref<number | null>(null);
const variantSpecEditingName = ref<string | null>(null);
const variantSpecGroupName = ref('');
const variantSpecGroupNamespace = ref('_null');
const variantSpecGroupIsFilterable = ref(false);
const variantSpecSelectedValues = ref<string[]>([]);
const variantSpecValueDescriptions = ref<Record<string, string>>({});
const variantSpecFreeItems = ref<any[]>([]);
const variantSpecOriginalGroup = ref<any>(null);

// Variant care form state
const variantCareEditing = ref<number | null>(null);
const variantCareInstruction = ref('');

// Variant features form state
const variantFeatureItems = ref<Record<number, any[]>>({});

function getVariantFeatureItems(variantIndex: number): any[] {
    if (!variantFeatureItems.value[variantIndex]) {
        variantFeatureItems.value[variantIndex] = [];
    }
    return variantFeatureItems.value[variantIndex];
}

function addVariantFeatureItem(variantIndex: number) {
    getVariantFeatureItems(variantIndex).push({
        lookup_slug: null,
        display_name: '',
        description: '',
    });
}

function removeVariantFeatureItem(variantIndex: number, index: number) {
    getVariantFeatureItems(variantIndex).splice(index, 1);
}

function commitVariantFeatures(variantIndex: number) {
    const variant = ctx.form.variants[variantIndex];
    if (!variant) return;
    const items = getVariantFeatureItems(variantIndex);
    for (const item of items) {
        if (item.display_name.trim()) {
            if (!variant.features) variant.features = [];
            variant.features.push({ ...item });
        }
    }
    variantFeatureItems.value[variantIndex] = [];
}

function toggleVariantFeature(
    slug: string,
    checked: boolean,
    variantIndex: number,
) {
    const opt = ctx.featureOptions.find((o) => o.slug === slug);
    if (!opt) return;
    const variant = ctx.form.variants[variantIndex];
    if (!variant) return;
    if (!variant.features) variant.features = [];

    if (checked) {
        if (!variant.features.some((f: any) => f.display_name === opt.label)) {
            variant.features.push({
                lookup_slug: opt.slug,
                display_name: opt.label,
                description: opt.description ?? '',
            });
        }
    } else {
        const idx = variant.features.findIndex(
            (f: any) => f.display_name === opt.label,
        );
        if (idx !== -1) variant.features.splice(idx, 1);
    }
}

// Props for spec namespaces
const props = defineProps<{
    specNamespaces: SpecNamespace[];
}>();

const specNamespaces = computed(() => props.specNamespaces ?? []);
const productSpecGroupNames = computed(() =>
    Object.keys(ctx.form.specifications || {}),
);
const selectedProductSpecGroup = ref('');
const activeVariantAccordion = ref<string | undefined>(undefined);
const variantSpecLookupOptions = computed(() => {
    const ns = variantSpecGroupNamespace.value;
    const actualNs = ns === '_null' ? '' : ns;
    return ctx.specLookupOptionsMap?.[actualNs] ?? [];
});

function onSelectProductSpecGroup(name: string) {
    if (!name) {
        variantSpecGroupName.value = '';
        variantSpecGroupNamespace.value = '_null';
        variantSpecGroupIsFilterable.value = false;
        variantSpecFreeItems.value = [];
        variantSpecSelectedValues.value = [];
        return;
    }
    const group = ctx.form.specifications?.[name];
    if (!group) return;

    variantSpecGroupName.value = name;
    variantSpecGroupNamespace.value = group.lookup_namespace || '_null';
    variantSpecGroupIsFilterable.value = group.is_filterable ?? false;
    variantSpecFreeItems.value = [];
    variantSpecSelectedValues.value = [];
}

function openVariantSpecForm(variantIndex: number) {
    variantSpecEditing.value = variantIndex;
    variantSpecEditingName.value = null;
    variantSpecGroupName.value = '';
    variantSpecGroupNamespace.value = '_null';
    variantSpecGroupIsFilterable.value = false;
    variantSpecSelectedValues.value = [];
    variantSpecValueDescriptions.value = {};
    variantSpecFreeItems.value = [];
    variantSpecOriginalGroup.value = null;
    selectedProductSpecGroup.value = '';
}

function onVariantSpecNamespaceChange(ns: string) {
    variantSpecGroupNamespace.value = ns;
    const nsObj = specNamespaces.value.find((n) => n.namespace === ns);
    variantSpecGroupName.value = nsObj!.label;
    variantSpecSelectedValues.value = [];
    variantSpecValueDescriptions.value = {};
}

function editVariantSpecGroup(variantIndex: number, groupName: string) {
    const variant = ctx.form.variants[variantIndex];
    if (!variant || !variant.specifications?.[groupName]) return;

    const group = variant.specifications[groupName];
    variantSpecOriginalGroup.value = {
        name: groupName,
        lookup_namespace: group.lookup_namespace,
        is_filterable: group.is_filterable,
        items: JSON.parse(JSON.stringify(group.items || [])),
    };
    variantSpecEditing.value = variantIndex;
    variantSpecEditingName.value = groupName;
    variantSpecGroupName.value = groupName;
    variantSpecGroupNamespace.value = group.lookup_namespace || '_null';
    variantSpecGroupIsFilterable.value = group.is_filterable ?? false;
    variantSpecFreeItems.value = (group.items || [])
        .filter((item: any) => !item.lookup_slug)
        .map((item: any) => ({ ...item }));
    variantSpecSelectedValues.value = (group.items || [])
        .filter((item: any) => item.lookup_slug)
        .map((item: any) => item.lookup_slug);
    variantSpecValueDescriptions.value = {};
    for (const item of group.items || []) {
        if (item.lookup_slug && item.description) {
            variantSpecValueDescriptions.value[item.lookup_slug] =
                item.description;
        }
    }

    delete variant.specifications[groupName];
}

function closeVariantSpecForm() {
    if (variantSpecEditingName.value && variantSpecOriginalGroup.value) {
        const variant = ctx.form.variants[variantSpecEditing.value!];
        if (variant) {
            if (!variant.specifications) variant.specifications = {};
            variant.specifications[variantSpecOriginalGroup.value.name] = {
                lookup_namespace:
                    variantSpecOriginalGroup.value.lookup_namespace,
                is_filterable: variantSpecOriginalGroup.value.is_filterable,
                items: variantSpecOriginalGroup.value.items,
            };
        }
    }

    variantSpecEditing.value = null;
    variantSpecEditingName.value = null;
    variantSpecGroupName.value = '';
    variantSpecGroupNamespace.value = '_null';
    variantSpecGroupIsFilterable.value = false;
    variantSpecSelectedValues.value = [];
    variantSpecValueDescriptions.value = {};
    variantSpecFreeItems.value = [];
    variantSpecOriginalGroup.value = null;
    selectedProductSpecGroup.value = '';
}

function getVariantSpecOptionLabel(slug: string): string {
    const opt = variantSpecLookupOptions.value.find(
        (o: any) => o.slug === slug,
    );
    return opt?.label ?? slug;
}

function getVariantSpecValueDescription(slug: string): string {
    return variantSpecValueDescriptions.value[slug] ?? '';
}

function setVariantSpecValueDescription(slug: string, value: string) {
    variantSpecValueDescriptions.value[slug] = value;
}

function buildVariantSpecItems(): any[] {
    const items: any[] = [];
    for (const slug of variantSpecSelectedValues.value) {
        const opt = variantSpecLookupOptions.value.find(
            (o: any) => o.slug === slug,
        );
        if (!opt) continue;
        items.push({
            lookup_slug: opt.slug,
            display_name: opt.label,
            description: variantSpecValueDescriptions.value[slug] ?? '',
        });
    }
    for (const item of variantSpecFreeItems.value) {
        if (item.display_name.trim()) {
            items.push({ ...item });
        }
    }
    return items;
}

function saveVariantSpecGroup(variantIndex: number) {
    if (!variantSpecGroupName.value.trim()) return;
    const variant = ctx.form.variants[variantIndex];
    if (!variant) return;

    const ns =
        variantSpecGroupNamespace.value === '_null'
            ? null
            : variantSpecGroupNamespace.value;
    if (!variant.specifications) variant.specifications = {};
    variant.specifications[variantSpecGroupName.value.trim()] = {
        lookup_namespace: ns,
        is_filterable: variantSpecGroupIsFilterable.value,
        items: buildVariantSpecItems(),
    };

    variantSpecEditing.value = null;
    variantSpecEditingName.value = null;
    variantSpecGroupName.value = '';
    variantSpecGroupNamespace.value = '_null';
    variantSpecGroupIsFilterable.value = false;
    variantSpecSelectedValues.value = [];
    variantSpecValueDescriptions.value = {};
    variantSpecFreeItems.value = [];
    variantSpecOriginalGroup.value = null;
}

function removeVariantSpecGroup(variantIndex: number, groupName: string) {
    const variant = ctx.form.variants[variantIndex];
    if (!variant?.specifications?.[groupName]) return;
    delete variant.specifications[groupName];
}

function toggleVariantSpecValue(slug: string, checked: boolean) {
    if (checked) {
        if (!variantSpecSelectedValues.value.includes(slug)) {
            variantSpecSelectedValues.value.push(slug);
        }
    } else {
        variantSpecSelectedValues.value =
            variantSpecSelectedValues.value.filter((s) => s !== slug);
    }
}

function toggleVariantSpecGroupExpand(name: string) {
    if (expandedVariantSpecGroups.value.has(name)) {
        expandedVariantSpecGroups.value.delete(name);
    } else {
        expandedVariantSpecGroups.value.add(name);
    }
}

function openVariantCareForm(variantIndex: number) {
    variantCareEditing.value = variantIndex;
    variantCareInstruction.value = '';
}

function closeVariantCareForm() {
    variantCareEditing.value = null;
    variantCareInstruction.value = '';
}

function addVariantCareInstruction(variantIndex: number) {
    if (!variantCareInstruction.value.trim()) return;
    const variant = ctx.form.variants[variantIndex];
    if (!variant) return;
    if (!variant.care_instructions) variant.care_instructions = [];
    variant.care_instructions.push(variantCareInstruction.value.trim());
    variantCareInstruction.value = '';
}

function removeVariantCareInstruction(variantIndex: number, index: number) {
    const variant = ctx.form.variants[variantIndex];
    if (!variant?.care_instructions) return;
    variant.care_instructions.splice(index, 1);
}

function copyVariantData(variant: any) {
    copiedVariantData.value = {
        name: variant.name,
        description: variant.description,
        price: variant.price,
        sale_price: variant.sale_price,
        profit_margin_value: variant.profit_margin_value,
        profit_margin_unit: variant.profit_margin_unit,
        weight: { ...variant.weight },
        dimensions: { ...variant.dimensions },
        features: JSON.parse(JSON.stringify(variant.features || [])),
        specifications: JSON.parse(
            JSON.stringify(variant.specifications || {}),
        ),
        care_instructions: [...(variant.care_instructions || [])],
        status: variant.status,
    };
    justCopied.value = true;
    setTimeout(() => (justCopied.value = false), 2000);
}

function pasteVariantData(variantIndex: number) {
    if (!copiedVariantData.value) return;
    const variant = ctx.form.variants[variantIndex];
    if (!variant) return;

    const hasExisting =
        variant.name ||
        variant.description ||
        variant.price ||
        variant.sale_price ||
        variant.profit_margin_value;

    if (
        hasExisting &&
        !confirm('Dán sẽ ghi đè thông tin hiện tại của biến thể. Tiếp tục?')
    ) {
        return;
    }

    Object.assign(variant, {
        name: copiedVariantData.value.name,
        description: copiedVariantData.value.description,
        price: copiedVariantData.value.price,
        sale_price: copiedVariantData.value.sale_price,
        profit_margin_value: copiedVariantData.value.profit_margin_value,
        profit_margin_unit: copiedVariantData.value.profit_margin_unit,
        weight: JSON.parse(JSON.stringify(copiedVariantData.value.weight)),
        dimensions: JSON.parse(
            JSON.stringify(copiedVariantData.value.dimensions),
        ),
        features: JSON.parse(JSON.stringify(copiedVariantData.value.features)),
        specifications: JSON.parse(
            JSON.stringify(copiedVariantData.value.specifications),
        ),
        care_instructions: [...copiedVariantData.value.care_instructions],
        status: copiedVariantData.value.status,
    });
}

function openAutoCreateDialog() {
    const count = ctx.availableCombinations.length;
    if (
        !confirm(
            `Tạo ${count} biến thể từ các tùy chọn đã chọn? Bạn có thể chỉnh sửa chi tiết sau.`,
        )
    ) {
        return;
    }

    const combinations = ctx.availableCombinations;
    ctx.form.variants = combinations.map((combo: any[]) => {
        const optionValues: Record<string, string> = {};
        for (const item of combo) {
            optionValues[item.group] = item.value;
        }
        const sku = Math.random().toString(36).substring(2, 10).toUpperCase();
        return {
            sku,
            name: null,
            slug: null,
            description: null,
            price: '',
            sale_price: '',
            profit_margin_value: null,
            profit_margin_unit: 'fixed' as const,
            weight: {},
            dimensions: {},
            option_values: optionValues,
            features: [],
            specifications: {},
            care_instructions: [],
            status: 'active' as const,
            primary_image_file: null,
            hover_image_file: null,
            gallery_files: [],
            removed_gallery_ids: [],
            dimension_image_file: null,
            swatch_image_file: null,
        };
    });

    ctx.expandedVariants = new Set([0]);
}

function triggerGalleryUpload(variantIndex: number) {
    const input = document.getElementById(
        `gallery-upload-${variantIndex}`,
    ) as HTMLInputElement | null;
    input?.click();
}

function handleGalleryUpload(variantIndex: number, event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        ctx.addVariantGalleryImage(variantIndex, Array.from(input.files));
        input.value = '';
    }
}

function handleDimensionUpload(variantIndex: number, event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        ctx.setVariantDimensionImage(variantIndex, input.files[0]);
        input.value = '';
    }
}

function handleSwatchUpload(variantIndex: number, event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        ctx.setVariantSwatchImage(variantIndex, input.files[0]);
        input.value = '';
    }
}

function getVariantSlug(variant: any): string {
    const productName = ctx.form.name || '';
    const nameSuffix = variant.name ? ` ${variant.name}` : '';
    const fullName = productName + nameSuffix;
    return slugify(fullName);
}

function removeGalleryImage(
    variantIndex: number,
    index: number,
    isExisting: boolean,
) {
    ctx.removeVariantGalleryImage(variantIndex, index, isExisting);
}

const MAX_GALLERY_IMAGES = 10;

function canAddMoreImages(variant: any) {
    const existingCount = variant.gallery_urls?.length ?? 0;
    const newCount = variant.gallery_files?.length ?? 0;
    return existingCount + newCount < MAX_GALLERY_IMAGES;
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <Label class="text-lg font-semibold">Biến thể</Label>
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="text-sm"
                :disabled="ctx.form.option_groups.length === 0"
                @click="openAutoCreateDialog"
            >
                <Zap class="mr-1 h-4 w-4" /> Tạo tự động
            </Button>
        </div>

        <div
            v-if="ctx.form.option_groups.length > 0"
            class="space-y-2 rounded-lg border"
        >
            <Label class="text-md px-4 pt-2 font-medium"
                >Thêm biến thể thủ công</Label
            >
            <Separator />
            <div
                class="grid grid-cols-1 gap-3 px-4 sm:grid-cols-2 md:grid-cols-3"
            >
                <div
                    v-for="group in ctx.form.option_groups"
                    :key="group.name"
                    class="flex flex-col gap-1"
                >
                    <Label class="text-sm">{{ group.name }}</Label>
                    <Select
                        :model-value="
                            ctx.newVariantCombo[group.namespace] ?? ''
                        "
                        @update:model-value="
                            ctx.newVariantCombo = {
                                ...ctx.newVariantCombo,
                                [group.namespace]: $event ? String($event) : '',
                            }
                        "
                    >
                        <SelectTrigger class="w-full text-sm">
                            <SelectValue placeholder="Chọn..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in group.options"
                                :key="opt.value"
                                :value="opt.value"
                                class="text-sm"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <Separator />
            <div class="flex justify-end px-4 pb-2">
                <Button
                    type="button"
                    size="sm"
                    class="text-sm"
                    :disabled="
                        Object.keys(ctx.newVariantCombo).length !==
                        ctx.form.option_groups.length
                    "
                    @click="ctx.addManualVariant"
                >
                    <Plus class="mr-1 h-4 w-4" /> Thêm
                </Button>
            </div>
        </div>

        <div v-if="ctx.form.variants.length > 0" class="space-y-2">
            <div class="flex items-center justify-between">
                <Label class="text-sm font-medium"
                    >Danh sách biến thể ({{ ctx.form.variants.length }})</Label
                >
            </div>

            <Accordion
                type="single"
                collapsible
                v-model="activeVariantAccordion"
                class="space-y-2"
            >
                <AccordionItem
                    v-for="(variant, vi) in ctx.form.variants"
                    :key="vi"
                    :value="String(vi)"
                    class="rounded-lg border px-4"
                >
                    <AccordionTrigger class="py-2.5 hover:no-underline">
                        <div class="flex w-full items-center gap-2">
                            <div class="flex flex-1 flex-wrap gap-1">
                                <Badge
                                    v-for="(
                                        label, li
                                    ) in ctx.getVariantOptionLabels(variant)"
                                    :key="li"
                                    variant="secondary"
                                    class="h-6 px-2 text-xs"
                                >
                                    {{ label }}
                                </Badge>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <span
                                    class="hidden font-mono text-sm text-muted-foreground md:block"
                                >
                                    {{ variant.sku || '—' }}
                                </span>
                                <span
                                    class="hidden text-sm font-medium md:block"
                                >
                                    {{
                                        variant.price
                                            ? `${String(variant.price)}₫`
                                            : '—'
                                    }}
                                </span>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0"
                                    :class="
                                        justCopied
                                            ? 'text-green-500'
                                            : 'text-muted-foreground'
                                    "
                                    @click.stop="copyVariantData(variant)"
                                >
                                    <ClipboardCheck
                                        v-if="justCopied"
                                        class="h-3.5 w-3.5"
                                    />
                                    <Clipboard v-else class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0"
                                    :class="
                                        copiedVariantData
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                    :disabled="!copiedVariantData"
                                    @click.stop="pasteVariantData(Number(vi))"
                                >
                                    <ClipboardCheck class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0 text-destructive"
                                    @click.stop="ctx.removeVariant(Number(vi))"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="space-y-4 pb-4">
                        <Field>
                            <FieldLabel class="text-sm"
                                >Tiêu đề biến thể</FieldLabel
                            >
                            <div class="flex items-center gap-1">
                                <span
                                    v-if="ctx.form.name"
                                    class="text-sm whitespace-nowrap text-muted-foreground"
                                    >{{ ctx.form.name }} &nbsp;</span
                                >
                                <Input
                                    :model-value="variant.name"
                                    @update:model-value="variant.name = $event"
                                    placeholder='VD: 48" Black Table'
                                    class="flex-1 text-sm"
                                />
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel class="text-xs text-muted-foreground"
                                >URL Slug</FieldLabel
                            >
                            <Input
                                :model-value="getVariantSlug(variant)"
                                readonly
                                class="bg-muted font-mono text-sm"
                            />
                        </Field>
                        <Field>
                            <FieldLabel class="text-sm"
                                >Mô tả biến thể</FieldLabel
                            >
                            <Textarea
                                :model-value="variant.description"
                                @update:model-value="
                                    variant.description = $event
                                "
                                placeholder="Mô tả chi tiết cho biến thể này..."
                                class="h-20 resize-none text-sm"
                            />
                        </Field>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <Field>
                                <FieldLabel class="text-sm">SKU</FieldLabel>
                                <Input
                                    v-model="variant.sku"
                                    placeholder="VD: A3X9K2M1"
                                    class="text-sm"
                                />
                            </Field>
                            <Field>
                                <FieldLabel class="text-sm"
                                    >Nhãn Swatch</FieldLabel
                                >
                                <Input
                                    v-model="variant.swatch_label"
                                    placeholder="VD: Midnight Blue"
                                    class="text-sm"
                                />
                            </Field>
                            <Field>
                                <FieldLabel class="text-sm"
                                    >Trạng thái</FieldLabel
                                >
                                <Select v-model="variant.status">
                                    <SelectTrigger class="text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            value="active"
                                            class="text-sm"
                                            >Hoạt động</SelectItem
                                        >
                                        <SelectItem
                                            value="inactive"
                                            class="text-sm"
                                            >Không hoạt động</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </Field>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <Field>
                                <FieldLabel class="text-sm"
                                    >Giá bán
                                    <span class="text-destructive"
                                        >*</span
                                    ></FieldLabel
                                >
                                <Input
                                    v-model="variant.price"
                                    placeholder="0"
                                    class="text-sm"
                                />
                            </Field>
                            <Field>
                                <FieldLabel class="text-sm"
                                    >Giảm còn
                                </FieldLabel>
                                <Input
                                    v-model="variant.sale_price"
                                    placeholder="0"
                                    class="text-sm"
                                    v-on.stop="console.info(JSON.stringify(variant))"
                                />
                            </Field>
                            <Field>
                                <FieldLabel class="text-sm"
                                    >Lợi nhuận (Giá trị)</FieldLabel
                                >
                                <Input
                                    :model-value="
                                        variant.profit_margin_value ?? ''
                                    "
                                    @update:model-value="
                                        variant.profit_margin_value = $event
                                            ? Number($event)
                                            : null
                                    "
                                    placeholder="0"
                                    class="text-sm"
                                />
                            </Field>
                            <Field>
                                <FieldLabel class="text-sm"
                                    >Lợi nhuận (Đơn vị)</FieldLabel
                                >
                                <Select v-model="variant.profit_margin_unit">
                                    <SelectTrigger class="text-sm">
                                        <SelectValue placeholder="Chọn" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="fixed">
                                            Cố định (VNĐ)
                                        </SelectItem>
                                        <SelectItem value="percentage">
                                            Phần trăm (%)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </Field>
                        </div>

                        <Field>
                            <FieldLabel class="text-sm">Kích thước</FieldLabel>
                            <div class="flex gap-2">
                                <Input
                                    :model-value="
                                        variant.dimensions?.length ?? ''
                                    "
                                    @update:model-value="
                                        variant.dimensions = {
                                            ...variant.dimensions,
                                            length: $event
                                                ? Number($event)
                                                : '',
                                        }
                                    "
                                    placeholder="D"
                                    class="text-sm"
                                />
                                <Input
                                    :model-value="
                                        variant.dimensions?.width ?? ''
                                    "
                                    @update:model-value="
                                        variant.dimensions = {
                                            ...variant.dimensions,
                                            width: $event ? Number($event) : '',
                                        }
                                    "
                                    placeholder="R"
                                    class="text-sm"
                                />
                                <Input
                                    :model-value="
                                        variant.dimensions?.height ?? ''
                                    "
                                    @update:model-value="
                                        variant.dimensions = {
                                            ...variant.dimensions,
                                            height: $event
                                                ? Number($event)
                                                : '',
                                        }
                                    "
                                    placeholder="C"
                                    class="text-sm"
                                />
                                <Select
                                    :model-value="
                                        variant.dimensions?.unit ?? 'cm'
                                    "
                                    @update:model-value="
                                        variant.dimensions = {
                                            ...variant.dimensions,
                                            unit: $event,
                                        }
                                    "
                                >
                                    <SelectTrigger class="text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cm" class="text-sm"
                                            >cm</SelectItem
                                        >
                                        <SelectItem value="mm" class="text-sm"
                                            >mm</SelectItem
                                        >
                                        <SelectItem value="m" class="text-sm"
                                            >m</SelectItem
                                        >
                                        <SelectItem value="inch" class="text-sm"
                                            >inch</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel class="text-sm">Cân nặng</FieldLabel>
                            <div class="flex gap-2">
                                <Input
                                    :model-value="variant.weight?.value ?? ''"
                                    @update:model-value="
                                        variant.weight = {
                                            ...variant.weight,
                                            value: $event ? Number($event) : '',
                                        }
                                    "
                                    placeholder="0"
                                    class="text-sm"
                                />
                                <Select
                                    :model-value="variant.weight?.unit ?? 'kg'"
                                    @update:model-value="
                                        variant.weight = {
                                            ...variant.weight,
                                            unit: $event,
                                        }
                                    "
                                >
                                    <SelectTrigger class="text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="kg" class="text-sm"
                                            >kg</SelectItem
                                        >
                                        <SelectItem value="g" class="text-sm"
                                            >g</SelectItem
                                        >
                                        <SelectItem value="lb" class="text-sm"
                                            >lb</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </Field>

                        <Separator />

                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-1.5 text-sm font-medium"
                                    >
                                        <Camera class="h-4 w-4" />
                                        Ảnh chính
                                    </Label>
                                    <div
                                        v-if="
                                            variant.primary_image_url ||
                                            variant.primary_image_file
                                        "
                                        class="group relative w-full overflow-hidden rounded-md border"
                                    >
                                        <img
                                            :src="
                                                variant.primary_image_file
                                                    ? createObjectURL(
                                                          variant.primary_image_file,
                                                      )
                                                    : variant.primary_image_url
                                            "
                                            class="h-full w-full cursor-pointer object-cover"
                                            @click="
                                                openPreview(
                                                    variant.primary_image_file
                                                        ? createObjectURL(
                                                              variant.primary_image_file,
                                                          )
                                                        : variant.primary_image_url,
                                                )
                                            "
                                        />
                                        <label
                                            class="absolute top-2 right-2 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <button
                                                id="primary_image"
                                                name="primary_image"
                                                @click="
                                                    ctx.removeVariantPrimaryImage(
                                                        Number(vi),
                                                    )!
                                                "
                                            >
                                                <X class="h-4 w-4 text-white" />
                                            </button>
                                        </label>
                                    </div>
                                    <label
                                        v-else
                                        class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed transition-colors hover:bg-muted"
                                    >
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="
                                                (e: Event) => {
                                                    const input =
                                                        e.target as HTMLInputElement;
                                                    if (input.files?.[0]) {
                                                        ctx.addVariantPrimaryImage(
                                                            Number(vi),
                                                            input.files[0],
                                                        );
                                                        input.value = '';
                                                    }
                                                }
                                            "
                                        />
                                        <ImagePlus
                                            class="mb-1 h-5 w-5 text-muted-foreground"
                                        />
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >Tải lên</span
                                        >
                                    </label>
                                    <p class="text-xs text-muted-foreground">
                                        Hiển thị trong danh sách
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-1.5 text-sm font-medium"
                                    >
                                        <Camera class="h-4 w-4" />
                                        Ảnh hover
                                    </Label>
                                    <div
                                        v-if="
                                            variant.hover_image_url ||
                                            variant.hover_image_file
                                        "
                                        class="group relative w-full overflow-hidden rounded-md border"
                                    >
                                        <img
                                            :src="
                                                variant.hover_image_file
                                                    ? createObjectURL(
                                                          variant.hover_image_file,
                                                      )
                                                    : variant.hover_image_url
                                            "
                                            class="h-full w-full cursor-pointer object-cover"
                                            @click="
                                                openPreview(
                                                    variant.hover_image_file
                                                        ? createObjectURL(
                                                              variant.hover_image_file,
                                                          )
                                                        : variant.hover_image_url,
                                                )
                                            "
                                        />
                                        <label
                                            class="absolute top-2 right-2 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <button
                                                id="hover_image"
                                                name="hover_image"
                                                @click="
                                                    ctx.removeVariantHoverImage(
                                                        Number(vi),
                                                    )!
                                                "
                                            >
                                                <X class="h-4 w-4 text-white" />
                                            </button>
                                        </label>
                                    </div>
                                    <label
                                        v-else
                                        class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed transition-colors hover:bg-muted"
                                    >
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="
                                                (e: Event) => {
                                                    const input =
                                                        e.target as HTMLInputElement;
                                                    if (input.files?.[0]) {
                                                        ctx.addVariantHoverImage(
                                                            Number(vi),
                                                            input.files[0],
                                                        );
                                                        input.value = '';
                                                    }
                                                }
                                            "
                                        />
                                        <ImagePlus
                                            class="mb-1 h-5 w-5 text-muted-foreground"
                                        />
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >Tải lên</span
                                        >
                                    </label>
                                    <p class="text-xs text-muted-foreground">
                                        Hiển thị khi hover
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label
                                        class="flex items-center gap-1.5 text-sm font-medium"
                                    >
                                        <Camera class="h-4 w-4" />
                                        Thư viện ảnh
                                        <Badge
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            {{
                                                (variant.gallery_urls?.length ??
                                                    0) +
                                                (variant.gallery_files
                                                    ?.length ?? 0)
                                            }}/{{ MAX_GALLERY_IMAGES }}
                                        </Badge>
                                    </Label>
                                    <div v-if="canAddMoreImages(variant)">
                                        <input
                                            :id="`gallery-upload-${vi}`"
                                            type="file"
                                            accept="image/*"
                                            multiple
                                            class="hidden"
                                            @change="
                                                handleGalleryUpload(
                                                    Number(vi),
                                                    $event,
                                                )
                                            "
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            class="text-xs"
                                            @click="
                                                triggerGalleryUpload(Number(vi))
                                            "
                                        >
                                            <ImagePlus
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            Thêm ảnh
                                        </Button>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <template
                                        v-for="(img, imgIdx) in [
                                            ...(variant.gallery_urls || []).map(
                                                (m: any) => ({
                                                    ...m,
                                                    isExisting: true,
                                                }),
                                            ),
                                            ...(
                                                variant.gallery_files || []
                                            ).map((f: File) => ({
                                                id: `new-${f.name}`,
                                                url: createObjectURL(f),
                                                thumb_url: createObjectURL(f),
                                                isExisting: false,
                                            })),
                                        ]"
                                        :key="img.id"
                                    >
                                        <div
                                            class="group relative max-w-[6.25rem] overflow-hidden rounded-md border"
                                        >
                                            <img
                                                :src="img.thumb_url"
                                                class="h-full w-full cursor-pointer object-cover"
                                                @click="openPreview(img.url)"
                                            />
                                            <button
                                                type="button"
                                                class="absolute top-0 right-0 rounded-tr-md bg-black/40 p-0.5 opacity-0 transition-opacity group-hover:opacity-100"
                                                @click.stop="
                                                    removeGalleryImage(
                                                        Number(vi),
                                                        imgIdx,
                                                        img.isExisting,
                                                    )
                                                "
                                            >
                                                <X class="h-3 w-3 text-white" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex flex-col space-y-2">
                                    <Label
                                        class="flex items-center gap-1.5 text-sm font-medium"
                                    >
                                        <Ruler class="h-4 w-4" />
                                        Bản vẽ kỹ thuật
                                    </Label>
                                    <div
                                        class="flex flex-col items-start gap-3"
                                    >
                                        <div
                                            v-if="
                                                variant.dimension_image_url ||
                                                variant.dimension_image_file
                                            "
                                            class="group relative w-40 overflow-hidden rounded-md border"
                                        >
                                            <img
                                                :src="
                                                    variant.dimension_image_file
                                                        ? createObjectURL(
                                                              variant.dimension_image_file,
                                                          )
                                                        : variant.dimension_image_url
                                                "
                                                class="h-full w-full cursor-pointer object-cover"
                                                @click="
                                                    openPreview(
                                                        variant.dimension_image_file
                                                            ? createObjectURL(
                                                                  variant.dimension_image_file,
                                                              )
                                                            : variant.dimension_image_url,
                                                    )
                                                "
                                            />
                                            <label
                                                class="absolute top-0.5 right-0.5 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
                                            >
                                                <button
                                                    type="button"
                                                    @click="
                                                        ctx.removeVariantDimensionImage(
                                                            Number(vi),
                                                        )!
                                                    "
                                                >
                                                    <X
                                                        class="h-4 w-4 text-white"
                                                    />
                                                </button>
                                            </label>
                                        </div>
                                        <label
                                            v-else
                                            class="flex h-28 w-full cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed px-3 transition-colors hover:bg-muted"
                                        >
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="
                                                    handleDimensionUpload(
                                                        Number(vi),
                                                        $event,
                                                    )
                                                "
                                            />
                                            <ImagePlus
                                                class="mb-1 h-6 w-6 text-muted-foreground"
                                            />
                                            <span
                                                class="text-xs text-muted-foreground"
                                                >Tải lên</span
                                            >
                                        </label>
                                    </div>
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <Label
                                        class="flex items-center gap-1.5 text-sm font-medium"
                                    >
                                        <Palette class="h-4 w-4" />
                                        Ảnh thẻ
                                    </Label>
                                    <div
                                        class="flex flex-col items-start gap-3"
                                    >
                                        <div
                                            v-if="
                                                variant.swatch_image_url ||
                                                variant.swatch_image_thumb_url ||
                                                variant.swatch_image_file
                                            "
                                            class="group relative ml-4 h-12 w-12 overflow-hidden rounded-full border"
                                        >
                                            <img
                                                :src="
                                                    variant.swatch_image_file
                                                        ? createObjectURL(
                                                              variant.swatch_image_file,
                                                          )
                                                        : variant.swatch_image_thumb_url ||
                                                          variant.swatch_image_url
                                                "
                                                class="h-full w-full cursor-pointer object-cover"
                                                @click="
                                                    openPreview(
                                                        variant.swatch_image_file
                                                            ? createObjectURL(
                                                                  variant.swatch_image_file,
                                                              )
                                                            : variant.swatch_image_url ||
                                                                  variant.swatch_image_thumb_url,
                                                    )
                                                "
                                            />
                                            <label
                                                class="absolute top-0.5 right-0.5 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
                                            >
                                                <button
                                                    type="button"
                                                    @click="
                                                        ctx.removeVariantSwatchImage(
                                                            Number(vi),
                                                        )!
                                                    "
                                                >
                                                    <X
                                                        class="h-4 w-4 text-white"
                                                    />
                                                </button>
                                            </label>
                                        </div>
                                        <label
                                            v-else
                                            class="ml-4 flex h-12 w-12 cursor-pointer flex-col items-center justify-center rounded-full border-2 border-dashed transition-colors hover:bg-muted"
                                        >
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="
                                                    handleSwatchUpload(
                                                        Number(vi),
                                                        $event,
                                                    )
                                                "
                                            />
                                            <ImagePlus
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Content Accordion -->
                            <Accordion
                                type="single"
                                collapsible
                                v-model="variantContentAccordion"
                                class="space-y-2"
                            >
                                <!-- Features Accordion -->
                                <AccordionItem
                                    value="features"
                                    class="rounded-lg border px-4"
                                >
                                    <AccordionTrigger
                                        class="py-2.5 hover:no-underline"
                                    >
                                        <div
                                            class="text-md flex items-center gap-2 font-semibold"
                                        >
                                            <Tag class="h-4 w-4" /> Tính năng
                                            nổi bật
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="space-y-3">
                                        <!-- Lookup selection -->
                                        <div
                                            v-if="ctx.featureOptions.length > 0"
                                            class="space-y-2"
                                        >
                                            <Label
                                                class="text-xs text-muted-foreground"
                                                >Chọn từ tra cứu:</Label
                                            >
                                            <div
                                                class="grid max-h-40 grid-cols-2 gap-2 overflow-y-auto rounded-md border p-2"
                                            >
                                                <label
                                                    v-for="opt in ctx.featureOptions"
                                                    :key="opt.id"
                                                    class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 transition-all hover:bg-muted/50"
                                                    :class="
                                                        (
                                                            variant.features ||
                                                            []
                                                        ).some(
                                                            (f: any) =>
                                                                f.display_name ===
                                                                opt.label,
                                                        )
                                                            ? 'border-primary bg-primary/5'
                                                            : ''
                                                    "
                                                >
                                                    <Checkbox
                                                        :model-value="
                                                            (
                                                                variant.features ||
                                                                []
                                                            ).some(
                                                                (f: any) =>
                                                                    f.display_name ===
                                                                    opt.label,
                                                            )
                                                        "
                                                        @update:model-value="
                                                            toggleVariantFeature(
                                                                opt.slug,
                                                                $event as boolean,
                                                                Number(vi),
                                                            )
                                                        "
                                                    />
                                                    <span
                                                        class="truncate text-sm"
                                                        >{{ opt.label }}</span
                                                    >
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Added features list -->
                                        <div
                                            v-if="
                                                (variant.features || [])
                                                    .length > 0
                                            "
                                            class="space-y-2 border-t pt-3"
                                        >
                                            <Label
                                                class="text-xs text-muted-foreground"
                                                >Đã thêm:</Label
                                            >
                                            <div class="space-y-2">
                                                <div
                                                    v-for="(
                                                        feature, i
                                                    ) in variant.features || []"
                                                    :key="i"
                                                    class="space-y-1 rounded-md border p-2"
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <Input
                                                            v-model="
                                                                feature.display_name
                                                            "
                                                            placeholder="Tên tính năng"
                                                            class="text-sm"
                                                        />
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="h-6 w-6 p-0 text-destructive"
                                                            @click="
                                                                (
                                                                    variant.features ||
                                                                    []
                                                                ).splice(i, 1)
                                                            "
                                                        >
                                                            <X
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </Button>
                                                    </div>
                                                    <Textarea
                                                        v-model="
                                                            feature.description
                                                        "
                                                        placeholder="Mô tả chi tiết..."
                                                        class="h-14 resize-none text-xs"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add new free-form feature -->
                                        <div class="space-y-2">
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <Label
                                                    class="text-xs text-muted-foreground"
                                                    >Hoặc nhập tự do:</Label
                                                >
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 text-xs"
                                                    @click="
                                                        addVariantFeatureItem(
                                                            Number(vi),
                                                        )
                                                    "
                                                >
                                                    <Plus
                                                        class="mr-1 h-3 w-3"
                                                    />
                                                    Thêm mục
                                                </Button>
                                            </div>
                                            <div
                                                v-for="(
                                                    item, idx
                                                ) in getVariantFeatureItems(
                                                    Number(vi),
                                                )"
                                                :key="idx"
                                                class="space-y-1 rounded-md border p-2"
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Input
                                                        v-model="
                                                            item.display_name
                                                        "
                                                        placeholder="Tên tính năng"
                                                        class="text-sm"
                                                    />
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        class="h-6 w-6 p-0 text-destructive"
                                                        @click="
                                                            removeVariantFeatureItem(
                                                                Number(vi),
                                                                idx,
                                                            )
                                                        "
                                                    >
                                                        <X
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </Button>
                                                </div>
                                                <Textarea
                                                    v-model="item.description"
                                                    placeholder="Mô tả chi tiết..."
                                                    class="h-14 resize-none text-xs"
                                                />
                                            </div>
                                            <Button
                                                v-if="
                                                    getVariantFeatureItems(
                                                        Number(vi),
                                                    ).length > 0
                                                "
                                                type="button"
                                                size="sm"
                                                class="text-sm"
                                                @click="
                                                    commitVariantFeatures(
                                                        Number(vi),
                                                    )
                                                "
                                            >
                                                <Plus class="mr-1 h-4 w-4" />
                                                Thêm vào danh sách
                                            </Button>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>

                                <!-- Specs Accordion -->
                                <AccordionItem
                                    value="specs"
                                    class="rounded-lg border px-4"
                                >
                                    <AccordionTrigger
                                        class="py-2.5 hover:no-underline"
                                    >
                                        <div
                                            class="text-md flex items-center gap-2 font-semibold"
                                        >
                                            <Settings class="h-4 w-4" /> Thông
                                            số kỹ thuật
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="space-y-3 pb-3">
                                        <div
                                            class="flex items-center justify-end"
                                        >
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="text-xs"
                                                :disabled="
                                                    variantSpecEditing ===
                                                    Number(vi)
                                                "
                                                @click="
                                                    openVariantSpecForm(
                                                        Number(vi),
                                                    )
                                                "
                                            >
                                                <Plus class="mr-1 h-3 w-3" />
                                                Thêm nhóm
                                            </Button>
                                        </div>

                                        <!-- Variant spec form -->
                                        <div
                                            v-if="
                                                variantSpecEditing ===
                                                Number(vi)
                                            "
                                            class="space-y-2 rounded-md border bg-muted/30 p-3"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <Label
                                                    class="text-sm font-medium"
                                                >
                                                    {{
                                                        variantSpecEditingName !==
                                                        null
                                                            ? 'Chỉnh sửa nhóm'
                                                            : 'Nhóm mới'
                                                    }}
                                                </Label>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 p-0"
                                                    @click="
                                                        closeVariantSpecForm()
                                                    "
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                </Button>
                                            </div>

                                            <!-- Select existing product group -->
                                            <Select
                                                v-if="
                                                    !variantSpecEditingName &&
                                                    productSpecGroupNames.length >
                                                        0
                                                "
                                                v-model="
                                                    selectedProductSpecGroup
                                                "
                                                @update:model-value="
                                                    onSelectProductSpecGroup!
                                                "
                                            >
                                                <SelectTrigger
                                                    class="w-full text-sm"
                                                >
                                                    <SelectValue
                                                        placeholder="Chọn nhóm từ sản phẩm..."
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        :value="null"
                                                        class="text-sm"
                                                        >— Nhập mới
                                                        —</SelectItem
                                                    >
                                                    <SelectItem
                                                        v-for="name in productSpecGroupNames"
                                                        :key="name"
                                                        :value="name"
                                                        class="text-sm"
                                                    >
                                                        {{ name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>

                                            <Input
                                                v-if="!selectedProductSpecGroup"
                                                v-model="variantSpecGroupName"
                                                placeholder="Tên nhóm"
                                                class="text-sm"
                                            />

                                            <Select
                                                v-model="
                                                    variantSpecGroupNamespace
                                                "
                                                @update:model-value="
                                                    onVariantSpecNamespaceChange(
                                                        String($event),
                                                    )
                                                "
                                            >
                                                <SelectTrigger
                                                    class="w-full text-sm"
                                                >
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        value="_null"
                                                        class="text-sm"
                                                        >— Không có
                                                        —</SelectItem
                                                    >
                                                    <SelectItem
                                                        v-for="ns in specNamespaces"
                                                        :key="ns.namespace"
                                                        :value="ns.namespace"
                                                        class="text-sm"
                                                    >
                                                        {{ ns.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>

                                            <div
                                                v-if="
                                                    variantSpecGroupNamespace !=
                                                        '_null' &&
                                                    variantSpecGroupNamespace !=
                                                        ''
                                                "
                                                class="flex items-center gap-2"
                                            >
                                                <Switch
                                                    id="variant_spec_filterable"
                                                    v-model="
                                                        variantSpecGroupIsFilterable
                                                    "
                                                    class="h-4 w-7"
                                                />
                                                <Label
                                                    for="variant_spec_filterable"
                                                    class="text-sm"
                                                    >Cho phép lọc</Label
                                                >
                                            </div>

                                            <div
                                                v-if="
                                                    variantSpecGroupNamespace !=
                                                        '_null' &&
                                                    variantSpecGroupNamespace !=
                                                        ''
                                                "
                                                class="space-y-2"
                                            >
                                                <Label
                                                    class="text-xs text-muted-foreground"
                                                    >Chọn giá trị từ tra
                                                    cứu:</Label
                                                >
                                                <div
                                                    v-if="
                                                        variantSpecLookupOptions.length >
                                                        0
                                                    "
                                                    class="flex flex-wrap gap-2"
                                                >
                                                    <label
                                                        v-for="opt in variantSpecLookupOptions"
                                                        :key="opt.id"
                                                        class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-1.5 text-sm transition-all hover:bg-muted/50"
                                                        :class="
                                                            variantSpecSelectedValues.includes(
                                                                opt.slug,
                                                            )
                                                                ? 'border-primary bg-primary/5'
                                                                : ''
                                                        "
                                                    >
                                                        <Checkbox
                                                            :model-value="
                                                                variantSpecSelectedValues.includes(
                                                                    opt.slug,
                                                                )
                                                            "
                                                            @update:model-value="
                                                                toggleVariantSpecValue(
                                                                    opt.slug,
                                                                    $event as boolean,
                                                                )
                                                            "
                                                        />

                                                        <span
                                                            class="truncate"
                                                            >{{
                                                                opt.label
                                                            }}</span
                                                        >
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Per-value description -->
                                            <div
                                                v-if="
                                                    variantSpecSelectedValues.length >
                                                    0
                                                "
                                                class="space-y-2"
                                            >
                                                <div
                                                    v-for="slug in variantSpecSelectedValues"
                                                    :key="slug"
                                                    class="space-y-1 rounded-md border p-2"
                                                >
                                                    <span
                                                        class="text-sm font-medium"
                                                        >{{
                                                            getVariantSpecOptionLabel(
                                                                slug,
                                                            )
                                                        }}</span
                                                    >
                                                    <Textarea
                                                        :model-value="
                                                            getVariantSpecValueDescription(
                                                                slug,
                                                            )
                                                        "
                                                        @update:model-value="
                                                            setVariantSpecValueDescription(
                                                                slug,
                                                                String($event),
                                                            )
                                                        "
                                                        placeholder="Mô tả cho giá trị này..."
                                                        class="h-16 resize-none text-xs"
                                                    />
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <div
                                                    class="flex items-center justify-between"
                                                >
                                                    <Label
                                                        class="text-xs text-muted-foreground"
                                                        >Hoặc nhập tự do:</Label
                                                    >
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        class="h-6 text-xs"
                                                        @click="
                                                            variantSpecFreeItems.push(
                                                                {
                                                                    lookup_slug:
                                                                        null,
                                                                    display_name:
                                                                        '',
                                                                    description:
                                                                        '',
                                                                },
                                                            )
                                                        "
                                                    >
                                                        <Plus
                                                            class="mr-1 h-3 w-3"
                                                        />
                                                        Thêm mục
                                                    </Button>
                                                </div>
                                                <div
                                                    v-for="(
                                                        item, idx
                                                    ) in variantSpecFreeItems"
                                                    :key="idx"
                                                    class="space-y-1 rounded-md border p-2"
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <Input
                                                            v-model="
                                                                item.display_name
                                                            "
                                                            placeholder="Tên"
                                                            class="text-sm"
                                                        />
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="h-6 w-6 p-0 text-destructive"
                                                            @click="
                                                                variantSpecFreeItems.splice(
                                                                    idx,
                                                                    1,
                                                                )
                                                            "
                                                        >
                                                            <X
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </Button>
                                                    </div>
                                                    <Textarea
                                                        v-model="
                                                            item.description
                                                        "
                                                        placeholder="Mô tả"
                                                        class="h-14 text-xs"
                                                    />
                                                </div>
                                            </div>

                                            <div class="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    class="text-sm"
                                                    :disabled="
                                                        !variantSpecGroupName.trim() ||
                                                        (!variantSpecEditingName &&
                                                            Object.keys(
                                                                variant.specifications ||
                                                                    {},
                                                            ).includes(
                                                                variantSpecGroupName.trim(),
                                                            )) ||
                                                        (variantSpecSelectedValues.length ===
                                                            0 &&
                                                            variantSpecFreeItems.length ===
                                                                0)
                                                    "
                                                    @click="
                                                        saveVariantSpecGroup(
                                                            Number(vi),
                                                        )
                                                    "
                                                >
                                                    <Plus
                                                        class="mr-1 h-4 w-4"
                                                    />
                                                    {{
                                                        variantSpecEditingName !==
                                                        null
                                                            ? 'Cập nhật'
                                                            : 'Thêm nhóm'
                                                    }}
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    class="text-sm"
                                                    @click="
                                                        closeVariantSpecForm()
                                                    "
                                                >
                                                    Hủy
                                                </Button>
                                            </div>
                                        </div>

                                        <!-- Display variant specs -->
                                        <div
                                            v-if="
                                                Object.keys(
                                                    variant.specifications ||
                                                        {},
                                                ).length > 0
                                            "
                                            class="space-y-2"
                                        >
                                            <div
                                                v-for="(
                                                    group, groupName
                                                ) in variant.specifications ||
                                                {}"
                                                :key="String(groupName)"
                                                class="rounded-md border"
                                            >
                                                <div
                                                    class="flex cursor-pointer items-center justify-between px-3 py-2 hover:bg-muted/50"
                                                    @click="
                                                        toggleVariantSpecGroupExpand(
                                                            String(groupName),
                                                        )
                                                    "
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <component
                                                            :is="
                                                                expandedVariantSpecGroups.has(
                                                                    String(
                                                                        groupName,
                                                                    ),
                                                                )
                                                                    ? ChevronDown
                                                                    : ChevronRight
                                                            "
                                                            class="h-4 w-4 text-muted-foreground"
                                                        />
                                                        <span
                                                            class="text-sm font-medium"
                                                            >{{
                                                                groupName
                                                            }}</span
                                                        >
                                                        <Badge
                                                            v-if="
                                                                group.is_filterable
                                                            "
                                                            variant="outline"
                                                            class="text-xs"
                                                        >
                                                            Lọc được
                                                        </Badge>
                                                    </div>
                                                    <div
                                                        class="flex items-center gap-1"
                                                    >
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="h-6 w-6 p-0"
                                                            @click.stop="
                                                                editVariantSpecGroup(
                                                                    Number(vi),
                                                                    String(
                                                                        groupName,
                                                                    ),
                                                                )
                                                            "
                                                        >
                                                            <Pencil
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            class="h-6 w-6 p-0 text-destructive"
                                                            @click.stop="
                                                                removeVariantSpecGroup(
                                                                    Number(vi),
                                                                    String(
                                                                        groupName,
                                                                    ),
                                                                )
                                                            "
                                                        >
                                                            <Trash2
                                                                class="h-3.5 w-3.5"
                                                            />
                                                        </Button>
                                                    </div>
                                                </div>
                                                <div
                                                    v-if="
                                                        expandedVariantSpecGroups.has(
                                                            String(groupName),
                                                        )
                                                    "
                                                    class="border-t px-3 py-2"
                                                >
                                                    <div class="space-y-1.5">
                                                        <div
                                                            v-for="(
                                                                item, idx
                                                            ) in group.items ||
                                                            []"
                                                            :key="idx"
                                                            class="flex items-start gap-2 py-1"
                                                        >
                                                            <span
                                                                class="text-sm font-medium"
                                                                >{{
                                                                    item.display_name
                                                                }}</span
                                                            >
                                                            <span
                                                                v-if="
                                                                    item.description
                                                                "
                                                                class="text-sm text-muted-foreground"
                                                                >—
                                                                {{
                                                                    item.description
                                                                }}</span
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-else-if="
                                                variantSpecEditing !==
                                                Number(vi)
                                            "
                                            class="text-sm text-muted-foreground italic"
                                        >
                                            Chưa có thông số
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>

                                <!-- Care Accordion -->
                                <AccordionItem
                                    value="care"
                                    class="rounded-lg border px-4"
                                >
                                    <AccordionTrigger
                                        class="py-2.5 hover:no-underline"
                                    >
                                        <div
                                            class="text-md flex items-center gap-2 font-semibold"
                                        >
                                            <Truck class="h-4 w-4" /> Hướng dẫn
                                            bảo quản
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent class="space-y-2 pb-3">
                                        <!-- Variant care form -->
                                        <div
                                            v-if="
                                                variantCareEditing ===
                                                Number(vi)
                                            "
                                            class="space-y-2 rounded-md border bg-muted/30 p-3"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <Label
                                                    class="text-sm font-medium"
                                                    >Thêm hướng dẫn</Label
                                                >
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 p-0"
                                                    @click="
                                                        closeVariantCareForm()
                                                    "
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                </Button>
                                            </div>
                                            <Textarea
                                                v-model="variantCareInstruction"
                                                placeholder="VD: Lau khô bằng khăn mềm, tránh ánh nắng trực tiếp..."
                                                class="h-20 resize-none text-sm"
                                            />
                                            <div class="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    class="text-sm"
                                                    :disabled="
                                                        !variantCareInstruction.trim()
                                                    "
                                                    @click="
                                                        addVariantCareInstruction(
                                                            Number(vi),
                                                        )
                                                    "
                                                >
                                                    <Plus
                                                        class="mr-1 h-4 w-4"
                                                    />
                                                    Thêm
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    class="text-sm"
                                                    @click="
                                                        closeVariantCareForm()
                                                    "
                                                >
                                                    Hủy
                                                </Button>
                                            </div>
                                        </div>

                                        <!-- Add button when not editing -->
                                        <div
                                            v-if="
                                                variantCareEditing !==
                                                Number(vi)
                                            "
                                            class="flex justify-end"
                                        >
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="text-xs"
                                                @click="
                                                    openVariantCareForm(
                                                        Number(vi),
                                                    )
                                                "
                                            >
                                                <Plus class="mr-1 h-3 w-3" />
                                                Thêm
                                            </Button>
                                        </div>

                                        <!-- Display variant care instructions -->
                                        <div
                                            v-if="
                                                (
                                                    variant.care_instructions ||
                                                    []
                                                ).length > 0
                                            "
                                            class="space-y-1"
                                        >
                                            <div
                                                v-for="(
                                                    instruction, idx
                                                ) in variant.care_instructions ||
                                                []"
                                                :key="idx"
                                                class="flex items-center gap-2 rounded-md border px-3 py-2"
                                            >
                                                <span class="flex-1 text-sm">{{
                                                    instruction
                                                }}</span>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-6 w-6 p-0 text-destructive"
                                                    @click="
                                                        removeVariantCareInstruction(
                                                            Number(vi),
                                                            Number(idx),
                                                        )
                                                    "
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div
                                            v-else-if="
                                                variantCareEditing !==
                                                Number(vi)
                                            "
                                            class="text-sm text-muted-foreground italic"
                                        >
                                            Chưa có hướng dẫn
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>

        <div
            v-if="ctx.form.variants.length === 0"
            class="py-6 text-center text-sm text-muted-foreground"
        >
            Chưa có biến thể nào. Sử dụng chế độ tạo ở trên hoặc thêm thủ công.
        </div>

        <ImagePreviewDialog
            :open="!!previewImageUrl"
            :src="previewImageUrl"
            @update:open="previewImageUrl = $event ? previewImageUrl : null"
            @close="previewImageUrl = null"
        />
    </div>
</template>
