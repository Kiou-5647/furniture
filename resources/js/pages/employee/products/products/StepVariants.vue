<script setup lang="ts">
import {
    Camera,
    ChevronRight,
    Clipboard,
    ClipboardCheck,
    ImagePlus,
    Layers,
    Palette,
    Pencil,
    Plus,
    Ruler,
    Settings,
    Trash2,
    X,
    Zap,
} from '@lucide/vue';
import { computed, inject, ref } from 'vue';
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
import {
    slugify,
    formatNumber,
    handleNumericInput,
    formatPrice,
} from '@/lib/utils';
import type { SpecNamespace } from '@/types';

const props = defineProps<{
    specNamespaces: SpecNamespace[];
}>();

const createObjectURL = URL.createObjectURL.bind(URL);
const ctx = inject<ProductFormContext>('productForm')!;

// --- State Management ---
const previewImageUrl = ref<string | null>(null);
const copiedVariantData = ref<any>(null);
const justCopied = ref(false);
const activeVariantAccordion = ref<string | undefined>(undefined);
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
const selectedProductSpecGroup = ref('');

// Variant care form state
const variantCareEditing = ref<number | null>(null);
const variantCareInstruction = ref('');

/* Features */
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

    ctx.form.variants = [...ctx.form.variants];
}

/* Specifications State & Logic */
const productSpecGroupNames = computed(() =>
    Object.keys(ctx.form.specifications || {}),
);

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
        return;
    }
    const group = ctx.form.specifications?.[name];
    if (!group) return;

    variantSpecGroupName.value = name;
    variantSpecGroupNamespace.value = group.lookup_namespace || '_null';
    variantSpecGroupIsFilterable.value = group.is_filterable ?? false;
}

function addSpecFreeItem() {
    variantSpecFreeItems.value.push({
        display_name: '',
        description: '',
        lookup_slug: null,
    });
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
        if (item.display_name.trim()) items.push({ ...item });
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
        if (!variantSpecSelectedValues.value.includes(slug))
            variantSpecSelectedValues.value.push(slug);
    } else {
        variantSpecSelectedValues.value =
            variantSpecSelectedValues.value.filter((s) => s !== slug);
    }
}

function toggleVariantSpecGroupExpand(name: string) {
    if (expandedVariantSpecGroups.value.has(name))
        expandedVariantSpecGroups.value.delete(name);
    else expandedVariantSpecGroups.value.add(name);
}

