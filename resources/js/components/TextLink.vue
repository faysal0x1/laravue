<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: [String, Object],
        required: true,
    },
    tabindex: {
        type: [String, Number],
        default: undefined,
    },
    method: {
        type: String,
        default: undefined,
    },
    as: {
        type: String,
        default: undefined,
    },
});

const hrefValue = computed(() =>
    typeof props.href === 'object' && props.href !== null ? props.href.url : props.href,
);

const methodValue = computed(() =>
    props.method ?? (typeof props.href === 'object' && props.href !== null ? props.href.method : undefined),
);
</script>

<template>
    <Link
        :href="hrefValue"
        :tabindex="props.tabindex"
        :method="methodValue"
        :as="props.as"
        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
    >
        <slot />
    </Link>
</template>
