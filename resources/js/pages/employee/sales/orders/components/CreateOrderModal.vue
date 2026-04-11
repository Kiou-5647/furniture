<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Loader2,
    MapPin,
    Minus,
    Package,
    Plus,
    Search,
    Tag,
    Trash2,
    User,
    X,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
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
import { Field, FieldContent, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { store } from '@/routes/employee/sales/orders';

interface VariantOption {
    id: string;
    name: string;
    sku: string;
    price: string;
    stock_at_store: number;
    stock_total: number;
    image_url?: string;
    purchasable_type: 'variant';
}

interface BundleOption {
    id: string;
    name: string;
    price: string;
    image_url?: string;
    purchasable_type: 'bundle';
    available_at_store: boolean;
    available_system_wide: boolean;
}

type CatalogItem = VariantOption | BundleOption;

interface BundleContentItem {
    product_id: string;
    product_name: string;
    quantity: number;
    variants: { id: string; name: string; sku: string; price: string }[];
}

const props = defineProps<{
    open: boolean;
    customerOptions: { id: string; name: string; email: string; phone?: string; default_address?: any }[];
    storeLocationId: string | null;
    storeName?: string;
    catalogItems: CatalogItem[];
    bundleContents?: Record<string, BundleContentItem[]>;
    shippingMethods?: { id: string; name: string; estimated_delivery_days: number | null; price: string }[];
}>();

const emit = defineEmits(['close', 'refresh']);

const showShipping = ref(false);
const customerSearch = ref('');
const catalogSearch = ref('');
const activeCategory = ref<'all' | 'variant' | 'bundle'>('all');
const showCustomerDropdown = ref(false);

interface OrderItem {
    purchasable_type: string;
    purchasable_id: string;
    purchasable_name: string;
    quantity: number;
    unit_price: string;
    configuration: Record<string, string | number | boolean | null>;
}

interface OrderForm {
    customer_id: string;
    guest_name: string;
    guest_phone: string;
    guest_email: string;
    notes: string;
    source: string;
    store_location_id: string;
    shipping_method_id: string;
    shipping_cost: string;
    province_code: string;
    ward_code: string;
    province_name: string;
    ward_name: string;
    building: string;
    address_number: string;
    items: OrderItem[];
}

const form = useForm<OrderForm>({
    customer_id: '',
    guest_name: '',
    guest_phone: '',
    guest_email: '',
    notes: '',
    source: 'in_store',
    store_location_id: '',
    shipping_method_id: '',
    shipping_cost: '',
    province_code: '',
    ward_code: '',
    province_name: '',
    ward_name: '',
    building: '',
    address_number: '',
    items: [],
});

// Bundle variant selection dialog
const showBundleDialog = ref(false);
const selectedBundle = ref<BundleOption | null>(null);
const bundleVariantSelections = ref<Record<string, string>>({});

const selectedCustomerLabel = computed(() => {
    if (!form.customer_id) return '';
    const c = props.customerOptions.find((c) => c.id === form.customer_id);
    return c ? `${c.name} (${c.email})` : '';
});

const filteredCustomerOptions = computed(() => {
    if (customerSearch.value.length < 1) return [];
    const q = customerSearch.value.toLowerCase();
    return props.customerOptions.filter(
        (c) =>
            c.name.toLowerCase().includes(q) ||
            (c.phone && c.phone.includes(q)) ||
            c.email.toLowerCase().includes(q),
    );
});

const filteredCatalog = computed(() => {
    let items = props.catalogItems;

    if (activeCategory.value !== 'all') {
        items = items.filter((i) => i.purchasable_type === activeCategory.value);
    }

    if (catalogSearch.value) {
        const q = catalogSearch.value.toLowerCase();
        items = items.filter((i) =>
            i.name.toLowerCase().includes(q) || ('sku' in i && i.sku.toLowerCase().includes(q)),
        );
    }

    return items;
});

const totalAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.unit_price) || 0) * item.quantity, 0);
});

const grandTotal = computed(() => {
    return totalAmount.value + (parseFloat(form.shipping_cost) || 0);
});

function selectCustomer(c: { id: string; name: string; email: string; phone?: string; default_address?: any }) {
    form.customer_id = c.id;
    form.guest_name = c.name;
    form.guest_email = c.email || '';
    if (c.phone) form.guest_phone = c.phone;

    // Auto-fill address from customer's default address
    const addr = c.default_address;
    if (addr?.address_data) {
        form.province_name = addr.province_name || '';
        form.ward_name = addr.ward_name || '';
        form.building = addr.address_data?.building || '';
        form.address_number = addr.address_data?.address_number || addr.address_data?.street || '';
    }

    customerSearch.value = '';
    showCustomerDropdown.value = false;
}

