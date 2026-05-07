<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle2,
    ListPlus,
    ChevronRight,
} from '@lucide/vue';
import { StepperIndicator } from 'reka-ui';
import { provide, ref, watch, computed } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import Badge from '@/components/ui/badge/Badge.vue';
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
    Stepper,
    StepperItem,
    StepperTitle,
    StepperTrigger,
} from '@/components/ui/stepper';
import { createLazyComponent } from '@/composables/createLazyComponent';
import { useProductForm } from '@/composables/useProductForm';
import type {
    LookupOptionGroup,
    LookupOptionItem,
} from '@/composables/useProductForm';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/employee/products';
import type { SpecNamespace, SpecLookupOption } from '@/types';
import type { Product } from '@/types/product';
import StepContent from './StepContent.vue';
import StepOptions from './StepOptions.vue';
import StepStock from './StepStock.vue';
import StepVariants from './StepVariants.vue';

const LookupFormModal = createLazyComponent(
    () =>
        import('@/pages/employee/settings/lookups/components/LookupFormModal.vue'),
);

const props = defineProps<{
    product: Product | null;
    vendorOptions: { id: string; label: string }[];
    categoryOptions: { id: string; label: string }[];
    collectionOptions: { id: string; label: string }[];
    locationOptions: {
        id: string;
        code: string;
        label: string;
        type: string;
        address: string;
    }[];
    variantOptions: LookupOptionGroup[];
    featureOptions: LookupOptionItem[];
    specNamespaces: SpecNamespace[];
    allSpecLookupOptions: Record<string, SpecLookupOption[]>;
    lookupNamespaces: { id: string | null; slug: string; label: string }[];
}>();

const ctx = useProductForm(
    props.product,
    props.variantOptions,
    props.featureOptions,
    props.allSpecLookupOptions,
    () => router.visit(index()),
);

const page = usePage();

provide('productForm', ctx);

const stockStepRef = ref<InstanceType<typeof StepStock> | null>(null);

watch(
    () => props.variantOptions,
    (newOptions) => {
        ctx.updateVariantOptions(newOptions);
    },
    { deep: true },
);

watch(
    () => props.featureOptions,
    (newOptions) => {
        ctx.updateFeatureOptions(newOptions);
    },
    { deep: true },
);

const showConfirmClose = ref(false);
const showLookupForm = ref(false);
const lookupFormNamespace = ref('');

const hasUnsavedChanges = computed(() => {
    return (
        ctx.form.name.trim() !== '' ||
        ctx.form.vendor_id !== null ||
        ctx.form.category_id !== null ||
        ctx.form.collection_id !== null ||
        ctx.form.option_groups.length > 0 ||
        ctx.form.variants.length > 0 ||
        ctx.form.features.length > 0 ||
        ctx.form.care_instructions.length > 0 ||
        Object.keys(ctx.form.specifications).length > 0
    );
});

function requestClose() {
    if (hasUnsavedChanges.value) {
        showConfirmClose.value = true;
    } else {
        router.visit(index().url);
    }
}

function confirmClose() {
    showConfirmClose.value = false;
    router.visit(index().url);
}

function handleOpenLookupForm(namespace: string) {
    lookupFormNamespace.value = namespace;
    showLookupForm.value = true;
}

function handleSubmit() {
    if (stockStepRef.value?.checkPriceAndSubmit) {
        stockStepRef.value.checkPriceAndSubmit();
    } else {
        ctx.form._force_update_price = false;
        ctx.submit();
    }
}

const lookupNamespaceId = computed(() => {
    const ns = props.lookupNamespaces.find(
        (n) => n.slug === lookupFormNamespace.value,
    );
    return ns?.id ?? '';
});

const lookupDisplayNamespace = computed(() => {
    const ns = props.lookupNamespaces.find(
        (n) => n.slug === lookupFormNamespace.value,
    );
    return ns?.label ?? lookupFormNamespace.value;
});

function handleLookupFormClosed() {
    showLookupForm.value = false;
    router.reload({
        only: [
            'variantOptions',
            'featureOptions',
            'specNamespaces',
            'allSpecLookupOptions',
        ],
    });
}

const canManageLookups = computed(() => {
    const permissions = page.props.auth?.user?.permissions ?? [];
    return permissions.includes('*') || permissions.includes('Quản lý tra cứu');
});

const currentStepTitle = computed(() => {
    return ctx.steps[ctx.currentStep - 1]?.title || 'Chi tiết sản phẩm';
});
</script>