/* [MEDIA_LOGIC] */
function openPreview(url: string) {
    previewImageUrl.value = url;
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

/* [UTILITY_LOGIC] */
function copyVariantData(variant: any) {
    copiedVariantData.value = {
        name: variant.name,
        description: variant.description,
        price: variant.price,
        profit_margin_value: variant.profit_margin_value,
        profit_margin_unit: variant.profit_margin_unit,
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
        profit_margin_value: copiedVariantData.value.profit_margin_value,
        profit_margin_unit: copiedVariantData.value.profit_margin_unit,
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
            profit_margin_value: null,
            profit_margin_unit: 'fixed' as const,
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

function getVariantSlug(variant: any): string {
    const productName = ctx.form.name || '';
    const nameSuffix = variant.name ? ` ${variant.name}` : '';
    const fullName = productName + nameSuffix;
    return slugify(fullName);
}

// Care Form Helpers
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
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <div class="rounded-lg bg-primary/10 p-1.5">
                        <Layers class="h-5 w-5 text-primary" />
                    </div>
                    <Label class="text-xl font-bold tracking-tight"
                        >Quản lý Biến thể</Label
                    >
                </div>
                <p class="text-sm text-muted-foreground">
                    Thiết lập chi tiết cho từng phiên bản sản phẩm, bao gồm giá,
                    SKU và hình ảnh.
                </p>
            </div>
            <Button
                type="button"
                variant="default"
                class="group relative overflow-hidden rounded-xl px-6 py-6 font-bold shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-95"
                :disabled="ctx.form.option_groups.length === 0"
                @click="openAutoCreateDialog"
            >
                <div
                    class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
                <Zap class="mr-2 h-4 w-4 fill-current" /> Tạo tự động
            </Button>
        </div>

        <div
            v-if="ctx.form.option_groups.length > 0"
            class="overflow-hidden rounded-2xl border bg-card shadow-sm transition-all hover:shadow-md"
        >
            <div
                class="flex items-center justify-between border-b bg-muted/30 px-4 py-3"
            >
                <div class="flex items-center gap-2">
                    <Plus class="h-4 w-4 text-primary" />
                    <Label class="text-sm font-semibold"
                        >Thêm biến thể thủ công</Label
                    >
                </div>
                <Button
                    type="button"
                    size="sm"
                    class="h-8 rounded-lg text-xs font-bold transition-all hover:bg-primary hover:text-primary-foreground"
                    :disabled="
                        Object.keys(ctx.newVariantCombo).length !==
                        ctx.form.option_groups.length
                    "
                    @click="ctx.addManualVariant"
                >
                    Thêm biến thể
                </Button>
            </div>

            <div class="p-4">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="group in ctx.form.option_groups"
                        :key="group.name"
                        class="space-y-1.5"
                    >
                        <Label
                            class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            {{ group.name }}
                        </Label>
                        <Select
                            :model-value="
                                ctx.newVariantCombo[group.namespace] ?? ''
                            "
                            @update:model-value="
                                ctx.newVariantCombo = {
                                    ...ctx.newVariantCombo,
                                    [group.namespace]: $event
                                        ? String($event)
                                        : '',
                                }
                            "
                        >
                            <SelectTrigger
                                class="h-9 rounded-lg text-sm focus:ring-primary"
                            >
                                <SelectValue placeholder="Chọn giá trị..." />
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
            </div>
        </div>

        <div v-if="ctx.form.variants.length > 0" class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <Label
                        class="text-sm font-bold tracking-widest text-muted-foreground uppercase"
                    >
                        Danh sách biến thể
                    </Label>
                    <Badge
                        variant="secondary"
                        class="rounded-full px-2 py-0 text-[10px] font-bold"
                    >
                        {{ ctx.form.variants.length }}
                    </Badge>
                </div>
            </div>
            <Accordion
                type="single"
                collapsible
                v-model="activeVariantAccordion"
                class="flex flex-col gap-3"
            >
                <AccordionItem
                    v-for="(variant, vi) in ctx.form.variants"
                    :key="vi"
                    :value="String(vi)"
                    class="group/variant rounded-xl border bg-background px-4 transition-all hover:border-primary/30 hover:shadow-sm"
                >
                    <AccordionTrigger class="px-4 py-3 hover:no-underline">
                        <div class="flex w-full items-center gap-4">
                            <!-- Variant Badges -->
                            <div class="flex flex-1 flex-wrap gap-1.5">
                                <Badge
                                    v-for="(
                                        label, li
                                    ) in ctx.getVariantOptionLabels(variant)"
                                    :key="li"
                                    variant="outline"
                                    class="h-5 px-2 text-[11px] font-medium transition-colors group-hover/variant:border-primary/40 group-hover/variant:text-primary"
                                >
                                    {{ label }}
                                </Badge>
                            </div>

                            <!-- Fast Info & Actions -->
                            <div class="flex shrink-0 items-center gap-4">
                                <div
                                    class="flex flex-col items-end gap-0.5 md:flex"
                                >
                                    <span
                                        class="font-mono text-[10px] font-bold text-muted-foreground uppercase"
                                    >
                                        {{ variant.sku || 'No SKU' }}
                                    </span>
                                    <span
                                        class="text-sm font-bold text-primary"
                                    >
                                        {{
                                            variant.price
                                                ? formatPrice(variant.price)
                                                : '—'
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 transition-colors"
                                        :class="
                                            justCopied
                                                ? 'text-green-500'
                                                : 'text-muted-foreground hover:text-foreground'
                                        "
                                        @click.stop="copyVariantData(variant)"
                                    >
                                        <ClipboardCheck
                                            v-if="justCopied"
                                            class="h-4 w-4"
                                        />
                                        <Clipboard v-else class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 transition-colors"
                                        :class="
                                            copiedVariantData
                                                ? 'text-primary'
                                                : 'text-muted-foreground hover:text-foreground'
                                        "
                                        :disabled="!copiedVariantData"
                                        @click.stop="
                                            pasteVariantData(Number(vi))
                                        "
                                    >
                                        <ClipboardCheck class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-muted-foreground transition-colors hover:text-destructive"
                                        @click.stop="
                                            ctx.removeVariant(Number(vi))
                                        "
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent>
                        <div
                            class="space-y-4 rounded-2xl bg-muted/30 p-4 transition-colors group-hover/variant:bg-muted/50"
                        >
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Field class="md:col-span-2">
                                    <FieldLabel class="text-sm font-bold"
                                        >Tiêu đề biến thể</FieldLabel
                                    >
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="ctx.form.name"
                                            class="text-sm font-medium whitespace-nowrap text-muted-foreground"
                                        >
                                            {{ ctx.form.name }}
                                        </span>
                                        <Input
                                            :model-value="variant.name"
                                            @update:model-value="
                                                variant.name = $event
                                            "
                                            placeholder='VD: 48" Black Table'
                                            class="h-9 flex-1 text-sm"
                                        />
                                    </div>
                                </Field>

                                <Field>
                                    <FieldLabel
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >URL Slug</FieldLabel
                                    >
                                    <div class="relative">
                                        <Input
                                            :model-value="
                                                getVariantSlug(variant)
                                            "
                                            readonly
                                            class="h-9 border-dashed bg-background font-mono text-xs"
                                        />
                                        <div
                                            class="absolute top-1/2 right-3 -translate-y-1/2 text-[10px] font-medium text-muted-foreground"
                                        >
                                            System Generated
                                        </div>
                                    </div>
                                </Field>

                                <Field>
                                    <FieldLabel
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                        >Trạng thái</FieldLabel
                                    >
                                    <Select v-model="variant.status">
                                        <SelectTrigger class="h-9 text-sm">
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

                            <Field>
                                <FieldLabel class="text-sm font-bold"
                                    >Mô tả biến thể</FieldLabel
                                >
                                <Textarea
                                    :model-value="variant.description"
                                    @update:model-value="
                                        variant.description = $event
                                    "
                                    placeholder="Mô tả chi tiết đặc điểm của biến thể này..."
                                    class="h-24 resize-none rounded-xl text-sm"
                                />
                            </Field>

                            <!-- BUSINESS & PRICING -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <Field>
                                    <FieldLabel class="text-sm font-bold"
                                        >SKU</FieldLabel
                                    >
                                    <Input
                                        v-model="variant.sku"
                                        placeholder="VD: A3X9K2M1"
                                        class="h-9 font-mono text-sm"
                                    />
                                </Field>
                                <Field>
                                    <FieldLabel class="text-sm font-bold"
                                        >Nhãn Swatch</FieldLabel
                                    >
                                    <Input
                                        v-model="variant.swatch_label"
                                        placeholder="VD: Midnight Blue"
                                        class="h-9 text-sm"
                                    />
                                </Field>
                                <Field>
                                    <FieldLabel class="text-sm font-bold">
                                        Giá bán
                                        <span class="text-destructive">*</span>
                                    </FieldLabel>
                                    <Input
                                        :model-value="
                                            formatNumber(variant.price)
                                        "
                                        @input="
                                            (e: any) =>
                                                handleNumericInput(
                                                    e,
                                                    'price',
                                                    variant,
                                                )
                                        "
                                        placeholder="0"
                                        class="h-9 text-sm font-bold text-primary"
                                    />
                                </Field>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <Field>
                                    <FieldLabel class="text-sm font-medium"
                                        >Lợi nhuận (Giá trị)</FieldLabel
                                    >
                                    <Input
                                        :model-value="
                                            variant.profit_margin_value
                                                ? formatNumber(
                                                      variant.profit_margin_value,
                                                  )
                                                : ''
                                        "
                                        @input="
                                            (e: any) =>
                                                handleNumericInput(
                                                    e,
                                                    'profit_margin_value',
                                                    variant,
                                                )
                                        "
                                        placeholder="0"
                                        class="h-9 text-sm"
                                    />
                                </Field>
                                <Field>
                                    <FieldLabel class="text-sm font-medium"
                                        >Lợi nhuận (Đơn vị)</FieldLabel
                                    >
                                    <Select
                                        v-model="variant.profit_margin_unit"
                                    >
                                        <SelectTrigger class="h-9 text-sm">
                                            <SelectValue
                                                placeholder="Chọn đơn vị"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="fixed"
                                                >Cố định (VNĐ)</SelectItem
                                            >
                                            <SelectItem value="percentage"
                                                >Phần trăm (%)</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                </Field>
                            </div>
                        </div>

                        <Separator class="my-2" />

                        <div
                            class="space-y-6 rounded-2xl border bg-muted/20 p-4"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <Camera class="h-4 w-4 text-primary" />
                                <Label
                                    class="text-sm font-bold tracking-wider uppercase"
                                    >Trung tâm hình ảnh</Label
                                >
                            </div>

                            <!-- MAIN IMAGES GRID -->
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <!-- Primary Image -->
                                <div class="space-y-2">
                                    <Label
                                        class="text-[11px] font-bold text-muted-foreground uppercase"
                                        >Ảnh chính</Label
                                    >
                                    <div
                                        class="group relative aspect-square overflow-hidden rounded-xl border bg-background shadow-sm transition-all hover:border-primary/40"
                                    >
                                        <img
                                            v-if="
                                                variant.primary_image_url ||
                                                variant.primary_image_file
                                            "
                                            :src="
                                                variant.primary_image_file
                                                    ? createObjectURL(
                                                          variant.primary_image_file,
                                                      )
                                                    : variant.primary_image_url
                                            "
                                            class="h-full w-full cursor-pointer object-cover transition-transform group-hover:scale-105"
                                            @click="
                                                openPreview(
                                                    variant.primary_image_file
                                                        ? createObjectURL(
                                                              variant.primary_image_file,
                                                          )
                                                        : variant.primary_image_url!,
                                                )
                                            "
                                        />
                                        <label
                                            v-else
                                            class="flex h-full w-full cursor-pointer flex-col items-center justify-center gap-1 text-muted-foreground transition-colors hover:bg-muted/50"
                                        >
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="
                                                    (e) => {
                                                        const input =
                                                            e.target as HTMLInputElement;
                                                        if (input.files?.[0])
                                                            ctx.addVariantPrimaryImage(
                                                                Number(vi),
                                                                input.files[0],
                                                            );
                                                        input.value = '';
                                                    }
                                                "
                                            />
                                            <ImagePlus class="h-5 w-5" />
                                            <span
                                                class="text-[10px] font-medium"
                                                >Tải lên</span
                                            >
                                        </label>
                                        <button
                                            v-if="
                                                variant.primary_image_url ||
                                                variant.primary_image_file
                                            "
                                            @click="
                                                ctx.removeVariantPrimaryImage(
                                                    Number(vi),
                                                )
                                            "
                                            class="absolute top-1.5 right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Hover Image -->
                                <div class="space-y-2">
                                    <Label
                                        class="text-[11px] font-bold text-muted-foreground uppercase"
                                        >Ảnh hover</Label
                                    >
                                    <div
                                        class="group relative aspect-square overflow-hidden rounded-xl border bg-background shadow-sm transition-all hover:border-primary/40"
                                    >
                                        <img
                                            v-if="
                                                variant.hover_image_url ||
                                                variant.hover_image_file
                                            "
                                            :src="
                                                variant.hover_image_file
                                                    ? createObjectURL(
                                                          variant.hover_image_file,
                                                      )
                                                    : variant.hover_image_url
                                            "
                                            class="h-full w-full cursor-pointer object-cover transition-transform group-hover:scale-105"
                                            @click="
                                                openPreview(
                                                    variant.hover_image_file
                                                        ? createObjectURL(
                                                              variant.hover_image_file,
                                                          )
                                                        : variant.hover_image_url!,
                                                )
                                            "
                                        />
                                        <label
                                            v-else
                                            class="flex h-full w-full cursor-pointer flex-col items-center justify-center gap-1 text-muted-foreground transition-colors hover:bg-muted/50"
                                        >
                                            <input
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                @change="
                                                    (e) => {
                                                        const input =
                                                            e.target as HTMLInputElement;
                                                        if (input.files?.[0])
                                                            ctx.addVariantHoverImage(
                                                                Number(vi),
                                                                input.files[0],
                                                            );
                                                        input.value = '';
                                                    }
                                                "
                                            />
                                            <ImagePlus class="h-5 w-5" />
                                            <span
                                                class="text-[10px] font-medium"
                                                >Tải lên</span
                                            >
                                        </label>
                                        <button
                                            v-if="
                                                variant.hover_image_url ||
                                                variant.hover_image_file
                                            "
                                            @click="
                                                ctx.removeVariantHoverImage(
                                                    Number(vi),
                                                )
                                            "
                                            class="absolute top-1.5 right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Dimension Image -->
                                <div class="space-y-2">
                                    <Label
                                        class="text-[11px] font-bold text-muted-foreground uppercase"
                                        >Kích thước</Label
                                    >
                                    <div
                                        class="group relative aspect-square overflow-hidden rounded-xl border bg-background shadow-sm transition-all hover:border-primary/40"
                                    >
                                        <img
                                            v-if="
                                                variant.dimension_image_url ||
                                                variant.dimension_image_file
                                            "
                                            :src="
                                                variant.dimension_image_file
                                                    ? createObjectURL(
                                                          variant.dimension_image_file,
                                                      )
                                                    : variant.dimension_image_url
                                            "
                                            class="h-full w-full cursor-pointer object-cover transition-transform group-hover:scale-105"
                                            @click="
                                                openPreview(
                                                    variant.dimension_image_file
                                                        ? createObjectURL(
                                                              variant.dimension_image_file,
                                                          )
                                                        : variant.dimension_image_url!,
                                                )
                                            "
                                        />
                                        <label
                                            v-else
                                            class="flex h-full w-full cursor-pointer flex-col items-center justify-center gap-1 text-muted-foreground transition-colors hover:bg-muted/50"
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
                                            <Ruler class="h-5 w-5" />
                                            <span
                                                class="text-[10px] font-medium"
                                                >Tải lên</span
                                            >
                                        </label>
                                        <button
                                            v-if="
                                                variant.dimension_image_url ||
                                                variant.dimension_image_file
                                            "
                                            @click="
                                                ctx.removeVariantDimensionImage(
                                                    Number(vi),
                                                )
                                            "
                                            class="absolute top-1.5 right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Swatch Image -->
                                <div class="space-y-2">
                                    <Label
                                        class="text-[11px] font-bold text-muted-foreground uppercase"
                                        >Swatch</Label
                                    >
                                    <div
                                        class="group relative aspect-square overflow-hidden rounded-xl border bg-background shadow-sm transition-all hover:border-primary/40"
                                    >
                                        <img
                                            v-if="
                                                variant.swatch_image_url ||
                                                variant.swatch_image_file
                                            "
                                            :src="
                                                variant.swatch_image_file
                                                    ? createObjectURL(
                                                          variant.swatch_image_file,
                                                      )
                                                    : variant.swatch_image_url
                                            "
                                            class="h-full w-full cursor-pointer object-cover transition-transform group-hover:scale-105"
                                            @click="
                                                openPreview(
                                                    variant.swatch_image_file
                                                        ? createObjectURL(
                                                              variant.swatch_image_file,
                                                          )
                                                        : variant.swatch_image_url!,
                                                )
                                            "
                                        />
                                        <label
                                            v-else
                                            class="flex h-full w-full cursor-pointer flex-col items-center justify-center gap-1 text-muted-foreground transition-colors hover:bg-muted/50"
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
                                            <Palette class="h-5 w-5" />
                                            <span
                                                class="text-[10px] font-medium"
                                                >Tải lên</span
                                            >
                                        </label>
                                        <button
                                            v-if="
                                                variant.swatch_image_url ||
                                                variant.swatch_image_file
                                            "
                                            @click="
                                                ctx.removeVariantSwatchImage(
                                                    Number(vi),
                                                )
                                            "
                                            class="absolute top-1.5 right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3 border-t pt-4">
                                <div class="flex items-center justify-between">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-bold"
                                    >
                                        <ImagePlus class="h-4 w-4" /> Thư viện
                                        ảnh
                                    </Label>
                                    <span
                                        class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                                    >
                                        {{
                                            (variant.gallery_urls?.length ??
                                                0) +
                                            (variant.gallery_files?.length ?? 0)
                                        }}
                                        / {{ MAX_GALLERY_IMAGES }}
                                    </span>
                                </div>

                                <div
                                    class="grid grid-cols-3 gap-3 sm:grid-cols-5 md:grid-cols-8"
                                >
                                    <!-- Existing Gallery Images -->
                                    <div
                                        v-for="(
                                            img, idx
                                        ) in variant.gallery_urls"
                                        :key="img.id"
                                        class="group relative aspect-square overflow-hidden rounded-lg border bg-background shadow-sm"
                                    >
                                        <img
                                            :src="img.url"
                                            class="h-full w-full cursor-pointer object-cover transition-transform group-hover:scale-110"
                                            @click="openPreview(img.url)"
                                        />
                                        <button
                                            @click="
                                                removeGalleryImage(
                                                    Number(vi),
                                                    Number(idx),
                                                    true,
                                                )
                                            "
                                            class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>

                                    <!-- New Uploaded Gallery Images -->
                                    <div
                                        v-for="(
                                            file, idx
                                        ) in variant.gallery_files"
                                        :key="idx"
                                        class="group relative aspect-square overflow-hidden rounded-lg border bg-background shadow-sm"
                                    >
                                        <img
                                            :src="createObjectURL(file)"
                                            class="h-full w-full cursor-pointer object-cover transition-transform group-hover:scale-110"
                                            @click="
                                                openPreview(
                                                    createObjectURL(file),
                                                )
                                            "
                                        />
                                        <button
                                            @click="
                                                removeGalleryImage(
                                                    Number(vi),
                                                    Number(idx),
                                                    false,
                                                )
                                            "
                                            class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>

                                    <!-- Upload Trigger -->
                                    <label
                                        v-if="canAddMoreImages(variant)"
                                        class="group flex aspect-square cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed transition-all hover:border-primary/50 hover:bg-muted"
                                    >
                                        <input
                                            type="file"
                                            multiple
                                            accept="image/*"
                                            class="hidden"
                                            @change="
                                                handleGalleryUpload(
                                                    Number(vi),
                                                    $event,
                                                )
                                            "
                                        />
                                        <Plus
                                            class="h-5 w-5 text-muted-foreground transition-colors group-hover:text-primary"
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- FEATURES SECTION -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-2">
                                <Zap class="h-4 w-4 text-primary" />
                                <Label
                                    class="text-sm font-bold tracking-wider uppercase"
                                >
                                    Đặc điểm nổi bật
                                </Label>
                            </div>

                            <!-- 1. Lookup Selection (Actual Chips) -->
                            <div
                                v-if="ctx.featureOptions.length > 0"
                                class="space-y-2"
                            >
                                <Label
                                    class="text-[11px] font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Chọn nhanh từ tra cứu
                                </Label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="opt in ctx.featureOptions"
                                        :key="opt.slug"
                                        type="button"
                                        @click="
                                            toggleVariantFeature(
                                                opt.slug,
                                                !(variant.features || []).some(
                                                    (f: any) =>
                                                        f.display_name ===
                                                        opt.label,
                                                ),
                                                Number(vi),
                                            )
                                        "
                                        class="rounded-full border px-3 py-1 text-xs font-medium transition-all"
                                        :class="
                                            (variant.features || []).some(
                                                (f: any) =>
                                                    f.display_name ===
                                                    opt.label,
                                            )
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-input bg-background text-foreground hover:bg-muted'
                                        "
                                    >
                                        {{ opt.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Confirmed Features (Editable) -->
                            <div
                                v-if="(variant.features || []).length > 0"
                                class="space-y-3 border-t pt-4"
                            >
                                <Label
                                    class="text-[11px] font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Danh sách tính năng
                                </Label>
                                <div class="grid gap-3">
                                    <div
                                        v-for="(
                                            feature, i
                                        ) in variant.features || []"
                                        :key="i"
                                        class="group relative space-y-2 rounded-xl border bg-background p-3 transition-colors hover:border-primary/30"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Input
                                                v-model="feature.display_name"
                                                placeholder="Tên tính năng"
                                                class="h-8 text-xs font-bold"
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
                                                @click="
                                                    (
                                                        variant.features || []
                                                    ).splice(i, 1)
                                                "
                                            >
                                                <X class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                        <Textarea
                                            v-model="feature.description"
                                            placeholder="Mô tả chi tiết..."
                                            class="h-16 resize-none bg-transparent text-xs"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Manual Input (Direct logic from original) -->
                            <div
                                class="space-y-3 rounded-2xl border bg-muted/30 p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <Label
                                        class="text-[11px] font-bold tracking-tight text-muted-foreground uppercase"
                                    >
                                        Nhập tự do
                                    </Label>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-7 text-[11px] font-bold text-primary hover:bg-primary/10"
                                        @click="
                                            addVariantFeatureItem(Number(vi))
                                        "
                                    >
                                        <Plus class="mr-1 h-3 w-3" /> Thêm mục
                                    </Button>
                                </div>

                                <div
                                    v-if="
                                        getVariantFeatureItems(Number(vi))
                                            .length > 0
                                    "
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="(
                                            item, idx
                                        ) in getVariantFeatureItems(Number(vi))"
                                        :key="idx"
                                        class="space-y-2 rounded-lg border bg-background p-3"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Input
                                                v-model="item.display_name"
                                                placeholder="Tên tính năng"
                                                class="h-8 text-xs"
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
                                                @click="
                                                    removeVariantFeatureItem(
                                                        Number(vi),
                                                        idx,
                                                    )
                                                "
                                            >
                                                <X class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                        <Textarea
                                            v-model="item.description"
                                            placeholder="Mô tả chi tiết..."
                                            class="h-14 resize-none text-xs"
                                        />
                                    </div>
                                    <Button
                                        type="button"
                                        size="sm"
                                        class="h-9 w-full rounded-lg text-xs font-bold"
                                        @click="
                                            commitVariantFeatures(Number(vi))
                                        "
                                    >
                                        <Plus class="mr-1 h-3 w-3" /> Xác nhận
                                        thêm vào danh sách
                                    </Button>
                                </div>
                                <div
                                    v-else
                                    class="flex items-center justify-center py-4 text-center"
                                >
                                    <p
                                        class="text-[11px] text-muted-foreground italic"
                                    >
                                        Chưa có mục nhập tự do nào
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- SPECIFICATIONS SECTION -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <div class="flex items-center gap-2">
                                    <Ruler class="h-4 w-4 text-primary" />
                                    <Label
                                        class="text-sm font-bold tracking-wider uppercase"
                                        >Thông số kỹ thuật</Label
                                    >
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs font-bold text-primary hover:bg-primary/10"
                                    @click="openVariantSpecForm(Number(vi))"
                                >
                                    <Plus class="mr-1 h-3 w-3" /> Thêm nhóm
                                </Button>
                            </div>

                            <div
                                v-if="
                                    Object.keys(variant.specifications || {})
                                        .length === 0
                                "
                                class="rounded-xl border border-dashed py-8 text-center"
                            >
                                <p class="text-xs text-muted-foreground">
                                    Chưa có thông số kỹ thuật cho biến thể này.
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="(
                                        group, groupName
                                    ) in variant.specifications"
                                    :key="groupName"
                                    class="group/spec overflow-hidden rounded-xl border bg-card transition-all hover:border-primary/30"
                                >
                                    <div
                                        class="flex cursor-pointer items-center justify-between bg-muted/50 px-4 py-2 transition-colors hover:bg-muted/80"
                                        @click="
                                            toggleVariantSpecGroupExpand(
                                                String(groupName),
                                            )
                                        "
                                    >
                                        <div class="flex items-center gap-2">
                                            <ChevronRight
                                                class="h-3.5 w-3.5 text-muted-foreground transition-transform"
                                                :class="{
                                                    'rotate-90':
                                                        expandedVariantSpecGroups.has(
                                                            String(groupName),
                                                        ),
                                                }"
                                            />
                                            <span class="text-xs font-bold">{{
                                                String(groupName)
                                            }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-6 w-6 p-0 text-muted-foreground hover:text-primary"
                                                @click.stop="
                                                    editVariantSpecGroup(
                                                        Number(vi),
                                                        String(groupName),
                                                    )
                                                "
                                            >
                                                <Pencil class="h-3 w-3" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                                @click.stop="
                                                    removeVariantSpecGroup(
                                                        Number(vi),
                                                        String(groupName),
                                                    )
                                                "
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div
                                        v-if="
                                            expandedVariantSpecGroups.has(
                                                String(groupName),
                                            )
                                        "
                                        class="space-y-2 p-3"
                                    >
                                        <div
                                            v-for="(item, idx) in group.items"
                                            :key="idx"
                                            class="flex items-center justify-between gap-4 rounded-lg border bg-background px-3 py-2 transition-colors hover:bg-muted/30"
                                        >
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-xs font-bold text-foreground"
                                                    >{{
                                                        item.display_name
                                                    }}</span
                                                >
                                                <span
                                                    v-if="item.description"
                                                    class="text-[10px] text-muted-foreground"
                                                    >{{
                                                        item.description
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Badge
                                                    variant="secondary"
                                                    class="px-1.5 py-0 font-mono text-[10px]"
                                                >
                                                    {{
                                                        item.lookup_slug ||
                                                        'Custom'
                                                    }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARE INSTRUCTIONS SECTION -->
                        <div
                            class="mt-3 space-y-3 rounded-2xl border bg-muted/20 p-4"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Settings class="h-4 w-4 text-primary" />
                                    <Label
                                        class="text-sm font-bold tracking-wider uppercase"
                                        >Hướng dẫn bảo quản</Label
                                    >
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs font-bold text-primary hover:bg-primary/10"
                                    @click="openVariantCareForm(Number(vi))"
                                >
                                    <Plus class="mr-1 h-3 w-3" /> Thêm
                                </Button>
                            </div>

                            <div
                                v-if="variant.care_instructions?.length"
                                class="space-y-2"
                            >
                                <div
                                    v-for="(
                                        instruction, idx
                                    ) in variant.care_instructions"
                                    :key="idx"
                                    class="flex items-center justify-between gap-3 rounded-lg border bg-background px-3 py-2 shadow-sm"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
                                        >
                                            <span
                                                class="text-[10px] font-bold"
                                                >{{ Number(idx) + 1 }}</span
                                            >
                                        </div>
                                        <span class="text-xs text-foreground">{{
                                            instruction
                                        }}</span>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                        @click="
                                            removeVariantCareInstruction(
                                                Number(vi),
                                                Number(idx),
                                            )
                                        "
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="py-4 text-center">
                                <p class="text-xs text-muted-foreground italic">
                                    Chưa có hướng dẫn bảo quản.
                                </p>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>

            <!-- FLOATING SPEC FORM -->
            <div
                v-if="variantSpecEditing !== null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 p-4 backdrop-blur-sm"
                @click.self="closeVariantSpecForm"
            >
                <div
                    class="w-full max-w-2xl animate-in overflow-hidden rounded-3xl border bg-card shadow-2xl duration-200 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b bg-muted/30 px-6 py-4"
                    >
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary/10 p-1.5">
                                <Ruler class="h-5 w-5 text-primary" />
                            </div>
                            <Label class="text-lg font-bold"
                                >Cấu hình Thông số</Label
                            >
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="rounded-full"
                            @click="closeVariantSpecForm"
                        >
                            <X class="h-5 w-5" />
                        </Button>
                    </div>

                    <div class="space-y-6 p-6">
                        <!-- CLONE FROM PRODUCT BAR -->
                        <div
                            class="mb-6 rounded-xl border border-dashed bg-muted/40 p-3"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex-1 space-y-1">
                                    <Label
                                        class="text-[10px] font-bold text-muted-foreground uppercase"
                                        >Sao chép từ nhóm sản phẩm</Label
                                    >
                                    <Select
                                        v-model="selectedProductSpecGroup"
                                        @update:model-value="
                                            onSelectProductSpecGroup(
                                                String($event),
                                            )
                                        "
                                    >
                                        <SelectTrigger class="h-8 text-xs">
                                            <SelectValue
                                                placeholder="Chọn nhóm sản phẩm để sao chép..."
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="name in productSpecGroupNames"
                                                :key="name"
                                                :value="name"
                                                class="text-xs"
                                            >
                                                {{ name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Field>
                                <FieldLabel
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >Tên nhóm thông số</FieldLabel
                                >
                                <Input
                                    v-model="variantSpecGroupName"
                                    placeholder="VD: Kích thước chi tiết"
                                    class="h-10"
                                />
                            </Field>
                            <Field>
                                <FieldLabel
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >Nhóm tra cứu (Lookup)</FieldLabel
                                >
                                <Select v-model="variantSpecGroupNamespace">
                                    <SelectTrigger class="h-10">
                                        <SelectValue
                                            placeholder="Chọn nhóm tra cứu..."
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_null"
                                            >Tùy chỉnh (Custom)</SelectItem
                                        >
                                        <SelectItem
                                            v-for="ns in specNamespaces"
                                            :key="ns.namespace"
                                            :value="ns.namespace"
                                        >
                                            {{ ns.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </Field>
                        </div>

                        <div
                            v-if="variantSpecGroupNamespace !== '_null'"
                            class="flex items-center gap-2"
                        >
                            <Switch
                                v-model="variantSpecGroupIsFilterable"
                                :id="`filter-${variantSpecEditingName}`"
                            />
                            <Label
                                for="filter-{{ variantSpecEditingName }}"
                                class="text-sm"
                            >
                                Cho phép lọc (Filterable)
                            </Label>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <Label
                                    class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                                    >Giá trị chi tiết</Label
                                >
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-8 text-xs"
                                    @click="addSpecFreeItem"
                                >
                                    <Plus class="mr-1 h-3 w-3" /> Thêm tùy chỉnh
                                </Button>
                            </div>

                            <div class="grid grid-cols-1 gap-2">
                                <!-- Lookup Options -->
                                <label
                                    v-for="opt in variantSpecLookupOptions"
                                    :key="opt.slug"
                                    class="group flex items-center gap-3 rounded-xl border p-3 transition-all hover:border-primary/40 hover:bg-primary/5"
                                    :class="
                                        variantSpecSelectedValues.includes(
                                            opt.slug,
                                        )
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
                                            : 'bg-card'
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
                                        class="h-4 w-4"
                                    />
                                    <div class="flex flex-1 items-center gap-2">
                                        <span
                                            class="text-sm font-medium whitespace-nowrap"
                                            >{{ opt.label }}</span
                                        >
                                        <Input
                                            v-if="
                                                variantSpecSelectedValues.includes(
                                                    opt.slug,
                                                )
                                            "
                                            v-model="
                                                variantSpecValueDescriptions[
                                                    opt.slug
                                                ]
                                            "
                                            placeholder="Mô tả chi tiết..."
                                            class="h-8 flex-1 px-2 py-1 text-xs"
                                        />
                                    </div>
                                </label>

                                <!-- Free Items -->
                                <div
                                    v-for="(item, idx) in variantSpecFreeItems"
                                    :key="idx"
                                    class="space-y-2 rounded-xl border bg-background p-3 transition-colors hover:border-primary/30"
                                >
                                    <div class="flex items-center gap-2">
                                        <Input
                                            v-model="item.display_name"
                                            placeholder="Tên giá trị..."
                                            class="h-9 flex-1 text-sm font-bold"
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
                                            @click="
                                                variantSpecFreeItems.splice(
                                                    idx,
                                                    1,
                                                )
                                            "
                                        >
                                            <X class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                    <Textarea
                                        v-model="item.description"
                                        placeholder="Mô tả chi tiết cho giá trị này..."
                                        class="h-16 resize-none text-xs"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4"
                    >
                        <Button
                            variant="ghost"
                            class="rounded-xl"
                            @click="closeVariantSpecForm"
                            >Hủy bỏ</Button
                        >
                        <Button
                            class="rounded-xl px-6 font-bold"
                            @click="saveVariantSpecGroup(variantSpecEditing!)"
                            >Lưu thay đổi</Button
                        >
                    </div>
                </div>
            </div>

            <!-- FLOATING CARE FORM -->
            <div
                v-if="variantCareEditing !== null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 p-4 backdrop-blur-sm"
                @click.self="closeVariantCareForm"
            >
                <div
                    class="w-full max-w-md animate-in overflow-hidden rounded-3xl border bg-card shadow-2xl duration-200 fade-in zoom-in"
                >
                    <div
                        class="flex items-center justify-between border-b bg-muted/30 px-6 py-4"
                    >
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary/10 p-1.5">
                                <Settings class="h-5 w-5 text-primary" />
                            </div>
                            <Label class="text-lg font-bold"
                                >Hướng dẫn bảo quản</Label
                            >
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="rounded-full"
                            @click="closeVariantCareForm"
                        >
                            <X class="h-5 w-5" />
                        </Button>
                    </div>

                    <div class="space-y-4 p-6">
                        <div class="flex gap-2">
                            <Input
                                v-model="variantCareInstruction"
                                placeholder="Nhập hướng dẫn bảo quản..."
                                class="h-10 flex-1"
                                @keyup.enter="
                                    addVariantCareInstruction(
                                        variantCareEditing!,
                                    )
                                "
                            />
                            <Button
                                type="button"
                                class="rounded-xl px-4 font-bold"
                                @click="
                                    addVariantCareInstruction(
                                        variantCareEditing!,
                                    )
                                "
                            >
                                <Plus class="h-4 w-4" />
                            </Button>
                        </div>

                        <div class="max-h-60 space-y-2 overflow-y-auto pr-2">
                            <div
                                v-for="(instr, idx) in ctx.form.variants[
                                    variantCareEditing!
                                ]?.care_instructions"
                                :key="idx"
                                class="flex items-center justify-between gap-3 rounded-lg border bg-muted/30 px-3 py-2 text-xs"
                            >
                                <span class="truncate">{{ instr }}</span>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                    @click="
                                        removeVariantCareInstruction(
                                            variantCareEditing!,
                                            Number(idx),
                                        )
                                    "
                                >
                                    <X class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4"
                    >
                        <Button
                            variant="ghost"
                            class="rounded-xl"
                            @click="closeVariantCareForm"
                            >Đóng</Button
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