function clearCustomer() {
    form.customer_id = '';
    form.guest_name = '';
    form.guest_email = '';
    form.guest_phone = '';
    customerSearch.value = '';
}

function closeCustomerDropdown() {
    setTimeout(() => {
        showCustomerDropdown.value = false;
    }, 200);
}

function isVariantOutOfStock(item: VariantOption): boolean {
    if (!showShipping.value) {
        return item.stock_at_store <= 0;
    }
    return item.stock_total <= 0;
}

function isBundleUnavailable(item: BundleOption): boolean {
    if (!showShipping.value) {
        return !item.available_at_store;
    }
    return !item.available_system_wide;
}

function addVariant(item: VariantOption) {
    if (isVariantOutOfStock(item)) return;

    form.items.push({
        purchasable_type: 'App\\Models\\Product\\ProductVariant',
        purchasable_id: item.id,
        purchasable_name: item.name,
        quantity: 1,
        unit_price: item.price,
        configuration: {},
    });
}

function addBundle(bundle: BundleOption) {
    if (isBundleUnavailable(bundle)) return;

    const contents = props.bundleContents?.[bundle.id];
    if (!contents) {
        // Bundle with no variant selection needed — add directly
        form.items.push({
            purchasable_type: 'App\\Models\\Product\\Bundle',
            purchasable_id: bundle.id,
            purchasable_name: bundle.name,
            quantity: 1,
            unit_price: bundle.price,
            configuration: {},
        });
        return;
    }

    // Open bundle variant selection dialog
    selectedBundle.value = bundle;
    bundleVariantSelections.value = {};
    contents.forEach((c) => {
        if (c.variants.length > 0) {
            bundleVariantSelections.value[c.product_id] = c.variants[0].id;
        }
    });
    showBundleDialog.value = true;
}

function confirmBundleSelection() {
    if (!selectedBundle.value) return;

    const selectedVariants = Object.entries(bundleVariantSelections.value).map(([productId, variantId]) => ({
        product_id: productId,
        variant_id: variantId,
        quantity: props.bundleContents?.[selectedBundle.value!.id]?.find((c) => c.product_id === productId)?.quantity ?? 1,
    }));

    form.items.push({
        purchasable_type: 'App\\Models\\Product\\Bundle',
        purchasable_id: selectedBundle.value.id,
        purchasable_name: selectedBundle.value.name,
        quantity: 1,
        unit_price: selectedBundle.value.price,
        configuration: { selected_variants: selectedVariants } as unknown as OrderItem['configuration'],
    });

    showBundleDialog.value = false;
    selectedBundle.value = null;
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function updateQuantity(index: number, delta: number) {
    const item = form.items[index];
    if (!item) return;
    item.quantity = Math.max(1, item.quantity + delta);
}

function submit() {
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('refresh');
            closeModal();
        },
    });
}

function closeModal() {
    form.reset();
    form.clearErrors();
    form.items = [];
    showShipping.value = false;
    emit('close');
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            form.items = [];
            showShipping.value = false;
            catalogSearch.value = '';
            customerSearch.value = '';
        }
    },
);

watch(
    () => form.source,
    () => {
        // Reset items when toggling shipping mode
        form.items = [];
    },
);

