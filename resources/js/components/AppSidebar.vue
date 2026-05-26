<script setup>
import { Link } from '@inertiajs/vue3';
import { BookOpen, FolderGit2 } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, } from '@/components/ui/sidebar';
import { useSidebarNav } from '@/composables/useSidebarNav.js';
import { logout } from '@/routes';

const { homeHref: dashboardUrl, items: mainNavItems } = useSidebarNav();

const footerNavItems = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar" class="bg-sidebar border-r border-outline-variant">
        <SidebarHeader class="p-stack-md pb-0">
            <div class="mb-stack-sm px-2 flex items-center justify-center group-data-[collapsible=icon]:hidden">
                <Link :href="dashboardUrl">
                    <img src="/assets/logo/run-fast-logo.png" alt="Run Fast Logo" class="h-30 w-40" />
                </Link>
            </div>
            <SidebarMenu class="group-data-[collapsible=icon]:block hidden">
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-stack-md">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="p-stack-md pt-0">
            <div class="space-y-2 group-data-[collapsible=icon]:hidden">
                <Link :href="logout()" method="post" as="button" class="flex w-full items-center gap-3 px-4 py-2 text-on-surface-variant hover:bg-sidebar-accent transition-all rounded-lg group">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span class="text-label-md">Sign Out</span>
                </Link>
            </div>
            
            <div class="group-data-[collapsible=icon]:block hidden">
                <NavUser />
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
