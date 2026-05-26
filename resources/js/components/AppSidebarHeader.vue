<script setup>
import { usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { getInitials } from '@/composables/useInitials.js';
const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});
const page = usePage();
const user = page.props.auth?.user ?? null;
const notifications = [
    { id: 1, title: 'Welcome to admin panel', time: 'now' },
    { id: 2, title: 'System is running normally', time: 'today' },
];
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="props.breadcrumbs && props.breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="props.breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button variant="ghost" size="icon" class="relative size-9 rounded-full">
                        <Bell class="size-4" />
                        <span class="absolute top-2 right-2 size-2 rounded-full bg-red-500" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-72">
                    <DropdownMenuLabel>Notifications</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        v-for="item in notifications"
                        :key="item.id"
                        class="flex flex-col items-start gap-0.5"
                    >
                        <span>{{ item.title }}</span>
                        <span class="text-xs text-muted-foreground">{{ item.time }}</span>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button variant="ghost" size="icon" class="size-9 rounded-full p-0.5">
                        <Avatar class="size-8 overflow-hidden rounded-full">
                            <AvatarImage v-if="user?.avatar" :src="user.avatar" :alt="user?.name ?? 'User'" />
                            <AvatarFallback>
                                {{ getInitials(user?.name ?? 'User') }}
                            </AvatarFallback>
                        </Avatar>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
