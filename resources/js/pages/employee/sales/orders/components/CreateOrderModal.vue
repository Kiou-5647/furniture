<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Loader2,
    MapPin,
    Minus,
    Plus,
    Search,
    Tag,
    Trash2,
    User,
    X,
} from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
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
import {
    Field,
    FieldContent,
    FieldError,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { formatPrice } from '@/lib/utils';
import { store } from '@/routes/employee/sales/orders';

export interface VariantOption {
    id: string;
    name: string;
    sku: string;
    price: string;
    stock_at_store: number;
    stock_total: number;
    image_url?: string;
    purchasable_type: 'variant';
}

export interface BundleOption {
    id: string;
    name: string;
    price: string;
    image_url?: string;
    purchasable_type: 'bundle';
    available_at_store: boolean;
    available_system_wide: boolean;
    discount_type: 'percentage' | 'fixed_amount' | 'fixed_price';
    discount_value: number;
}

export type CatalogItem = VariantOption | BundleOption;

export interface BundleContentItem {
    id: string;
    product_card_id: string;
    product_name: string;
    quantity: number;
    variants: {
        id: string;
        sku: string;
        slug: string;
        name: string;
        price: string;
        sale_price: string;
        in_stock: boolean;
        primary_image_url?: string | null;
        swatch_image_url?: string | null;
    }[];
}

export interface OrderItem {
    purchasable_type: string;
    purchasable_id: string;
    purchasable_name: string;
    quantity: number;
    unit_price: string;
    configuration: Record<string, any>;
}

export interface OrderForm {
    customer_id: string;
    guest_name: string;
    guest_phone: string;
    guest_email: string;
    notes: string;
    source: string;
    store_location_id: string;
    shipping_method_id: string | null;
    shipping_cost: string | null;
    payment_method: string | null;
    province_code: string | undefined;
    ward_code: string | undefined;
    province_name: string | undefined;
    ward_name: string | undefined;
    street: string;
    items: OrderItem[];
}

const props = defineProps<{
    open: boolean;
    customerOptions: {
        id: string;
        name: string;
        email: string;
        phone?: string;
        default_address?: any;
    }[];
    paymentMethodOptions: { value: string; label: string }[];
    storeLocationId: string | null;
    storeName?: string;
    catalogItems: CatalogItem[];
    bundleContents?: Record<string, BundleContentItem[]>;
    shippingMethods?: {
        id: string;
        name: string;
        estimated_delivery_days: number | null;
        price: string;
    }[];
}>();

const page = usePage();

const emit = defineEmits(['close', 'refresh']);

const showShipping = ref(false);
const customerSearch = ref('');
const catalogSearch = ref('');
const activeCategory = ref<'all' | 'variant' | 'bundle'>('all');
const showCustomerDropdown = ref(false);

// Province and ward dropdowns
const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);

async function loadProvinces() {
    loadingProvinces.value = true;
    try {
        const response = await fetch('/api/geodata/provinces');
        provinces.value = await response.json();
    } catch {
        // Silently fail
    } finally {
        loadingProvinces.value = false;
    }
}

async function loadWards(provinceCode: string) {
    loadingWards.value = true;
    try {
        const response = await fetch(
            `/api/geodata/wards?province_code=${provinceCode}`,
        );
        wards.value = await response.json();
    } catch {
        // Silently fail
    } finally {
        loadingWards.value = false;
    }
}

onMounted(() => {
    loadProvinces();
});

const wardDisplayLabel = computed(() => {
    if (form.ward_name) return form.ward_name;
    if (!form.province_code) return 'Chọn Tỉnh trước';
    if (loadingWards.value) return 'Đang tải...';
    return 'Chọn Quận/Huyện';
});

const form = useForm<OrderForm>({
    customer_id: '',
    guest_name: '',
    guest_phone: '',
    guest_email: '',
    notes: '',
    source: 'in_store',
    store_location_id: props.storeLocationId ?? '',
    shipping_method_id: '',
    shipping_cost: '',
    payment_method: 'cash',
    province_code: undefined,
    ward_code: undefined,
    province_name: undefined,
    ward_name: undefined,
    street: '',
    items: [],
});

// Bundle variant selection dialog
const selectedBundle = ref<BundleOption | null>(null);
const bundleVariantSelections = ref<Record<string, string>>({});
const showBundleDialog = ref(false);

