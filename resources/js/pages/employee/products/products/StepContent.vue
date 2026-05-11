<script setup lang="ts">
import { parseDate } from '@internationalized/date';
import {
    X,
    Tag,
    Settings,
    Plus,
    Pencil,
    ChevronRight,
    CalendarIcon,
    Package,
    Truck,
} from '@lucide/vue';
import { computed, inject, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Field,
    FieldContent,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import type { ProductFormContext } from '@/composables/useProductForm';
import type { LookupOptionItem } from '@/composables/useProductForm';
import { formatDateOnly } from '@/lib/date-utils';
import { cn } from '@/lib';
import type { SpecItem } from '@/types';
import type { SpecNamespace } from '@/types';

const ctx = inject<ProductFormContext>('productForm')!;

const activeAccordion = ref<string | undefined>(undefined);

watch(
    () => ctx.newArrivalMonths,
    (months: number | null) => {
        if (months && ctx.form.published_date) {
            const pubDate = new Date(ctx.form.published_date);
            pubDate.setMonth(pubDate.getMonth() + months);
            ctx.form.new_arrival_until = pubDate.toISOString().split('T')[0];
        } else if (months === null) {
            ctx.form.new_arrival_until = '';
        }
    },
);

const props = defineProps<{
    vendorOptions: { id: string; label: string }[];
    categoryOptions: { id: string; label: string }[];
    collectionOptions: { id: string; label: string }[];
    product: object | null;
    featureOptions: LookupOptionItem[];
    specNamespaces: SpecNamespace[];
}>();

const newFeatureItems = ref<SpecItem[]>([]);

function addFreeFeatureItem() {
    newFeatureItems.value.push({
        lookup_slug: null,
        display_name: '',
        description: '',
    });
}

function removeFreeFeatureItem(index: number) {
    newFeatureItems.value.splice(index, 1);
}

function addFreeFeatureItems() {
    for (const item of newFeatureItems.value) {
        if (item.display_name.trim()) {
            ctx.form.features.push({ ...item });
        }
    }
    newFeatureItems.value = [];
}

function toggleFeature(slug: string, checked: boolean) {
    const opt = ctx.featureOptions.find((o) => o.slug === slug);
    if (!opt) return;

    if (checked) {
        if (
            !ctx.form.features.some(
                (f: SpecItem) => f.display_name === opt.label,
            )
        ) {
            ctx.form.features.push({
                lookup_slug: opt.slug,
                display_name: opt.label,
                description: opt.description ?? '',
            });
        }
    } else {
        const idx = ctx.form.features.findIndex(
            (f: SpecItem) => f.display_name === opt.label,
        );
        if (idx !== -1) ctx.form.features.splice(idx, 1);
    }
}

function toggleSpecValueSelection(slug: string, checked: boolean) {
    if (checked) {
        if (!ctx.specSelectedValues.includes(slug)) {
            ctx.specSelectedValues.push(slug);
        }
    } else {
        const idx = ctx.specSelectedValues.indexOf(slug);
        if (idx !== -1) ctx.specSelectedValues.splice(idx, 1);
    }
}

function getOptionLabel(slug: string): string {
    const opt = ctx.specLookupOptions.find(
        (o: { slug: string }) => o.slug === slug,
    );
    return opt?.label ?? slug;
}

function getSpecValueDescription(slug: string): string {
    return ctx.specValueDescriptions[slug] ?? '';
}

function setSpecValueDescription(slug: string, value: string) {
    ctx.specValueDescriptions[slug] = value;
}

function addFreeSpecItem() {
    ctx.specGroupItems.push({
        lookup_slug: null,
        display_name: '',
        description: '',
    });
}

function removeFreeSpecItem(index: number) {
    ctx.specGroupItems.splice(index, 1);
}

// Editing existing groups
const editingGroupName = ref<string | null | undefined>(undefined);
const expandedGroups = ref<Set<string>>(new Set());

function openNewGroupForm() {
    resetSpecForm();
    editingGroupName.value = '';
}

function startEditGroup(name: string) {
    editingGroupName.value = name;
    const group = ctx.form.specifications[name];
    if (!group) return;

    ctx.specGroupName = name;
    ctx.specGroupNamespace = group.lookup_namespace || '_null';
    ctx.specGroupIsFilterable = group.is_filterable;
    ctx.specGroupItems = group.items
        .filter((item: SpecItem) => !item.lookup_slug)
        .map((item: SpecItem) => ({ ...item }));

    if (group.lookup_namespace) {
        const opts = ctx.specLookupOptionsMap?.[group.lookup_namespace] ?? [];
        ctx.specLookupOptions = opts;
        ctx.specSelectedValues = group.items
            .filter((item: SpecItem) => item.lookup_slug)
            .map((item: SpecItem) => item.lookup_slug);
        ctx.specValueDescriptions = {};
    }

    delete ctx.form.specifications[name];
}

function cancelEditGroup() {
    if (editingGroupName.value) {
        const name = editingGroupName.value;
        const ns =
            ctx.specGroupNamespace === '_null' ? null : ctx.specGroupNamespace;
        ctx.form.specifications[name] = {
            lookup_namespace: ns,
            is_filterable: ctx.specGroupIsFilterable,
            items: buildGroupItems(),
        };
    }
    resetSpecForm();
}

function buildGroupItems(): SpecItem[] {
    const items: SpecItem[] = [];

    for (const slug of ctx.specSelectedValues) {
        const opt = ctx.specLookupOptions.find((o) => o.slug === slug);
        if (!opt) continue;
        items.push({
            lookup_slug: opt.slug,
            display_name: opt.label,
            description: ctx.specValueDescriptions[slug] ?? '',
        });
    }

    for (const item of ctx.specGroupItems) {
        if (item.display_name.trim()) {
            items.push({ ...item });
        }
    }

    return items;
}

function resetSpecForm() {
    editingGroupName.value = null;
    ctx.specGroupName = '';
    ctx.specGroupNamespace = '_null';
    ctx.specGroupIsFilterable = false;
    ctx.specGroupItems = [];
    ctx.specSelectedValues = [];
    ctx.specValueDescriptions = {};
    ctx.specLookupOptions = [];
}

function closeSpecForm() {
    editingGroupName.value = undefined;
    resetSpecForm();
}

function toggleGroupExpand(name: string) {
    if (expandedGroups.value.has(name)) {
        expandedGroups.value.delete(name);
    } else {
        expandedGroups.value.add(name);
    }
}

function setSpecGroupWithLabel(ns: string) {
    const nsObj = props.specNamespaces.find((n) => n.namespace === ns);
    ctx.setSpecGroupNamespace!(ns, nsObj!.label);
}

const fileInputRef = ref<HTMLInputElement | null>(null);
const localPreviewUrl = ref<string | null>(null);

const specLookupOptions = computed(() => {
    const map = ctx.specLookupOptionsMap;
    const ns = ctx.specGroupNamespace;
    const actualNs = ns === '_null' ? '' : ns;

    return map?.[actualNs] ?? [];
});

watch(
    () => ctx.specLookupOptionsMap,
    () => {},
    { deep: true }
);

watch(
    () => ctx.featureOptions,
    () => {},
    { deep: true }
);

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];

        if (!ctx.form.assembly_info) {
            ctx.form.assembly_info = { required: true };
        }

        ctx.form.assembly_info.manual_file = file;

        // Create a temporary URL for the browser to preview the local file
        localPreviewUrl.value = URL.createObjectURL(file);
    }
}
</script>

