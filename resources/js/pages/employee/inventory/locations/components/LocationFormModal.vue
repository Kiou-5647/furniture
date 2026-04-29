<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Building2,
    Loader2,
    MapPin,
    Package,
    Phone,
    Truck,
    User,
} from '@lucide/vue';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
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
import { store, update } from '@/routes/employee/inventory/locations';

const props = defineProps<{
    open: boolean;
    location: any | null;
    typeOptions: { value: string; label: string; color: string }[];
    managerOptions: { id: string; label: string }[];
}>();

const emit = defineEmits(['close']);

const provinces = ref<{ value: string; label: string }[]>([]);
const wards = ref<{ value: string; label: string }[]>([]);
const loadingProvinces = ref(false);
const loadingWards = ref(false);

const form = useForm({
    name: '',
    type: 'warehouse' as 'warehouse' | 'retail' | 'vendor',
    province_code: undefined as string | undefined,
    province_name: undefined as string | undefined,
    ward_code: undefined as string | undefined,
    ward_name: undefined as string | undefined,
    street: undefined as string | undefined,
    phone: undefined as string | undefined,
    manager_id: undefined as string | undefined,
    is_active: true,
});

async function loadProvinces() {
    loadingProvinces.value = true;
    try {
        const response = await fetch('/api/geodata/provinces');
        provinces.value = await response.json();
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
    } finally {
        loadingWards.value = false;
    }
}

onMounted(() => {
    loadProvinces();
});

watch(
    () => props.location,
    (newLocation) => {
        if (newLocation && props.open) {
            form.name = newLocation.name;
            form.type = newLocation.type;
            form.street = newLocation.street;
            form.ward_code = newLocation.ward_code;
            form.ward_name = newLocation.ward_name;
            form.province_code = newLocation.province_code;
            form.province_name = newLocation.province_name;
            form.phone = newLocation.phone;
            form.manager_id = newLocation.manager_id;
            form.is_active = newLocation.is_active;

            // Defer ward loading to after dialog animation
            if (newLocation.province_code) {
                nextTick(() => {
                    setTimeout(() => {
                        loadWards(newLocation.province_code);
                    }, 100);
                });
            }
        } else if (!newLocation && props.open) {
            form.reset();
            form.is_active = true;
        }
    },
    { immediate: true },
);

watch(
    () => form.province_code,
    (newProvinceCode) => {
        if (newProvinceCode) {
            const province = provinces.value.find(
                (p) => p.value === newProvinceCode,
            );
            form.province_name = province?.label;
            form.ward_code = undefined;
            form.ward_name = undefined;
            loadWards(newProvinceCode);
        }
    },
);

watch(
    () => form.ward_code,
    (newWardCode) => {
        if (newWardCode) {
            const ward = wards.value.find((w) => w.value === newWardCode);
            form.ward_name = ward?.label;
        }
    },
);

const typeIconMap: Record<string, any> = {
    warehouse: Package,
    retail: Building2,
    vendor: Truck,
};

const selectedType = computed(() =>
    props.typeOptions.find((t) => t.value === form.type),
);

const wardDisplayLabel = computed(() => {
    if (form.ward_name) return form.ward_name;
    if (!form.province_code) return 'Chọn Tỉnh trước';
    if (loadingWards.value) return 'Đang tải...';
    return 'Chọn Phường/Xã';
});

