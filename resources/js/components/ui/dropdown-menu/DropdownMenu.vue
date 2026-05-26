<script setup>
import { DropdownMenuRoot, useForwardProps } from "reka-ui";
const props = defineProps();
const forwarded = useForwardProps(props);
</script>

<template>
  <DropdownMenuRoot
    v-slot="slotProps"
    data-slot="dropdown-menu"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </DropdownMenuRoot>
</template>
