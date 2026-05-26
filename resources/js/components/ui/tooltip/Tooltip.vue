<script setup>
import { TooltipRoot, useForwardProps } from "reka-ui";
const props = defineProps();
const forwarded = useForwardProps(props);
</script>

<template>
  <TooltipRoot
    v-slot="slotProps"
    data-slot="tooltip"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </TooltipRoot>
</template>
