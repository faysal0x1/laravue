<script setup>
import { cn } from '@/lib/utils.js';
const props = defineProps();
</script>

<template>
  <div
    data-slot="skeleton"
    :class="cn('animate-pulse rounded-md bg-primary/10', props.class)"
  />
</template>
