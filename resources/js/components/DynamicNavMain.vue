<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { state } = useSidebar();

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
            <template v-for="item in items" :key="item.title">
                <!-- Items with children -->
                <template v-if="item.items && item.items.length > 0">
                    <!-- Collapsible for Expanded State -->
                    <Collapsible v-if="state === 'expanded'" as-child :default-open="item.isActive"
                        class="group/collapsible">
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton :tooltip="item.title" :is-active="item.isActive">
                                    <component :is="getIcon(item.icon)" v-if="item.icon" />
                                    <span>{{ item.title }}</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                        <SidebarMenuSubButton as-child :is-active="subItem.isActive">
                                            <Link :href="subItem.href || '#'">
                                                <span>{{ subItem.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>

                    <!-- Dropdown for Collapsed State -->
                    <SidebarMenuItem v-else>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton :tooltip="item.title" :is-active="item.isActive">
                                    <component :is="getIcon(item.icon)" v-if="item.icon" />
                                    <span class="sr-only">{{ item.title }}</span>
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent side="right" align="start" :side-offset="8" class="min-w-48">
                                <div
                                    class="px-2 py-1.5 text-md font-semibold tracking-wider">
                                    {{ item.title }}
                                </div>

                                <template v-for="subItem in item.items" :key="subItem.title">
                                    <DropdownMenuItem as-child>
                                        <Link :href="subItem.href || '#'"
                                            class="w-full flex items-center justify-between">
                                            <span>{{ subItem.title }}</span>
                                            <div v-if="subItem.isActive" class="h-1.5 w-1.5 rounded-full bg-primary" />
                                        </Link>
                                    </DropdownMenuItem>
                                </template>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </SidebarMenuItem>
                </template>

                <!-- Simple Item (No children) -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :tooltip="item.title" :is-active="item.isActive">
                        <Link :href="item.href || '#'">
                            <component :is="getIcon(item.icon)" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
