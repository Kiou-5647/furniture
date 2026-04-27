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
const targetOptions = ref<{ label: string; value: string }[]>([]);
const isLoadingTargets = ref(false);

async function fetchTargets() {
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

        if (!url) throw new Error('Unsupported target type');

        const response = await fetch(url, {
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

            <form @submit.prevent="submit" class="grid gap-4 py-4">
                <!-- Name -->
                <div class="grid gap-2">
                    <Label for="name">Tên chương trình</Label>
                    <Input id="name" v-model="form.name" placeholder="Ví dụ: Sale Hè 2026" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Type -->
                    <div class="grid gap-2">
                        <Label>Loại giảm giá</Label>
                        <Select v-model="form.type">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="percentage">Phần trăm (%)</SelectItem>
                                <SelectItem value="fixed_amount">Số tiền cố định</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Value -->
                    <div class="grid gap-2">
                        <Label for="value">Giá trị</Label>
                        <Input id="value" type="number" v-model.number="form.value" step="0.01" />
                    </div>
                </div>

                <!-- Target Type -->
                <div class="grid gap-2">
                    <Label>Đối tượng áp dụng</Label>
                    <Select v-model="form.discountable_type">
                        <SelectTrigger>
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

                <!-- Target Specific ID (Only show if a type is selected) -->
                <div v-if="form.discountable_type && form.discountable_type !== 'null'" class="grid gap-2">
                    <Label>Chọn mục tiêu cụ thể</Label>
                    <Select v-model="form.discountable_id" :disabled="isLoadingTargets">
                        <SelectTrigger>
                            <SelectValue :placeholder="isLoadingTargets ? 'Đang tải...' : 'Chọn mục tiêu'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in targetOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div class="grid gap-2">
                        <Label>Ngày bắt đầu</Label>
                        <div class="relative">
                            <Input type="date" v-model="form.start_at!" />
                        </div>
                    </div>
                    <!-- End Date -->
                    <div class="grid gap-2">
                        <Label>Ngày kết thúc</Label>
                        <div class="relative">
                            <Input type="date" v-model="form.end_at!" />
                        </div>
                    </div>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center space-x-2">
                    <Checkbox id="is_active" v-model="form.is_active" @update:checked="form.is_active = $event" />
                    <Label for="is_active" class="cursor-pointer">Kích hoạt giảm giá này</Label>
                </div>

                <DialogFooter class="flex flex-col gap-2 sm:flex-row">
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
                        <Button type="submit" :disabled="form.processing">
                            {{ discount ? 'Cập nhật' : 'Tạo mới' }}
                        </Button>
                    </div>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
