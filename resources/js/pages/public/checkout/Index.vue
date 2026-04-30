<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ChevronRight,
    CreditCard,
    ImageOff,
    MapPin,
    Truck,
} from '@lucide/vue';
import { ref, computed, onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { Textarea } from '@/components/ui/textarea';
import ShopLayout from '@/layouts/ShopLayout.vue';
import { formatPrice } from '@/lib/utils';
import { index } from '@/routes/cart';
import { store } from '@/routes/customer/checkout';
import { useCartStore } from '@/stores/cart';

const props = defineProps<{
    customer: any;
    shipping_methods: any[];
    freeship_threshold: number;
}>();

const { state, fetchCart } = useCartStore();

// Form state
const form = useForm({
    customer_id: props.customer?.user_id || null,
    guest_name: props.customer?.full_name || '',
    guest_phone: props.customer?.phone || '',
    guest_email: props.customer?.user?.email || '',
    province_code: props.customer?.province_code || '',
    province_name: props.customer?.province_name || '',
    ward_code: props.customer?.ward_code || '',
    ward_name: props.customer?.ward_name || '',
    street: props.customer?.street || '',
    shipping_method_id: '',
    payment_method: 'cod',
    notes: '',
    items: [] as any[],
});

// --- Geodata State ---
const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);

// --- Geodata API Calls ---
async function loadProvinces() {
    loadingProvinces.value = true;
    try {
        const response = await fetch('/api/geodata/provinces');
        provinces.value = await response.json();
    } catch (e) {
        console.error('Failed to load provinces', e);
    } finally {
        loadingProvinces.value = false;
    }
}

async function loadWards(provinceCode: string) {
    if (!provinceCode) return;
    loadingWards.value = true;
    try {
        const response = await fetch(
            `/api/geodata/wards?province_code=${provinceCode}`,
        );
        wards.value = await response.json();
    } catch (e) {
        console.error('Failed to load wards', e);
    } finally {
        loadingWards.value = false;
    }
}

const skipProvinceWatch = ref(false);

const wardDisplayLabel = computed(() => {
    if (form.ward_name) return form.ward_name;
    if (!form.province_code) return 'Chọn Tỉnh trước';
    if (loadingWards.value) return 'Đang tải...';
    return 'Chọn Quận/Huyện';
});

const shippingMethodName = computed(() => {
    if(!form.shipping_method_id) return 'Chọn phương thức vận chuyển...'
    const method = props.shipping_methods.find((value) => form.shipping_method_id === value);
    return method.name;
})

// 1. Watch Province: Update name and load wards
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

// 2. Watch Ward: Update name
watch(
    () => form.ward_code,
    (newCode) => {
        if (newCode) {
            const ward = wards.value.find((w) => w.value === newCode);
            form.ward_name = ward?.label ?? '';
        }
    },
);

// --- Pricing & Freeship Logic ---
const availableItems = computed(() => {
    return state.items.filter((item) => item.is_available);
});

const originalTotal = computed(() => {
    return availableItems.value.reduce((sum, item) => {
        const price = item.original_unit_price || item.unit_price;
        return sum + price * item.quantity;
    }, 0);
});

const shippingAmount = computed(() => {
    const method = props.shipping_methods.find(
        (m) => m.id === form.shipping_method_id,
    );
    if (!method) return 0;
    if (originalTotal.value >= props.freeship_threshold) return 0;
    return method.price;
});

const finalTotal = computed(() => {
    return state.totals.total + Number(shippingAmount.value);
});