<template>
    <AppLayout>
        <div
            class="relative flex flex-col overflow-hidden bg-muted/10"
            style="height: calc(100vh - 80px)"
        >
            <!-- PREMIUM HEADER -->
            <div
                class="z-20 flex shrink-0 items-center justify-between gap-6 border-b bg-background/80 px-8 py-5 backdrop-blur-md"
            >
                <div class="flex items-center gap-5">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-10 w-10 rounded-full transition-all hover:bg-muted active:scale-90"
                        @click="requestClose()"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                    <div class="h-8 w-[1px] bg-border" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                {{
                                    product
                                        ? 'Chỉnh sửa sản phẩm'
                                        : 'Thêm sản phẩm mới'
                                }}
                            </h1>
                            <Badge
                                variant="secondary"
                                class="px-2 py-0 text-[10px] font-bold tracking-wider uppercase"
                            >
                                {{ currentStepTitle }}
                            </Badge>
                        </div>
                        <p class="max-w-md text-sm text-muted-foreground">
                            {{
                                product
                                    ? 'Cập nhật thông tin chi tiết, biến thể và hình ảnh sản phẩm.'
                                    : 'Thiết lập thông tin cơ bản và cấu hình cho sản phẩm mới.'
                            }}
                        </p>
                    </div>
                </div>

                <div class="hidden items-center gap-4 sm:flex">
                    <div
                        class="flex items-center gap-2 rounded-full border bg-muted/50 px-3 py-1"
                    >
                        <span class="text-xs font-medium text-muted-foreground"
                            >Tiến độ:</span
                        >
                        <span class="text-xs font-bold text-primary">
                            Bước {{ ctx.currentStep }} / {{ ctx.steps.length }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- MOBILE NAVIGATION (Enhanced) -->
            <div
                class="shrink-0 border-b bg-background/50 px-4 py-3 backdrop-blur-sm lg:hidden"
            >
                <Stepper
                    v-model="ctx.currentStep"
                    class="flex w-full items-center justify-between"
                    orientation="horizontal"
                    :linear="false"
                >
                    <StepperItem
                        v-for="(step, i) in ctx.steps"
                        :key="i"
                        v-slot="{ state }"
                        class="relative flex flex-1 flex-col items-center gap-1.5"
                        :step="i + 1"
                    >
                        <StepperTrigger as-child>
                            <Button
                                :variant="
                                    state === 'active' ? 'default' : 'secondary'
                                "
                                class="z-10 h-9 w-9 shrink-0 rounded-full shadow-sm transition-all duration-300"
                                :class="{
                                    'bg-green-500 text-white hover:bg-green-600':
                                        ctx.stepStates[i] === 'complete' &&
                                        state !== 'active',
                                    'bg-amber-400 text-amber-900 hover:bg-amber-500':
                                        ctx.stepStates[i] === 'incomplete' &&
                                        state !== 'active',
                                    'scale-110 ring-4 ring-primary/20':
                                        state === 'active',
                                }"
                            >
                                <StepperIndicator class="h-4 w-4">
                                    <CheckCircle2
                                        v-if="ctx.stepStates[i] === 'complete'"
                                        class="h-3.5 w-3.5"
                                    />
                                    <component
                                        v-else
                                        :is="step.icon"
                                        class="h-3.5 w-3.5"
                                    />
                                </StepperIndicator>
                            </Button>
                        </StepperTrigger>
                        <StepperTitle
                            class="text-[10px] font-bold tracking-tighter uppercase"
                            :class="
                                state === 'active'
                                    ? 'text-primary'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ step.title }}
                        </StepperTitle>
                    </StepperItem>
                </Stepper>
            </div>
            <!-- MAIN WORKSPACE -->
            <div class="flex min-h-0 flex-1 overflow-hidden">
                <!-- DESKTOP SIDEBAR (Studio Style) -->
                <div
                    class="hidden w-64 flex-col overflow-hidden border-r bg-background px-4 py-8 lg:flex"
                >
                    <Stepper
                        v-model="ctx.currentStep"
                        class="flex w-full flex-col gap-3"
                        orientation="vertical"
                        :linear="false"
                    >
                        <StepperItem
                            v-for="(step, i) in ctx.steps"
                            :key="i"
                            v-slot="{ state }"
                            class="relative flex w-full cursor-pointer flex-row items-center gap-3"
                            :step="i + 1"
                        >
                            <StepperTrigger as-child>
                                <div
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200"
                                    :class="
                                        state === 'active'
                                            ? 'bg-primary/10 text-primary shadow-sm ring-1 ring-primary/20'
                                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                    "
                                >
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition-all"
                                        :class="
                                            state === 'active'
                                                ? 'bg-primary text-primary-foreground shadow-sm'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                    >
                                        <component
                                            :is="step.icon"
                                            class="h-3.5 w-3.5"
                                        />
                                    </div>
                                    <StepperTitle
                                        class="text-sm leading-none font-semibold"
                                    >
                                        {{ step.title }}
                                    </StepperTitle>
                                </div>
                            </StepperTrigger>
                        </StepperItem>
                    </Stepper>
                </div>

                <!-- CONTENT AREA -->
                <div class="flex-1 overflow-y-auto bg-muted/10 px-6 py-8">
                    <div class="mx-auto max-w-5xl">
                        <form @submit.prevent="ctx.submit" class="h-full">
                            <div class="transition-all duration-300">
                                <StepContent
                                    v-show="ctx.currentStep === 1"
                                    :vendor-options="vendorOptions"
                                    :category-options="categoryOptions"
                                    :collection-options="collectionOptions"
                                    :product="product"
                                    :feature-options="featureOptions"
                                    :spec-namespaces="specNamespaces"
                                    @open-lookup-form="handleOpenLookupForm"
                                />
                                <StepOptions
                                    v-show="ctx.currentStep === 2"
                                    :variant-options="variantOptions"
                                    @open-lookup-form="handleOpenLookupForm"
                                />
                                <StepVariants
                                    v-show="ctx.currentStep === 3"
                                    :spec-namespaces="specNamespaces"
                                />
                                <StepStock
                                    v-show="ctx.currentStep === 4"
                                    :location-options="locationOptions"
                                    ref="stockStepRef"
                                />
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- PROFESSIONAL TOOLBAR (Footer) -->
            <div
                class="z-10 flex shrink-0 items-center justify-between border-t bg-background/80 px-8 py-4 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] backdrop-blur-md"
            >
                <div class="flex items-center gap-3">
                    <Button
                        v-if="canManageLookups"
                        variant="outline"
                        size="sm"
                        class="text-xs font-medium transition-all hover:bg-primary/5 hover:text-primary"
                        @click="handleOpenLookupForm('chat-lieu')"
                    >
                        <ListPlus class="mr-2 h-3.5 w-3.5" /> Quản lý tra cứu
                    </Button>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="px-5"
                        @click="requestClose()"
                    >
                        Hủy
                    </Button>
                    <Button
                        v-if="ctx.currentStep > 1"
                        variant="outline"
                        size="sm"
                        class="px-5"
                        @click="ctx.prevStep()"
                    >
                        Quay lại
                    </Button>

                    <div class="flex items-center gap-3">
                        <!-- Validation Alert: Fixed as a contained Toast -->
                        <div
                            v-if="
                                !ctx.isValid && ctx.validationErrors.length > 0
                            "
                            class="fixed right-8 bottom-24 z-50 w-80 animate-in fade-in slide-in-from-bottom-4"
                        >
                            <Alert
                                variant="destructive"
                                class="border-destructive/20 bg-destructive/95 text-white shadow-2xl"
                            >
                                <AlertCircle class="h-4 w-4" />
                                <AlertTitle class="text-xs font-bold"
                                    >Thiếu thông tin bắt buộc</AlertTitle
                                >
                                <AlertDescription
                                    class="text-[11px]"
                                >
                                    <ul class="mt-1 list-none space-y-1 p-0">
                                        <li
                                            v-for="(
                                                err, i
                                            ) in ctx.validationErrors"
                                            :key="i"
                                            class="flex items-start gap-1"
                                        >
                                            <span class="text-white/50">•</span>
                                            {{ err }}
                                        </li>
                                    </ul>
                                </AlertDescription>
                            </Alert>
                        </div>

                        <Button
                            v-if="ctx.currentStep < ctx.steps.length"
                            size="sm"
                            class="px-8 font-bold transition-all hover:scale-105"
                            @click="ctx.nextStep()"
                        >
                            Tiếp theo
                            <ChevronRight class="ml-2 h-4 w-4" />
                        </Button>
                        <template v-else>
                            <Button
                                size="sm"
                                :disabled="!ctx.isValid"
                                class="px-8 font-bold transition-all hover:scale-105"
                                @click="handleSubmit()"
                            >
                                {{
                                    product
                                        ? 'Cập nhật sản phẩm'
                                        : 'Hoàn tất & Tạo sản phẩm'
                                }}
                            </Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <LookupFormModal
        v-if="showLookupForm"
        :open="showLookupForm"
        :namespace_id="lookupNamespaceId"
        :display_namespace="lookupDisplayNamespace"
        :lookup="null"
        :namespaces="lookupNamespaces"
        @close="handleLookupFormClosed"
    />
    <Dialog :open="showConfirmClose" @update:open="showConfirmClose = $event">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Xác nhận đóng</DialogTitle>
                <DialogDescription>
                    Bạn có thay đổi chưa lưu. Đóng sẽ mất tất cả dữ liệu đã
                    nhập. Bạn có chắc chắn muốn đóng?
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showConfirmClose = false">
                    Tiếp tục chỉnh sửa
                </Button>
                <Button variant="destructive" @click="confirmClose()">
                    Đóng và xóa thay đổi
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
