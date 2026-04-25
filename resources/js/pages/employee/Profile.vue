<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { X, ImageIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { formatDateOnly } from '@/lib/date-utils';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';
import EmployeeProfileController from '@/actions/App/Http/Controllers/Setting/EmployeeProfileController';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    // 🎯 Catch the props passed from EmployeeProfileController
    user?: any;
    employee?: any;
    departments?: Array<{ id: string; name: string }>;
    avatar?: string;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Cài đặt hồ sơ',
        href: edit(),
    },
];

const page = usePage();

const user = computed(() => props.user || page.props.auth.user);
const employee = computed(() => props.employee || user.value?.employee);
const isEmployee = computed(() => user.value?.type === 'employee');
const hireDate = computed(() => formatDateOnly(props.employee.hire_date));
const avatarPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerUpload = () => {
    fileInput.value?.click();
};

// Handle the file selection for the preview
const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        avatarPreview.value = URL.createObjectURL(file);
    }
};

// Clear the preview and the hidden input
const removeAvatar = (event: Event) => {
    event.stopPropagation(); // Prevent triggerUpload from firing
    avatarPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = ''; // Clear the actual file input
    }
};

console.info(props.mustVerifyEmail)
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Cài đặt hồ sơ" />

        <h1 class="sr-only">Cài đặt hồ sơ</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    :title="
                        isEmployee ? 'Thông tin nhân viên' : 'Thông tin hồ sơ'
                    "
                    :description="
                        isEmployee
                            ? 'Cập nhật chi tiết công việc và tài khoản của bạn'
                            : 'Cập nhật tên và địa chỉ email của bạn'
                    "
                />

                <Form
                    v-bind="EmployeeProfileController.update()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                <div class="grid gap-2 col-span-1 md:col-span-2">
                    <div class="flex flex-col items-center justify-center gap-4">

                        <!-- CUSTOM UPLOADER UI (The "Lookalike") -->
                        <div
                            @click="triggerUpload"
                            class="group relative w-40 h-40 cursor-pointer overflow-hidden rounded-full border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 transition-colors hover:border-primary/50"
                        >
                            <!-- Actual Image Preview -->
                            <img
                                v-if="avatarPreview || avatar"
                                :src="avatarPreview || avatar"
                                class="w-full h-full object-cover"
                                alt="Avatar Preview"
                            />

                            <!-- Placeholder Icon -->
                            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-1 text-gray-400">
                                <ImageIcon class="h-6 w-6" />
                                <span class="text-[10px]">Tải ảnh</span>
                            </div>

                            <!-- Remove Button (X) -->
                            <button
                                v-if="avatarPreview || avatar"
                                @click="removeAvatar"
                                type="button"
                                class="absolute top-1 right-1 rounded-full bg-black/60 p-1 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-black/80"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </div>

                        <input
                            ref="fileInput"
                            type="file"
                            name="avatar"
                            class="hidden"
                            @change="handleFileChange"
                            accept="image/*"
                        />
                        <Label>Ảnh đại diện</Label>
                    </div>
                    <InputError class="mt-2" :message="errors.avatar" />
                </div>
                    <!-- Identity Section -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Tên</Label>
                            <Input
                                id="name"
                                class="mt-1 block w-full"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Họ và tên"
                            />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Địa chỉ email</Label>
                            <Input
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="Địa chỉ email"
                            />
                            <InputError class="mt-2" :message="errors.email" />
                        </div>
                        <div v-if="mustVerifyEmail && !user.email_verified_at">
                            <p class="-mt-4 text-sm text-muted-foreground">
                                Địa chỉ email của bạn chưa được xác minh.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                >
                                    Nhấn vào đây để gửi lại email xác minh.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600"
                            >
                                Một liên kết xác minh mới đã được gửi đến địa
                                chỉ email của bạn.
                            </div>
                        </div>
                    </div>

                    <!-- Employee Section: Using the caught 'employee' prop -->
                    <div
                        v-if="isEmployee"
                        class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-4 md:grid-cols-2 dark:border-gray-700"
                    >
                        <div class="grid gap-2">
                            <Label for="full_name">Họ và tên đầy đủ</Label>
                            <Input
                                id="full_name"
                                class="mt-1 block w-full"
                                name="full_name"
                                :default-value="employee?.full_name"
                                required
                                placeholder="Nguyễn Văn A"
                            />
                            <InputError
                                class="mt-2"
                                :message="errors.full_name"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Số điện thoại</Label>
                            <Input
                                id="phone"
                                class="mt-1 block w-full"
                                name="phone"
                                :default-value="employee?.phone"
                                placeholder="090..."
                            />
                            <InputError class="mt-2" :message="errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-muted-foreground"
                                >Phòng ban</Label
                            >
                            <div
                                class="mt-1 block w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 p-2 text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
                            >
                                {{
                                    departments?.find(
                                        (d) => d.id === employee?.department_id,
                                    )?.name || 'Không xác định'
                                }}
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-muted-foreground"
                                >Ngày vào làm</Label
                            >
                            <div
                                class="mt-1 block w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 p-2 text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
                            >
                                {{ hireDate || 'Không xác định' }}
                            </div>
                        </div>
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Địa chỉ email của bạn chưa được xác minh.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Nhấn vào đây để gửi lại email xác minh.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            Một liên kết xác minh mới đã được gửi đến địa chỉ
                            email của bạn.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Lưu
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Đã lưu.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
