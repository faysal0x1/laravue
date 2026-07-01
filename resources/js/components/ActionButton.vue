<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { PropType } from 'vue';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    icon: {
        type: null as unknown as PropType<any>,
        default: null,
    },
    route: {
        type: [String, Object] as PropType<string | Record<string, any> | null>,
        default: null,
    },
    permission: {
        type: [String, Array] as PropType<string | string[]>,
        default: null,
    },
    variant: {
        type: String,
        default: 'secondary',
    },
    size: {
        type: String,
        default: 'sm',
    },
    class: {
        type: null,
        default: undefined,
    },
    title: {
        type: String,
        default: '',
    },
    hideText: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits<{
    click: [];
}>();

const page = usePage();

const routeHref = computed(() => {
    if (!props.route) return '';
    if (typeof props.route === 'string') return props.route;
    return props.route?.url ?? props.route?.href ?? '';
});

const hasRoute = computed(() => Boolean(routeHref.value));

const handleClick = (event: Event) => {
    emit('click');
    if (!hasRoute.value) {
        event.preventDefault();
    }
};

const permissionKeys = computed(() => {
    if (props.permission == null) return [];
    return Array.isArray(props.permission) ? props.permission : [props.permission];
});

const hasPermission = (key: string) => {
    const pageProps = page.props ?? {};
    const permissionFlags = pageProps?.permissions as Record<string, boolean> | undefined;
    if (permissionFlags && typeof permissionFlags[key] === 'boolean') {
        return permissionFlags[key];
    }

    const abilities = pageProps?.auth?.user?.abilities as Record<string, boolean> | undefined;
    if (abilities && typeof abilities[key] === 'boolean') {
        return abilities[key];
    }

    const permissions = pageProps?.auth?.user?.permissions as string[] | undefined;
    if (Array.isArray(permissions)) {
        return permissions.includes(key);
    }

    return true;
};

const allowed = computed(() => {
    if (permissionKeys.value.length === 0) return true;
    return permissionKeys.value.every((key) => hasPermission(key));
});

const label = computed(() => props.title || props.name);
</script>

<template>
    <template v-if="allowed">
        <Button as-child :variant="props.variant" :size="props.size" :class="props.class">
            <component
                :is="hasRoute ? Link : 'button'"
                :href="hasRoute ? routeHref : undefined"
                :title="label"
                type="button"
                @click="handleClick"
                class="inline-flex items-center"
            >
                <span v-if="icon" class="inline-flex items-center justify-center">
                    <component :is="icon" class="h-4 w-4" />
                </span>
                <span v-if="!hideText" class="ml-2">{{ name }}</span>
            </component>
        </Button>
    </template>
</template>
