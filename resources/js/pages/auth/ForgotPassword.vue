<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';
</script>

<template>
    <AuthBase
        title="Quên mật khẩu"
        description="Nhập email của bạn để nhận liên kết đặt lại mật khẩu"
    >
        <Head title="Quên mật khẩu" />
        <div class="mb-4 text-sm text-muted-foreground">
            Quên mật khẩu? Không vấn đề. Chỉ cần cho chúng tôi biết địa chỉ
            email của bạn và chúng tôi sẽ gửi cho bạn một liên kết đặt lại mật
            khẩu qua email để bạn có thể tạo mật khẩu mới.
        </div>
        <Form
            v-bind="email.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-2">
                <Label for="email">Địa chỉ email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>
            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Gửi liên kết đặt lại
            </Button>
            <div class="text-center text-sm">
                <TextLink :href="login()" class="underline underline-offset-4">
                    Quay lại đăng nhập
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
<script lang="ts">
import TextLink from '@/components/TextLink.vue';
</script>
