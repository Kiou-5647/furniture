<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    Plus,
    Trash2,
    X,
    Palette,
    Image as ImageIcon,
    ListPlus,
} from '@lucide/vue';
import { computed, inject } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import type {
    LookupOptionGroup,
    ProductFormContext,
} from '../../../../../composables/useProductForm';

const ctx = inject<ProductFormContext>('productForm')!;
const page = usePage();

const props = defineProps<{
    variantOptions: LookupOptionGroup[];
}>();

const emit = defineEmits<{
    openLookupForm: [namespace: string];
}>();

const canManageLookups = computed(() => {
    const permissions = page.props.auth?.user?.permissions ?? [];
    return permissions.includes('*') || permissions.includes('lookups.manage');
});

const hasColorOptions = computed(() => {
    return ctx.selectedVariantOptions.some(
        (opt) => opt.metadata?.color_hex || opt.metadata?.hex_code,
    );
});

const selectedNamespaceLabel = computed(() => {
    const ns = props.variantOptions.find(
        (n) => n.namespace === ctx.selectedOptionNamespace,
    );
    return ns?.label ?? '';
});

function toggleSelection(opt: { slug: string }, checked: boolean) {
    if (checked) {
        ctx.selectedOptionValues.push(opt.slug);
    } else {
        ctx.selectedOptionValues = ctx.selectedOptionValues.filter(
            (s) => s !== opt.slug,
        );
    }
}

