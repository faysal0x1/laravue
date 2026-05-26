<script setup>
import { useVModel } from "@vueuse/core";
import { cn } from '@/lib/utils.js';
const props = defineProps({
    modelValue: {
        default: undefined,
    },
    defaultValue: {
        default: undefined,
    },
    class: {
        type: String,
        default: '',
    },
});
const emits = defineEmits(['update:modelValue']);
const modelValue = useVModel(props, "modelValue", emits, {
    passive: true,
    defaultValue: props.defaultValue,
});
</script>

<template>
  <input
    v-model="modelValue"
    data-slot="input"
    :class="cn(
      'file:text-foreground placeholder:text-outline selection:bg-primary selection:text-primary-foreground bg-surface-container-lowest border-outline-variant text-on-surface w-full min-w-0 rounded-lg border px-4 py-3 text-base transition-all outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      'focus-visible:border-primary focus-visible:ring-primary-container focus-visible:ring-2',
      'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
      props.class,
    )"
  >
</template>
