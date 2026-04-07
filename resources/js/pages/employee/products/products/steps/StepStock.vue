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
import { inject, ref } from 'vue';
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
    });

    closeAddDialog();
}

function removeStockEntry(variantIndex: number, locationId: string) {
    ctx.removeStockEntry(variantIndex, locationId);
}

function getLocationLabel(locationId: string): string {
    const loc = props.locationOptions.find((l) => l.id === locationId);
    return loc ? `${loc.code} - ${loc.label}` : locationId;
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
                    class="rounded-lg border px-4"
                >
                    <AccordionTrigger class="py-2.5 hover:no-underline">
                        <div class="flex w-full items-center gap-2">
                            <div
                                class="flex flex-1 flex-wrap items-center gap-2"
                            >
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
                                <Badge
                                    v-if="
                                        !ctx.getVariantOptionLabels(variant)
                                            .length
                                    "
                                    variant="outline"
                                    class="h-6 px-2 text-xs"
                                >
                                    {{
                                        variant.sku ||
                                        `Variant ${Number(vi) + 1}`
                                    }}
                                </Badge>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <Badge
                                    :variant="
                                        getVariantTotalStock(Number(vi)) > 0
                                            ? 'default'
                                            : 'destructive'
                                    "
                                    class="h-6 px-2 text-xs"
                                >
                                    {{ getVariantTotalStock(Number(vi)) }} trong
                                    kho
                                </Badge>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0"
                                    @click.stop="openAddDialog(Number(vi))"
                                    :disabled="
                                        getUsedLocationIds(Number(vi)).size >=
                                        locationOptions.length
                                    "
                                >
                                    <Plus class="h-3.5 w-3.5" />
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
                                class="rounded-md border p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <MapPin
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <span class="text-sm font-medium">
                                            {{
                                                getLocationLabel(
                                                    stock.location_id,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ stock.quantity }} units
                                            <template
                                                v-if="stock.cost_per_unit"
                                            >
                                                @
                                                {{
                                                    formatCurrency(
                                                        stock.cost_per_unit,
                                                    )
                                                }}
                                            </template>
                                        </span>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0"
                                            @click="
                                                openAdjustment(
                                                    Number(vi),
                                                    stock.location_id,
                                                )
                                            "
                                        >
                                            <Settings2 class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 w-7 p-0 text-destructive"
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

                                <!-- Adjustment Panel -->
                                <div
                                    v-if="
                                        adjustingStockIndex === Number(vi) &&
                                        adjustingLocationId ===
                                            stock.location_id
                                    "
                                    class="mt-3 rounded-md border bg-muted/30 p-3"
                                >
                                    <div
                                        class="mb-3 flex items-center justify-between"
                                    >
                                        <span class="text-sm font-medium"
                                            >Điều chỉnh</span
                                        >
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-6 w-6 p-0"
                                            @click="closeAdjustment"
                                        >
                                            <X class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>

                                    <RadioGroup
                                        v-model="adjustmentType"
                                        class="mb-3"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <RadioGroupItem
                                                    value="add"
                                                    id="adj-add"
                                                />
                                                <Label
                                                    for="adj-add"
                                                    class="text-xs"
                                                    >Thêm hàng</Label
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <RadioGroupItem
                                                    value="remove"
                                                    id="adj-remove"
                                                />
                                                <Label
                                                    for="adj-remove"
                                                    class="text-xs"
                                                    >Giảm hàng</Label
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <RadioGroupItem
                                                    value="cost"
                                                    id="adj-cost"
                                                />
                                                <Label
                                                    for="adj-cost"
                                                    class="text-xs"
                                                    >Đổi giá vốn</Label
                                                >
                                            </div>
                                        </div>
                                    </RadioGroup>

                                    <div class="space-y-2">
                                        <div
                                            v-if="
                                                adjustmentType === 'add' ||
                                                adjustmentType === 'remove'
                                            "
                                            class="grid gap-1.5"
                                        >
                                            <Label
                                                class="text-xs text-muted-foreground"
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
                                                class="text-sm"
                                            />
                                        </div>

                                        <div
                                            v-if="adjustmentType !== 'remove'"
                                            class="grid gap-1.5"
                                        >
                                            <Label
                                                class="text-xs text-muted-foreground"
                                            >
                                                Giá vốn
                                            </Label>
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
                                                class="text-sm"
                                            />
                                        </div>

                                        <div
                                            class="flex items-center justify-between rounded-md bg-background p-2 text-xs"
                                        >
                                            <span class="text-muted-foreground">
                                                Dự kiến:
                                                {{ getExpectedQuantity() }}
                                                units
                                                <template
                                                    v-if="getExpectedCost()"
                                                >
                                                    @
                                                    {{
                                                        formatCurrency(
                                                            getExpectedCost()!,
                                                        )
                                                    }}
                                                </template>
                                            </span>
                                            <span
                                                v-if="getExpectedPrice() > 0"
                                                class="font-medium text-primary"
                                            >
                                                Giá bán dự kiến:
                                                {{
                                                    formatCurrency(
                                                        getExpectedPrice(),
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                @click="closeAdjustment"
                                            >
                                                Hủy
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
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
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-amber-500" />
                        Cảnh báo thay đổi giá
                    </DialogTitle>
                    <DialogDescription>
                        Giá bán sẽ giảm do giá vốn trung bình thay đổi. Xác nhận
                        để tiếp tục.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-3 py-4">
                    <div
                        v-for="item in priceConfirmData.variants"
                        :key="item.index"
                        class="rounded-md border bg-amber-50 p-3 dark:bg-amber-950/20"
                    >
                        <p class="font-medium">
                            {{ getVariantLabel(item.index) }}
                        </p>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-muted-foreground"
                                    >Giá hiện tại:</span
                                >
                                <span class="ml-2 font-medium">{{
                                    formatCurrency(item.currentPrice)
                                }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground"
                                    >Giá mới:</span
                                >
                                <span
                                    class="ml-2 font-medium text-amber-600 dark:text-amber-400"
                                    >{{ formatCurrency(item.newPrice) }}</span
                                >
                            </div>
                            <div>
                                <span class="text-muted-foreground"
                                    >Giá vốn mới:</span
                                >
                                <span class="ml-2">{{
                                    formatCurrency(item.newCost)
                                }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground"
                                    >Giá sàn:</span
                                >
                                <span class="ml-2">{{
                                    formatCurrency(item.floorPrice)
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelPriceUpdate">
                        Giữ giá cũ
                    </Button>
                    <Button variant="destructive" @click="confirmPriceUpdate">
                        Xác nhận giảm giá
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