const getSelectedVariant = (content: BundleContentItem) => {
    const variantId = bundleVariantSelections.value[content.id];
    return content.variants.find((v) => v.id === variantId);
};

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
        items = items.filter(
            (i) => i.purchasable_type === activeCategory.value,
        );
    }

    if (catalogSearch.value) {
        const q = catalogSearch.value.toLowerCase();
        items = items.filter(
            (i) =>
                i.name.toLowerCase().includes(q) ||
                ('sku' in i && i.sku.toLowerCase().includes(q)),
        );
    }

    return items;
});

const totalAmount = computed(() => {
    return form.items.reduce(
        (sum, item) => sum + (parseFloat(item.unit_price) || 0) * item.quantity,
        0,
    );
});

const grandTotal = computed(() => {
    return totalAmount.value + effectiveShippingCost.value;
});

const freeshipThreshold = computed(
    () => page.props.settings?.freeship_threshold || 0,
);

const effectiveShippingCost = computed(() => {
    // Only apply free shipping if shipping is enabled AND threshold is met
    if (showShipping.value && totalAmount.value >= freeshipThreshold.value) {
        return 0;
    }
    return parseFloat(form.shipping_cost || '0');
});

const isFreeShippingActive = computed(() => {
    return showShipping.value && totalAmount.value >= freeshipThreshold.value;
});

const freeshipMessage = computed(() => {
    if (!showShipping.value) return null;

    const diff = freeshipThreshold.value - totalAmount.value;

    if (diff > 0) {
        return `Mua thêm ${diff.toLocaleString('vi-VN')}đ để được miễn phí vận chuyển`;
    }

    return 'Đã đạt ngưỡng miễn phí vận chuyển!';
});

