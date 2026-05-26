<script setup>
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials.js';
const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    showEmail: {
        type: Boolean,
        default: false,
    },
    team: {
        type: Object,
        default: null,
    },
});
const { getInitials } = useInitials();
const userName = computed(() => props.user?.name ?? 'Guest');
const userEmail = computed(() => props.user?.email ?? '');
const userAvatar = computed(() => props.user?.avatar ?? '');
const showAvatar = computed(() => userAvatar.value !== '');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="userAvatar" :alt="userName" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(userName) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ userName }}</span>
        <span v-if="team" class="truncate text-xs text-muted-foreground">{{
            team.name
        }}</span>
        <span
            v-else-if="showEmail"
            class="truncate text-xs text-muted-foreground"
            >{{ userEmail }}</span
        >
    </div>
</template>
