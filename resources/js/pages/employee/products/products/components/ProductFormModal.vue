<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle } from '@lucide/vue';
import { computed, provide, ref, watch } from 'vue';
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
import { Separator } from '@/components/ui/separator';
import {
    Stepper,
    StepperIndicator,
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
import type { SpecNamespace, SpecLookupOption } from '@/types';
import type { Product } from '@/types/product';
import StepContent from '../steps/StepContent.vue';
import StepOptions from '../steps/StepOptions.vue';
import StepVariants from '../steps/StepVariants.vue';

const LookupFormModal = createLazyComponent(
    () =>
        import('@/pages/employee/settings/lookups/components/LookupFormModal.vue'),
);

const props = defineProps<{
    open: boolean;
    product: Product | null;
    vendorOptions: { id: string; label: string }[];
    categoryOptions: { id: string; label: string }[];
    collectionOptions: { id: string; label: string }[];
    variantOptions: LookupOptionGroup[];
    featureOptions: LookupOptionItem[];
    specNamespaces: SpecNamespace[];
    allSpecLookupOptions: Record<string, SpecLookupOption[]>;
    lookupNamespaces: { id: string | null; slug: string; label: string }[];
}>();

const emit = defineEmits(['close']);

const ctx = useProductForm(
    props.product,
    props.open,
    props.variantOptions,
    props.featureOptions,
    props.allSpecLookupOptions,
    emit,
);

provide('productForm', ctx);

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
        Object.keys(ctx.form.specifications).length > 0 ||
        ctx.form.is_custom_made
    );
});

function requestClose() {
    if (hasUnsavedChanges.value) {
        showConfirmClose.value = true;
    } else {
        ctx.closeModal();
    }
}

function confirmClose() {
    showConfirmClose.value = false;
    ctx.closeModal();
}

function handleOpenLookupForm(namespace: string) {
    lookupFormNamespace.value = namespace;
    showLookupForm.value = true;
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
    <Dialog :open="open" @update:open="(val) => !val && requestClose()">
        <DialogContent
            class="flex max-h-[95vh] w-[90vw] max-w-7xl flex-col gap-0 overflow-hidden p-0 sm:max-w-5xl"
        >
            <DialogHeader class="px-5 pt-4">
                <DialogTitle class="text-2xl">{{
                    product ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới'
                }}</DialogTitle>
                <DialogDescription class="text-md">
                    {{
                        product
                            ? 'Cập nhật thông tin, biến thể và hình ảnh.'
                            : 'Tạo sản phẩm mới với đầy đủ thông tin.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <Separator class="mt-2" />

            <div class="flex flex-1 overflow-hidden">
                <!-- Vertical Stepper Sidebar -->
                <div
                    class="flex w-20 shrink-0 flex-col border-r px-2 py-4 md:w-40 md:px-4"
                >
                    <Stepper
                        v-model="ctx.currentStep"
                        class="mx-auto flex w-full max-w-md flex-col justify-start gap-10"
                        orientation="vertical"
                        :linear="false"
                    >
                        <StepperItem
                            v-for="(step, i) in ctx.steps"
                            :key="i"
                            v-slot="{ state }"
                            class="relative flex w-full cursor-pointer flex-col items-center gap-2 md:flex-row md:items-center md:gap-6"
                            :step="i + 1"
                        >
                            <StepperSeparator
                                v-if="i !== ctx.steps.length - 1"
                                class="absolute top-[60px] block h-full w-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary md:top-[30px] md:left-[15px] md:h-[150%]"
                            />

                            <StepperTrigger as-child>
                                <Button
                                    :variant="
                                        state === 'completed' ||
                                        state === 'active'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="z-10 h-8 w-8 shrink-0 rounded-full transition-all duration-200"
                                    :class="[
                                        state === 'active' &&
                                            'ring-2 ring-ring ring-offset-2 ring-offset-background',
                                        ctx.stepStates[i] === 'complete' &&
                                            state !== 'active' &&
                                            'bg-green-600 text-white hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600',
                                        ctx.stepStates[i] === 'incomplete' &&
                                            state !== 'active' &&
                                            'bg-amber-400 text-amber-900 hover:bg-amber-500 dark:bg-amber-600 dark:text-amber-100 dark:hover:bg-amber-500',
                                    ]"
                                >
                                    <StepperIndicator class="h-4 w-4">
                                        <CheckCircle2
                                            v-if="
                                                ctx.stepStates[i] === 'complete'
                                            "
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
                                class="text-xs leading-tight font-semibold md:text-sm"
                                :class="
                                    state === 'active'
                                        ? 'text-primary'
                                        : ctx.stepStates[i] === 'complete'
                                          ? 'text-green-600'
                                          : ctx.stepStates[i] === 'incomplete'
                                            ? 'text-amber-600'
                                            : 'text-muted-foreground'
                                "
                            >
                                {{ step.title }}
                            </StepperTitle>
                        </StepperItem>
                    </Stepper>
                </div>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto px-5 py-4">
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
                    </form>
                </div>
            </div>

            <Separator />

            <DialogFooter class="px-5 py-3">
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
                    <Button v-if="ctx.isValid" size="sm" @click="ctx.submit()">
                        {{ product ? 'Cập nhật' : 'Tạo sản phẩm' }}
                    </Button>
                    <Button v-else size="sm" disabled>
                        {{ product ? 'Cập nhật' : 'Tạo sản phẩm' }}
                    </Button>
                </template>
            </DialogFooter>
        </DialogContent>
    </Dialog>

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
                <Button variant="destructive" @click="confirmClose">
                    Đóng và xóa thay đổi
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <LookupFormModal
        v-if="showLookupForm"
        :open="showLookupForm"
        :namespace_id="lookupNamespaceId"
        :display_namespace="lookupDisplayNamespace"
        :lookup="null"
        :namespaces="lookupNamespaces"
        @close="handleLookupFormClosed"
    />
</template>
