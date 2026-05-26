<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps();
</script>

<template>
  <li
    data-slot="breadcrumb-item"
    :class="cn('inline-flex items-center gap-1.5', props.class)"
  >
    <slot />
  </li>
</template>
