<script setup lang="ts">
import { Check, ChevronDown, Search } from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

type SelectOption = {
    label: string;
    value: string | number;
};

const props = withDefaults(
    defineProps<{
        id: string;
        modelValue: string | number | null;
        options: SelectOption[];
        placeholder?: string;
        searchPlaceholder?: string;
    }>(),
    {
        placeholder: 'Select an option',
        searchPlaceholder: 'Search...',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();

const root = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const open = ref(false);
const search = ref('');

const selectedOption = computed(() =>
    props.options.find(
        (option) => String(option.value) === String(props.modelValue ?? ''),
    ),
);

const filteredOptions = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return props.options;
    }

    return props.options.filter((option) =>
        option.label.toLowerCase().includes(query),
    );
});

const toggle = async () => {
    open.value = !open.value;

    if (open.value) {
        await nextTick();
        searchInput.value?.focus();
    }
};

const close = () => {
    open.value = false;
    search.value = '';
};

const selectOption = (option: SelectOption) => {
    emit('update:modelValue', option.value);
    close();
};

const onDocumentClick = (event: MouseEvent) => {
    if (!root.value?.contains(event.target as Node)) {
        close();
    }
};

watch(open, (isOpen) => {
    if (!isOpen) {
        search.value = '';
    }
});

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
});
</script>

<template>
    <div ref="root" class="relative">
        <button
            :id="id"
            type="button"
            class="flex h-11 w-full items-center justify-between gap-3 rounded-lg border border-outline bg-surface-container-lowest px-4 text-left text-sm transition outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
            :class="
                selectedOption ? 'text-on-surface' : 'text-on-surface-variant'
            "
            :aria-expanded="open"
            aria-haspopup="listbox"
            @click="toggle"
        >
            <span class="min-w-0 truncate">
                {{ selectedOption?.label ?? placeholder }}
            </span>
            <ChevronDown
                class="size-4 shrink-0 text-on-surface-variant transition"
                :class="{ 'rotate-180': open }"
            />
        </button>

        <div
            v-if="open"
            class="absolute z-30 mt-2 w-full overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest shadow-lg"
        >
            <div class="relative border-b border-outline-variant p-2">
                <Search
                    class="absolute top-1/2 left-5 size-4 -translate-y-1/2 text-on-surface-variant"
                />
                <input
                    ref="searchInput"
                    v-model="search"
                    type="text"
                    class="h-10 w-full rounded-md border border-outline bg-surface-container-lowest pr-3 pl-9 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    :placeholder="searchPlaceholder"
                    @keydown.esc="close"
                />
            </div>

            <ul class="max-h-64 overflow-y-auto py-1" role="listbox">
                <li v-if="filteredOptions.length === 0">
                    <div class="px-4 py-3 text-sm text-on-surface-variant">
                        No options found
                    </div>
                </li>

                <li
                    v-for="option in filteredOptions"
                    :key="option.value"
                    role="option"
                    :aria-selected="
                        String(option.value) === String(modelValue ?? '')
                    "
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left text-sm text-on-surface transition hover:bg-surface-container-low"
                        @click="selectOption(option)"
                    >
                        <span class="min-w-0 truncate">{{ option.label }}</span>
                        <Check
                            v-if="
                                String(option.value) ===
                                String(modelValue ?? '')
                            "
                            class="size-4 shrink-0 text-primary"
                        />
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
