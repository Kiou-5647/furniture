<script setup lang="ts">
import {
    AlertTriangle,
    MapPin,
    Package,
    Plus,
    Settings2,
    Trash2,
    Warehouse,
    X,
} from '@lucide/vue';
import { inject, ref, watch } from 'vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Separator from '@/components/ui/separator/Separator.vue';
import type { ProductFormContext } from '@/composables/useProductForm';
import type { VariantStock } from '@/types/product';

const ctx = inject<ProductFormContext>('productForm')!;

const props = defineProps<{
    locationOptions: {
        id: string;
        code: string;
        label: string;
        type: string;
        address: string;
    }[];
}>();

const activeStockAccordion = ref<string | undefined>(undefined);
const showAddDialog = ref(false);
const addDialogVariantIndex = ref<number | null>(null);
const addDialogLocationId = ref('');
const addDialogQuantity = ref(1);
const addDialogCost = ref<number | null>(null);

const adjustingStockIndex = ref<number | null>(null);
const adjustingLocationId = ref<string | null>(null);
const adjustmentType = ref<'add' | 'remove' | 'cost'>('add');
const adjustmentQuantity = ref(0);
const adjustmentCost = ref<number | null>(null);
const adjustmentReason = ref<string>('');
const adjustmentNotes = ref<string>('');

watch(adjustmentType, (newType) => {
    if (newType === 'add') {
        adjustmentReason.value = 'receive';
    } else if (newType === 'remove') {
        adjustmentReason.value = 'damage';
    } else {
        adjustmentReason.value = 'adjust';
    }
});

const showPriceConfirmDialog = ref(false);
const priceConfirmData = ref<{
    hasPriceDecrease: boolean;
    variants: Array<{
        index: number;
        currentPrice: number;
        newPrice: number;
        floorPrice: number;
        newCost: number;
    }>;
}>({ hasPriceDecrease: false, variants: [] });

function getAvailableLocations(
    variantIndex: number,
    currentLocationId?: string,
): typeof props.locationOptions {
    const usedLocationIds = new Set(
        (ctx.form.variants[variantIndex]?.stock ?? [])
            .filter((s: VariantStock) => s.location_id !== currentLocationId)
            .map((s: VariantStock) => s.location_id),
    );
    return props.locationOptions.filter((loc) => !usedLocationIds.has(loc.id));
}

function getUsedLocationIds(variantIndex: number): Set<string> {
    return new Set(
        (ctx.form.variants[variantIndex]?.stock ?? []).map(
            (s: VariantStock) => s.location_id,
        ),
    );
}

function getVariantTotalStock(variantIndex: number): number {
    const variant = ctx.form.variants[variantIndex];
    if (!variant?.stock) return 0;
    return variant.stock.reduce(
        (sum: number, s: VariantStock) => sum + (s.quantity || 0),
        0,
    );
}

function openAddDialog(variantIndex: number) {
    const available = getAvailableLocations(variantIndex);
    if (available.length === 0) return;

    addDialogVariantIndex.value = variantIndex;
    addDialogLocationId.value = available[0].id;
    addDialogQuantity.value = 1;
    addDialogCost.value = null;
    showAddDialog.value = true;
}

function closeAddDialog() {
    showAddDialog.value = false;
    addDialogVariantIndex.value = null;
    addDialogLocationId.value = '';
    addDialogQuantity.value = 1;
    addDialogCost.value = null;
}

function saveAddDialog() {
    if (addDialogVariantIndex.value === null) return;

    ctx.form.variants[addDialogVariantIndex.value].stock.push({
        location_id: addDialogLocationId.value,
        quantity: addDialogQuantity.value,
        cost_per_unit: addDialogCost.value,
        movement_type: 'receive',
        movement_notes: 'Nhập kho ban đầu',
    });

    closeAddDialog();
}

function removeStockEntry(variantIndex: number, locationId: string) {
    ctx.removeStockEntry(variantIndex, locationId);
}

