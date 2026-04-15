<script setup lang="ts">
import heroImage from '@images/4787421-interior-2685521.jpg';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthSplitLayout
        title="Đăng nhập"
        description="Nhập thông tin tài khoản để tiếp tục."
    >
        <Head title="Đăng nhập" />

        <!-- Override left panel with hero image -->
        <template #left>
            <div class="relative hidden h-full flex-col p-10 text-white lg:flex">
                <img
                    :src="heroImage"
                    alt="Nội thất sang trọng"
                    class="absolute inset-0 h-full w-full object-cover brightness-[.65]"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-gray-700/80 via-gray-700/30 to-gray-700/10" />
                <div class="relative z-20 flex items-center text-lg font-semibold tracking-tight">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 40 42"
                        class="mr-2 h-6 w-6 fill-current text-white"
                    >
                        <path d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                    </svg>
                    Nội thất
                </div>
                <div class="relative z-20 mt-auto">
                    <blockquote class="text-lg font-light leading-relaxed tracking-tight text-white/90">
                        "Kiến tạo không gian sống tinh tế và hiện đại cho ngôi nhà của bạn."
                    </blockquote>
                    <p class="mt-3 text-sm text-white/60 font-medium">
                        — Nội Thất & Trang Trí
                    </p>
                </div>
            </div>
        </template>

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-700 ring-1 ring-green-600/20"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="font-medium">Địa chỉ email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-10"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="font-medium">Mật khẩu</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm font-medium"
                            :tabindex="5"
                        >
                            Quên mật khẩu?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="h-10"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <label
                        for="remember"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <Button
                    type="submit"
                    class="mt-2 h-10 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    {{ processing ? 'Đang xử lý...' : 'Đăng nhập' }}
                </Button>
            </div>

            <div
                v-if="canRegister"
                class="relative text-center text-sm text-muted-foreground"
            >
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t" />
                </div>
                <span class="relative bg-background px-2">
                    Hoặc
                </span>
            </div>
            <div
                v-if="canRegister"
                class="text-center text-sm text-muted-foreground -mt-4"
            >
                Chưa có tài khoản?
                <TextLink :href="register()" :tabindex="5" class="font-semibold">
                    Đăng ký ngay
                </TextLink>
            </div>
        </Form>
    </AuthSplitLayout>
</template>
