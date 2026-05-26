<script setup>
import { reactiveOmit } from "@vueuse/core";
import { NavigationMenuItem } from "reka-ui";
import { cn } from '@/lib/utils.js';
const props = defineProps();
const delegatedProps = reactiveOmit(props, "class");
</script>

<template>
  <NavigationMenuItem
    data-slot="navigation-menu-item"
    v-bind="delegatedProps"
    :class="cn('relative', props.class)"
  >
    <slot />
  </NavigationMenuItem>
</template>