watch(
    () => form.shipping_method_id,
    (methodId) => {
        const method = (props.shippingMethods ?? []).find((m) => m.id === methodId);
        if (method) {
            form.shipping_cost = method.price;
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent class="flex h-[90vh] max-h-[90vh] flex-col gap-0 overflow-hidden p-0 sm:max-w-[1200px]">
            <DialogHeader class="shrink-0 px-6 pt-5 pb-3">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle class="text-left text-lg">
                            Tạo đơn hàng mới
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{ storeName ? `Cửa hàng: ${storeName}` : 'Chọn sản phẩm và tạo đơn hàng' }}
                        </DialogDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Label class="text-sm">Cần giao hàng</Label>
                        <Switch :model-value="showShipping" @update:model-value="showShipping = $event" />
                    </div>
                </div>
            </DialogHeader>

            <!-- Two-Panel Layout -->
            <div class="flex min-h-0 flex-1 overflow-hidden">
                <!-- Left Panel: Order Info + Items -->
                <div class="flex w-[420px] shrink-0 flex-col border-r">
                    <!-- Customer -->
                    <div class="space-y-3 border-b p-4">
                        <Field>
                            <FieldLabel>
                                <User class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                Khách hàng
                            </FieldLabel>
                            <FieldContent>
                                <div
                                    v-if="!form.customer_id"
                                    class="relative"
                                    @click.stop
                                >
                                    <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        v-model="customerSearch"
                                        placeholder="Tìm theo tên hoặc SĐT..."
                                        class="w-full pl-9 text-sm"
                                        @focus="showCustomerDropdown = true"
                                        @blur="closeCustomerDropdown"
                                    />
                                    <div
                                        v-if="showCustomerDropdown && filteredCustomerOptions.length > 0"
                                        class="absolute z-10 mt-1 w-full max-h-48 overflow-y-auto rounded-md border bg-popover shadow-md"
                                    >
                                        <div
                                            v-for="c in filteredCustomerOptions"
                                            :key="c.id"
                                            class="cursor-pointer px-3 py-2 text-sm hover:bg-muted"
                                            @click="selectCustomer(c)"
                                        >
                                            <span class="font-medium">{{ c.name }}</span>
                                            <span v-if="c.phone" class="ml-2 text-xs text-muted-foreground">{{ c.phone }}</span>
                                            <span class="ml-1 text-xs text-muted-foreground">({{ c.email }})</span>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex items-center justify-between rounded-md border bg-muted px-3 py-2"
                                >
                                    <span class="text-sm font-medium">{{ selectedCustomerLabel }}</span>
                                    <Button variant="ghost" size="icon" class="h-6 w-6" @click="clearCustomer()">
                                        <X class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <FieldError :errors="[form.errors.customer_id]" />
                            </FieldContent>
                        </Field>

                        <!-- Quick customer fields -->
                        <div class="grid grid-cols-2 gap-2">
                            <Input v-model="form.guest_name" placeholder="Họ tên" class="text-sm" />
                            <Input v-model="form.guest_phone" placeholder="SĐT" class="text-sm" />
                        </div>
                    </div>

                    <!-- Shipping Address (conditional) -->
                    <div v-if="showShipping" class="shrink-0 space-y-3 border-b p-4">
                        <div class="flex items-center gap-2 text-sm font-medium">
                            <MapPin class="h-4 w-4" /> Địa chỉ giao hàng
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <Input v-model="form.province_name" placeholder="Tỉnh/TP" class="text-sm" />
                            <Input v-model="form.ward_name" placeholder="Quận/Huyện" class="text-sm" />
                        </div>
                        <Input v-model="form.building" placeholder="Tòa nhà / Chung cư" class="text-sm" />
                        <Input v-model="form.address_number" placeholder="Số nhà / Đường" class="text-sm" />
                        <div class="grid grid-cols-2 gap-2">
                            <Field>
                                <FieldContent>
                                    <Select v-model="form.shipping_method_id">
                                        <SelectTrigger class="w-full text-sm">
                                            <SelectValue placeholder="Phương thức..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="m in shippingMethods ?? []"
                                                :key="m.id"
                                                :value="m.id"
                                            >
                                                {{ m.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </FieldContent>
                            </Field>
                            <Input v-model="form.shipping_cost" type="number" placeholder="Phí ship" class="text-sm" />
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div v-if="form.items.length === 0" class="flex h-full items-center justify-center text-sm text-muted-foreground">
                            Chưa có sản phẩm nào
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="rounded-lg border p-3"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <Badge
                                                :variant="item.purchasable_type.includes('Bundle') ? 'secondary' : 'outline'"
                                                class="text-[10px]"
                                            >
                                                {{ item.purchasable_type.includes('Bundle') ? 'Gói' : 'SP' }}
                                            </Badge>
                                            <span class="text-sm font-medium">{{ item.purchasable_name }}</span>
                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="flex items-center border rounded">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-6 w-6"
                                                    :disabled="item.quantity <= 1"
                                                    @click="updateQuantity(index, -1)"
                                                >
                                                    <Minus class="h-3 w-3" />
                                                </Button>
                                                <span class="w-8 text-center text-xs tabular-nums">{{ item.quantity }}</span>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-6 w-6"
                                                    @click="updateQuantity(index, 1)"
                                                >
                                                    <Plus class="h-3 w-3" />
                                                </Button>
                                            </div>
                                            <span class="ml-auto text-sm font-medium tabular-nums">
                                                {{ ((parseFloat(item.unit_price) || 0) * item.quantity).toLocaleString('vi-VN') }}đ
                                            </span>
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-6 w-6 shrink-0 text-destructive"
                                        @click="removeItem(index)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="shrink-0 border-t p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Tạm tính</span>
                            <span class="tabular-nums">{{ totalAmount.toLocaleString('vi-VN') }}đ</span>
                        </div>
                        <div v-if="showShipping && parseFloat(form.shipping_cost) > 0" class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Phí vận chuyển</span>
                            <span class="tabular-nums">{{ Number(form.shipping_cost).toLocaleString('vi-VN') }}đ</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-medium">Tổng cộng</span>
                            <span class="text-lg font-bold tabular-nums">{{ grandTotal.toLocaleString('vi-VN') }}đ</span>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Catalog -->
                <div class="flex flex-1 flex-col">
                    <!-- Search & Filter -->
                    <div class="shrink-0 border-b p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="catalogSearch"
                                    placeholder="Tìm theo tên hoặc SKU..."
                                    class="pl-9 text-sm"
                                />
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                :variant="activeCategory === 'all' ? 'default' : 'outline'"
                                size="sm"
                                @click="activeCategory = 'all'"
                            >
                                Tất cả
                            </Button>
                            <Button
                                :variant="activeCategory === 'variant' ? 'default' : 'outline'"
                                size="sm"
                                @click="activeCategory = 'variant'"
                            >
                                <Package class="mr-1 h-3.5 w-3.5" /> Biến thể
                            </Button>
                            <Button
                                :variant="activeCategory === 'bundle' ? 'default' : 'outline'"
                                size="sm"
                                @click="activeCategory = 'bundle'"
                            >
                                <Tag class="mr-1 h-3.5 w-3.5" /> Gói SP
                            </Button>
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
                            <div
                                v-for="item in filteredCatalog"
                                :key="item.id"
                                :class="[
                                    'cursor-pointer rounded-lg border p-3 transition-colors hover:border-primary',
                                    ((item.purchasable_type === 'variant' && isVariantOutOfStock(item as VariantOption)) || (item.purchasable_type === 'bundle' && isBundleUnavailable(item as BundleOption))) && 'opacity-50 cursor-not-allowed hover:border-border',
                                ]"
                                @click="(item as any).purchasable_type === 'variant' ? addVariant(item as VariantOption) : addBundle(item as BundleOption)"
                            >
                                <div class="flex items-start gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <Badge
                                                :variant="(item as any).purchasable_type === 'bundle' ? 'secondary' : 'outline'"
                                                class="text-[10px]"
                                            >
                                                {{ (item as any).purchasable_type === 'bundle' ? 'Gói' : 'SP' }}
                                            </Badge>
                                        </div>
                                        <p class="mt-1 text-sm font-medium truncate">{{ item.name }}</p>
                                        <p v-if="'sku' in item" class="text-[11px] text-muted-foreground font-mono">{{ (item as VariantOption).sku }}</p>

                                        <!-- Stock info -->
                                        <div v-if="'stock_at_store' in item" class="mt-1.5 text-[11px]">
                                            <span v-if="(item as VariantOption).stock_at_store > 0" class="text-green-600">
                                                Tại store: {{ (item as VariantOption).stock_at_store }}
                                            </span>
                                            <span v-else-if="showShipping && (item as VariantOption).stock_total > 0" class="text-amber-600">
                                                Hết tại store · Tổng: {{ (item as VariantOption).stock_total }}
                                            </span>
                                            <span v-else class="text-red-500">
                                                Hết hàng
                                            </span>
                                        </div>

                                        <p class="mt-1 text-sm font-bold tabular-nums">
                                            {{ Number(item.price).toLocaleString('vi-VN') }}đ
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="filteredCatalog.length === 0" class="py-12 text-center text-sm text-muted-foreground">
                            Không tìm thấy sản phẩm
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="border-t px-6 py-4">
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button type="submit" :disabled="form.processing || form.items.length === 0" @click="submit">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Tạo đơn hàng
                </Button>
            </DialogFooter>
        </DialogContent>

        <!-- Bundle Variant Selection Dialog -->
        <Dialog :open="showBundleDialog" @update:open="(val) => !val && (showBundleDialog = false)">
            <DialogContent class="max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Chọn biến thể cho gói "{{ selectedBundle?.name }}"</DialogTitle>
                    <DialogDescription>Chọn biến thể cho từng sản phẩm trong gói</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div
                        v-for="content in bundleContents?.[selectedBundle?.id ?? ''] ?? []"
                        :key="content.product_id"
                        class="rounded-lg border p-3"
                    >
                        <p class="text-sm font-medium">{{ content.product_name }} (×{{ content.quantity }})</p>
                        <select
                            v-model="bundleVariantSelections[content.product_id]"
                            class="mt-2 w-full rounded-md border bg-background px-3 py-2 text-sm"
                        >
                            <option
                                v-for="v in content.variants"
                                :key="v.id"
                                :value="v.id"
                            >
                                {{ v.name }} — {{ v.sku }} — {{ Number(v.price).toLocaleString('vi-VN') }}đ
                            </option>
                        </select>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showBundleDialog = false">
                        Hủy
                    </Button>
                    <Button @click="confirmBundleSelection">
                        Thêm vào đơn
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </Dialog>
</template>
