<script setup>
import { onClickOutside } from '@vueuse/core';
import { ChevronDown, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select options…',
    },
    searchPlaceholder: {
        type: String,
        default: 'Search…',
    },
    optionLabel: {
        type: String,
        default: 'label',
    },
    optionValue: {
        type: String,
        default: 'value',
    },
    optionDescription: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const search = ref('');
const container = ref(null);

onClickOutside(container, () => {
    open.value = false;
    search.value = '';
});

const filteredOptions = computed(() => {
    if (!search.value.trim()) return props.options;
    const q = search.value.toLowerCase();
    return props.options.filter((opt) =>
        String(opt[props.optionLabel] ?? '').toLowerCase().includes(q),
    );
});

const isSelected = (optValue) => props.modelValue.includes(optValue);

const toggle = (optValue) => {
    const next = isSelected(optValue)
        ? props.modelValue.filter((v) => v !== optValue)
        : [...props.modelValue, optValue];
    emit('update:modelValue', next);
};

const remove = (optValue, e) => {
    e.stopPropagation();
    emit('update:modelValue', props.modelValue.filter((v) => v !== optValue));
};

const triggerLabel = computed(() => {
    if (props.modelValue.length === 0) return null;
    if (props.modelValue.length === 1) {
        const found = props.options.find((o) => o[props.optionValue] === props.modelValue[0]);
        return found ? found[props.optionLabel] : props.modelValue[0];
    }
    return `${props.modelValue.length} selected`;
});
</script>

<template>
    <div ref="container" class="relative">
        <!-- Trigger -->
        <button
            type="button"
            class="bg-surface-container-lowest border-outline-variant text-on-surface flex min-h-[42px] w-full items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            :class="open ? 'border-primary ring-2 ring-primary-container' : 'hover:border-primary/50'"
            @click="open = !open"
        >
            <!-- Selected badges / placeholder -->
            <div class="flex flex-1 flex-wrap gap-1.5 overflow-hidden">
                <template v-if="modelValue.length > 0 && modelValue.length <= 3">
                    <span
                        v-for="val in modelValue"
                        :key="val"
                        class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                    >
                        {{ options.find((o) => o[optionValue] === val)?.[optionLabel] ?? val }}
                        <X class="h-3 w-3 cursor-pointer opacity-60 hover:opacity-100" @click="remove(val, $event)" />
                    </span>
                </template>
                <span
                    v-else-if="modelValue.length > 3"
                    class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                >
                    {{ triggerLabel }}
                </span>
                <span v-else class="text-muted-foreground">{{ placeholder }}</span>
            </div>
            <ChevronDown
                class="ml-auto h-4 w-4 shrink-0 text-muted-foreground transition-transform"
                :class="open ? 'rotate-180' : ''"
            />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-1 w-full rounded-lg border border-border/60 bg-popover shadow-lg"
            >
                <!-- Search -->
                <div class="border-b border-border/60 p-2">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full rounded-md bg-transparent py-1.5 pl-8 pr-3 text-sm outline-none placeholder:text-muted-foreground focus:bg-muted/30"
                        />
                    </div>
                </div>

                <!-- Options list -->
                <ul class="max-h-52 overflow-auto p-1">
                    <li
                        v-for="option in filteredOptions"
                        :key="option[optionValue]"
                        class="flex cursor-pointer items-center gap-2.5 rounded-md px-2.5 py-2 text-sm transition-colors"
                        :class="isSelected(option[optionValue])
                            ? 'bg-primary/10 text-primary'
                            : 'text-foreground hover:bg-muted/50'"
                        @click="toggle(option[optionValue])"
                    >
                        <!-- Checkbox -->
                        <span
                            class="flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors"
                            :class="isSelected(option[optionValue])
                                ? 'border-primary bg-primary'
                                : 'border-muted-foreground/40'"
                        >
                            <svg v-if="isSelected(option[optionValue])" class="h-2.5 w-2.5 text-primary-foreground" fill="none" viewBox="0 0 12 12">
                                <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>

                        <div class="flex min-w-0 flex-col">
                            <span class="font-medium leading-tight">{{ option[optionLabel] }}</span>
                            <span
                                v-if="optionDescription && option[optionDescription] != null"
                                class="text-xs"
                                :class="isSelected(option[optionValue]) ? 'text-primary/60' : 'text-muted-foreground'"
                            >
                                {{ option[optionDescription] }}
                            </span>
                        </div>
                    </li>

                    <li v-if="filteredOptions.length === 0" class="py-6 text-center text-xs text-muted-foreground">
                        No results found.
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
