<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';
</script>
<template>
    <AuthBase title="Đặt lại mật khẩu" description="Nhập mật khẩu mới của bạn">
        <Head title="Đặt lại mật khẩu" />
        <Form
            v-bind="update()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-2">
                <Label for="email">Địa chỉ email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    readonly
                />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="password">Mật khẩu mới</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="errors.password" />
            </div>
            <div class="grid gap-2">
                <Label for="password_confirmation">Xác nhận mật khẩu</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="errors.password_confirmation" />
            </div>
            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Đặt lại mật khẩu
            </Button>
        </Form>
    </AuthBase>
</template>
