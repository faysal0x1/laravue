<script setup>
import { cn } from '@/lib/utils.js';
import { alertVariants } from ".";
const props = defineProps();
</script>

<template>
  <div
    data-slot="alert"
    :class="cn(alertVariants({ variant }), props.class)"
    role="alert"
  >
    <slot />
  </div>
</template>
