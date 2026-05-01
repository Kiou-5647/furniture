<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Settings2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, handleNumericInput } from '@/lib/utils';
import { update } from '@/routes/employee/settings/general';
import type { BreadcrumbItem } from '@/types/navigation';

type GeneralSettingsForm = {
    site_name: string;
    freeship_threshold: number;
    default_warranty: number;
    [key: string]: any;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tra cứu', href: '' }];

const props = defineProps<{
    settings: Record<string, any>;
    labels: Record<string, string>;
}>();

const form = useForm<GeneralSettingsForm>({
    site_name: props.settings.site_name,
    freeship_threshold: props.settings.freeship_threshold,
    default_warranty: props.settings.default_warranty,
});

const goBack = () => {
    window.history.back();
};

const submit = () => {
    // Use Wayfinder function to get the URL
    form.post(update().url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.info('Cập nhật cấu hình chung thành công');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="relative flex flex-col overflow-hidden"
            style="height: calc(100vh - 80px)"
        >
            <!-- HEADER: Sticky and Clean -->
            <div
                class="flex shrink-0 items-center justify-start gap-6 px-4 py-4"
            >
                <Button variant="outline" class="h-8 w-8" @click="goBack">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <div class="flex items-center gap-2">
                        <Settings2 class="h-5 w-5 text-muted-foreground" />
                        <h1 class="text-2xl font-bold">Cài đặt chung</h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Quản lý các thông số vận hành cơ bản của hệ thống.
                    </p>
                </div>
            </div>

            <div class="@container flex-1 overflow-y-auto px-4">
                <Card class="">
                    <CardHeader>
                        <CardTitle class="text-xl">Thông tin cơ bản</CardTitle>
                        <CardDescription />
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 py-2">
                            <div
                                v-for="(value, key) in settings"
                                :key="key"
                                class="grid items-center gap-2 sm:grid-cols-3"
                            >
                                <Label
                                    :for="key"
                                    class="text-right font-medium text-muted-foreground"
                                >
                                    {{ labels[key] }}
                                </Label>

                                <div class="sm:col-span-2">
                                    <template v-if="key === 'freeship_threshold'">
                                        <Input
                                            :id="key"
                                            type="text"
                                            inputmode="numeric"
                                            :model-value="formatNumber(form.freeship_threshold)"
                                            @input="handleNumericInput($event, key, form)"
                                            :class="{ 'border-destructive ring-destructive': form.errors[key] }"
                                            class="max-w-md"
                                            placeholder="Ví dụ: 500.000"
                                        />
                                    </template>
                                    <template v-else>
                                        <Input
                                            :id="key"
                                            v-model="form[key]"
                                            :type="
                                                key === 'default_warranty'
                                                    ? 'number'
                                                    : 'text'
                                            "
                                            :class="{
                                                'border-destructive ring-destructive':
                                                    form.errors[key],
                                            }"
                                            class="max-w-md"
                                        />
                                    </template>

                                    <p
                                        v-if="form.errors[key]"
                                        class="mt-1 text-xs font-medium text-destructive"
                                    >
                                        {{ form.errors[key] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- FOOTER: Sticky Action Bar -->
            <div
                class="flex shrink-0 justify-end gap-3 border-t bg-background p-4"
            >
                <Button variant="outline" size="sm" @click="goBack">
                    Hủy
                </Button>
                <Button
                    :disabled="form.processing"
                    @click="submit"
                    class="gap-2 px-8"
                >
                    <Save class="h-4 w-4" />
                    {{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}
                </Button>
            </div>
        </div>
    </AppLayout>
    <Sonner />
</template>
