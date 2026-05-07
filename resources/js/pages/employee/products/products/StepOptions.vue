<script setup lang="ts">
import { Plus, Trash2, X, Layers, Search } from '@lucide/vue';
import { computed, inject } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/SearchableSelect.vue'; // Integrated
import { Switch } from '@/components/ui/switch';
import type {
    LookupOptionGroup,
    ProductFormContext,
} from '@/composables/useProductForm';

const ctx = inject<ProductFormContext>('productForm')!;

const props = defineProps<{
    variantOptions: LookupOptionGroup[];
}>();

const totalSelectedValues = computed(() => ctx.selectedOptionValues.length);

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
    const groupIdx = Number(gi);
    const isCurrentlySwatch = ctx.form.option_groups[groupIdx].is_swatches;
    ctx.form.option_groups.forEach((g: any) => {
        g.is_swatches = false;
    });
    if (!isCurrentlySwatch) {
        ctx.form.option_groups[groupIdx].is_swatches = true;
    }
}
function getFullOption(slug: string) {
    return props.variantOptions
        .flatMap((ns) => ns.options)
        .find((o) => o.slug == slug);
}

console.info(props.variantOptions);
</script>

<template>
    <div class="space-y-10 pb-6">
        <!-- HEADER -->
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="rounded-md bg-primary/10 p-1.5">
                    <Layers class="h-5 w-5 text-primary" />
                </div>
                <Label class="text-lg font-bold tracking-tight"
                    >Tùy chọn biến thể</Label
                >
            </div>
            <p class="text-sm text-muted-foreground">
                Thiết lập các thuộc tính để tạo biến thể sản phẩm (ví dụ: Màu
                sắc, Kích thước, Chất liệu).
            </p>
        </div>

        <!-- PHASE 1: THE ATTRIBUTE WORKBENCH -->
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground"
                >
                    1
                </div>
                <Label class="font-semibold">Chọn và Thêm thuộc tính</Label>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- LEFT: COMMAND CENTER -->
                <div class="w-full shrink-0 space-y-4 lg:w-80">
                    <div
                        class="space-y-6 rounded-2xl border bg-background p-5 shadow-sm"
                    >
                        <div class="space-y-3">
                            <Label
                                class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Nhóm tra cứu</Label
                            >
                            <SearchableSelect
                                v-model="ctx.selectedOptionNamespace"
                                :options="variantOptions"
                                value-key="namespace"
                                label-key="label"
                                placeholder="Tìm nhóm thuộc tính..."
                                class="w-full"
                            />
                        </div>

                        <div
                            v-if="ctx.selectedOptionNamespace"
                            class="space-y-4 border-t pt-4"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-medium text-muted-foreground"
                                    >Đã chọn:</span
                                >
                                <Badge
                                    variant="secondary"
                                    class="px-2 text-[10px]"
                                    >{{ totalSelectedValues }} mục</Badge
                                >
                            </div>

                            <!-- Primary Action: Add Group -->
                            <Button
                                @click="ctx.addOptionGroup(variantOptions)"
                                :disabled="totalSelectedValues === 0"
                                class="w-full rounded-xl py-6 font-bold transition-all hover:scale-[1.02] active:scale-95"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Thêm nhóm biến thể
                            </Button>
                            <p
                                class="text-center text-[10px] text-muted-foreground italic"
                            >
                                Lưu nhóm này vào danh sách cấu hình bên dưới
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: VALUE PICKER -->
                <div class="flex-1">
                    <div
                        v-if="ctx.selectedOptionNamespace"
                        class="min-h-[300px] rounded-2xl border bg-background p-6 shadow-sm"
                    >
                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <label
                                v-for="opt in ctx.selectedVariantOptions"
                                :key="opt.id"
                                class="group relative flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition-all hover:border-primary/40 hover:bg-primary/5"
                                :class="
                                    ctx.selectedOptionValues.includes(opt.slug)
                                        ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
                                        : 'bg-card'
                                "
                            >
                                <Checkbox
                                    :model-value="
                                        ctx.selectedOptionValues.includes(
                                            opt.slug,
                                        )
                                    "
                                    @update:model-value="
                                        toggleSelection(opt, $event as boolean)
                                    "
                                    class="h-4 w-4"
                                />
                                <div class="flex flex-col overflow-hidden">
                                    <span
                                        class="truncate text-sm font-medium"
                                        >{{ opt.label }}</span
                                    >
                                    <span
                                        v-if="opt.description"
                                        class="truncate text-[10px] text-muted-foreground"
                                        >{{ opt.description }}</span
                                    >
                                </div>

                                <!-- VISUAL PREVIEW: Image first, then Color -->
                                <div
                                    class="absolute right-3 flex items-center justify-center"
                                >
                                    <img
                                        v-if="
                                            opt.image_thumb_url || opt.image_url
                                        "
                                        :src="
                                            opt.image_thumb_url ||
                                            opt.image_url!
                                        "
                                        class="h-10 w-10 rounded-md border object-cover shadow-sm"
                                    />
                                    <div
                                        v-else-if="
                                            opt.metadata?.color_hex ||
                                            opt.metadata?.hex_code
                                        "
                                        class="h-4 w-4 rounded-full border shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                opt.metadata?.color_hex ||
                                                opt.metadata?.hex_code,
                                        }"
                                    />
                                </div>
                            </label>
                        </div>
                        <div
                            v-if="ctx.selectedVariantOptions.length === 0"
                            class="py-12 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                Không tìm thấy giá trị nào trong nhóm này.
                            </p>
                        </div>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-dashed bg-muted/20 p-12 text-center"
                    >
                        <Search
                            class="mx-auto mb-3 h-8 w-8 text-muted-foreground"
                        />
                        <p class="text-sm font-medium text-muted-foreground">
                            Vui lòng chọn một nhóm tra cứu để bắt đầu chọn giá
                            trị.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHASE 2: CONFIGURATION ZONE -->
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground"
                >
                    2
                </div>
                <Label class="font-semibold">Cấu hình nhóm hiển thị</Label>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div
                    v-for="(group, gi) in ctx.form.option_groups"
                    :key="gi"
                    class="group overflow-hidden rounded-2xl border bg-background shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="flex items-center justify-between border-b bg-muted/30 px-4 py-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-md border bg-background shadow-sm"
                            >
                                <Layers
                                    class="h-3.5 w-3.5 text-muted-foreground"
                                />
                            </div>
                            <Input
                                v-model="group.name"
                                class="h-7 border-none bg-transparent p-0 text-sm font-bold focus-visible:ring-0"
                                placeholder="Tên nhóm..."
                            />
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-7 w-7 text-muted-foreground hover:text-destructive"
                            @click="ctx.removeOptionGroup(Number(gi))"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>

                    <div class="space-y-4 p-4">
                        <div class="flex items-center justify-between">
                            <Label
                                class="text-xs font-semibold text-muted-foreground"
                                >Giá trị thuộc nhóm này</Label
                            >
                            <div class="flex items-center gap-2">
                                <Label class="text-[10px] font-medium"
                                    >Hiển thị màu/ảnh</Label
                                >
                                <Switch
                                    :model-value="group.is_swatches"
                                    @update:model-value="
                                        toggleSwatch(Number(gi))
                                    "
                                    class="h-4 w-7"
                                />
                            </div>
                        </div>

                        <div
                            v-for="(option, oi) in group.options"
                            :key="oi"
                            class="group/item relative flex items-center gap-3 rounded-full border bg-muted/50 px-3 py-1.5 transition-all hover:border-primary/30 hover:bg-muted"
                        >
                            <!-- VISUALS: Only render if swatch mode is ON -->
                            <div
                                v-if="group.is_swatches"
                                class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-background shadow-sm"
                            >
                                <template v-if="getFullOption(option.value)">
                                    <!-- Image -->
                                    <img
                                        v-if="
                                            getFullOption(option.value)
                                                ?.image_thumb_url ||
                                            getFullOption(option.value)
                                                ?.image_url
                                        "
                                        :src="
                                            getFullOption(option.value)
                                                ?.image_thumb_url ||
                                            getFullOption(option.value)
                                                ?.image_url!
                                        "
                                        class="h-full w-full object-cover"
                                    />
                                    <!-- Color Swatch -->
                                    <div
                                        v-else-if="
                                            getFullOption(option.value)
                                                ?.metadata?.color_hex ||
                                            getFullOption(option.value)
                                                ?.metadata?.hex_code
                                        "
                                        class="h-full w-full"
                                        :style="{
                                            backgroundColor:
                                                getFullOption(option.value)
                                                    ?.metadata?.color_hex ||
                                                getFullOption(option.value)
                                                    ?.metadata?.hex_code,
                                        }"
                                    />
                                    <!-- Fallback Icon if no image/color but swatch mode is ON -->
                                    <Layers
                                        v-else
                                        class="h-3.5 w-3.5 text-muted-foreground/50"
                                    />
                                </template>
                                <Layers
                                    v-else
                                    class="h-3.5 w-3.5 text-muted-foreground/50"
                                />
                            </div>

                            <!-- LABEL -->
                            <span class="text-xs font-medium">{{
                                option.label
                            }}</span>

                            <!-- HEX CODE: Only show in 'Normal' mode for better contrast -->
                            <Badge
                                v-if="
                                    !group.is_swatches &&
                                    (option.metadata?.color_hex ||
                                        getFullOption(option.value)?.metadata
                                            ?.color_hex)
                                "
                                variant="outline"
                                class="ml-1 h-4 px-1 font-mono text-[9px] text-muted-foreground"
                            >
                                {{
                                    option.metadata?.color_hex ||
                                    getFullOption(option.value)?.metadata
                                        ?.color_hex
                                }}
                            </Badge>

                            <!-- DELETE BUTTON -->
                            <Button
                                variant="ghost"
                                size="icon"
                                class="ml-auto h-4 w-4 p-0 text-muted-foreground opacity-0 group-hover/item:opacity-100 hover:text-destructive"
                                @click="group.options.splice(Number(oi), 1)"
                            >
                                <X class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
