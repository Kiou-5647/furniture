<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, ShieldCheck, UserCog } from '@lucide/vue';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { toast } from 'vue-sonner';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, permissions, syncPermissions, syncRoles } from '@/routes/employee/hr/employees';

interface PermissionItem {
    name: string;
    label: string;
    assigned: boolean;
}

interface GroupedPermission {
    module: string;
    label: string;
    permissions: PermissionItem[];
}

interface RolePermissionsMap {
    [roleName: string]: string[];
}

const props = defineProps<{
    employeeId: string;
}>();

const loading = ref(false);
const syncLoading = ref<string | null>(null); // Theo dõi item nào đang sync
const employee = ref<any>(null);
const groupedPermissions = ref<GroupedPermission[]>([]);
const rolePermissions = ref<RolePermissionsMap>({});
const activeRoles = ref<string[]>([]);
const directPermissions = ref<string[]>([]);

// Tải dữ liệu ban đầu từ API permissions
async function loadData() {
    loading.value = true;
    try {
        const response = await axios.get(permissions(props.employeeId).url);
        const { data, groupedPermissions: gp, rolePermissions: rp } = response.data;

        employee.value = data;
        groupedPermissions.value = gp;
        rolePermissions.value = rp;

        // Lấy danh sách role hiện tại của user
        activeRoles.value = data.user?.roles?.map((r: any) => r.name) || [];

        // Lấy danh sách quyền trực tiếp hiện tại
        directPermissions.value = gp
            .flatMap((group: any) => group.permissions)
            .filter((p: any) => p.assigned)
            .map((p: any) => p.name);

    } catch (error) {
        console.error('Lỗi tải dữ liệu quyền hạn:', error);
    } finally {
        loading.value = false;
    }
}

onMounted(loadData);


async function togglePermission(permissionName: string, isChecked: boolean) {
    syncLoading.value = permissionName;
    try {
        const current = [...directPermissions.value];
        if (isChecked) {
            if (!current.includes(permissionName)) current.push(permissionName);
        } else {
            const index = current.indexOf(permissionName);
            if (index > -1) current.splice(index, 1);
        }

        await axios.patch(syncPermissions(props.employeeId).url, {
            permissions: current
        });

        directPermissions.value = current;
    } catch {
        toast.error('Không thể cập nhật quyền hạn. Vui lòng thử lại.');
    } finally {
        syncLoading.value = null;
    }
}

async function toggleRole(roleName: string, isChecked: boolean) {
    const isAdding = isChecked;
    let finalPermissions = [...directPermissions.value];

    if (isAdding) {
        const confirmCopy = confirm(`Vai trò "${roleName}" bao gồm nhiều quyền hạn. Bạn có muốn copy tất cả quyền của vai trò này vào quyền trực tiếp của nhân viên không?`);
        if (confirmCopy) {
            const rolePerms = rolePermissions.value[roleName] || [];
            rolePerms.forEach(p => {
                if (!finalPermissions.includes(p)) finalPermissions.push(p);
            });
        }
    } else {
        const confirmRemove = confirm(`Bạn đang bỏ chọn vai trò "${roleName}". Bạn có muốn xóa tất cả các quyền hạn được kế thừa từ vai trò này khỏi quyền trực tiếp không?`);
        if (confirmRemove) {
            const rolePerms = rolePermissions.value[roleName] || [];
            finalPermissions = finalPermissions.filter(p => !rolePerms.includes(p));
        }
    }

    try {
        // 1. Sync Role
        const currentRoles = [...activeRoles.value];
        if (isAdding) {
            if (!currentRoles.includes(roleName)) currentRoles.push(roleName);
        } else {
            const index = currentRoles.indexOf(roleName);
            if (index > -1) currentRoles.splice(index, 1);
        }
        await axios.patch(syncRoles(props.employeeId).url, { roles: currentRoles });
        activeRoles.value = currentRoles;

        // 2. Sync Permissions nếu có thay đổi
        if (JSON.stringify(finalPermissions) !== JSON.stringify(directPermissions.value)) {
            await axios.patch(syncPermissions(props.employeeId).url, { permissions: finalPermissions });
            directPermissions.value = finalPermissions;
        }
    } catch {
        toast.error('Lỗi cập nhật vai trò/quyền hạn.');
    }
}

const breadcrumbs = computed(() => [
    { title: 'Quản lý nhân sự', href: index().url },
    { title: 'Nhân viên', href: index().url },
    { title: 'Quản lý quyền hạn', href: '#' },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="loading" class="flex h-screen items-center justify-center">
            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
        </div>

        <div v-else class="space-y-6 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Heading :title="`Quyền hạn: ${employee?.full_name}`"
                        :description="`Quản lý vai trò và quyền truy cập hệ thống`" />
                </div>
                <Button variant="outline" @click="router.visit(index())">
                    Quay lại
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                <!-- Left Side: Roles -->
                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <UserCog class="h-5 w-5" /> Vai trò
                        </CardTitle>
                        <CardDescription>Gán các vai trò định sẵn cho nhân viên</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-for="(perms, roleName) in rolePermissions" :key="roleName"
                            class="flex items-center space-x-3 p-2 rounded-md hover:bg-muted transition-colors">
                            <Checkbox :id="roleName" :model-value="activeRoles.includes(roleName)"
                                @update:model-value="(val: any) => toggleRole(roleName, val)" />
                            <Label :for="roleName" class="cursor-pointer font-medium">
                                {{ roleName.replace('_', ' ') }}
                            </Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Right Side: Detailed Permissions -->
                <Card class="lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-lg">
                            <ShieldCheck class="h-5 w-5" /> Quyền hạn trực tiếp
                        </CardTitle>
                        <CardDescription>
                            Mọi thay đổi ở đây sẽ được áp dụng tức thì vào hệ thống.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div v-for="group in groupedPermissions" :key="group.module" class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold uppercase tracking-wider text-muted-foreground">
                                        {{ group.label }}
                                    </span>
                                    <Separator class="flex-1" />
                                </div>
                                <div class="grid grid-cols-1 gap-3">
                                    <div v-for="perm in group.permissions" :key="perm.name"
                                        class="flex items-center space-x-3">
                                        <Checkbox :id="perm.name" :model-value="directPermissions.includes(perm.name)"
                                            @update:model-value="(val: any) => togglePermission(perm.name, val)"
                                            :disabled="syncLoading === perm.name" />
                                        <Label :for="perm.name" class="text-sm cursor-pointer flex items-center gap-2">
                                            {{ perm.label }}
                                            <Loader2 v-if="syncLoading === perm.name"
                                                class="h-3 w-3 animate-spin text-muted-foreground" />
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