// --- Submission ---
function submitOrder() {
    const validationErrors = {};

    if (!form.guest_name)
        validationErrors.guest_name = 'Vui lòng nhập họ và tên';
    if (!form.guest_phone)
        validationErrors.guest_phone = 'Vui lòng nhập số điện thoại';
    if (!form.province_code)
        validationErrors.province_code = 'Vui lòng chọn tỉnh/thành';
    if (!form.ward_code)
        validationErrors.ward_code = 'Vui lòng chọn quận/huyện';
    if (!form.street)
        validationErrors.street = 'Vui lòng nhập địa chỉ chi tiết';
    if (!form.shipping_method_id)
        validationErrors.shipping_method_id =
            'Vui lòng chọn phương thức vận chuyển';
    if (!form.payment_method)
        validationErrors.payment_method =
            'Vui lòng chọn phương thức thanh toán';

    if (Object.keys(validationErrors).length > 0) {
        form.errors = validationErrors;
        toast.error('Vui lòng điền đầy đủ các thông tin bắt buộc');
        return;
    }



    form.items = availableItems.value.map((item) => ({
        purchasable_type: item.purchasable_type,
        purchasable_id: item.purchasable_id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        configuration: item.configuration || {},
    }));

    form.post(store().url, {
        onError: (errors) => {
            console.error(errors);
            toast.error('Lỗi: ' + errors.error);
        },
        onSuccess: () => {
            toast.success('Tạo đơn hàng thành công!');
        },
        preserveState: true,
    });
}

onMounted(async () => {
    fetchCart();
    await loadProvinces();

    // If pre-filled, load the wards immediately
    if (form.province_code) {
        await loadWards(form.province_code);
    }
});
</script>