function getLocationInformation(locationId: string) {
    const loc = props.locationOptions.find((l) => l.id === locationId);
    return {
        label: loc?.label,
        code: loc?.code,
        address: loc?.address,
    };
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
    }).format(value);
}

function getVariantLabel(variantIndex: number): string {
    const variant = ctx.form.variants[variantIndex];
    const optionLabels = ctx.getVariantOptionLabels(variant);
    if (optionLabels.length > 0) {
        return optionLabels.join(' / ');
    }
    return variant?.sku || `Biến thể ${variantIndex + 1}`;
}

function openAdjustment(variantIndex: number, locationId: string) {
    adjustingStockIndex.value = variantIndex;
    adjustingLocationId.value = locationId;
    adjustmentType.value = 'add';
    adjustmentQuantity.value = 0;
    adjustmentReason.value = 'receive';
    adjustmentNotes.value = '';

    const variant = ctx.form.variants[variantIndex];
    const stock = variant?.stock?.find(
        (s: VariantStock) => s.location_id === locationId,
    );
    adjustmentCost.value = stock?.cost_per_unit ?? null;
}

function closeAdjustment() {
    adjustingStockIndex.value = null;
    adjustingLocationId.value = null;
    adjustmentType.value = 'add';
    adjustmentQuantity.value = 0;
    adjustmentCost.value = null;
    adjustmentReason.value = '';
    adjustmentNotes.value = '';
}

function saveAdjustment() {
    if (
        adjustingStockIndex.value === null ||
        adjustingLocationId.value === null
    )
        return;

    const variant = ctx.form.variants[adjustingStockIndex.value];
    const stock = variant?.stock?.find(
        (s: VariantStock) => s.location_id === adjustingLocationId.value,
    );
    if (!stock) return;

    if (adjustmentType.value === 'add') {
        stock.quantity += adjustmentQuantity.value;
        if (adjustmentCost.value !== null) {
            stock.cost_per_unit = adjustmentCost.value;
        }
    } else if (adjustmentType.value === 'remove') {
        stock.quantity = Math.max(0, stock.quantity - adjustmentQuantity.value);
    } else if (adjustmentType.value === 'cost') {
        if (adjustmentCost.value !== null) {
            stock.cost_per_unit = adjustmentCost.value;
        }
        adjustmentReason.value = 'adjust';
    }

    if (adjustmentQuantity.value > 0 || adjustmentType.value === 'cost') {
        stock.movement_type = adjustmentReason.value || 'adjust';
        stock.movement_notes = adjustmentNotes.value;
    }

    closeAdjustment();
}

function getExpectedQuantity(): number {
    if (
        adjustingStockIndex.value === null ||
        adjustingLocationId.value === null
    )
        return 0;

    const variant = ctx.form.variants[adjustingStockIndex.value];
    const stock = variant?.stock?.find(
        (s: VariantStock) => s.location_id === adjustingLocationId.value,
    );
    if (!stock) return 0;

    if (adjustmentType.value === 'add') {
        return stock.quantity + adjustmentQuantity.value;
    } else if (adjustmentType.value === 'remove') {
        return Math.max(0, stock.quantity - adjustmentQuantity.value);
    }
    return stock.quantity;
}

function getExpectedCost(): number | null {
    if (
        adjustingStockIndex.value === null ||
        adjustingLocationId.value === null
    )
        return null;

    if (adjustmentType.value === 'remove') return null;

    const variant = ctx.form.variants[adjustingStockIndex.value];
    const stock = variant?.stock?.find(
        (s: VariantStock) => s.location_id === adjustingLocationId.value,
    );
    if (!stock) return adjustmentCost.value;

    if (adjustmentType.value === 'add' && adjustmentCost.value !== null) {
        const oldQty = stock.quantity;
        const oldCost = stock.cost_per_unit ?? 0;
        const newQty = adjustmentQuantity.value;
        const newCost = adjustmentCost.value;

        if (oldQty > 0 && newCost > 0) {
            return (oldCost * oldQty + newCost * newQty) / (oldQty + newQty);
        }
        return newCost;
    }

    return adjustmentCost.value;
}

