<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Xác minh email"
        description="Vui lòng xác minh địa chỉ email của bạn bằng cách nhấp vào liên kết mà chúng tôi vừa gửi cho bạn."
    >
        <Head title="Xác minh email" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            Một liên kết xác minh mới đã được gửi đến địa chỉ email bạn đã cung
            cấp trong quá trình đăng ký.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                Gửi lại email xác minh
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Đăng xuất
            </TextLink>
        </Form>
    </AuthLayout>
</template>