<template>
    <div class="space-y-6 pb-10">
        <!-- Top Header -->
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <Heading
                    title="Chi tiết sản phẩm"
                    description="Quản lý thông tin, tính năng và thông số kỹ thuật"
                />
            </div>
        </div>

        <!-- Basic Info Section -->
        <Card class="overflow-hidden shadow-sm">
            <CardHeader class="border-b bg-muted/30 pb-4">
                <CardTitle class="flex items-center gap-2 text-base">
                    <Package class="h-4 w-4 text-primary" /> Thông tin cơ bản
                </CardTitle>
            </CardHeader>
            <CardContent class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Product Name -->
                    <div class="md:col-span-2">
                        <Field>
                            <FieldLabel for="name"
                                >Tên sản phẩm
                                <span class="text-destructive"
                                    >*</span
                                ></FieldLabel
                            >
                            <FieldContent>
                                <Input
                                    id="name"
                                    v-model="ctx.form.name"
                                    placeholder="VD: Sofa Sven da thật"
                                    class="text-sm"
                                    required
                                />
                                <FieldError :errors="[ctx.form.errors.name]" />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Vendor -->
                    <div class="md:col-span-1">
                        <Field>
                            <FieldLabel>Nhà cung cấp</FieldLabel>
                            <FieldContent>
                                <Select v-model="ctx.form.vendor_id">
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue placeholder="Chọn..." />
                                    </SelectTrigger>
                                    <SelectContent
                                        class="min-w-(--radix-select-trigger-width)"
                                    >
                                        <SelectItem :value="null"
                                            >Không có</SelectItem
                                        >
                                        <SelectItem
                                            v-for="v in vendorOptions"
                                            :key="v.id"
                                            :value="v.id"
                                        >
                                            {{ v.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[ctx.form.errors.vendor_id]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Category -->
                    <div class="md:col-span-1">
                        <Field>
                            <FieldLabel
                                >Danh mục
                                <span class="text-destructive"
                                    >*</span
                                ></FieldLabel
                            >
                            <FieldContent>
                                <Select v-model="ctx.form.category_id">
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue placeholder="Chọn..." />
                                    </SelectTrigger>
                                    <SelectContent
                                        class="min-w-(--radix-select-trigger-width)"
                                    >
                                        <SelectItem
                                            v-for="c in categoryOptions"
                                            :key="c.id"
                                            :value="c.id"
                                        >
                                            {{ c.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[ctx.form.errors.category_id]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Collection -->
                    <div class="md:col-span-1">
                        <Field>
                            <FieldLabel>Bộ sưu tập</FieldLabel>
                            <FieldContent>
                                <Select v-model="ctx.form.collection_id">
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue placeholder="Chọn..." />
                                    </SelectTrigger>
                                    <SelectContent
                                        class="min-w-(--radix-select-trigger-width)"
                                    >
                                        <SelectItem
                                            v-for="c in collectionOptions"
                                            :key="c.id"
                                            :value="c.id"
                                        >
                                            {{ c.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[ctx.form.errors.collection_id]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-1">
                        <Field>
                            <FieldLabel
                                >Trạng thái
                                <span class="text-destructive"
                                    >*</span
                                ></FieldLabel
                            >
                            <FieldContent>
                                <Select v-model="ctx.form.status">
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent
                                        class="min-w-(--radix-select-trigger-width)"
                                    >
                                        <SelectItem
                                            v-for="s in ctx.statusOptions"
                                            :key="s.value"
                                            :value="s.value"
                                        >
                                            {{ s.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[ctx.form.errors.status]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Warranty -->
                    <div class="md:col-span-1">
                        <Field>
                            <FieldLabel>Bảo hành (tháng)</FieldLabel>
                            <FieldContent>
                                <Input
                                    :model-value="
                                        ctx.form.warranty_months ?? undefined
                                    "
                                    @update:model-value="
                                        ctx.form.warranty_months = $event
                                            ? Number($event)
                                            : null
                                    "
                                />
                                <FieldError
                                    :errors="[ctx.form.errors.warranty_months]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Published Date -->
                    <div class="md:col-span-1">
                        <Field>
                            <FieldLabel>Ngày xuất bản</FieldLabel>
                            <FieldContent>
                                <Popover v-slot="{ close }">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="
                                                cn(
                                                    'w-full justify-start text-left font-normal',
                                                    !ctx.form.published_date &&
                                                        'text-muted-foreground',
                                                )
                                            "
                                        >
                                            <CalendarIcon
                                                class="mr-2 h-4 w-4"
                                            />
                                            {{
                                                ctx.form.published_date
                                                    ? formatDateOnly(
                                                          ctx.form
                                                              .published_date,
                                                      )
                                                    : 'Chọn ngày'
                                            }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent
                                        class="w-auto p-0"
                                        align="start"
                                    >
                                        <Calendar
                                            :model-value="
                                                ctx.form.published_date
                                                    ? parseDate(
                                                          ctx.form
                                                              .published_date,
                                                      )
                                                    : undefined
                                            "
                                            @update:model-value="
                                                (date) => {
                                                    ctx.form.published_date =
                                                        date
                                                            ? date.toString()
                                                            : '';
                                                    close();
                                                }
                                            "
                                            layout="month-and-year"
                                            initial-focus
                                        />
                                    </PopoverContent>
                                </Popover>
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- New Arrival Until (Conditional) -->
                    <div
                        v-if="ctx.form.status === 'published'"
                        class="md:col-span-1"
                    >
                        <Field>
                            <FieldLabel>Thời gian hàng mới</FieldLabel>
                            <FieldContent>
                                <div
                                    v-if="!ctx.form.new_arrival_until"
                                    class="flex gap-2"
                                >
                                    <Select
                                        :model-value="
                                            ctx.newArrivalMonths
                                                ? String(ctx.newArrivalMonths)
                                                : ''
                                        "
                                        @update:model-value="
                                            ctx.newArrivalMonths = $event
                                                ? Number($event)
                                                : null
                                        "
                                    >
                                        <SelectTrigger class="flex w-full">
                                            <SelectValue
                                                placeholder="Chọn..."
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="m in [1, 2, 3, 6, 12]"
                                                :key="m"
                                                :value="String(m)"
                                                >{{ m }} tháng</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        :model-value="
                                            ctx.newArrivalMonths ?? ''
                                        "
                                        @update:model-value="
                                            ctx.newArrivalMonths = $event
                                                ? Number($event)
                                                : null
                                        "
                                        placeholder="Nhập"
                                        class="w-20 text-sm"
                                    />
                                </div>
                                <Popover v-else v-slot="{ close }">
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="
                                                cn(
                                                    'w-full justify-start text-left font-normal',
                                                    !ctx.form
                                                        .new_arrival_until &&
                                                        'text-muted-foreground',
                                                )
                                            "
                                        >
                                            <CalendarIcon
                                                class="mr-2 h-4 w-4"
                                            />
                                            {{
                                                ctx.form.new_arrival_until
                                                    ? formatDateOnly(
                                                          ctx.form
                                                              .new_arrival_until,
                                                      )
                                                    : 'Chọn ngày'
                                            }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent
                                        class="w-auto p-0"
                                        align="start"
                                    >
                                        <Calendar
                                            :model-value="
                                                ctx.form.new_arrival_until
                                                    ? parseDate(
                                                          ctx.form
                                                              .new_arrival_until,
                                                      )
                                                    : undefined
                                            "
                                            @update:model-value="
                                                (date) => {
                                                    ctx.form.new_arrival_until =
                                                        date
                                                            ? date.toString()
                                                            : '';
                                                    close();
                                                }
                                            "
                                            :default-placeholder="undefined"
                                            layout="month-and-year"
                                            initial-focus
                                        />
                                    </PopoverContent>
                                </Popover>
                            </FieldContent>
                        </Field>
                    </div>
                </div>
            </CardContent>
        </Card>
        <div
            v-if="ctx.form.new_arrival_until"
            class="rounded-lg bg-muted/20 py-2 text-center text-sm text-muted-foreground"
        >
            Hàng mới đến cho đến:
            {{ formatDateOnly(ctx.form.new_arrival_until) }}
        </div>
        <!-- Features Section -->
        <Accordion
            type="single"
            collapsible
            v-model="activeAccordion"
            class="space-y-4"
        >
            <AccordionItem
                value="features"
                class="overflow-hidden rounded-xl border bg-card shadow-sm"
            >
                <AccordionTrigger
                    class="px-6 py-4 transition-colors hover:bg-muted/50 hover:no-underline"
                >
                    <div class="text-md flex items-center gap-3 font-semibold">
                        <div class="rounded-md bg-primary/10 p-1.5">
                            <Tag class="h-4 w-4 text-primary" />
                        </div>
                        Tính năng nổi bật
                    </div>
                </AccordionTrigger>
                <AccordionContent class="space-y-6 p-6">
                    <!-- Lookup selection -->
                    <div v-if="ctx.featureOptions.length > 0" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Label
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Chọn từ tra cứu
                            </Label>
                            <Badge variant="secondary" class="text-[10px]">
                                {{ ctx.featureOptions.length }} tùy chọn
                            </Badge>
                        </div>
                        <div
                            class="grid grid-cols-1 gap-2 overflow-y-auto rounded-lg border bg-muted/20 p-3 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <label
                                v-for="opt in ctx.featureOptions"
                                :key="opt.id"
                                class="flex cursor-pointer items-center gap-3 rounded-md border bg-background p-3 transition-all hover:border-primary/50 hover:bg-primary/5"
                                :class="
                                    ctx.form.features.some(
                                        (f: SpecItem) =>
                                            f.display_name === opt.label,
                                    )
                                        ? 'border-primary ring-1 ring-primary/20'
                                        : ''
                                "
                            >
                                <Checkbox
                                    :model-value="
                                        ctx.form.features.some(
                                            (f: SpecItem) =>
                                                f.display_name === opt.label,
                                        )
                                    "
                                    @update:model-value="
                                        toggleFeature(
                                            opt.slug,
                                            $event as boolean,
                                        )
                                    "
                                />
                                <span class="truncate text-sm font-medium">{{
                                    opt.label
                                }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Added features list -->
                    <div v-if="ctx.form.features.length > 0" class="space-y-3">
                        <Label
                            class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            Chi tiết tính năng đã chọn
                        </Label>
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="(feature, i) in ctx.form.features"
                                :key="i"
                                class="group relative rounded-xl border bg-muted/10 p-4 transition-all hover:border-primary/30 hover:bg-muted/30"
                            >
                                <div class="mb-3 flex items-center gap-2">
                                    <Input
                                        v-model="feature.display_name"
                                        placeholder="Tên tính năng"
                                        class="h-9 border-none bg-transparent p-0 font-bold shadow-none focus:ring-offset-0 focus-visible:ring-0"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="ml-auto h-6 w-6 p-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:text-destructive"
                                        @click="ctx.removeFeature(Number(i))"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <Textarea
                                    v-model="feature.description"
                                    placeholder="Mô tả chi tiết đặc điểm nổi bật..."
                                    class="h-20 resize-none bg-background/50 text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Free-form Entry -->
                    <div class="space-y-3 border-t pt-4">
                        <div class="flex items-center justify-between">
                            <Label
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Nhập tùy chỉnh
                            </Label>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-7 text-xs"
                                @click="addFreeFeatureItem()"
                            >
                                <Plus class="mr-1 h-3 w-3" /> Thêm mục mới
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                v-for="(item, idx) in newFeatureItems"
                                :key="idx"
                                class="space-y-3 rounded-xl border bg-muted/20 p-4"
                            >
                                <div class="flex items-center gap-2">
                                    <Input
                                        v-model="item.display_name"
                                        placeholder="Tên tính năng tùy chỉnh"
                                        class="h-9"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-9 w-9 p-0 text-destructive"
                                        @click="removeFreeFeatureItem(idx)"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                                <Textarea
                                    v-model="item.description"
                                    placeholder="Mô tả chi tiết..."
                                    class="h-20 resize-none text-sm"
                                />
                            </div>
                        </div>
                        <Button
                            v-if="newFeatureItems.length > 0"
                            type="button"
                            variant="secondary"
                            size="sm"
                            class="w-full"
                            @click="addFreeFeatureItems"
                        >
                            <Plus class="mr-2 h-4 w-4" /> Lưu vào danh sách tính
                            năng
                        </Button>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Specifications Section -->
            <AccordionItem
                value="specs"
                class="overflow-hidden rounded-xl border bg-card shadow-sm"
            >
                <AccordionTrigger
                    class="px-6 py-4 transition-colors hover:bg-muted/50 hover:no-underline"
                >
                    <div class="text-md flex items-center gap-3 font-semibold">
                        <div class="rounded-md bg-primary/10 p-1.5">
                            <Settings class="h-4 w-4 text-primary" />
                        </div>
                        Thông số kỹ thuật
                    </div>
                </AccordionTrigger>
                <AccordionContent class="space-y-6 p-6">
                    <!-- Global Actions -->
                    <div
                        class="flex items-center justify-between border-b pb-4"
                    >
                        <Button
                            type="button"
                            variant="default"
                            size="sm"
                            class="text-xs"
                            @click="openNewGroupForm()"
                        >
                            <Plus class="mr-2 h-3.5 w-3.5" /> Thêm nhóm thông số
                        </Button>
                    </div>

                    <!-- Edit/Create Group Panel -->
                    <div
                        v-if="
                            editingGroupName !== undefined &&
                            editingGroupName !== null
                        "
                        class="space-y-4 rounded-xl border bg-muted/40 p-5 ring-2 ring-primary/20"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2 w-2 animate-pulse rounded-full bg-primary"
                                />
                                <Label class="text-sm font-bold">
                                    {{
                                        editingGroupName !== null
                                            ? 'Chỉnh sửa nhóm'
                                            : 'Nhóm thông số mới'
                                    }}
                                </Label>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-7 w-7 p-0"
                                @click="cancelEditGroup()"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label
                                    class="text-xs text-muted-foreground uppercase"
                                    >Tên nhóm</Label
                                >
                                <Input
                                    v-model="ctx.specGroupName"
                                    placeholder="VD: Kích thước, Chất liệu..."
                                />
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs text-muted-foreground uppercase"
                                    >Nhóm tra cứu</Label
                                >
                                <Select
                                    v-model="ctx.specGroupNamespace"
                                    @update:model-value="setSpecGroupWithLabel!"
                                >
                                    <SelectTrigger class="w-full text-sm">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_null"
                                            >Sử dụng nhập thủ công</SelectItem
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
                            </div>
                        </div>

                        <!-- Filterable Toggle -->
                        <div
                            v-if="
                                ctx.specGroupNamespace !== '_null' &&
                                ctx.specGroupNamespace !== ''
                            "
                            class="flex items-center gap-3 rounded-lg border bg-background p-3"
                        >
                            <Switch
                                id="spec_filterable"
                                v-model="ctx.specGroupIsFilterable"
                                class="h-4 w-7"
                            />
                            <div class="flex flex-col">
                                <Label
                                    for="spec_filterable"
                                    class="cursor-pointer text-sm font-medium"
                                    >Cho phép lọc sản phẩm</Label
                                >
                                <p class="text-[11px] text-muted-foreground">
                                    Khách hàng có thể dùng thông số này để tìm
                                    kiếm
                                </p>
                            </div>
                        </div>

                        <!-- Lookup Value Selection -->
                        <div
                            v-if="
                                ctx.specGroupNamespace !== '_null' &&
                                ctx.specGroupNamespace !== ''
                            "
                            class="mt-4 space-y-3"
                        >
                            <Label
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Chọn giá trị từ tra cứu:
                            </Label>
                            <div  v-if="specLookupOptions.length > 0" class="grid grid-cols-1 gap-2">
                                <div
                                    v-for="opt in specLookupOptions"
                                    :key="opt.id"
                                    class="flex items-center gap-3 rounded-lg border p-2 transition-all"
                                    :class="
                                        ctx.specSelectedValues.includes(
                                            opt.slug,
                                        )
                                            ? 'border-primary bg-primary/5'
                                            : 'bg-background'
                                    "
                                >
                                    <Checkbox
                                        :model-value="
                                            ctx.specSelectedValues.includes(
                                                opt.slug,
                                            )
                                        "
                                        @update:model-value="
                                            toggleSpecValueSelection(
                                                opt.slug,
                                                $event as boolean,
                                            )
                                        "
                                    />
                                    <div class="flex flex-1 items-center gap-2">
                                        <span class="text-sm font-medium">{{
                                            getOptionLabel(opt.slug)
                                        }}</span>
                                        <Input
                                            v-if="
                                                ctx.specSelectedValues.includes(
                                                    opt.slug,
                                                )
                                            "
                                            :model-value="
                                                getSpecValueDescription(
                                                    opt.slug,
                                                )
                                            "
                                            @update:model-value="
                                                setSpecValueDescription(
                                                    opt.slug,
                                                    $event as string,
                                                )
                                            "
                                            placeholder="Ghi chú giá trị này..."
                                            class="ml-auto h-7 w-1/2 text-xs"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Free-text items within group -->
                        <div class="space-y-3 border-t pt-4">
                            <div class="flex items-center justify-between">
                                <Label
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                    >Chi tiết bổ sung</Label
                                >
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs"
                                    @click="addFreeSpecItem()"
                                >
                                    <Plus class="mr-1 h-3 w-3" /> Thêm dòng
                                </Button>
                            </div>
                            <div
                                v-for="(item, idx) in ctx.specGroupItems"
                                :key="idx"
                                class="flex gap-2"
                            >
                                <Input
                                    v-model="item.display_name"
                                    placeholder="Tên thông số"
                                    class="h-8 flex-1 text-xs"
                                />
                                <Input
                                    v-model="item.description"
                                    placeholder="Giá trị"
                                    class="h-8 flex-1 text-xs"
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 text-destructive"
                                    @click="removeFreeSpecItem(idx)"
                                >
                                    <X class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="cancelEditGroup()"
                                >Hủy bỏ</Button
                            >
                            <Button
                                size="sm"
                                :disabled="!ctx.specGroupName.trim()"
                                @click="
                                    ctx.addSpecGroup();
                                    closeSpecForm();
                                "
                            >
                                <Plus class="mr-1 h-4 w-4" />
                                {{
                                    editingGroupName === ''
                                        ? 'Thêm nhóm'
                                        : 'Cập nhật'
                                }}
                            </Button>
                        </div>
                    </div>

                    <!-- List of Existing Groups -->
                    <div
                        class="grid grid-cols-1 items-start gap-4 md:grid-cols-2"
                    >
                        <div
                            v-for="(group, name) in ctx.form.specifications"
                            :key="name"
                            class="overflow-hidden rounded-xl border bg-card shadow-sm transition-all hover:border-primary/30"
                        >
                            <div
                                @click="toggleGroupExpand(String(name))"
                                class="flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="rounded-md bg-muted p-1.5">
                                        <Settings
                                            class="h-3.5 w-3.5 text-muted-foreground"
                                        />
                                    </div>
                                    <span class="text-sm font-bold">{{
                                        name
                                    }}</span>
                                    <Badge
                                        v-if="group.is_filterable"
                                        variant="secondary"
                                        class="px-1.5 py-0 text-[9px]"
                                        >Lọc</Badge
                                    >
                                </div>
                                <ChevronRight
                                    class="h-4 w-4 text-muted-foreground transition-transform"
                                    :class="{
                                        'rotate-90': expandedGroups.has(
                                            String(name),
                                        ),
                                    }"
                                />
                            </div>

                            <div
                                v-if="expandedGroups.has(String(name))"
                                class="space-y-3 border-t bg-muted/10 p-4 pt-0"
                            >
                                <div
                                    v-for="item in group.items"
                                    :key="item.display_name"
                                    class="flex items-center border-b border-muted py-1 text-xs last:border-0"
                                >
                                    <span
                                        class="min-w-25 text-muted-foreground"
                                    >
                                        {{ item.display_name }}
                                    </span>
                                    <span class="font-medium text-foreground">{{
                                        item.description
                                    }}</span>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 w-full text-[11px] text-primary hover:text-primary"
                                    @click="startEditGroup(String(name))"
                                >
                                    <Pencil class="mr-2 h-3 w-3" /> Chỉnh sửa
                                    nhóm
                                </Button>
                            </div>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Care Instructions Section -->
            <AccordionItem
                value="care"
                class="overflow-hidden rounded-xl border bg-card shadow-sm"
            >
                <AccordionTrigger
                    class="px-6 py-4 transition-colors hover:bg-muted/50 hover:no-underline"
                >
                    <div class="text-md flex items-center gap-3 font-semibold">
                        <div class="rounded-md bg-primary/10 p-1.5">
                            <Truck class="h-4 w-4 text-primary" />
                        </div>
                        Hướng dẫn bảo quản
                    </div>
                </AccordionTrigger>
                <AccordionContent class="p-6">
                    <div class="space-y-6">
                        <!-- Instructions Grid -->
                        <div
                            v-if="ctx.form.care_instructions.length > 0"
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="(item, i) in ctx.form.care_instructions"
                                :key="i"
                                class="group relative flex items-start gap-3 rounded-xl border bg-background p-3 transition-all hover:border-primary/40 hover:shadow-sm hover:ring-1 hover:ring-primary/10"
                            >
                                <div
                                    class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary"
                                >
                                    {{ Number(i) + 1 }}
                                </div>
                                <span
                                    class="text-sm leading-relaxed text-muted-foreground group-hover:text-foreground"
                                >
                                    {{ item }}
                                </span>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    class="absolute top-2 right-2 h-6 w-6 opacity-0 transition-all group-hover:opacity-100 hover:text-destructive"
                                    @click="
                                        ctx.removeCareInstruction(Number(i))
                                    "
                                >
                                    <X class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>

                        <!-- Add New Entry Zone -->
                        <div
                            class="rounded-2xl border-2 border-dashed border-muted-foreground/20 bg-muted/20 p-4 transition-colors focus-within:border-primary/30 focus-within:bg-muted/40"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center"
                            >
                                <div class="flex-1">
                                    <Input
                                        v-model="ctx.newCareInstruction"
                                        placeholder="Nhập hướng dẫn bảo quản mới..."
                                        @keyup.enter="ctx.addCareInstruction"
                                        class="h-10 border-none bg-transparent shadow-none focus-visible:ring-0"
                                    />
                                </div>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="h-10 px-5 font-medium transition-all hover:scale-105 active:scale-95"
                                    @click="ctx.addCareInstruction"
                                >
                                    <Plus class="mr-2 h-4 w-4" /> Thêm hướng dẫn
                                </Button>
                            </div>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Assembly Section -->
            <AccordionItem
                value="assembly"
                class="overflow-hidden rounded-xl border bg-card shadow-sm"
            >
                <AccordionTrigger
                    class="px-6 py-4 transition-colors hover:bg-muted/50 hover:no-underline"
                >
                    <div class="text-md flex items-center gap-3 font-semibold">
                        <div class="rounded-md bg-primary/10 p-1.5">
                            <Settings class="h-4 w-4 text-primary" />
                        </div>
                        Thông tin lắp ráp
                    </div>
                </AccordionTrigger>
                <AccordionContent class="p-6">
                    <div class="space-y-6">
                        <!-- Master Switch -->
                        <div
                            class="flex items-center justify-between rounded-xl p-4 transition-all"
                            :class="
                                ctx.form.assembly_info.required
                                    ? 'bg-primary/5 ring-1 ring-primary/20'
                                    : 'bg-muted/30'
                            "
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg"
                                    :class="
                                        ctx.form.assembly_info.required
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    <Settings class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-sm leading-none font-bold">
                                        Yêu cầu lắp ráp
                                    </p>
                                    <p
                                        class="mt-1 text-[11px] text-muted-foreground"
                                    >
                                        Kích hoạt để nhập chi tiết lắp đặt
                                    </p>
                                </div>
                            </div>
                            <Switch
                                id="assembly_required"
                                v-model="ctx.form.assembly_info.required"
                                class="h-5 w-9"
                            />
                        </div>

                        <!-- Config Panel -->
                        <template v-if="ctx.form.assembly_info.required">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <!-- Left Col: Basic Specs -->
                                <div
                                    class="space-y-4 rounded-2xl border bg-background p-5 shadow-sm"
                                >
                                    <div
                                        class="flex items-center gap-2 border-b pb-3"
                                    >
                                        <span
                                            class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                            >Thông số kỹ thuật</span
                                        >
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <Field class="space-y-2">
                                            <FieldLabel
                                                class="text-[11px] font-semibold"
                                                >Thời gian (phút)</FieldLabel
                                            >
                                            <FieldContent>
                                                <Input
                                                    :model-value="
                                                        ctx.form.assembly_info
                                                            .estimated_minutes ??
                                                        ''
                                                    "
                                                    @update:model-value="
                                                        ctx.form.assembly_info.estimated_minutes =
                                                            $event
                                                                ? Number($event)
                                                                : null
                                                    "
                                                    type="number"
                                                    placeholder="30"
                                                    class="h-9"
                                                />
                                            </FieldContent>
                                        </Field>
                                        <Field class="space-y-2">
                                            <FieldLabel
                                                class="text-[11px] font-semibold"
                                                >Mức độ khó</FieldLabel
                                            >
                                            <FieldContent>
                                                <Select
                                                    v-model="
                                                        ctx.form.assembly_info
                                                            .difficulty_level
                                                    "
                                                >
                                                    <SelectTrigger class="h-9">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="d in ctx.difficultyOptions"
                                                            :key="d.value"
                                                            :value="d.value"
                                                        >
                                                            {{ d.label }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </FieldContent>
                                        </Field>
                                    </div>
                                </div>

                                <!-- Right Col: Document Management -->
                                <div
                                    class="space-y-4 rounded-2xl border bg-background p-5 shadow-sm"
                                >
                                    <div
                                        class="flex items-center gap-2 border-b pb-3"
                                    >
                                        <span
                                            class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                            >Tài liệu hướng dẫn</span
                                        >
                                    </div>
                                    <div class="space-y-3">
                                        <div
                                            class="relative flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-4 transition-all hover:border-primary/40 hover:bg-primary/5"
                                        >
                                            <!-- Use native input here so ref points directly to the DOM element -->
                                            <input
                                                ref="fileInputRef"
                                                type="file"
                                                accept="application/pdf"
                                                @change="handleFileChange"
                                                class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                            />
                                            <div
                                                class="flex flex-col items-center text-center"
                                            >
                                                <div
                                                    class="mb-2 rounded-full bg-muted p-2 text-muted-foreground"
                                                >
                                                    <Plus class="h-4 w-4" />
                                                </div>
                                                <p class="text-xs font-medium">
                                                    Tải lên file PDF
                                                </p>
                                                <p
                                                    class="text-[10px] text-muted-foreground"
                                                >
                                                    Kéo thả hoặc click để chọn
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                localPreviewUrl ||
                                                ctx.form.assembly_info
                                                    .manual_url
                                            "
                                            class="flex items-center justify-between rounded-lg bg-muted/50 p-2 px-3 transition-all hover:bg-muted/80"
                                        >
                                            <div
                                                class="flex items-center gap-2 overflow-hidden"
                                            >
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded bg-primary text-[10px] font-bold text-primary-foreground"
                                                >
                                                    PDF
                                                </div>
                                                <a
                                                    :href="
                                                        localPreviewUrl ||
                                                        ctx.form.assembly_info
                                                            .manual_url
                                                    "
                                                    target="_blank"
                                                    class="truncate text-xs font-medium hover:underline"
                                                >
                                                    {{
                                                        localPreviewUrl
                                                            ? 'Xem bản nháp (Local)'
                                                            : 'Xem tài liệu hiện tại'
                                                    }}
                                                </a>
                                            </div>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                                @click="
                                                    () => {
                                                        ctx.form.assembly_info.manual_url =
                                                            null;
                                                        localPreviewUrl = null;
                                                        if (fileInputRef?.value)
                                                            fileInputRef.value =
                                                                '';
                                                    }
                                                "
                                            >
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Full Width Note -->
                            <Field class="space-y-2">
                                <FieldLabel
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >Ghi chú bổ sung</FieldLabel
                                >
                                <FieldContent>
                                    <Textarea
                                        :model-value="
                                            ctx.form.assembly_info
                                                ?.additional_information ?? ''
                                        "
                                        @update:model-value="
                                            (val) => {
                                                if (!ctx.form.assembly_info)
                                                    ctx.form.assembly_info = {
                                                        required: true,
                                                    };
                                                ctx.form.assembly_info.additional_information =
                                                    val;
                                            }
                                        "
                                        placeholder="Ví dụ: Yêu cầu sử dụng tua vít 4 cạnh, lưu ý về góc lắp đặt..."
                                        class="h-24 resize-none rounded-xl border-none bg-muted/30 text-sm focus:ring-1 focus:ring-primary/20"
                                    />
                                </FieldContent>
                            </Field>
                        </template>
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </div>
</template>