function toggleSwatch(gi: number) {
    const isCurrentlySwatch = ctx.form.option_groups[Number(gi)].is_swatches;
    ctx.form.option_groups.forEach((g: any) => {
        g.is_swatches = false;
    });
    if (!isCurrentlySwatch) {
        ctx.form.option_groups[Number(gi)].is_swatches = true;
    }
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <Label class="text-lg font-semibold">Nhóm tùy chọn</Label>
        </div>

        <div class="space-y-3 rounded-lg border p-4">
            <Label class="text-sm font-medium">Chọn nhóm từ Lookup</Label>
            <div class="flex gap-2">
                <Select v-model="ctx.selectedOptionNamespace">
                    <SelectTrigger class="h-9 flex-1 text-sm">
                        <SelectValue placeholder="Chọn nhóm thuộc tính..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="ns in variantOptions"
                            :key="ns.namespace"
                            :value="ns.namespace"
                            class="text-sm"
                        >
                            {{ ns.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div v-if="ctx.selectedOptionNamespace" class="space-y-2">
                <Input
                    v-model="ctx.customOptionGroupName"
                    :placeholder="`Tên nhóm (mặc định: ${selectedNamespaceLabel})`"
                    class="text-sm"
                />
            </div>

            <div
                v-if="
                    ctx.selectedOptionNamespace &&
                    ctx.selectedVariantOptions.length > 0
                "
                class="space-y-2"
            >
                <Label class="text-xs text-muted-foreground"
                    >Chọn giá trị:</Label
                >

                <div
                    class="grid max-h-52 grid-cols-2 gap-2 overflow-y-auto rounded-md border p-2"
                    :class="hasColorOptions ? 'grid-cols-3' : ''"
                >
                    <label
                        v-for="opt in ctx.selectedVariantOptions"
                        :key="opt.id"
                        class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 transition-all hover:bg-muted/50"
                        :class="
                            ctx.selectedOptionValues.includes(opt.slug)
                                ? 'border-primary bg-primary/5'
                                : ''
                        "
                    >
                        <Checkbox
                            :checked="
                                ctx.selectedOptionValues.includes(opt.slug)
                            "
                            @update:model-value="
                                toggleSelection(opt, $event as boolean)
                            "
                        />

                        <div
                            v-if="
                                opt.metadata?.color_hex ||
                                opt.metadata?.hex_code
                            "
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border"
                            :style="{
                                backgroundColor:
                                    opt.metadata?.color_hex ||
                                    opt.metadata?.hex_code,
                                borderColor:
                                    (opt.metadata?.color_hex ||
                                        opt.metadata?.hex_code) === '#FFFFFF' ||
                                    (opt.metadata?.color_hex ||
                                        opt.metadata?.hex_code) === '#ffffff'
                                        ? '#e5e7eb'
                                        : 'transparent',
                            }"
                        />

                        <img
                            v-else-if="opt.image_thumb_url || opt.image_url"
                            :src="opt.image_thumb_url! ?? opt.image_url!"
                            :alt="opt.label"
                            class="h-5 w-5 shrink-0 rounded-sm object-cover"
                        />

                        <Palette
                            v-else-if="hasColorOptions"
                            class="h-4 w-4 shrink-0 text-muted-foreground/50"
                        />

                        <ImageIcon
                            v-else-if="
                                variantOptions
                                    .flatMap((ns) => ns.options)
                                    .some(
                                        (o) => o.image_thumb_url || o.image_url,
                                    )
                            "
                            class="h-4 w-4 shrink-0 text-muted-foreground/50"
                        />

                        <span class="truncate text-sm">{{ opt.label }}</span>

                        <Badge
                            v-if="
                                opt.metadata?.color_hex ||
                                opt.metadata?.hex_code
                            "
                            variant="outline"
                            class="ml-auto h-4 shrink-0 px-1.5 font-mono text-xs"
                        >
                            {{
                                opt.metadata?.color_hex ||
                                opt.metadata?.hex_code
                            }}
                        </Badge>
                    </label>
                </div>
            </div>

            <div class="flex gap-2">
                <Button
                    type="button"
                    size="sm"
                    class="h-9 text-sm"
                    :disabled="
                        !ctx.selectedOptionNamespace ||
                        ctx.selectedOptionValues.length === 0
                    "
                    @click="ctx.addOptionGroup(variantOptions)"
                >
                    <Plus class="mr-1 h-4 w-4" /> Thêm nhóm
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-9 text-sm"
                    :disabled="
                        !canManageLookups || !ctx.selectedOptionNamespace
                    "
                    @click="
                        ctx.selectedOptionNamespace &&
                        emit('openLookupForm', ctx.selectedOptionNamespace)
                    "
                >
                    <ListPlus class="mr-1 h-4 w-4" /> Quản lý tra cứu
                </Button>
            </div>
        </div>

        <div
            v-for="(group, gi) in ctx.form.option_groups"
            :key="gi"
            class="space-y-2 rounded-lg border p-4"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Badge variant="secondary" class="text-sm font-medium">
                        {{ group.name }}
                    </Badge>
                    <span class="text-xs text-muted-foreground">
                        {{ group.options.length }} giá trị
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Switch
                        :model-value="group.is_swatches"
                        @update:model-value="toggleSwatch(Number(gi))"
                        class="h-4 w-7"
                    />
                    <span class="text-xs text-muted-foreground">Swatch</span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="h-7 w-7 p-0 text-destructive"
                        @click="ctx.removeOptionGroup(Number(gi))"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div class="ml-6 flex flex-wrap gap-2">
                <Badge
                    v-for="(option, oi) in group.options"
                    :key="oi"
                    variant="outline"
                    class="flex items-center gap-1.5 text-sm"
                >
                    <div
                        v-if="
                            option.metadata?.color_hex ||
                            option.metadata?.hex_code
                        "
                        class="h-3.5 w-3.5 shrink-0 rounded-full border"
                        :style="{
                            backgroundColor:
                                option.metadata?.color_hex ||
                                option.metadata?.hex_code,
                            borderColor:
                                (option.metadata?.color_hex ||
                                    option.metadata?.hex_code) === '#FFFFFF' ||
                                (option.metadata?.color_hex ||
                                    option.metadata?.hex_code) === '#ffffff'
                                    ? '#e5e7eb'
                                    : 'transparent',
                        }"
                    />

                    <img
                        v-else-if="option.image_thumb_url || option.image_url"
                        :src="option.image_thumb_url ?? option.image_url"
                        :alt="option.label"
                        class="h-4 w-4 shrink-0 rounded-sm object-cover"
                    />

                    <span>{{ option.label }}</span>

                    <button
                        type="button"
                        class="ml-0.5 hover:text-destructive"
                        @click="group.options.splice(oi, 1)"
                    >
                        <X class="h-3 w-3" />
                    </button>
                </Badge>
            </div>
        </div>

        <div
            v-if="ctx.form.option_groups.length === 0"
            class="py-6 text-center text-sm text-muted-foreground"
        >
            Chưa có nhóm tùy chọn nào. Chọn từ Lookup ở trên để thêm.
        </div>
    </div>
</template>