function getExpectedPrice(): number {
    if (adjustingStockIndex.value === null) return 0;

    const variant = ctx.form.variants[adjustingStockIndex.value];
    const marginValue = Number(variant?.profit_margin_value) || 0;
    const marginUnit = variant?.profit_margin_unit || 'fixed';
    const cost = adjustmentCost.value ?? 0;

    if (cost <= 0 || marginValue <= 0) return 0;

    if (marginUnit === 'percentage') {
        return cost * (1 + marginValue / 100);
    }
    return cost + marginValue;
}

function calculateVariantWAC(variantIndex: number): number | null {
    const variant = ctx.form.variants[variantIndex];
    if (!variant?.stock?.length) return null;

    let totalValue = 0;
    let totalQty = 0;

    for (const s of variant.stock) {
        if (s.quantity > 0 && s.cost_per_unit) {
            totalValue += s.cost_per_unit * s.quantity;
            totalQty += s.quantity;
        }
    }

    if (totalQty === 0) return null;
    return totalValue / totalQty;
}

function calculatePriceFromCost(
    cost: number,
    marginValue: number,
    marginUnit: string,
): number {
    if (marginValue <= 0) return cost;
    if (marginUnit === 'percentage') {
        return cost * (1 + marginValue / 100);
    }
    return cost + marginValue;
}

function calculateFloorPrice(
    cost: number,
    marginValue: number,
    marginUnit: string,
): number {
    if (marginValue <= 0) return 0;
    if (marginUnit === 'percentage') {
        return cost * (1 + marginValue / 100);
    }
    return cost + marginValue;
}

function checkAllVariantsForPriceDecrease(): {
    hasPriceDecrease: boolean;
    variants: Array<{
        index: number;
        currentPrice: number;
        newPrice: number;
        floorPrice: number;
        newCost: number;
    }>;
} {
    const variantsWithDecrease: Array<{
        index: number;
        currentPrice: number;
        newPrice: number;
        floorPrice: number;
        newCost: number;
    }> = [];

    for (let i = 0; i < ctx.form.variants.length; i++) {
        const variant = ctx.form.variants[i];
        const currentPrice = Number(variant?.price) || 0;
        const marginValue = Number(variant?.profit_margin_value) || 0;
        const marginUnit = variant?.profit_margin_unit || 'fixed';

        const wacCost = calculateVariantWAC(i);
        if (wacCost === null || wacCost <= 0) continue;

        const newPrice = calculatePriceFromCost(
            wacCost,
            marginValue,
            marginUnit,
        );
        const floorPrice = calculateFloorPrice(
            wacCost,
            marginValue,
            marginUnit,
        );
        const finalPrice = Math.max(newPrice, floorPrice);

        if (finalPrice < currentPrice) {
            variantsWithDecrease.push({
                index: i,
                currentPrice,
                newPrice: finalPrice,
                floorPrice,
                newCost: wacCost,
            });
        }
    }

    return {
        hasPriceDecrease: variantsWithDecrease.length > 0,
        variants: variantsWithDecrease,
    };
}

function checkPriceAndSubmit(): void {
    const priceCheck = checkAllVariantsForPriceDecrease();

    if (priceCheck.hasPriceDecrease) {
        priceConfirmData.value = priceCheck;
        showPriceConfirmDialog.value = true;
    } else {
        ctx.form._force_update_price = false;
        ctx.submit();
    }
}

function confirmPriceUpdate() {
    showPriceConfirmDialog.value = false;
    ctx.form._force_update_price = true;
    ctx.submit();
}

function cancelPriceUpdate() {
    showPriceConfirmDialog.value = false;
    ctx.form._force_update_price = false;
    ctx.submit();
}

