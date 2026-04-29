<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard as employeeDashboard } from '@/routes/employee';
import type { NavItem } from '@/types';
import DynamicNavMain from './DynamicNavMain.vue';

const page = usePage();
const dashboard = computed(() => {
    if (page.props.auth?.user.type == 'employee') {
        return employeeDashboard().url;
    }
    return employeeDashboard().url;
})
const menu = computed(() => (page.props.menu as NavItem[]) || []);

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <DynamicNavMain :items="menu" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="[]"/>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
e>
