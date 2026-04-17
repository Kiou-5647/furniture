<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { parseDate } from '@internationalized/date';
import {
    X,
    Tag,
    Truck,
    Settings,
    Plus,
    ListPlus,
    Trash2,
    Pencil,
    ChevronDown,
    ChevronRight,
    CalendarIcon,
} from '@lucide/vue';
import { computed, inject, ref, watch } from 'vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
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
import { cn } from '@/lib/utils';
import type { SpecItem } from '@/types';
import type { SpecNamespace } from '@/types';

const ctx = inject<ProductFormContext>('productForm')!;
const page = usePage();

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

const emit = defineEmits<{
    openLookupForm: [namespace: string];
}>();

const canManageLookups = computed(() => {
    const permissions = page.props.auth?.user?.permissions ?? [];
    return permissions.includes('*') || permissions.includes('lookups.manage');
});

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
</script>

<template>
    <div class="space-y-4 pb-2">
        <!-- Basic Info Section -->
        <div class="space-y-3">
            <Label class="text-lg font-semibold">Thông tin cơ bản</Label>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <div class="col-span-2 flex flex-col gap-2">
                    <Field>
                        <FieldLabel for="name"
                            >Tên sản phẩm
                            <span class="text-destructive">*</span></FieldLabel
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

                <div class="col-span-2 flex flex-col gap-2 md:col-span-1">
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
                            <FieldError :errors="[ctx.form.errors.vendor_id]" />
                        </FieldContent>
                    </Field>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <div class="flex flex-col gap-2">
                    <Field>
                        <FieldLabel
                            >Danh mục<span class="text-destructive"
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

                <div class="flex flex-col gap-2">
                    <Field>
                        <FieldLabel
                            >Bộ sưu tập<span class="text-destructive"
                                >*</span
                            ></FieldLabel
                        >
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
                <div class="flex flex-col gap-2">
                    <Field>
                        <FieldLabel
                            >Trạng thái
                            <span class="text-destructive">*</span></FieldLabel
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
                            <FieldError :errors="[ctx.form.errors.status]" />
                        </FieldContent>
                    </Field>
                </div>
                <div class="flex flex-col gap-2">
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
                                placeholder="12"
                            />
                            <FieldError
                                :errors="[ctx.form.errors.warranty_months]"
                            />
                        </FieldContent>
                    </Field>
                </div>

                <div class="col-span-2 flex flex-col gap-2 md:col-span-1">
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
                                        <CalendarIcon />
                                        {{
                                            ctx.form.published_date ??
                                            'Chọn ngày'
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
                                                      ctx.form.published_date,
                                                  )
                                                : undefined
                                        "
                                        @update:model-value="
                                            (date) => {
                                                ctx.form.published_date = date
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
                <div
                    v-if="ctx.form.status === 'published'"
                    class="col-span-2 flex flex-col gap-2 md:col-span-1"
                >
                    <Field>
                        <FieldLabel>Thời gian hàng mới</FieldLabel>
                        <FieldContent>
                            <!-- Show select + input when new_arrival_until is null -->
                            <template v-if="!ctx.form.new_arrival_until">
                                <div class="flex gap-2">
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
                                            <SelectItem value="1"
                                                >1 tháng</SelectItem
                                            >
                                            <SelectItem value="2"
                                                >2 tháng</SelectItem
                                            >
                                            <SelectItem value="3"
                                                >3 tháng</SelectItem
                                            >
                                            <SelectItem value="6"
                                                >6 tháng</SelectItem
                                            >
                                            <SelectItem value="12"
                                                >12 tháng</SelectItem
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
                            </template>
                            <!-- Always show calendar when new_arrival_until is set -->
                            <template v-else>
                                <Popover v-slot="{ close }">
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
                                            <CalendarIcon />
                                            {{
                                                ctx.form.new_arrival_until ??
                                                'Chọn ngày kết thúc'
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
                            </template>
                        </FieldContent>
                    </Field>
                </div>
            </div>

            <div
                v-if="ctx.form.new_arrival_until"
                class="text-sm text-muted-foreground"
            >
                Hàng mới đến: {{ ctx.form.new_arrival_until }}
            </div>
        </div>

        <div class="flex flex-wrap gap-4 pt-1">
            <div class="flex items-center gap-2">
                <Switch
                    id="is_featured"
                    v-model="ctx.form.is_featured"
                    class="h-4 w-7"
                />
                <Label for="is_featured">Nổi bật</Label>
            </div>
        </div>

        <!-- Content Section -->
        <Accordion
            type="single"
            collapsible
            v-model="activeAccordion"
            class="space-y-2"
        >
            <AccordionItem value="features" class="rounded-lg border px-4">
                <AccordionTrigger class="py-2.5 hover:no-underline">
                    <div class="text-md flex items-center gap-2 font-semibold">
                        <Tag class="h-4 w-4" /> Tính năng nổi bật
                    </div>
                </AccordionTrigger>
                <AccordionContent class="space-y-3">
                    <!-- Lookup selection -->
                    <div v-if="ctx.featureOptions.length > 0" class="space-y-2">
                        <Label class="text-xs text-muted-foreground"
                            >Chọn từ tra cứu:</Label
                        >
                        <div
                            class="grid max-h-60 grid-cols-1 gap-2 overflow-y-auto rounded-md border p-2 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <label
                                v-for="opt in ctx.featureOptions"
                                :key="opt.id"
                                class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 transition-all hover:bg-muted/50"
                                :class="
                                    ctx.form.features.some(
                                        (f: SpecItem) =>
                                            f.display_name === opt.label,
                                    )
                                        ? 'border-primary bg-primary/5'
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
                                <span class="truncate text-sm">{{
                                    opt.label
                                }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Added features list -->
                    <div
                        v-if="ctx.form.features.length > 0"
                        class="space-y-2 border-t pt-3"
                    >
                        <Label class="text-xs text-muted-foreground"
                            >Đã thêm:</Label
                        >
                        <div class="space-y-2">
                            <div
                                v-for="(feature, i) in ctx.form.features"
                                :key="i"
                                class="space-y-1 rounded-md border p-2"
                            >
                                <div class="flex items-center gap-2">
                                    <Input
                                        v-model="feature.display_name"
                                        placeholder="Tên tính năng"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0 text-destructive"
                                        @click="ctx.removeFeature(Number(i))"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <Textarea
                                    v-model="feature.description"
                                    placeholder="Mô tả chi tiết..."
                                    class="h-14 resize-none text-xs"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Add new free-form feature -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs text-muted-foreground"
                                >Hoặc nhập tự do:</Label
                            >
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-6 text-xs"
                                @click="addFreeFeatureItem()"
                            >
                                <Plus class="mr-1 h-3 w-3" /> Thêm mục
                            </Button>
                        </div>
                        <div
                            v-for="(item, idx) in newFeatureItems"
                            :key="idx"
                            class="space-y-1 rounded-md border p-2"
                        >
                            <div class="flex items-center gap-2">
                                <Input
                                    v-model="item.display_name"
                                    placeholder="Tên tính năng"
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0 text-destructive"
                                    @click="removeFreeFeatureItem(idx)"
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
                            v-if="newFeatureItems.length > 0"
                            type="button"
                            size="sm"
                            @click="addFreeFeatureItems"
                        >
                            <Plus class="mr-1 h-4 w-4" /> Thêm vào danh sách
                        </Button>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <AccordionItem value="specs" class="rounded-lg border px-4">
                <AccordionTrigger class="py-2.5 hover:no-underline">
                    <div class="text-md flex items-center gap-2 font-semibold">
                        <Settings class="h-4 w-4" /> Thông số kỹ thuật
                    </div>
                </AccordionTrigger>
                <AccordionContent class="space-y-3 pb-3">
                    <div class="flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="
                                emit(
                                    'openLookupForm',
                                    ctx.specGroupNamespace || 'chat-lieu',
                                )
                            "
                        >
                            <ListPlus class="mr-1 h-3.5 w-3.5" /> Quản lý tra
                            cứu
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            @click="openNewGroupForm()"
                        >
                            <Plus class="mr-1 h-4 w-4" /> Thêm nhóm
                        </Button>
                    </div>

                    <div
                        v-if="
                            editingGroupName !== undefined &&
                            editingGroupName !== null
                        "
                        class="space-y-2 rounded-md border bg-muted/30 p-3"
                    >
                        <div class="flex items-center justify-between">
                            <Label class="text-sm font-medium">
                                {{
                                    editingGroupName !== null
                                        ? 'Chỉnh sửa nhóm'
                                        : 'Nhóm mới'
                                }}
                            </Label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-6 w-6 p-0"
                                @click="cancelEditGroup()"
                            >
                                <X class="h-3.5 w-3.5" />
                            </Button>
                        </div>

                        <!-- Group name -->
                        <Input
                            v-model="ctx.specGroupName"
                            placeholder="Tên nhóm"
                        />

                        <!-- Namespace selector -->
                        <Select
                            v-model="ctx.specGroupNamespace"
                            @update:model-value="setSpecGroupWithLabel!"
                        >
                            <SelectTrigger class="w-full text-sm">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="_null"
                                    >— Không có —</SelectItem
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

                        <!-- Filterable toggle -->
                        <div
                            class="flex items-center gap-2"
                            v-if="
                                ctx.specGroupNamespace != '_null' &&
                                ctx.specGroupNamespace != ''
                            "
                        >
                            <Switch
                                id="spec_filterable"
                                v-model="ctx.specGroupIsFilterable"
                                class="h-4 w-7"
                            />
                            <Label for="spec_filterable">Cho phép lọc</Label>
                        </div>

                        <!-- Lookup value selection -->
                        <div
                            v-if="
                                ctx.specGroupNamespace != '_null' &&
                                ctx.specGroupNamespace != ''
                            "
                            class="space-y-2"
                        >
                            <Label class="text-xs text-muted-foreground"
                                >Chọn giá trị từ lookup:</Label
                            >
                            <div
                                v-if="ctx.specLookupOptions.length > 0"
                                class="flex flex-wrap gap-2"
                            >
                                <label
                                    v-for="opt in ctx.specLookupOptions"
                                    :key="opt.id"
                                    class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-1.5 text-sm transition-all hover:bg-muted/50"
                                    :class="
                                        ctx.specSelectedValues.includes(
                                            opt.slug,
                                        )
                                            ? 'border-primary bg-primary/5'
                                            : ''
                                    "
                                >
                                    <Checkbox
                                        :checked="
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
                                    <div
                                        v-if="
                                            opt.metadata?.color_hex ||
                                            opt.metadata?.hex_code
                                        "
                                        class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-gray-200 dark:border-gray-700"
                                        :style="{
                                            backgroundColor:
                                                opt.metadata?.color_hex ||
                                                opt.metadata?.hex_code,
                                        }"
                                    />
                                    <span class="truncate">{{
                                        opt.label
                                    }}</span>
                                </label>
                            </div>
                            <div
                                v-else
                                class="text-sm text-muted-foreground italic"
                            >
                                Chưa có giá trị nào.
                                <Button
                                    v-if="canManageLookups"
                                    type="button"
                                    variant="link"
                                    size="sm"
                                    class="h-auto p-0 text-xs"
                                    @click="
                                        emit(
                                            'openLookupForm',
                                            ctx.specGroupNamespace,
                                        )
                                    "
                                >
                                    Thêm mới
                                </Button>
                            </div>

                            <!-- Per-value description -->
                            <div
                                v-if="ctx.specSelectedValues.length > 0"
                                class="space-y-2"
                            >
                                <div
                                    v-for="slug in ctx.specSelectedValues"
                                    :key="slug"
                                    class="space-y-1 rounded-md border p-2"
                                >
                                    <span class="text-sm font-medium">{{
                                        getOptionLabel(slug)
                                    }}</span>
                                    <Textarea
                                        :model-value="
                                            getSpecValueDescription(slug)
                                        "
                                        @update:model-value="
                                            setSpecValueDescription(
                                                slug,
                                                String($event),
                                            )
                                        "
                                        placeholder="Mô tả cho giá trị này..."
                                        class="h-16 resize-none text-xs"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Free-typed items -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label class="text-xs text-muted-foreground"
                                    >Hoặc nhập tự do:</Label
                                >
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 text-xs"
                                    @click="addFreeSpecItem()"
                                >
                                    <Plus class="mr-1 h-3 w-3" /> Thêm mục
                                </Button>
                            </div>
                            <div
                                v-for="(item, idx) in ctx.specGroupItems"
                                :key="idx"
                                class="space-y-1 rounded-md border p-2"
                            >
                                <div class="flex items-center gap-2">
                                    <Input
                                        v-model="item.display_name"
                                        placeholder="Giá trị"
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0 text-destructive"
                                        @click="removeFreeSpecItem(idx)"
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
                        </div>

                        <!-- Save / Cancel -->
                        <div class="flex gap-2">
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
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="cancelEditGroup()"
                            >
                                Hủy
                            </Button>
                        </div>
                    </div>

                    <!-- Created groups displayed like product page -->
                    <div
                        v-if="Object.keys(ctx.form.specifications).length > 0"
                        class="space-y-2 border-t pt-3"
                    >
                        <Label class="text-xs text-muted-foreground"
                            >Đã thêm:</Label
                        >
                        <div
                            v-for="(group, groupName) in ctx.form
                                .specifications"
                            :key="groupName"
                            class="rounded-md border"
                        >
                            <!-- Group header -->
                            <div
                                class="flex cursor-pointer items-center justify-between px-3 py-2 hover:bg-muted/50"
                                @click="toggleGroupExpand(String(groupName))"
                            >
                                <div class="flex items-center gap-2">
                                    <component
                                        :is="
                                            expandedGroups.has(
                                                String(groupName),
                                            )
                                                ? ChevronDown
                                                : ChevronRight
                                        "
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-sm font-medium">{{
                                        groupName
                                    }}</span>
                                    <Badge
                                        v-if="group.is_filterable"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        Lọc được
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0"
                                        @click.stop="
                                            startEditGroup(String(groupName))
                                        "
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0 text-destructive"
                                        @click.stop="
                                            ctx.removeSpecGroup(
                                                String(groupName),
                                            )
                                        "
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Group items -->
                            <div
                                v-if="expandedGroups.has(String(groupName))"
                                class="border-t px-3 py-2"
                            >
                                <div class="space-y-1.5">
                                    <div
                                        v-for="(item, itemIdx) in group.items"
                                        :key="itemIdx"
                                        class="flex items-start gap-2 py-1"
                                    >
                                        <span class="text-sm font-medium">{{
                                            item.display_name
                                        }}</span>
                                        <span
                                            v-if="item.description"
                                            class="text-sm text-muted-foreground"
                                            >— {{ item.description }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <AccordionItem value="care" class="rounded-lg border px-4">
                <AccordionTrigger class="py-2.5 hover:no-underline">
                    <div class="text-md flex items-center gap-2 font-semibold">
                        <Truck class="h-4 w-4" /> Hướng dẫn bảo quản
                    </div>
                </AccordionTrigger>
                <AccordionContent class="space-y-2 pb-3">
                    <div
                        v-for="(item, i) in ctx.form.care_instructions"
                        :key="i"
                        class="flex items-center gap-2"
                    >
                        <span class="flex-1 text-sm">{{ item }}</span>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="h-6 w-6 p-0 text-destructive"
                            @click="ctx.removeCareInstruction(Number(i))"
                        >
                            <X class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <div class="flex gap-2">
                        <Input
                            v-model="ctx.newCareInstruction"
                            placeholder="Thêm hướng dẫn..."
                            @keyup.enter="ctx.addCareInstruction"
                        />
                        <Button
                            type="button"
                            size="sm"
                            @click="ctx.addCareInstruction"
                        >
                            <Plus class="mr-1 h-4 w-4" /> Thêm
                        </Button>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <AccordionItem value="assembly" class="rounded-lg border px-4">
                <AccordionTrigger class="py-2.5 hover:no-underline">
                    <div class="text-md flex items-center gap-2 font-semibold">
                        <Settings class="h-4 w-4" /> Lắp ráp
                    </div>
                </AccordionTrigger>
                <AccordionContent class="space-y-3 pb-4">
                    <Field orientation="horizontal">
                        <Switch
                            id="assembly_required"
                            v-model="ctx.form.assembly_info.required"
                            class="h-4 w-7"
                        />
                        <FieldLabel for="assembly_required"
                            >Yêu cầu lắp ráp</FieldLabel
                        >
                    </Field>

                    <template v-if="ctx.form.assembly_info.required">
                        <div class="grid grid-cols-2 gap-3">
                            <Field>
                                <FieldLabel
                                    >Thời gian ước tính (phút)</FieldLabel
                                >
                                <Input
                                    :model-value="
                                        ctx.form.assembly_info
                                            .estimated_minutes ?? ''
                                    "
                                    @update:model-value="
                                        ctx.form.assembly_info.estimated_minutes =
                                            $event ? Number($event) : null
                                    "
                                    type="number"
                                    min="1"
                                    placeholder="30"
                                />
                            </Field>
                            <Field>
                                <FieldLabel>Giá lắp ráp</FieldLabel>
                                <Input
                                    :model-value="
                                        ctx.form.assembly_info.price ?? ''
                                    "
                                    @update:model-value="
                                        ctx.form.assembly_info.price = $event
                                            ? Number($event)
                                            : null
                                    "
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                />
                            </Field>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <Field>
                                <FieldLabel>Mức độ khó</FieldLabel>
                                <Select
                                    v-model="
                                        ctx.form.assembly_info.difficulty_level
                                    "
                                >
                                    <SelectTrigger>
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
                            </Field>
                            <Field>
                                <FieldLabel>Link hướng dẫn (URL)</FieldLabel>
                                <Input
                                    v-model="
                                        ctx.form.assembly_info.instructions_url
                                    "
                                    placeholder="https://..."
                                />
                            </Field>
                        </div>
                        <Field>
                            <FieldLabel>Thông tin thêm</FieldLabel>
                            <Textarea
                                v-model="
                                    ctx.form.assembly_info
                                        .additional_information
                                "
                                placeholder="Thông tin bổ sung về lắp ráp..."
                                class="h-20 resize-none text-sm"
                            />
                        </Field>
                    </template>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </div>
</template>
