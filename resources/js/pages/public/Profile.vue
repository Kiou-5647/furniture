<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import ImageUploader from '@/components/custom/ImageUploader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import ShopLayout from '@/layouts/ShopLayout.vue';
import { update } from '@/routes/customer/profile';
import { send } from '@/routes/verification';
import CustomerLayout from '@/layouts/settings/CustomerLayout.vue';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    user?: any;
    customer?: any;
    avatar?: string;
};

const props = defineProps<Props>();

const page = usePage();

const user = computed(() => props.user || page.props.auth.user);
const customer = computed(() => props.customer || user.value?.customer);

const form = useForm<{
    name: string;
    email: string;
    full_name: string | undefined;
    phone: string | undefined;
    province_code: string | undefined;
    province_name: string | undefined;
    ward_code: string | undefined;
    ward_name: string | undefined;
    street: string | undefined;
    avatar: File | null;
    avatar_url: string | null;
}>({
    name: props.user.name,
    email: props.user.email,
    full_name: props.customer.full_name,
    phone: props.customer.phone,
    province_code: props.customer.province_code,
    province_name: props.customer.province_name,
    ward_code: props.customer.ward_code,
    ward_name: props.customer.ward_name,
    street: props.customer?.street,
    avatar: null,
    avatar_url: props.avatar ?? '',
});

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
        toast.error('Không thể fetch dữ liệu tỉnh/thành');
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
        toast.error('Không thể fetch dữ liệu phường xã');
    } finally {
        loadingWards.value = false;
    }
}

onMounted(() => {
    loadProvinces();
    loadWards(props.customer.province_code);
});

const wardDisplayLabel = computed(() => {
    if (form.ward_name) return form.ward_name;
    if (!form.province_code) return 'Chọn Tỉnh trước';
    if (loadingWards.value) return 'Đang tải...';
    return 'Chọn Quận/Huyện';
});

const previewUrl = computed(() => {
    console.info(form.avatar);
    if (form.avatar) return URL.createObjectURL(form.avatar);
    return props.avatar ?? null;
});

watch(
    () => form.province_code,
    async (newCode) => {
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

function handleEmitError(message: string) {
    toast.error(message);
}

function submit() {
    form.patch(update().url, {
        onSuccess: () => toast.info('Cập nhật thông tin thành công!'),
        onError: (errors) => () =>
            toast.error(
                errors.error || 'Có lỗi xảy ra trong quá trình cập nhật!',
            ),
    });
}
</script>

<template>
    <ShopLayout>
        <CustomerLayout>
            <div class="mx-auto flex max-w-[600px] flex-col space-y-6">
                <Card>
                    <CardHeader class="m-2">
                        <CardTitle>Thông tin hồ sơ</CardTitle>
                        <CardDescription
                            >Cập nhật thông tin cá nhân của bạn</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="@container">
                        <div class="mt-3 flex flex-row justify-center">
                            <div
                                class="flex flex-col items-center justify-center"
                            >
                                <FieldLabel class="text-sm font-medium"
                                    >Hình ảnh đại diện</FieldLabel
                                >
                                <ImageUploader
                                    v-model="form.avatar"
                                    :preview-url="previewUrl"
                                    aspect-ratio="square"
                                    class="mt-2 h-30 w-30 justify-self-center"
                                    hint=" "
                                    @error="handleEmitError"
                                    @remove-image="form.avatar_url = ''"
                                />

                                <FieldError :errors="[form.errors.avatar]" />
                            </div>
                        </div>
                        <div class="mt-6 flex flex-col">
                            <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="name">Tên</Label>
                                    <Input
                                        v-model="form.name"
                                        class="mt-2 block w-full"
                                        id="name"
                                        name="name"
                                        required
                                        autocomplete="name"
                                        placeholder="Họ và tên"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.name"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="email">Địa chỉ email</Label>
                                    <Input
                                        v-model="form.email"
                                        type="email"
                                        class="mt-2 block w-full"
                                        name="email"
                                        required
                                        autocomplete="username"
                                        placeholder="Địa chỉ email"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.email"
                                    />
                                </div>
                                <div
                                    v-if="
                                        mustVerifyEmail &&
                                        !user.email_verified_at
                                    "
                                >
                                    <p
                                        class="-mt-4 text-sm text-muted-foreground"
                                    >
                                        Địa chỉ email của bạn chưa được xác
                                        minh.
                                        <Link
                                            :href="send()"
                                            as="button"
                                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                        >
                                            Nhấn vào đây để gửi lại email xác
                                            minh.
                                        </Link>
                                    </p>

                                    <div
                                        v-if="
                                            status === 'verification-link-sent'
                                        "
                                        class="mt-2 text-sm font-medium text-green-600"
                                    >
                                        Một liên kết xác minh mới đã được gửi
                                        đến địa chỉ email của bạn.
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="full_name"
                                        >Họ và tên đầy đủ</Label
                                    >
                                    <Input
                                        v-model="form.full_name"
                                        id="full_name"
                                        class="mt-2 block w-full"
                                        name="full_name"
                                        required
                                        placeholder="Nguyễn Văn A"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.full_name"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="phone">Số điện thoại</Label>
                                    <Input
                                        v-model="form.phone"
                                        id="phone"
                                        class="mt-2 block w-full"
                                        name="phone"
                                        :default-value="customer?.phone"
                                        placeholder="090..."
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.phone"
                                    />
                                </div>
                                <div class="grid gap-2">
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
                                                        :key="province.value"
                                                        :value="province.value"
                                                    >
                                                        {{ province.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </FieldContent>
                                    </Field>
                                </div>
                                <div class="grid gap-2">
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
                                <div class="col-span-2 flex flex-col gap-2">
                                    <Field>
                                        <FieldLabel>Địa chỉ</FieldLabel>
                                        <FieldContent>
                                            <Input
                                                v-model="form.street"
                                                placeholder="Địa chỉ"
                                                class="text-sm"
                                            />
                                        </FieldContent>
                                    </Field>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <div class="mt-6 flex w-full justify-end">
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="form.recentlySuccessful"
                                    class="text-sm text-neutral-600"
                                >
                                    Đã lưu.
                                </p>
                            </Transition>
                            <Button
                                @click="submit"
                                :disabled="form.processing"
                                data-test="update-profile-button"
                                >Lưu
                            </Button>
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </CustomerLayout>
    </ShopLayout>
</template>