function submit() {
    if (props.location) {
        form.put(update(props.location).url, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(store().url, {
            onSuccess: () => closeModal(),
        });
    }
}

function closeModal() {
    form.reset();
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-[600px]"
        >
            <DialogHeader class="px-4 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <DialogTitle class="text-left text-lg sm:text-xl">
                            {{ location ? 'Chỉnh sửa' : 'Thêm' }} vị trí
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{
                                location
                                    ? 'Cập nhật thông tin vị trí kho hàng'
                                    : 'Tạo vị trí kho hàng mới'
                            }}
                        </DialogDescription>
                    </div>
                    <Badge
                        v-if="selectedType"
                        variant="outline"
                        class="shrink-0 gap-1.5"
                    >
                        <component
                            :is="typeIconMap[form.type]"
                            class="h-3.5 w-3.5"
                        />
                        {{ selectedType.label }}
                    </Badge>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="px-4 pb-4 sm:px-6">
                <div class="space-y-4">
                    <!-- Name + Type -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                <Package
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Tên vị trí
                                <span class="text-destructive">*</span>
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.name"
                                    placeholder="Ví dụ: Kho chính HCM"
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.name]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>
                                Loại vị trí
                                <span class="text-destructive">*</span>
                            </FieldLabel>
                            <FieldContent>
                                <Select v-model="form.type">
                                    <SelectTrigger class="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="typeOption in typeOptions"
                                            :key="typeOption.value"
                                            :value="typeOption.value"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <component
                                                    :is="
                                                        typeIconMap[
                                                            typeOption.value
                                                        ]
                                                    "
                                                    class="h-4 w-4"
                                                />
                                                {{ typeOption.label }}
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError :errors="[form.errors.type]" />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Phone + Manager -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                <Phone
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Số điện thoại
                            </FieldLabel>
                            <FieldContent>
                                <Input
                                    v-model="form.phone"
                                    placeholder="0123 456 789"
                                    class="w-full"
                                />
                                <FieldError :errors="[form.errors.phone]" />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>
                                <User
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Người quản lý
                            </FieldLabel>
                            <FieldContent>
                                <Select v-model="form.manager_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Chọn người quản lý..."
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="m in managerOptions"
                                            :key="m.id"
                                            :value="m.id"
                                        >
                                            {{ m.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[form.errors.manager_id]"
                                />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Province + Ward -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field>
                            <FieldLabel>
                                <MapPin
                                    class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                Tỉnh/Thành phố
                            </FieldLabel>
                            <FieldContent>
                                <Select v-model="form.province_code">
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            :placeholder="
                                                loadingProvinces
                                                    ? 'Đang tải...'
                                                    : 'Chọn Tỉnh/Thành'
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="province in provinces"
                                            :key="province.value"
                                            :value="province.value"
                                        >
                                            {{ province.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError
                                    :errors="[form.errors.province_code]"
                                />
                            </FieldContent>
                        </Field>

                        <Field>
                            <FieldLabel>Phường/Xã</FieldLabel>
                            <FieldContent>
                                <Select
                                    v-model="form.ward_code"
                                    :disabled="
                                        !form.province_code || loadingWards
                                    "
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            :placeholder="wardDisplayLabel"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <template v-if="loadingWards">
                                            <div
                                                class="flex items-center gap-2 px-2 py-1.5 text-sm text-muted-foreground"
                                            >
                                                <Loader2
                                                    class="h-3 w-3 animate-spin"
                                                />
                                                Đang tải phường/xã...
                                            </div>
                                        </template>
                                        <SelectItem
                                            v-else
                                            v-for="ward in wards"
                                            :key="ward.value"
                                            :value="ward.value"
                                        >
                                            {{ ward.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError :errors="[form.errors.ward_code]" />
                            </FieldContent>
                        </Field>
                    </div>

                    <!-- Building -->
                    <Field>
                        <FieldLabel>Địa chỉ</FieldLabel>
                        <FieldContent>
                            <Input
                                v-model="form.street"
                                placeholder="Ví dụ: Tòa nhà A, Khu phức hợp B"
                                class="w-full"
                            />
                            <FieldError :errors="[form.errors.street]" />
                        </FieldContent>
                    </Field>
                </div>

                <!-- Status Toggle -->
                <div
                    class="mt-6 flex items-center justify-between rounded-lg border p-4"
                >
                    <div class="space-y-0.5">
                        <Label class="text-base">Kích hoạt</Label>
                        <p class="text-sm text-muted-foreground">
                            Hiển thị và cho phép sử dụng
                        </p>
                    </div>
                    <Switch v-model="form.is_active" />
                </div>

                <DialogFooter class="mt-6 gap-2 sm:mt-8">
                    <Button type="button" variant="outline" @click="closeModal">
                        Hủy
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ location ? 'Lưu thay đổi' : 'Tạo mới' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
