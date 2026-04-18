<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, CheckCircle2 } from '@lucide/vue';
import { StepperIndicator } from 'reka-ui';
import { provide, ref, watch, computed } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
    StepperSeparator,
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
import { index } from '@/routes/employee/products/items';
import type { SpecNamespace, SpecLookupOption } from '@/types';
import type { Product } from '@/types/product';
import StepContent from './steps/StepContent.vue';
import StepOptions from './steps/StepOptions.vue';
import StepStock from './steps/StepStock.vue';
import StepVariants from './steps/StepVariants.vue';

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
    console.info(ctx.form.variants);
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
</script>

<template>
    <AppLayout>
        <div
            class="relative flex flex-col overflow-hidden"
            style="height: calc(100vh - 80px)"
        >
            <!-- HEADER -->
            <div
                class="flex shrink-0 items-center justify-start gap-6 px-4 py-3"
            >
                <Button
                    variant="outline"
                    class="h-8 w-8"
                    @click="requestClose()"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">
                        {{
                            product ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới'
                        }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{
                            product
                                ? 'Cập nhật thông tin, biến thể và hình ảnh.'
                                : 'Tạo sản phẩm mới với đầy đủ thông tin.'
                        }}
                    </p>
                </div>
            </div>
            <!-- MOBILE STEPPER -->
            <div class="shrink-0 border-t border-b px-4 py-3 md:hidden">
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
                        <StepperSeparator
                            v-if="i !== ctx.steps.length - 1"
                            class="absolute top-3 left-[63%] block h-0.5 w-[80%] shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                        />

                        <StepperTrigger as-child>
                            <Button
                                :variant="
                                    ctx.stepStates[i] === 'complete' ||
                                    ctx.stepStates[i] === 'incomplete' ||
                                    state === 'active'
                                        ? 'default'
                                        : 'secondary'
                                "
                                class="z-10 h-7 w-7 shrink-0 rounded-full transition-all duration-200"
                                :class="{
                                    'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600':
                                        ctx.stepStates[i] === 'complete' &&
                                        state !== 'active',
                                    'bg-amber-400 text-amber-900 hover:bg-amber-500 dark:bg-amber-600 dark:text-amber-100 dark:hover:bg-amber-500':
                                        ctx.stepStates[i] === 'incomplete' &&
                                        state !== 'active',
                                    'ring-2 ring-ring ring-offset-2 ring-offset-background':
                                        state === 'active',
                                }"
                            >
                                <StepperIndicator class="h-3.5 w-3.5">
                                    <CheckCircle2
                                        v-if="ctx.stepStates[i] === 'complete'"
                                        class="h-3 w-3"
                                        :class="
                                            ctx.stepStates[i] === 'complete' &&
                                            state !== 'active'
                                                ? 'text-black dark:text-white'
                                                : ''
                                        "
                                    />
                                    <component
                                        v-else-if="
                                            ctx.stepStates[i] === 'incomplete'
                                        "
                                        :is="step.icon"
                                        class="h-3 w-3"
                                        :class="
                                            ctx.stepStates[i] ===
                                                'incomplete' &&
                                            state !== 'active'
                                                ? 'text-amber-900 dark:text-amber-100'
                                                : 'text-current'
                                        "
                                    />
                                    <component
                                        v-else
                                        :is="step.icon"
                                        class="h-3 w-3"
                                        :class="
                                            state === 'active'
                                                ? 'text-foreground'
                                                : 'text-muted-foreground'
                                        "
                                    />
                                </StepperIndicator>
                            </Button>
                        </StepperTrigger>
                        <StepperTitle
                            class="text-[10px] leading-tight font-medium"
                            :class="{
                                'text-green-600 dark:text-green-500':
                                    ctx.stepStates[i] === 'complete',
                                'text-amber-600 dark:text-amber-500':
                                    ctx.stepStates[i] === 'incomplete',
                                'text-primary': state === 'active',
                                'text-muted-foreground':
                                    ctx.stepStates[i] === 'untouched',
                            }"
                        >
                            {{ step.title }}
                        </StepperTitle>
                    </StepperItem>
                </Stepper>
            </div>
            <!-- MIDDLE -->
            <div class="flex min-h-0 flex-1 overflow-hidden border-t">
                <div
                    class="hidden w-40 shrink-0 flex-col overflow-hidden border-r px-4 py-4 md:flex"
                >
                    <Stepper
                        v-model="ctx.currentStep"
                        class="flex w-full flex-col justify-start gap-10"
                        orientation="vertical"
                        :linear="false"
                    >
                        <StepperItem
                            v-for="(step, i) in ctx.steps"
                            :key="i"
                            v-slot="{ state }"
                            class="relative flex w-full cursor-pointer flex-row items-center gap-6"
                            :step="i + 1"
                        >
                            <StepperSeparator
                                v-if="i !== ctx.steps.length - 1"
                                class="absolute top-[30px] left-[15px] block h-[150%] w-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                            />

                            <StepperTrigger as-child>
                                <Button
                                    :variant="
                                        ctx.stepStates[i] === 'complete' ||
                                        ctx.stepStates[i] === 'incomplete' ||
                                        state === 'active'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="z-10 h-8 w-8 shrink-0 rounded-full transition-all duration-200"
                                    :class="{
                                        'bg-green-600 text-white hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600':
                                            ctx.stepStates[i] === 'complete' &&
                                            state !== 'active',
                                        'bg-amber-400 text-amber-900 hover:bg-amber-500 dark:bg-amber-600 dark:text-amber-100 dark:hover:bg-amber-500':
                                            ctx.stepStates[i] ===
                                                'incomplete' &&
                                            state !== 'active',
                                        'ring-2 ring-ring ring-offset-2 ring-offset-background':
                                            state === 'active',
                                    }"
                                >
                                    <StepperIndicator class="h-4 w-4">
                                        <CheckCircle2
                                            v-if="
                                                ctx.stepStates[i] === 'complete'
                                            "
                                            class="h-3.5 w-3.5"
                                            :class="
                                                ctx.stepStates[i] ===
                                                    'complete' &&
                                                state !== 'active'
                                                    ? 'text-black dark:text-white'
                                                    : ''
                                            "
                                        />
                                        <component
                                            v-else-if="
                                                ctx.stepStates[i] ===
                                                'incomplete'
                                            "
                                            :is="step.icon"
                                            class="h-3.5 w-3.5"
                                            :class="
                                                ctx.stepStates[i] ===
                                                    'incomplete' &&
                                                state !== 'active'
                                                    ? 'text-amber-900 dark:text-amber-100'
                                                    : 'text-current'
                                            "
                                        />
                                        <component
                                            v-else
                                            :is="step.icon"
                                            class="h-3.5 w-3.5"
                                            :class="
                                                state === 'active'
                                                    ? 'text-foreground'
                                                    : 'text-muted-foreground'
                                            "
                                        />
                                    </StepperIndicator>
                                </Button>
                            </StepperTrigger>
                            <StepperTitle
                                class="text-[10px] leading-tight font-medium"
                                :class="{
                                    'text-green-600 dark:text-green-500':
                                        ctx.stepStates[i] === 'complete',
                                    'text-amber-600 dark:text-amber-500':
                                        ctx.stepStates[i] === 'incomplete',
                                    'text-primary': state === 'active',
                                    'text-muted-foreground':
                                        ctx.stepStates[i] === 'untouched',
                                }"
                            >
                                {{ step.title }}
                            </StepperTitle>
                        </StepperItem>
                    </Stepper>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-4">
                    <form @submit.prevent="ctx.submit" class="h-full">
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
                    </form>
                </div>
            </div>
            <!-- FOOTER -->
            <div
                class="flex shrink-0 justify-end gap-3 border-t bg-background p-4"
            >
                <Button variant="outline" size="sm" @click="requestClose()">
                    Hủy
                </Button>
                <Button
                    v-if="ctx.currentStep > 1"
                    variant="outline"
                    size="sm"
                    @click="ctx.prevStep()"
                >
                    Quay lại
                </Button>
                <Button
                    v-if="ctx.currentStep < ctx.steps.length"
                    size="sm"
                    @click="ctx.nextStep()"
                >
                    Tiếp theo
                </Button>
                <template v-else>
                    <Alert
                        v-if="!ctx.isValid && ctx.validationErrors.length > 0"
                        variant="destructive"
                        class="mr-auto px-3 py-2"
                    >
                        <AlertCircle class="h-3.5 w-3.5" />
                        <AlertTitle class="text-xs">
                            Thiếu thông tin bắt buộc
                        </AlertTitle>
                        <AlertDescription class="text-xs">
                            <ul class="mt-1 ml-4 list-disc">
                                <li
                                    v-for="(err, i) in ctx.validationErrors"
                                    :key="i"
                                >
                                    {{ err }}
                                </li>
                            </ul>
                        </AlertDescription>
                    </Alert>
                    <Button
                        v-if="ctx.isValid"
                        size="sm"
                        @click="handleSubmit()"
                    >
                        {{ product ? 'Cập nhật' : 'Tạo sản phẩm' }}
                    </Button>
                    <Button v-else size="sm" disabled>
                        {{ product ? 'Cập nhật' : 'Tạo sản phẩm' }}
                    </Button>
                </template>
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
