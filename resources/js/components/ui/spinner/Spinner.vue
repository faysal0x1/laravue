<script setup>
import { Loader2Icon } from "lucide-vue-next";
import { cn } from '@/lib/utils.js';
const props = defineProps();
</script>

<template>
  <Loader2Icon
    role="status"
    aria-label="Loading"
    :class="cn('size-4 animate-spin', props.class)"
  />
</template>
