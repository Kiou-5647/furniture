<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';
import { ChevronRight } from '@lucide/vue';
import * as LucideIcons from '@lucide/vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { state, setOpen } = useSidebar();

function expandSidebar(): void {
    if (state.value === 'collapsed') {
        setOpen(true);
    }
}

function getIcon(name: string | LucideIcon | undefined): any {
    if (!name) return null;
    if (typeof name !== 'string') return name;
    return (LucideIcons as any)[name] || LucideIcons.HelpCircle;
}
</script>

<template>
    <SidebarGroup>
        <SidebarGroupLabel>Nền tảng</SidebarGroupLabel>
        <SidebarMenu>
            <Collapsible
                v-for="item in items"
                :key="item.title"
                as-child
                :default-open="item.isActive"
                class="group/collapsible"
            >
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton
                            :tooltip="item.title"
                            @click="expandSidebar"
                        >
                            <component
                                v-if="item.icon"
                                :is="getIcon(item.icon)"
                            />
                            <Link v-if="item.href && item.href != '#'" :href="item.href">
                                <span>{{ item.title }}</span>
                            </Link>
                            <span v-else>{{ item.title }}</span>

                            <ChevronRight
                                v-if="item.items?.length! > 0"
                                class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                            />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub v-if="item.items?.length! > 0">
                            <SidebarMenuSubItem
                                v-for="subItem in item.items"
                                :key="subItem.title"
                            >
                                <SidebarMenuSubButton
                                    as-child
                                    :is-active="subItem.isActive"
                                >
                                    <Link :href="subItem.href || '#'">
                                        <span>{{ subItem.title }}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
</template>
