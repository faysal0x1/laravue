<script setup>
import { Primitive } from "reka-ui";
import { cn } from '@/lib/utils.js';
import { buttonVariants } from ".";
const props = defineProps({
    as: {
        type: String,
        default: 'button',
    },
    asChild: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'default',
    },
    size: {
        type: String,
        default: 'default',
    },
    class: {
        type: null,
        default: undefined,
    },
});
</script>

<template>
  <Primitive
    data-slot="button"
    :as="props.as"
    :as-child="props.asChild"
    :class="cn(buttonVariants({ variant: props.variant, size: props.size }), props.class)"
  >
    <slot />
  </Primitive>
</template>