<template>
    <ShopLayout>
        <div class="mx-auto max-w-[1600px] px-4 py-12 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <div
                class="mb-8 flex items-center gap-2 text-sm text-muted-foreground"
            >
                <Link
                    :href="index().url"
                    class="transition-colors hover:text-primary"
                    >Giỏ hàng</Link
                >
                <ChevronRight class="h-4 w-4" />
                <span class="font-bold text-foreground">Thanh toán</span>
            </div>

            <h1 class="mb-10 text-3xl font-bold">Thông tin thanh toán</h1>

            <div class="grid grid-cols-1 items-start gap-10 lg:grid-cols-4">
                <!-- Left Column: Static Form -->
                <div class="lg:col-span-2">
                    <section
                        class="rounded-3xl border bg-white p-6 shadow-sm transition-all hover:shadow-md"
                    >
                        <!-- 1. Shipping Address Section -->
                        <div class="mb-8">
                            <div class="mb-6 flex items-center gap-2">
                                <div
                                    class="rounded-full bg-primary/10 p-2 text-primary"
                                >
                                    <MapPin class="h-5 w-5" />
                                </div>
                                <h2 class="text-xl font-bold">
                                    Địa chỉ giao hàng
                                </h2>
                            </div>

                            <div class="flex flex-col gap-3">
                                <!-- Row 1: Name and Email -->
                                <div
                                    class="grid w-full grid-cols-1 gap-6 sm:grid-cols-2"
                                >
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold"
                                            >Họ và tên</Label
                                        >
                                        <Input
                                            v-model="form.guest_name"
                                            placeholder="Nguyễn Văn A"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.guest_name,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.guest_name"
                                            class="text-xs text-destructive"
                                        >
                                            {{ form.errors.guest_name }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold"
                                            >Email</Label
                                        >
                                        <Input
                                            v-model="form.guest_email"
                                            type="email"
                                            placeholder="example@gmail.com"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.guest_email,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.guest_email"
                                            class="text-xs text-destructive"
                                        >
                                            {{ form.errors.guest_email }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Row 2: Phone, Province, Ward -->
                                <div
                                    class="grid grid-cols-1 gap-6 sm:grid-cols-3"
                                >
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold"
                                            >Số điện thoại</Label
                                        >
                                        <Input
                                            v-model="form.guest_phone"
                                            placeholder="090..."
                                            :class="{
                                                'border-destructive':
                                                    form.errors.guest_phone,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.guest_phone"
                                            class="text-xs text-destructive"
                                        >
                                            {{ form.errors.guest_phone }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold"
                                            >Tỉnh/Thành phố</Label
                                        >
                                        <Select v-model="form.province_code">
                                            <SelectTrigger
                                                :class="{
                                                    'border-destructive':
                                                        form.errors
                                                            .province_code,
                                                }"
                                                class="w-full"
                                            >
                                                <SelectValue
                                                    placeholder="Chọn tỉnh/thành"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="province in provinces"
                                                    :key="province.value"
                                                    :value="province.value"
                                                    >{{
                                                        province.label
                                                    }}</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                        <p
                                            v-if="form.errors.province_code"
                                            class="text-xs text-destructive"
                                        >
                                            {{ form.errors.province_code }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold"
                                            >Quận/Huyện</Label
                                        >
                                        <Select
                                            v-model="form.ward_code"
                                            :disabled="
                                                !form.province_code ||
                                                loadingWards
                                            "
                                        >
                                            <SelectTrigger
                                                :class="{
                                                    'border-destructive':
                                                        form.errors.ward_code,
                                                }"
                                                class="w-full"
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
                                                    >{{
                                                        ward.label
                                                    }}</SelectItem
                                                >
                                            </SelectContent>
                                        </Select>
                                        <p
                                            v-if="form.errors.ward_code"
                                            class="text-xs text-destructive"
                                        >
                                            {{ form.errors.ward_code }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Detailed Address -->
                                <div class="space-y-2 sm:col-span-2">
                                    <Label class="text-sm font-semibold"
                                        >Địa chỉ chi tiết</Label
                                    >
                                    <Input
                                        v-model="form.street"
                                        placeholder="Số nhà, tên đường, căn hộ..."
                                        :class="{
                                            'border-destructive':
                                                form.errors.street,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.street"
                                        class="text-xs text-destructive"
                                    >
                                        {{ form.errors.street }}
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-6 space-y-2">
                                <Label class="text-sm font-semibold"
                                    >Ghi chú đơn hàng (tùy chọn)</Label
                                >
                                <Textarea
                                    v-model="form.notes"
                                    placeholder="Ví dụ: Giao hàng sau 5h chiều..."
                                    class="resize-none"
                                />
                            </div>
                        </div>

                        <div class="h-px bg-zinc-100" />

                        <!-- Shipping & Payment Selection -->
                        <div class="py-8">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <div class="mb-2 flex items-center gap-2">
                                        <div
                                            class="rounded-full bg-primary/10 p-1 text-primary"
                                        >
                                            <Truck class="h-4 w-4" />
                                        </div>
                                        <Label class="text-sm font-semibold"
                                            >Phương thức vận chuyển</Label
                                        >
                                    </div>
                                    <Select v-model="form.shipping_method_id">
                                        <SelectTrigger
                                            :class="{
                                                'border-destructive':
                                                    form.errors
                                                        .shipping_method_id,
                                            }"
                                            class="w-full"
                                        >
                                            <SelectValue
                                                placeholder="'Chọn phương thức vận chuyển...'"

                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="method in shipping_methods"
                                                :key="method.id"
                                                :value="method.id"
                                            >
                                                <div
                                                    class="flex w-full items-center justify-between gap-4"
                                                >
                                                    <span>{{
                                                        method.name
                                                    }}</span>
                                                </div>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p
                                        v-if="form.errors.shipping_method_id"
                                        class="text-xs text-destructive"
                                    >
                                        {{ form.errors.shipping_method_id }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <div class="mb-2 flex items-center gap-2">
                                        <div
                                            class="rounded-full bg-primary/10 p-1 text-primary"
                                        >
                                            <CreditCard class="h-4 w-4" />
                                        </div>
                                        <Label class="text-sm font-semibold"
                                            >Phương thức thanh toán</Label
                                        >
                                    </div>
                                    <Select v-model="form.payment_method">
                                        <SelectTrigger
                                            :class="{
                                                'border-destructive':
                                                    form.errors.payment_method,
                                            }"
                                            class="w-full"
                                        >
                                            <SelectValue
                                                placeholder="Chọn phương thức..."
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="cod"
                                                >Thanh toán khi nhận hàng</SelectItem
                                            >
                                            <SelectItem value="bank_transfer"
                                                >Chuyển khoản /
                                                VNPAY</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                    <p
                                        v-if="form.errors.payment_method"
                                        class="text-xs text-destructive"
                                    >
                                        {{ form.errors.payment_method }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column: The Summary Card -->
                <div class="lg:col-span-2">
                    <div
                        class="flex flex-col overflow-hidden rounded-3xl border bg-white shadow-sm"
                    >
                        <!-- 1. Total Preview Header (Static) -->
                        <div class="border-b bg-zinc-50/50 p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="font-bold text-zinc-900">
                                    Tổng kết đơn hàng
                                </h2>
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    {{ availableItems.length }} sản phẩm
                                </span>
                            </div>

                            <div class="mb-4 grid grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground"
                                        >Tạm tính:</span
                                    >
                                    <span class="font-medium">{{
                                        formatPrice(state.totals.subtotal)
                                    }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground"
                                        >Vận chuyển:</span
                                    >
                                    <span
                                        :class="
                                            shippingAmount === 0
                                                ? 'font-bold text-green-600'
                                                : 'font-medium'
                                        "
                                    >
                                        {{
                                            shippingAmount === 0
                                                ? 'Miễn phí'
                                                : formatPrice(shippingAmount)
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground"
                                        >Giảm giá:</span
                                    >
                                    <span class="font-medium text-green-600"
                                        >-
                                        {{
                                            formatPrice(
                                                state.totals.discount || 0,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between border-t pt-2 text-lg font-bold text-zinc-900"
                                >
                                    <span>Tổng cộng:</span>
                                    <span>{{ formatPrice(finalTotal) }}</span>
                                </div>
                            </div>

                            <Button
                                class="w-full rounded-lg bg-orange-400/80 py-6 text-lg font-bold text-white shadow-md transition-all hover:scale-[1.01] active:scale-[0.99]"
                                :disabled="form.processing"
                                @click="submitOrder"
                            >
                                {{
                                    form.processing
                                        ? 'Đang xử lý...'
                                        : 'Đặt hàng ngay'
                                }}
                            </Button>
                        </div>

                        <!-- 2. Scrollable Items Area -->
                        <div
                            class="max-h-[600px] space-y-4 overflow-y-auto p-6"
                        >
                            <div
                                v-if="availableItems.length === 0"
                                class="py-10 text-center"
                            >
                                <p class="text-muted-foreground">
                                    Không có sản phẩm khả dụng.
                                </p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="item in availableItems"
                                    :key="item.id"
                                    class="flex flex-col gap-4 rounded-2xl border bg-white p-5 transition-all"
                                >
                                    <!-- MAIN ITEM ROW (Exact Cart Logic) -->
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="h-24 w-24 shrink-0 overflow-hidden rounded-xl border bg-muted"
                                        >
                                            <img
                                                v-if="item.image_url"
                                                :src="item.image_url"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center text-muted-foreground"
                                            >
                                                <ImageOff class="h-8 w-8" />
                                            </div>
                                        </div>

                                        <div class="flex-1 space-y-1">
                                            <div
                                                class="flex items-start justify-between"
                                            >
                                                <div class="flex flex-col">
                                                    <p
                                                        class="text-lg leading-tight font-bold text-zinc-900"
                                                    >
                                                        {{ item.name }}
                                                    </p>
                                                    <Badge
                                                        v-if="
                                                            !item.is_available
                                                        "
                                                        variant="destructive"
                                                        class="mt-1 w-fit text-[10px]"
                                                    >
                                                        Hết hàng
                                                    </Badge>
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    v-if="
                                                        item.original_unit_price >
                                                        item.unit_price
                                                    "
                                                    class="text-sm text-muted-foreground line-through"
                                                >
                                                    {{
                                                        formatPrice(
                                                            item.original_unit_price,
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    class="text-lg font-bold text-orange-500"
                                                >
                                                    {{
                                                        formatPrice(
                                                            item.unit_price,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="mt-3 flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center gap-2 rounded-lg bg-zinc-100 px-3 py-1"
                                                >
                                                    <span
                                                        class="text-sm font-medium"
                                                        >Số lượng:
                                                        {{
                                                            item.quantity
                                                        }}</span
                                                    >
                                                </div>
                                                <p class="text-lg font-bold">
                                                    {{
                                                        formatPrice(
                                                            item.unit_price *
                                                                item.quantity,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- BUNDLE ACCORDION (Exact copy of Cart Index) -->
                                    <Accordion
                                        v-if="
                                            item.purchasable_type ===
                                            'App\\Models\\Product\\Bundle'
                                        "
                                        type="single"
                                        collapsible
                                        class="w-full"
                                    >
                                        <AccordionItem
                                            value="contents"
                                            class="border-none"
                                        >
                                            <AccordionTrigger
                                                class="px-0 py-2 text-xs font-semibold text-muted-foreground hover:no-underline"
                                            >
                                                Xem nội dung gói
                                            </AccordionTrigger>
                                            <AccordionContent
                                                class="space-y-3 pl-4"
                                            >
                                                <div
                                                    v-for="variant in item.selected_variants"
                                                    :key="variant.id"
                                                    class="flex items-center justify-between border-b border-zinc-100 py-2 last:border-0"
                                                    :class="{
                                                        'opacity-50':
                                                            variant.available_stock <=
                                                            0,
                                                    }"
                                                >
                                                    <div
                                                        class="flex items-center gap-3"
                                                    >
                                                        <div
                                                            class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border bg-muted"
                                                        >
                                                            <img
                                                                v-if="
                                                                    variant.image_url
                                                                "
                                                                :src="
                                                                    variant.image_url
                                                                "
                                                                class="h-full w-full object-cover"
                                                            />
                                                            <div
                                                                v-else
                                                                class="flex h-full w-full items-center justify-center text-muted-foreground"
                                                            >
                                                                <ImageOff
                                                                    class="h-4 w-4"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex flex-col"
                                                        >
                                                            <p
                                                                class="text-xs font-medium"
                                                            >
                                                                {{
                                                                    variant.name
                                                                }}
                                                            </p>
                                                            <div
                                                                class="flex items-center gap-2"
                                                            >
                                                                <span
                                                                    class="font-mono text-[10px] text-muted-foreground"
                                                                    >{{
                                                                        variant.sku
                                                                    }}</span
                                                                >
                                                                <span
                                                                    v-if="
                                                                        variant.available_stock <=
                                                                        0
                                                                    "
                                                                    class="flex items-center gap-1 text-[10px] font-bold text-destructive"
                                                                >
                                                                    <AlertCircle
                                                                        class="h-3 w-3"
                                                                    />
                                                                    Hết hàng
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p
                                                            class="text-xs font-bold"
                                                        >
                                                            x{{
                                                                variant.quantity
                                                            }}
                                                        </p>
                                                        <span
                                                            v-if="
                                                                variant.sale_price &&
                                                                variant.sale_price <
                                                                    variant.price
                                                            "
                                                            class="text-xs text-muted-foreground line-through"
                                                        >
                                                            {{
                                                                formatPrice(
                                                                    variant.sale_price,
                                                                )
                                                            }}
                                                        </span>
                                                        &nbsp;
                                                        <span
                                                            class="text-xs font-bold text-orange-500"
                                                        >
                                                            {{
                                                                formatPrice(
                                                                    variant.price,
                                                                )
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </AccordionContent>
                                        </AccordionItem>
                                    </Accordion>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
    <Sonner />
</template>