async function selectCustomer(c: {
    id: string;
    name: string;
    email: string;
    phone?: string;
    default_address?: any;
}) {
    form.customer_id = c.id;
    form.guest_name = c.name;
    form.guest_email = c.email || '';
    if (c.phone) form.guest_phone = c.phone;

    // Auto-fill address from customer's default address
    const addr = c.default_address;
    if (addr?.address_data) {
        // Ensure provinces are loaded first
        if (provinces.value.length === 0) {
            await loadProvinces();
        }

        skipProvinceWatch.value = true;
        form.province_code = addr.province_code || '';
        form.province_name = addr.province_name || '';
        form.street = addr.address_data?.street || '';

        // Load wards for this province, then set ward code
        if (form.province_code) {
            await loadWards(form.province_code);
            form.ward_code = addr.ward_code || '';
            form.ward_name = addr.ward_name || '';
        }
        skipProvinceWatch.value = false;
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

    // Check if item already exists in cart
    const existingIndex = form.items.findIndex(
        (i) =>
            i.purchasable_type === 'App\\Models\\Product\\ProductVariant' &&
            i.purchasable_id === item.id,
    );

    if (existingIndex !== -1) {
        // Increment quantity
        form.items[existingIndex].quantity += 1;
    } else {
        form.items.push({
            purchasable_type: 'App\\Models\\Product\\ProductVariant',
            purchasable_id: item.id,
            purchasable_name: item.name,
            quantity: 1,
            unit_price: item.price,
            configuration: {},
        });
    }
}

const bundleDynamicPrice = computed(() => {
    if (!selectedBundle.value) return 0;

    let total = 0;
    const contents = props.bundleContents?.[selectedBundle.value.id] ?? [];

    contents.forEach((content) => {
        const variantId = bundleVariantSelections.value[content.id];
        const variant = content.variants.find((v) => v.id === variantId);
        total +=
            parseFloat(variant?.sale_price || variant?.price || '0') *
            content.quantity;
    });

    const bundle = selectedBundle.value;
    switch (bundle.discount_type) {
        case 'percentage':
            return Math.max(0, total * (1 - bundle.discount_value / 100));
        case 'fixed_amount':
            return Math.max(0, total - bundle.discount_value);
        case 'fixed_price':
            return bundle.discount_value;
        default:
            return total;
    }
});

function addBundle(bundle: BundleOption) {
    if (isBundleUnavailable(bundle)) return;

    const contents = props.bundleContents?.[bundle.id];
    if (!contents) return;

    selectedBundle.value = bundle;
    bundleVariantSelections.value = {};

    // Set default selections based on the first variant of each slot
    contents.forEach((c) => {
        if (c.variants.length > 0) {
            bundleVariantSelections.value[c.id] = c.variants[0].id;
        }
    });
    showBundleDialog.value = true;
}

function confirmBundleSelection() {
    if (!selectedBundle.value) return;

    const contents = props.bundleContents?.[selectedBundle.value!.id] ?? [];
    const configuration: Record<string, string> = {};
    let variantTotal = 0;

    contents.forEach((content) => {
        const variantId = bundleVariantSelections.value[content.id];
        if (variantId) {
            configuration[content.id] = variantId;
            const variant = content.variants.find((v) => v.id === variantId);
            variantTotal +=
                parseFloat(variant?.sale_price || variant?.price || '0') *
                content.quantity;
        }
    });

    const bundleData = selectedBundle.value;
    let finalPrice = variantTotal;
    if (bundleData.discount_type === 'percentage') {
        finalPrice = Math.max(
            0,
            variantTotal * (1 - bundleData.discount_value / 100),
        );
    } else if (bundleData.discount_type === 'fixed_amount') {
        finalPrice = Math.max(0, variantTotal - bundleData.discount_value);
    } else if (bundleData.discount_type === 'fixed_price') {
        finalPrice = bundleData.discount_value;
    }

    form.items.push({
        purchasable_type: 'App\\Models\\Product\\Bundle',
        purchasable_id: selectedBundle.value.id,
        purchasable_name: selectedBundle.value.name,
        quantity: 1,
        unit_price: String(finalPrice),
        configuration: configuration,
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

const skipProvinceWatch = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            form.items = [];
            showShipping.value = false;
            catalogSearch.value = '';
            customerSearch.value = '';
            provinces.value = [];
            wards.value = [];
            loadProvinces();
        }
    },
);

watch(
    () => showShipping.value,
    (newValue) => {
        if (newValue) {
            form.shipping_method_id = (props.shippingMethods ?? [])[0].id;
        } else {
            form.shipping_method_id = null;
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
        const method = (props.shippingMethods ?? []).find(
            (m) => m.id === methodId,
        );
        if (method) {
            form.shipping_cost = method.price;
        }
    },
);

watch(
    () => form.province_code,
    async (newCode) => {
        if (skipProvinceWatch.value) return;
        if (newCode) {
            const province = provinces.value.find((p) => p.value === newCode);
            form.province_name = province?.label ?? '';
            form.ward_code = '';
            form.ward_name = '';
            wards.value = [];
            await loadWards(newCode);
        }
    },
);

watch(
    () => form.ward_code,
    (newCode) => {
        if (newCode) {
            const ward = wards.value.find((w) => w.value === newCode);
            form.ward_name = ward?.label ?? '';
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="flex h-[90vh] max-h-[90vh] flex-col gap-0 overflow-hidden p-0 sm:max-w-[1200px]"
        >
            <DialogHeader class="shrink-0 px-6 pt-5 pb-3">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle class="text-left text-lg">
                            Tạo đơn hàng mới
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{
                                storeName
                                    ? `Cửa hàng: ${storeName}`
                                    : 'Chọn sản phẩm và tạo đơn hàng'
                            }}
                        </DialogDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Label class="text-sm">Cần giao hàng</Label>
                        <Switch
                            :model-value="showShipping"
                            @update:model-value="showShipping = $event"
                        />
                    </div>
                </div>
            </DialogHeader>

            <!-- Two-Panel Layout -->
            <div class="flex min-h-0 flex-1 overflow-hidden">
                <!-- Left Panel: Order Info + Items (scrollable) -->
                <div
                    class="flex w-[420px] shrink-0 flex-col overflow-y-auto border-r"
                >
                    <!-- Customer -->
                    <div class="space-y-3 border-b p-4">
                        <Field>
                            <FieldLabel>
                                <User
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Khách hàng
                            </FieldLabel>
                            <FieldContent>
                                <div
                                    v-if="!form.customer_id"
                                    class="relative"
                                    @click.stop
                                >
                                    <Search
                                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="customerSearch"
                                        placeholder="Tìm theo tên hoặc SĐT..."
                                        class="w-full pl-9 text-sm"
                                        @focus="showCustomerDropdown = true"
                                        @blur="closeCustomerDropdown"
                                    />
                                    <div
                                        v-if="
                                            showCustomerDropdown &&
                                            filteredCustomerOptions.length > 0
                                        "
                                        class="absolute z-10 mt-1 max-h-48 w-full overflow-y-auto rounded-md border bg-popover shadow-md"
                                    >
                                        <div
                                            v-for="c in filteredCustomerOptions"
                                            :key="c.id"
                                            class="cursor-pointer px-3 py-2 text-sm hover:bg-muted"
                                            @click="selectCustomer(c)"
                                        >
                                            <span class="font-medium">{{
                                                c.name
                                            }}</span>
                                            <span
                                                v-if="c.phone"
                                                class="ml-2 text-xs text-muted-foreground"
                                                >{{ c.phone }}</span
                                            >
                                            <span
                                                class="ml-1 text-xs text-muted-foreground"
                                                >({{ c.email }})</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex items-center justify-between rounded-md border bg-muted px-3 py-2"
                                >
                                    <span class="text-sm font-medium">{{
                                        selectedCustomerLabel
                                    }}</span>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-6 w-6"
                                        @click="clearCustomer()"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <FieldError
                                    :errors="[form.errors.customer_id]"
                                />
                            </FieldContent>
                        </Field>

                        <!-- Quick customer fields -->
                        <div class="grid grid-cols-2 gap-2">
                            <Input
                                v-model="form.guest_name"
                                placeholder="Họ tên"
                                class="text-sm"
                            />
                            <Input
                                v-model="form.guest_phone"
                                placeholder="SĐT"
                                class="text-sm"
                            />
                        </div>
                    </div>

                    <!-- Shipping Address (accordion) -->
                    <div v-if="showShipping" class="shrink-0 border-b">
                        <Accordion type="single" collapsible>
                            <AccordionItem value="shipping" class="border-0">
                                <AccordionTrigger
                                    class="px-4 py-3 text-sm font-medium hover:no-underline"
                                >
                                    <div class="flex items-center gap-2">
                                        <MapPin class="h-4 w-4" /> Địa chỉ giao
                                        hàng
                                    </div>
                                </AccordionTrigger>
                                <AccordionContent class="space-y-3 px-4 pb-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <Field>
                                            <FieldLabel>Tỉnh/TP</FieldLabel>
                                            <FieldContent>
                                                <Select
                                                    v-model="form.province_code"
                                                >
                                                    <SelectTrigger
                                                        class="w-full text-sm"
                                                    >
                                                        <SelectValue
                                                            :placeholder="
                                                                loadingProvinces
                                                                    ? 'Đang tải...'
                                                                    : 'Chọn Tỉnh/TP'
                                                            "
                                                        />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="province in provinces"
                                                            :key="
                                                                province.value
                                                            "
                                                            :value="
                                                                province.value
                                                            "
                                                        >
                                                            {{ province.label }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </FieldContent>
                                        </Field>
                                        <Field>
                                            <FieldLabel>Quận/Huyện</FieldLabel>
                                            <FieldContent>
                                                <Select
                                                    v-model="form.ward_code"
                                                    :disabled="
                                                        !form.province_code ||
                                                        loadingWards
                                                    "
                                                >
                                                    <SelectTrigger
                                                        class="w-full text-sm"
                                                    >
                                                        <SelectValue
                                                            :placeholder="
                                                                wardDisplayLabel
                                                            "
                                                        />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="ward in wards"
                                                            :key="ward.value"
                                                            :value="ward.value"
                                                        >
                                                            {{ ward.label }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </FieldContent>
                                        </Field>
                                    </div>
                                    <Input
                                        v-model="form.street"
                                        placeholder="Địa chỉ"
                                        class="text-sm"
                                    />
                                    <div class="grid grid-cols-2 gap-2">
                                        <Field>
                                            <FieldContent>
                                                <Select
                                                    v-model="
                                                        form.shipping_method_id
                                                    "
                                                >
                                                    <SelectTrigger
                                                        class="w-full text-sm"
                                                    >
                                                        <SelectValue
                                                            placeholder="Phương thức..."
                                                        />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            v-for="m in shippingMethods ??
                                                            []"
                                                            :key="m.id"
                                                            :value="m.id"
                                                        >
                                                            {{ m.name }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </FieldContent>
                                        </Field>
                                        <Input
                                            v-model="form.shipping_cost!"
                                            type="number"
                                            placeholder="Phí ship"
                                            class="text-sm"
                                        />
                                    </div>
                                    <!-- Payment Method for shipping orders -->
                                    <Field>
                                        <FieldLabel
                                            >Phương thức thanh toán</FieldLabel
                                        >
                                        <FieldContent>
                                            <Select
                                                v-model="form.payment_method"
                                            >
                                                <SelectTrigger
                                                    class="w-full text-sm"
                                                >
                                                    <SelectValue
                                                        placeholder="Chọn phương thức..."
                                                    />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="m in paymentMethodOptions"
                                                        :key="m.value"
                                                        :value="m.value"
                                                    >
                                                        {{ m.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </FieldContent>
                                    </Field>
                                </AccordionContent>
                            </AccordionItem>
                        </Accordion>
                    </div>

                    <!-- Order Items -->
                    <div class="p-4">
                        <div
                            v-if="form.items.length === 0"
                            class="flex h-full items-center justify-center text-sm text-muted-foreground"
                        >
                            Chưa có sản phẩm nào
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="rounded-lg border p-3"
                            >
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div class="flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <Badge
                                                :variant="
                                                    item.purchasable_type.includes(
                                                        'Bundle',
                                                    )
                                                        ? 'secondary'
                                                        : 'outline'
                                                "
                                                class="text-[10px]"
                                            >
                                                {{
                                                    item.purchasable_type.includes(
                                                        'Bundle',
                                                    )
                                                        ? 'Gói'
                                                        : 'SP'
                                                }}
                                            </Badge>
                                            <span class="text-sm font-medium">{{
                                                item.purchasable_name
                                            }}</span>
                                        </div>
                                        <div
                                            class="mt-2 flex items-center gap-2"
                                        >
                                            <div
                                                class="flex items-center rounded border"
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-6 w-6"
                                                    :disabled="
                                                        item.quantity <= 1
                                                    "
                                                    @click="
                                                        updateQuantity(
                                                            index,
                                                            -1,
                                                        )
                                                    "
                                                >
                                                    <Minus class="h-3 w-3" />
                                                </Button>
                                                <span
                                                    class="w-8 text-center text-xs tabular-nums"
                                                    >{{ item.quantity }}</span
                                                >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-6 w-6"
                                                    @click="
                                                        updateQuantity(index, 1)
                                                    "
                                                >
                                                    <Plus class="h-3 w-3" />
                                                </Button>
                                            </div>
                                            <span
                                                class="ml-auto text-sm font-medium tabular-nums"
                                            >
                                                {{
                                                    parseFloat(
                                                        item.unit_price,
                                                    ).toLocaleString('vi-VN')
                                                }}đ
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
                </div>

                <!-- Right Panel: Catalog -->
                <div class="flex flex-1 flex-col">
                    <!-- Search & Filter -->
                    <div class="shrink-0 space-y-3 border-b p-4">
                        <div class="flex items-center gap-2">
                            <Search class="h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="catalogSearch"
                                placeholder="Tìm sản phẩm..."
                                class="flex-1"
                            />
                        </div>
                        <div class="flex gap-2">
                            <Badge
                                :variant="
                                    activeCategory === 'all'
                                        ? 'default'
                                        : 'outline'
                                "
                                class="cursor-pointer"
                                @click="activeCategory = 'all'"
                            >
                                Tất cả
                            </Badge>
                            <Badge
                                :variant="
                                    activeCategory === 'variant'
                                        ? 'default'
                                        : 'outline'
                                "
                                class="cursor-pointer"
                                @click="activeCategory = 'variant'"
                            >
                                Sản phẩm
                            </Badge>
                            <Badge
                                :variant="
                                    activeCategory === 'bundle'
                                        ? 'default'
                                        : 'outline'
                                "
                                class="cursor-pointer"
                                @click="activeCategory = 'bundle'"
                            >
                                Gói
                            </Badge>
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                v-for="item in filteredCatalog"
                                :key="item.id"
                                :class="[
                                    'cursor-pointer rounded-lg border p-3 transition-colors hover:border-primary',
                                    ((item.purchasable_type === 'variant' &&
                                        isVariantOutOfStock(
                                            item as VariantOption,
                                        )) ||
                                        (item.purchasable_type === 'bundle' &&
                                            isBundleUnavailable(
                                                item as BundleOption,
                                            ))) &&
                                        'cursor-not-allowed opacity-50 hover:border-border',
                                ]"
                                @click="
                                    (item as any).purchasable_type === 'variant'
                                        ? addVariant(item as VariantOption)
                                        : addBundle(item as BundleOption)
                                "
                            >
                                <!-- Product Image -->
                                <div
                                    v-if="item.image_url"
                                    class="mb-2 aspect-square overflow-hidden rounded-md border bg-muted"
                                >
                                    <img
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div class="flex items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <Badge
                                                :variant="
                                                    (item as any)
                                                        .purchasable_type ===
                                                    'bundle'
                                                        ? 'secondary'
                                                        : 'outline'
                                                "
                                                class="text-[10px]"
                                            >
                                                {{
                                                    (item as any)
                                                        .purchasable_type ===
                                                    'bundle'
                                                        ? 'Gói'
                                                        : 'SP'
                                                }}
                                            </Badge>
                                            <Tag
                                                v-if="'discount_type' in item"
                                                class="h-3 w-3 text-orange-500"
                                            />
                                        </div>
                                        <p
                                            class="mt-1 truncate text-sm font-medium"
                                        >
                                            {{ item.name }}
                                        </p>
                                        <p
                                            v-if="'sku' in item"
                                            class="font-mono text-[11px] text-muted-foreground"
                                        >
                                            {{ (item as VariantOption).sku }}
                                        </p>

                                        <!-- Stock info -->
                                        <div
                                            v-if="'stock_at_store' in item"
                                            class="mt-1.5 text-[11px]"
                                        >
                                            <span
                                                v-if="
                                                    (item as VariantOption)
                                                        .stock_at_store > 0
                                                "
                                                class="text-green-600"
                                            >
                                                Có sẵn tại cửa hàng:
                                                {{
                                                    (item as VariantOption)
                                                        .stock_at_store
                                                }}
                                            </span>
                                            <div
                                                v-else-if="
                                                    showShipping &&
                                                    (item as VariantOption)
                                                        .stock_total > 0
                                                "
                                            >
                                                <p class="text-amber-600">
                                                    Không có sẵn tại cửa hàng
                                                </p>
                                                <p>
                                                    Tổng số lượng toàn hệ thống:
                                                    {{
                                                        (item as VariantOption)
                                                            .stock_total
                                                    }}
                                                </p>
                                            </div>
                                            <span v-else class="text-red-500">
                                                Hết hàng
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 text-sm font-bold tabular-nums"
                                        >
                                            {{
                                                Number(
                                                    item.price,
                                                ).toLocaleString('vi-VN')
                                            }}đ
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="filteredCatalog.length === 0"
                            class="py-12 text-center text-sm text-muted-foreground"
                        >
                            Không tìm thấy sản phẩm
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter
                class="flex items-center justify-between border-t px-6 py-4"
            >
                <div v-if="form.items.length > 0" class="space-y-0.5">
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-muted-foreground">Tạm tính</span>
                        <span class="tabular-nums"
                            >{{ formatPrice(totalAmount) }}</span
                        >
                        <span
                            v-if="
                                showShipping &&
                                parseFloat(form.shipping_cost!) > 0
                            "
                            class="text-muted-foreground"
                            >· Phí ship</span
                        >
                        <span
                            v-if="
                                showShipping &&
                                parseFloat(form.shipping_cost!) > 0
                            "
                            class="tabular-nums"
                            :class="{ 'line-through text-muted-foreground': isFreeShippingActive }"
                            >{{
                                formatPrice(form.shipping_cost!)
                            }}</span
                        >
                    </div>
                    <div
                        v-if="freeshipMessage"
                        class="flex items-center gap-1.5 justify-self-end text-sm font-medium"
                        :class="
                            totalAmount >= freeshipThreshold
                                ? 'text-green-600'
                                : 'text-orange-500'
                        "
                    >
                        <Tag class="h-3 w-3" />
                        {{ freeshipMessage }}
                    </div>
                    <div
                        class="justify-self-end text-lg font-bold tabular-nums"
                    >
                        Tổng: {{ formatPrice(grandTotal) }}đ
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button type="button" variant="outline" @click="closeModal">
                        Hủy
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing || form.items.length === 0"
                        @click="submit"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Tạo đơn hàng
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>

        <!-- Bundle Variant Selection Dialog -->
        <Dialog
            :open="showBundleDialog"
            @update:open="(val) => !val && (showBundleDialog = false)"
        >
            <DialogContent class="max-w-[700px] overflow-hidden p-0">
                <DialogHeader class="border-b bg-zinc-50 px-6 pt-6 pb-4">
                    <DialogTitle class="text-xl font-bold text-zinc-900">
                        Cấu hình gói: {{ selectedBundle?.name }}
                    </DialogTitle>
                    <DialogDescription>
                        Vui lòng chọn phiên bản cho từng sản phẩm trong gói này.
                    </DialogDescription>
                </DialogHeader>

                <div class="max-h-[60vh] space-y-6 overflow-y-auto p-6">
                    <div
                        v-for="content in bundleContents?.[
                            selectedBundle?.id ?? ''
                        ] ?? []"
                        :key="content.id"
                        class="flex items-start gap-4 rounded-2xl border p-4 transition-colors hover:bg-zinc-50"
                    >
                        <!-- Selection Thumbnail -->
                        <div
                            class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border bg-white"
                        >
                            <img
                                :src="
                                    getSelectedVariant(content)
                                        ?.primary_image_url ||
                                    '/placeholder-product.jpg'
                                "
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div class="flex flex-1 flex-col gap-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3
                                        class="flex items-center gap-2 font-semibold text-zinc-900"
                                    >
                                        {{
                                            content.product_name +
                                            ' ' +
                                            getSelectedVariant(content)?.name
                                        }}
                                        <Badge
                                            variant="secondary"
                                            class="text-[10px]"
                                            >x{{ content.quantity }}</Badge
                                        >
                                    </h3>
                                </div>
                                <span
                                    class="font-medium text-zinc-900 tabular-nums"
                                >
                                    {{
                                        formatPrice(
                                            getSelectedVariant(content)
                                                ?.sale_price ||
                                                getSelectedVariant(content)
                                                    ?.price ||
                                                0,
                                        )
                                    }}
                                </span>
                            </div>

                            <!-- Visual Swatch Selection -->
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="v in content.variants"
                                    :key="v.id"
                                    @click="
                                        bundleVariantSelections[content.id] =
                                            v.id
                                    "
                                    class="group relative h-8 w-8 rounded-full border-2 transition-all"
                                    :class="[
                                        bundleVariantSelections[content.id] ===
                                        v.id
                                            ? 'scale-110 border-orange-400 ring-2 ring-orange-400/20'
                                            : 'border-transparent hover:scale-110',
                                        !v.in_stock
                                            ? 'cursor-not-allowed opacity-30 grayscale'
                                            : 'cursor-pointer',
                                    ]"
                                >
                                    <img
                                        :src="v.primary_image_url!"
                                        class="h-full w-full rounded-full object-cover"
                                    />
                                    <span
                                        class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 rounded bg-zinc-800 px-2 py-1 text-[10px] whitespace-nowrap text-white opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        {{
                                            content.product_name + ' ' + v.name
                                        }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="flex items-center justify-between border-t bg-zinc-50 p-6"
                >
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-zinc-500 uppercase"
                            >Tổng cộng</span
                        >
                        <span
                            class="text-2xl font-bold text-orange-500 tabular-nums"
                        >
                            {{ formatPrice(bundleDynamicPrice) }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            @click="showBundleDialog = false"
                            >Hủy</Button
                        >
                        <Button
                            @click="confirmBundleSelection"
                            class="bg-orange-400 text-white hover:bg-orange-500"
                            :disabled="
                                Object.keys(bundleVariantSelections).length <
                                (props.bundleContents?.[
                                    selectedBundle?.id ?? ''
                                ]?.length ?? 0)
                            "
                        >
                            Xác nhận thêm
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </Dialog>
</template>
