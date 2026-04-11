<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Shield } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { permissions as permissionsRoute, syncRolesPermissions } from '@/routes/employee/hr/employees';

const props = defineProps<{
    open: boolean;
    employee: any | null;
    roleOptions: { id: string; label: string }[];
    permissionOptions: { id: string; label: string }[];
}>();

const emit = defineEmits(['close', 'refresh']);

const saving = ref(false);
const loading = ref(true);
const error = ref<string | null>(null);

const selectedRoles = ref<string[]>([]);
const activePermissions = ref<string[]>([]);
const rolePermissions = ref<Record<string, string[]>>({});

watch(
    () => props.employee,
    async (newEmp) => {
        if (newEmp && props.open) {
            await loadEmployeePermissions();
        }
    },
    { immediate: true },
);

async function loadEmployeePermissions() {
    loading.value = true;
    error.value = null;

    try {
        const res = await fetch(permissionsRoute(props.employee).url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!res.ok) throw new Error('Không thể tải dữ liệu.');

        const json = await res.json();
        const data = json.data;
        rolePermissions.value = json.rolePermissions;

        selectedRoles.value = data.user?.roles ?? [];
        activePermissions.value = data.user?.permissions ?? [];
        initialRoles.value = [...selectedRoles.value];
        initialPermissions.value = [...activePermissions.value];
    } catch (e: any) {
        error.value = e.message ?? 'Lỗi không xác định.';
    } finally {
        loading.value = false;
    }
}

const initialRoles = ref<string[]>([]);
const initialPermissions = ref<string[]>([]);

const permissionsFromSelectedRoles = computed(() => {
    const perms = new Set<string>();
    selectedRoles.value.forEach((role) => {
        (rolePermissions.value[role] ?? []).forEach((p) => perms.add(p));
    });
    return perms;
});

function isPermissionFromRole(permId: string): boolean {
    return permissionsFromSelectedRoles.value.has(permId);
}

function toggleRole(roleId: string) {
    const idx = selectedRoles.value.indexOf(roleId);
    if (idx === -1) {
        selectedRoles.value.push(roleId);
        // Add role's permissions
        (rolePermissions.value[roleId] ?? []).forEach((p) => {
            if (!activePermissions.value.includes(p)) {
                activePermissions.value.push(p);
            }
        });
    } else {
        selectedRoles.value.splice(idx, 1);
        // Remove permissions that are no longer from any selected role
        // but keep manually added ones
        const remaining = new Set<string>();
        selectedRoles.value.forEach((role) => {
            (rolePermissions.value[role] ?? []).forEach((p) => remaining.add(p));
        });
        // Keep only permissions from remaining roles or manually added
        activePermissions.value = activePermissions.value.filter((p) => {
            return remaining.has(p) || !isPermissionFromRoleBeforeDeselect(p, roleId);
        });
    }
}

function isPermissionFromRoleBeforeDeselect(permId: string, excludedRole: string): boolean {
    return (rolePermissions.value[excludedRole] ?? []).includes(permId);
}

function togglePermission(permId: string) {
    const idx = activePermissions.value.indexOf(permId);
    if (idx === -1) {
        activePermissions.value.push(permId);
    } else {
        activePermissions.value.splice(idx, 1);
    }
}

async function save() {
    if (!props.employee) return;
    saving.value = true;
    error.value = null;

    const route = syncRolesPermissions(props.employee);

    router.post(route.url, {
        roles: selectedRoles.value,
        permissions: activePermissions.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            saving.value = false;
            emit('refresh');
            emit('close');
        },
        onError: (errors) => {
            saving.value = false;
            const firstError = Object.values(errors)[0];
            error.value = (firstError as string) ?? 'Không thể lưu quyền hạn.';
        },
    });
}

function closeModal() {
    selectedRoles.value = [];
    activePermissions.value = [];
    initialRoles.value = [];
    initialPermissions.value = [];
    error.value = null;
    emit('close');
}

