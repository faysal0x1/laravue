<script setup>
import { Primitive } from "reka-ui";
import { cn } from '@/lib/utils.js';
const props = defineProps({
    as: {
        type: String,
        default: 'div',
    },
    asChild: {
        type: Boolean,
        default: false,
    },
    class: {
        type: null,
        default: undefined,
    },
});
</script>

<template>
  <Primitive
    data-slot="sidebar-group-label"
    data-sidebar="group-label"
    :as="props.as"
    :as-child="props.asChild"
    :class="cn(
      'text-on-surface-variant ring-sidebar-ring flex h-8 shrink-0 items-center rounded-md px-4 text-label-md font-bold uppercase tracking-wider outline-hidden transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 [&>svg]:size-4 [&>svg]:shrink-0',
      'group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0',
      props.class)"
  >
    <slot />
  </Primitive>
</template>
