<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import SearchableSelect from '@/components/ui/SearchableSelect.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/employee/sales/discounts';
import type { Discount } from '@/types/discount';

const props = defineProps<{
    open: boolean;
    discountableTypes: Record<string, string>;
    discount?: Discount | null;
}>();

const emit = defineEmits(['close', 'delete']);

function formatDateForInput(dateString: string | null | undefined): string {
    if (!dateString) return '';
    return dateString.split('T')[0];
}

// Form State
const form = useForm({
    name: props.discount?.name ?? '',
    type: props.discount?.type ?? 'percentage',
    value: props.discount?.value ?? 0,
    start_at: formatDateForInput(props.discount?.start_at),
    end_at: formatDateForInput(props.discount?.end_at),
    is_active: props.discount?.is_active ?? true,
    discountable_type: props.discount?.discountable_type ?? null,
    discountable_id: props.discount?.discountable_id ?? null,
});

// Dynamic Targets State
const targetOptions = ref<any[]>([]);
const isLoadingTargets = ref(false);

async function fetchTargets(search = '') {
    if (!form.discountable_type || form.discountable_type === 'null') {
        targetOptions.value = [];
        return;
    }

    isLoadingTargets.value = true;
    try {
        let url = '';
        const type = form.discountable_type;

        if (type.includes('Category')) url = '/nhan-vien/ban-hang/giam-gia/targets/categories';
        else if (type.includes('Collection')) url = '/nhan-vien/ban-hang/giam-gia/targets/collections';
        else if (type.includes('Vendor')) url = '/nhan-vien/ban-hang/giam-gia/targets/vendors';
        else if (type.includes('ProductVariant')) url = '/nhan-vien/ban-hang/giam-gia/targets/variants';
        else if (type.includes('Product')) url = '/nhan-vien/ban-hang/giam-gia/targets/products';

        if (!url) throw new Error('Unsupported target type');

        const response = await fetch(`${url}?search=${encodeURIComponent(search)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const data = await response.json();
        targetOptions.value = data;
    } catch (e) {
        console.error('Failed to fetch targets', e);
    } finally {
        isLoadingTargets.value = false;
    }
}

watch(() => form.discountable_type, () => {
    form.discountable_id = null;
    fetchTargets();
});


onMounted(() => {
    if (form.discountable_type) {
        fetchTargets();
    }
});

function submit() {
    if (props.discount) {
        form.put(update(props.discount.id).url, {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(store().url, {
            onSuccess: () => emit('close'),
        });
    }
}

function handleConfirmDelete() {
    if (props.discount) {
        emit('delete', props.discount);
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>
                    {{ discount ? 'Chỉnh sửa giảm giá' : 'Thêm giảm giá mới' }}
                </DialogTitle>
                <DialogDescription>
                    Thiết lập thông tin chi tiết cho chương trình giảm giá.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="grid gap-6 py-4">
                <!-- General Information -->
                <div class="grid gap-4 p-4 rounded-lg border bg-muted/30">
                    <div class="grid gap-2">
                        <Label for="name" class="text-sm font-semibold">Tên chương trình</Label>
                        <Input id="name" v-model="form.name" placeholder="Ví dụ: Sale Hè 2026" class="bg-background" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label class="text-sm font-semibold">Loại giảm giá</Label>
                            <Select v-model="form.type">
                                <SelectTrigger class="bg-background">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="percentage">Phần trăm (%)</SelectItem>
                                    <SelectItem value="fixed_amount">Số tiền cố định</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label for="value" class="text-sm font-semibold">Giá trị</Label>
                            <Input id="value" type="number" v-model.number="form.value" step="0.01" class="bg-background" />
                        </div>
                    </div>
                </div>

                <!-- Target Configuration -->
                <div class="grid gap-4 p-4 rounded-lg border bg-muted/30">
                    <div class="grid gap-2">
                        <Label class="text-sm font-semibold">Đối tượng áp dụng</Label>
                        <Select v-model="form.discountable_type">
                            <SelectTrigger class="bg-background">
                                <SelectValue placeholder="Chọn loại đối tượng" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="null">Toàn bộ sản phẩm (Universal)</SelectItem>
                                <SelectItem
                                    v-for="(label, value) in discountableTypes"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="form.discountable_type && form.discountable_type !== 'null'" class="grid gap-2">
                        <Label class="text-sm font-semibold">Chọn mục tiêu cụ thể</Label>
                        <SearchableSelect
                            v-model="form.discountable_id"
                            :options="targetOptions"
                            :is-loading="isLoadingTargets"
                            placeholder="Tìm kiếm mục tiêu..."
                            value-key="id"
                            label-key="name"
                            server-search
                            @search="fetchTargets"
                        >
                            <template #item="{ option }">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <img
                                        v-if="option.primary_image || option.image"
                                        :src="option.primary_image || option.image"
                                        class="size-8 rounded-md object-cover bg-muted"
                                    />
                                    <div class="flex flex-col overflow-hidden text-left">
                                        <span class="truncate font-medium">
                                            <template v-if="option.product_name">
                                                {{ option.product_name }}
                                                <span v-if="option.name && option.name !== option.product_name" class="text-muted-foreground">
                                                    ({{ option.name }})
                                                </span>
                                            </template>
                                            <template v-else>
                                                {{ option.name }}
                                            </template>
                                        </span>
                                        <span v-if="option.sku" class="text-xs text-muted-foreground truncate">
                                            {{ option.sku }} - {{ option.price }}
                                        </span>
                                        <span v-if="option.variants_count !== undefined" class="text-xs text-muted-foreground truncate">
                                            ({{ option.variants_count }} Variants)
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </SearchableSelect>
                    </div>
                </div>

                <!-- Schedule & Status -->
                <div class="grid gap-4 p-4 rounded-lg border bg-muted/30">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label class="text-sm font-semibold">Ngày bắt đầu</Label>
                            <Input type="date" v-model="form.start_at!" class="bg-background" />
                        </div>
                        <div class="grid gap-2">
                            <Label class="text-sm font-semibold">Ngày kết thúc</Label>
                            <Input type="date" v-model="form.end_at!" class="bg-background" />
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <Checkbox id="is_active" v-model="form.is_active" @update:checked="form.is_active = $event" />
                        <Label for="is_active" class="cursor-pointer text-sm font-medium">Kích hoạt giảm giá này</Label>
                    </div>
                </div>

                <DialogFooter class="flex flex-col gap-2 sm:flex-row pt-2">
                    <Button
                        v-if="discount"
                        variant="destructive"
                        class="sm:w-auto"
                        @click="handleConfirmDelete"
                    >
                        Xóa giảm giá
                    </Button>
                    <div class="flex gap-2 sm:ml-auto">
                        <Button variant="outline" @click="emit('close')">Hủy</Button>
                        <Button type="submit" :disabled="form.processing" class="px-8">
                            {{ discount ? 'Cập nhật' : 'Tạo mới' }}
                        </Button>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