function clearAll() {
    selectedRoles.value = [];
    activePermissions.value = [];
}

function resetPermissions() {
    activePermissions.value = [...initialPermissions.value];
    selectedRoles.value = [...initialRoles.value];
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && closeModal()">
        <DialogContent
            class="max-h-[90vh] gap-0 overflow-y-auto p-0 sm:max-w-[900px]"
        >
            <DialogHeader class="px-4 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
                <div class="min-w-0">
                    <DialogTitle class="text-left text-lg sm:text-xl">
                        Quản lý quyền — {{ employee?.full_name }}
                    </DialogTitle>
                    <DialogDescription class="mt-1">
                        Chọn vai trò để tự động cấp quyền, sau đó tinh chỉnh thủ công
                    </DialogDescription>
                </div>
            </DialogHeader>

            <div class="px-4 pb-4 sm:px-6">
                <div v-if="loading" class="py-8 text-center text-sm text-muted-foreground">
                    <Loader2 class="mx-auto mb-2 h-5 w-5 animate-spin" />
                    Đang tải dữ liệu...
                </div>

                <div v-else class="space-y-4">
                    <div v-if="error" class="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                        {{ error }}
                    </div>

                    <!-- Roles -->
                    <div>
                        <div class="flex items-center justify-between">
                            <Label class="text-base">Vai trò</Label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="clearAll"
                            >
                                Xóa tất cả
                            </Button>
                        </div>
                        <p class="mb-2 text-sm text-muted-foreground">
                            Chọn vai trò sẽ tự động cấp các quyền tương ứng
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="role in roleOptions"
                                :key="role.id"
                                type="button"
                                :variant="selectedRoles.includes(role.id) ? 'default' : 'outline'"
                                size="sm"
                                @click="toggleRole(role.id)"
                            >
                                <Shield class="mr-1.5 h-3.5 w-3.5" />
                                {{ role.label }}
                            </Button>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div>
                        <div class="flex items-center justify-between">
                            <Label class="text-base">Quyền hạn</Label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="h-7 text-xs"
                                @click="resetPermissions"
                            >
                                Đặt lại
                            </Button>
                        </div>
                        <p class="mb-2 text-sm text-muted-foreground">
                            Quyền tô đậm là từ vai trò, có thể bật/tắt thủ công
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="perm in permissionOptions"
                                :key="perm.id"
                                type="button"
                                :variant="activePermissions.includes(perm.id) ? 'default' : 'outline'"
                                :class="[
                                    'text-xs',
                                    activePermissions.includes(perm.id) && !isPermissionFromRole(perm.id) ? 'border-amber-500 ring-1 ring-amber-500' : '',
                                ]"
                                @click="togglePermission(perm.id)"
                            >
                                {{ perm.label }}
                            </Button>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="rounded-lg border p-4">
                        <Label class="text-sm">Tổng quan</Label>
                        <div class="mt-2 space-y-1 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground">Vai trò:</span>
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-if="selectedRoles.length === 0"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        Chưa có
                                    </Badge>
                                    <Badge
                                        v-for="r in selectedRoles"
                                        :key="r"
                                        variant="secondary"
                                        class="text-xs"
                                    >
                                        {{ roleOptions.find((ro) => ro.id === r)?.label ?? r }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-muted-foreground">Quyền đang cấp:</span>
                                <Badge variant="outline" class="text-xs">
                                    {{ activePermissions.length }} quyền
                                </Badge>
                                <span
                                    v-if="activePermissions.filter((p) => !isPermissionFromRole(p)).length > 0"
                                    class="text-xs text-amber-600"
                                >
                                    ({{ activePermissions.filter((p) => !isPermissionFromRole(p)).length }} thủ công)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="mt-6 p-3 gap-2 sm:mt-8">
                <Button type="button" variant="outline" @click="closeModal">
                    Hủy
                </Button>
                <Button type="button" :disabled="saving || loading" @click="save">
                    <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                    {{ saving ? 'Đang lưu...' : 'Lưu thay đổi' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