defineExpose({ checkPriceAndSubmit });
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <Label class="text-lg font-semibold">Quản lý tồn kho</Label>
            <Badge variant="outline" class="text-xs">
                {{ ctx.form.variants.length }} biến thể
            </Badge>
        </div>

        <p class="text-sm text-muted-foreground">
            Thêm số lượng tồn kho cho từng biến thể tại các vị trí lưu trữ khác
            nhau.
        </p>

        <div
            v-if="ctx.form.variants.length === 0"
            class="rounded-lg border border-dashed p-8 text-center"
        >
            <Package class="mx-auto mb-2 h-10 w-10 text-muted-foreground" />
            <p class="text-sm text-muted-foreground">Chưa có biến thể nào</p>
            <p class="text-xs text-muted-foreground">
                Vui lòng tạo biến thể ở bước "Biến thể" trước
            </p>
        </div>

        <div v-else class="space-y-2">
            <Accordion
                type="single"
                collapsible
                v-model="activeStockAccordion"
                class="space-y-2"
            >
                <AccordionItem
                    v-for="(variant, vi) in ctx.form.variants"
                    :key="vi"
                    :value="String(vi)"
                    class="overflow-hidden rounded-xl border bg-background transition-all hover:border-primary/30"
                >
                    <AccordionTrigger class="px-4 py-3 hover:no-underline">
                        <div
                            class="flex w-full items-center justify-between gap-2"
                        >
                            <div class="flex flex-1 items-center gap-2">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="(
                                            label, li
                                        ) in ctx.getVariantOptionLabels(
                                            variant,
                                        )"
                                        :key="li"
                                        variant="secondary"
                                        class="h-5 px-2 text-[10px] font-medium"
                                    >
                                        {{ label }}
                                    </Badge>
                                    <Badge
                                        v-if="
                                            !ctx.getVariantOptionLabels(variant)
                                                .length
                                        "
                                        variant="outline"
                                        class="h-5 px-2 font-mono text-[10px]"
                                    >
                                        {{
                                            variant.sku ||
                                            `Var ${Number(vi) + 1}`
                                        }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <Badge
                                    :variant="
                                        getVariantTotalStock(Number(vi)) > 0
                                            ? 'default'
                                            : 'destructive'
                                    "
                                    class="h-6 px-2 text-xs font-bold"
                                >
                                    {{ getVariantTotalStock(Number(vi)) }} units
                                </Badge>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 w-7 p-0 text-muted-foreground hover:text-primary"
                                    @click.stop="openAddDialog(Number(vi))"
                                    :disabled="
                                        getUsedLocationIds(Number(vi)).size >=
                                        locationOptions.length
                                    "
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="space-y-4 pb-4">
                        <div
                            v-if="!variant.stock?.length"
                            class="rounded-md bg-muted/50 p-4 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                Chưa có thông tin tồn kho cho biến thể này
                            </p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="stock in variant.stock"
                                :key="stock.location_id"
                                class="group relative rounded-xl border bg-card p-3 transition-all hover:border-primary/30"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <MapPin
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <div class="flex flex-col">
                                            <div>
                                                <span
                                                    class="text-sm font-semibold"
                                                >
                                                    {{
                                                        getLocationInformation(
                                                            stock.location_id,
                                                        ).label
                                                    }}
                                                </span>
                                                -
                                                <span class="font-mono text-sm">
                                                    {{
                                                        getLocationInformation(
                                                            stock.location_id,
                                                        ).code
                                                    }}
                                                </span>
                                            </div>

                                            <span
                                                class="text-[10px] leading-tight text-muted-foreground"
                                            >
                                                {{
                                                    getLocationInformation(
                                                        stock.location_id,
                                                    ).address
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <span class="text-sm font-bold">{{
                                                stock.quantity
                                            }}</span>
                                            <span
                                                class="ml-1 text-[10px] text-muted-foreground"
                                                >units</span
                                            >
                                            <template
                                                v-if="stock.cost_per_unit"
                                            >
                                                <span
                                                    class="mx-1 text-muted-foreground"
                                                    >|</span
                                                >
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >{{
                                                        formatCurrency(
                                                            stock.cost_per_unit,
                                                        )
                                                    }}</span
                                                >
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 w-7 p-0 text-muted-foreground hover:text-primary"
                                                @click="
                                                    openAdjustment(
                                                        Number(vi),
                                                        stock.location_id,
                                                    )
                                                "
                                            >
                                                <Settings2
                                                    class="h-3.5 w-3.5"
                                                />
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
                                                @click="
                                                    removeStockEntry(
                                                        Number(vi),
                                                        stock.location_id,
                                                    )
                                                "
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adjustment Panel -->
                                <div
                                    v-if="
                                        adjustingStockIndex === Number(vi) &&
                                        adjustingLocationId ===
                                            stock.location_id
                                    "
                                    class="mt-3 space-y-4 rounded-xl border bg-muted/50 p-4 shadow-inner"
                                >
                                    <!-- 1. Header & Action Type -->
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Settings2
                                                class="h-4 w-4 text-primary"
                                            />
                                            <span
                                                class="text-sm font-bold tracking-tight"
                                                >Điều chỉnh tồn kho</span
                                            >
                                        </div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-6 w-6 rounded-full p-0"
                                            @click="closeAdjustment"
                                        >
                                            <X class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>

                                    <!-- Action Type Selector (Professional Chips) -->
                                    <RadioGroup
                                        v-model="adjustmentType"
                                        class="mb-4 grid grid-cols-3 gap-2"
                                    >
                                        <div class="relative">
                                            <RadioGroupItem
                                                value="add"
                                                id="adj-add"
                                                class="sr-only"
                                            />
                                            <Label
                                                for="adj-add"
                                                class="flex cursor-pointer items-center justify-center rounded-lg border bg-background px-3 py-2 text-xs font-medium transition-all peer-data-[state=checked]:border-primary peer-data-[state=checked]:bg-primary/10 peer-data-[state=checked]:text-primary hover:bg-muted"
                                                :class="
                                                    adjustmentType === 'add'
                                                        ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary/20'
                                                        : ''
                                                "
                                            >
                                                Thêm hàng
                                            </Label>
                                        </div>
                                        <div class="relative">
                                            <RadioGroupItem
                                                value="remove"
                                                id="adj-remove"
                                                class="sr-only"
                                            />
                                            <Label
                                                for="adj-remove"
                                                class="flex cursor-pointer items-center justify-center rounded-lg border bg-background px-3 py-2 text-xs font-medium transition-all hover:bg-muted"
                                                :class="
                                                    adjustmentType === 'remove'
                                                        ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary/20'
                                                        : ''
                                                "
                                            >
                                                Giảm hàng
                                            </Label>
                                        </div>
                                        <div class="relative">
                                            <RadioGroupItem
                                                value="cost"
                                                id="adj-cost"
                                                class="sr-only"
                                            />
                                            <Label
                                                for="adj-cost"
                                                class="flex cursor-pointer items-center justify-center rounded-lg border bg-background px-3 py-2 text-xs font-medium transition-all hover:bg-muted"
                                                :class="
                                                    adjustmentType === 'cost'
                                                        ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary/20'
                                                        : ''
                                                "
                                            >
                                                Đổi giá vốn
                                            </Label>
                                        </div>
                                    </RadioGroup>

                                    <!-- 2. Value Inputs -->
                                    <div
                                        class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                    >
                                        <div
                                            v-if="adjustmentType !== 'cost'"
                                            class="space-y-1.5"
                                        >
                                            <Label
                                                class="text-[10px] font-bold text-muted-foreground uppercase"
                                            >
                                                {{
                                                    adjustmentType === 'add'
                                                        ? 'Số lượng thêm'
                                                        : 'Số lượng giảm'
                                                }}
                                            </Label>
                                            <Input
                                                type="number"
                                                v-model.number="
                                                    adjustmentQuantity
                                                "
                                                min="1"
                                                class="h-9 text-sm"
                                            />
                                        </div>

                                        <div
                                            v-if="adjustmentType !== 'remove'"
                                            class="space-y-1.5"
                                        >
                                            <Label
                                                class="text-[10px] font-bold text-muted-foreground uppercase"
                                                >Giá vốn mới</Label
                                            >
                                            <Input
                                                type="number"
                                                :model-value="
                                                    adjustmentCost ?? ''
                                                "
                                                @update:model-value="
                                                    (val) => {
                                                        adjustmentCost = val
                                                            ? Number(val)
                                                            : null;
                                                    }
                                                "
                                                placeholder="0"
                                                min="0"
                                                step="1000"
                                                class="h-9 text-sm"
                                            />
                                        </div>
                                    </div>

                                    <!-- 3. Documentation -->
                                    <div class="space-y-3 pt-2">
                                        <div
                                            v-if="adjustmentType !== 'cost'"
                                            class="space-y-1.5"
                                        >
                                            <Label
                                                class="text-[10px] font-bold text-muted-foreground uppercase"
                                                >Lý do điều chỉnh</Label
                                            >
                                            <Select v-model="adjustmentReason">
                                                <SelectTrigger
                                                    class="h-9 text-xs"
                                                >
                                                    <SelectValue
                                                        placeholder="Chọn lý do"
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <template
                                                        v-if="
                                                            adjustmentType ===
                                                            'add'
                                                        "
                                                    >
                                                        <SelectItem
                                                            value="receive"
                                                            class="text-xs"
                                                            >Nhập kho (Nhập
                                                            mới)</SelectItem
                                                        >
                                                        <SelectItem
                                                            value="restock"
                                                            class="text-xs"
                                                            >Nhập bổ
                                                            sung</SelectItem
                                                        >
                                                        <SelectItem
                                                            value="return"
                                                            class="text-xs"
                                                            >Trả
                                                            hàng</SelectItem
                                                        >
                                                        <SelectItem
                                                            value="adjust"
                                                            class="text-xs"
                                                            >Điều chỉnh (Kiểm
                                                            kê)</SelectItem
                                                        >
                                                    </template>
                                                    <template
                                                        v-if="
                                                            adjustmentType ===
                                                            'remove'
                                                        "
                                                    >
                                                        <SelectItem
                                                            value="damage"
                                                            class="text-xs"
                                                            >Hư hỏng / Hao
                                                            hụt</SelectItem
                                                        >
                                                        <SelectItem
                                                            value="sell"
                                                            class="text-xs"
                                                            >Xuất
                                                            bán</SelectItem
                                                        >
                                                        <SelectItem
                                                            value="adjust"
                                                            class="text-xs"
                                                            >Điều chỉnh (Kiểm
                                                            kê)</SelectItem
                                                        >
                                                    </template>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div class="space-y-1.5">
                                            <Label
                                                class="text-[10px] font-bold text-muted-foreground uppercase"
                                                >Ghi chú chi tiết</Label
                                            >
                                            <Input
                                                v-model="adjustmentNotes"
                                                class="h-9 text-xs"
                                                placeholder="Ví dụ: Lô hàng bị móp méo..."
                                            />
                                        </div>
                                    </div>

                                    <!-- 4. Summary & Footer -->
                                    <div class="mt-4 space-y-3">
                                        <div
                                            class="flex items-center justify-between rounded-lg border bg-background p-3 text-xs"
                                        >
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-muted-foreground"
                                                    >Dự kiến tồn kho:</span
                                                >
                                                <span class="text-sm font-bold"
                                                    >{{
                                                        getExpectedQuantity()
                                                    }}
                                                    units</span
                                                >
                                            </div>
                                            <div
                                                v-if="getExpectedCost()"
                                                class="flex flex-col text-right"
                                            >
                                                <span
                                                    class="text-muted-foreground"
                                                    >Giá vốn mới:</span
                                                >
                                                <span
                                                    class="text-sm font-bold text-primary"
                                                    >{{
                                                        formatCurrency(
                                                            getExpectedCost()!,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                v-if="getExpectedPrice() > 0"
                                                class="flex flex-col space-y-0.5 text-right"
                                            >
                                                <div
                                                    class="flex items-center justify-end gap-1"
                                                >
                                                    <span
                                                        class="text-muted-foreground"
                                                        >Giá hiện tại:</span
                                                    >
                                                    <span
                                                        class="text-xs font-medium text-muted-foreground line-through"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                Number(
                                                                    ctx.form
                                                                        .variants[
                                                                        adjustingStockIndex!
                                                                    ].price,
                                                                ) || 0,
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="flex items-center justify-end gap-1"
                                                >
                                                    <span
                                                        class="text-muted-foreground"
                                                        >Giá bán dự kiến:</span
                                                    >
                                                    <span
                                                        class="text-sm font-bold text-green-600"
                                                    >
                                                        {{
                                                            formatCurrency(
                                                                getExpectedPrice(),
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-8 text-xs"
                                                @click="closeAdjustment"
                                            >
                                                Hủy
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                class="h-8 text-xs font-bold"
                                                @click="saveAdjustment"
                                            >
                                                Lưu điều chỉnh
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="flex justify-end">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="text-xs"
                                :disabled="
                                    getUsedLocationIds(Number(vi)).size >=
                                    locationOptions.length
                                "
                                @click="openAddDialog(Number(vi))"
                            >
                                <Plus class="mr-1 h-3 w-3" />
                                Thêm vị trí
                            </Button>
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>

        <div class="rounded-lg border bg-muted/50 p-4">
            <div class="flex items-start gap-3">
                <Warehouse
                    class="mt-0.5 h-5 w-5 shrink-0 text-muted-foreground"
                />
                <div>
                    <p class="text-sm font-medium">Mẹo</p>
                    <ul class="mt-1 text-xs text-muted-foreground">
                        <li>
                            • Giá vốn trung bình (WAC) sẽ được tính tự động từ
                            các lần nhập kho
                        </li>
                        <li>
                            • Thêm tồn kho tại các vị trí khác nhau (kho, cửa
                            hàng, nhà cung cấp)
                        </li>
                        <li>• Giá bán = Giá vốn + Lợi nhuận (tối thiểu)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Add Inventory Dialog -->
        <Dialog :open="showAddDialog" @update:open="showAddDialog = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Thêm kho mới</DialogTitle>
                    <DialogDescription>
                        Chọn vị trí và nhập thông tin tồn kho.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="grid gap-1.5">
                        <Label>Vị trí</Label>
                        <Select v-model="addDialogLocationId">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="loc in getAvailableLocations(
                                        addDialogVariantIndex ?? 0,
                                    )"
                                    :key="loc.id"
                                    :value="loc.id"
                                >
                                    {{ loc.code }} - {{ loc.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-1.5">
                        <Label>Số lượng</Label>
                        <Input
                            type="number"
                            v-model.number="addDialogQuantity"
                            min="1"
                        />
                    </div>

                    <div class="grid gap-1.5">
                        <Label>Giá vốn</Label>
                        <Input
                            type="number"
                            :model-value="addDialogCost ?? ''"
                            @update:model-value="
                                (val) => {
                                    addDialogCost = val ? Number(val) : null;
                                }
                            "
                            placeholder="0"
                            min="0"
                            step="1000"
                        />
                    </div>

                    <div
                        v-if="addDialogCost && addDialogCost > 0"
                        class="rounded-md bg-muted/50 p-2 text-xs"
                    >
                        <span class="text-muted-foreground"
                            >Giá bán dự kiến:</span
                        >
                        <span class="ml-2 font-medium text-primary">
                            {{
                                formatCurrency(
                                    (() => {
                                        const variant =
                                            ctx.form.variants[
                                                addDialogVariantIndex ?? 0
                                            ];
                                        const margin =
                                            Number(
                                                variant?.profit_margin_value,
                                            ) || 0;
                                        const unit =
                                            variant?.profit_margin_unit ||
                                            'fixed';
                                        if (margin <= 0) return 0;
                                        if (unit === 'percentage')
                                            return (
                                                addDialogCost *
                                                (1 + margin / 100)
                                            );
                                        return addDialogCost + margin;
                                    })(),
                                )
                            }}
                        </span>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="closeAddDialog">
                        Hủy
                    </Button>
                    <Button @click="saveAddDialog"> Thêm vào danh sách </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Price Decrease Confirmation Dialog -->
        <Dialog
            :open="showPriceConfirmDialog"
            @update:open="showPriceConfirmDialog = $event"
        >
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <div class="flex items-center gap-3 text-destructive">
                        <div class="rounded-full bg-destructive/10 p-2">
                            <AlertTriangle class="h-5 w-5" />
                        </div>
                        <DialogTitle class="text-xl"
                            >Cảnh báo giảm giá bán</DialogTitle
                        >
                    </div>
                    <DialogDescription class="pt-2 text-sm">
                        Việc điều chỉnh giá vốn sẽ khiến giá bán của một số biến
                        thể thấp hơn mức hiện tại để duy trì biên lợi nhuận.
                    </DialogDescription>
                </DialogHeader>

                <div class="my-6 space-y-4">
                    <!-- Impact Table -->
                    <div class="overflow-hidden rounded-xl border bg-muted/20">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b bg-muted">
                                <tr>
                                    <th
                                        class="px-4 py-2 font-bold text-muted-foreground uppercase"
                                    >
                                        Biến thể
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right font-bold text-muted-foreground uppercase"
                                    >
                                        Giá hiện tại
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right font-bold text-muted-foreground text-primary uppercase"
                                    >
                                        Giá mới
                                    </th>
                                    <th
                                        class="px-4 py-2 text-right font-bold text-destructive text-muted-foreground uppercase"
                                    >
                                        Chênh lệch
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="v in priceConfirmData.variants"
                                    :key="v.index"
                                    class="transition-colors hover:bg-muted/50"
                                >
                                    <td class="px-4 py-2 font-medium">
                                        {{
                                            ctx
                                                .getVariantOptionLabels(
                                                    ctx.form.variants[v.index],
                                                )
                                                .join(' / ') ||
                                            ctx.form.variants[v.index].sku
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-2 text-right text-muted-foreground"
                                    >
                                        {{ formatCurrency(v.currentPrice) }}
                                    </td>
                                    <td
                                        class="px-4 py-2 text-right font-bold text-primary"
                                    >
                                        {{ formatCurrency(v.newPrice) }}
                                    </td>
                                    <td
                                        class="px-4 py-2 text-right font-bold text-destructive"
                                    >
                                        {{
                                            formatCurrency(
                                                v.newPrice - v.currentPrice,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p
                        class="text-center text-[11px] text-muted-foreground italic"
                    >
                        * Giá mới được tính dựa trên Giá vốn mới + Biên lợi
                        nhuận thiết lập.
                    </p>
                </div>

                <DialogFooter class="flex gap-2 space-x-2 sm:gap-0">
                    <Button
                        type="button"
                        variant="outline"
                        class="flex-1 sm:flex-none"
                        @click="cancelPriceUpdate"
                    >
                        Hủy bỏ & Giữ giá cũ
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        class="flex-1 font-bold sm:flex-none"
                        @click="confirmPriceUpdate"
                    >
                        Xác nhận cập nhật giá
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
