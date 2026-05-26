<script setup>
import { computed } from 'vue';
import SearchableSelect from '@/components/forms/SearchableSelect.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
    gapClass: {
        type: String,
        default: 'gap-4',
    },
});

const normalizedFields = computed(() =>
    props.fields.map((field) => ({
        type: 'text',
        label: '',
        placeholder: '',
        autocomplete: undefined,
        required: false,
        options: [],
        rows: 3,
        icon: null,
        ...field,
    })),
);
</script>

<template>
    <div class="grid" :class="gapClass">
        <div
            v-for="field in normalizedFields"
            :key="field.name"
            class="grid gap-2"
        >
            <Label :for="field.name">{{ field.label || field.name }}</Label>

            <slot :name="`field-${field.name}`" :field="field" :form="form">
                <template v-if="field.type === 'password'">
                    <div class="group relative">
                        <component
                            v-if="field.icon"
                            :is="field.icon"
                            class="absolute top-1/2 left-3 z-10 h-5 w-5 -translate-y-1/2 text-on-surface-variant transition-colors group-focus-within:text-primary"
                        />
                        <PasswordInput
                            :id="field.name"
                            v-model="form[field.name]"
                            :placeholder="field.placeholder"
                            :autocomplete="field.autocomplete"
                            :class="field.icon ? 'pl-10' : ''"
                        />
                    </div>
                </template>

                <template v-else-if="field.type === 'textarea'">
                    <textarea
                        :id="field.name"
                        v-model="form[field.name]"
                        :placeholder="field.placeholder"
                        :autocomplete="field.autocomplete"
                        :required="field.required"
                        :rows="field.rows"
                        class="min-h-[96px] w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-sm text-on-surface transition-all outline-none placeholder:text-outline focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary-container"
                    />
                </template>

                <template v-else-if="field.type === 'select'">
                    <SearchableSelect
                        :id="field.name"
                        v-model="form[field.name]"
                        :options="field.options"
                        :placeholder="
                            field.placeholder || `Select ${field.label}`
                        "
                        :search-placeholder="`Search ${field.label || field.name}...`"
                    />
                </template>

                <template v-else-if="field.type === 'file'">
                    <div class="group relative">
                        <component
                            v-if="field.icon"
                            :is="field.icon"
                            class="absolute top-1/2 left-3 z-10 h-5 w-5 -translate-y-1/2 text-on-surface-variant transition-colors group-focus-within:text-primary"
                        />
                        <Input
                            :id="field.name"
                            type="file"
                            :required="field.required"
                            :class="field.icon ? 'pl-10' : ''"
                            @change="form[field.name] = $event.target.files[0]"
                        />
                    </div>
                </template>

                <template v-else-if="field.type === 'checkbox'">
                    <label class="inline-flex items-center gap-2">
                        <input
                            :id="field.name"
                            v-model="form[field.name]"
                            type="checkbox"
                            class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-2 focus:ring-primary-container"
                        />
                        <span class="text-sm text-on-surface">{{
                            field.placeholder || field.label
                        }}</span>
                    </label>
                </template>

                <template v-else>
                    <div class="group relative">
                        <component
                            v-if="field.icon"
                            :is="field.icon"
                            class="absolute top-1/2 left-3 z-10 h-5 w-5 -translate-y-1/2 text-on-surface-variant transition-colors group-focus-within:text-primary"
                        />
                        <Input
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="field.type"
                            :placeholder="field.placeholder"
                            :autocomplete="field.autocomplete"
                            :required="field.required"
                            :step="field.step"
                            :min="field.min"
                            :max="field.max"
                            :pattern="field.pattern"
                            :inputmode="field.inputmode"
                            :class="field.icon ? 'pl-10' : ''"
                        />
                    </div>
                </template>
            </slot>

            <InputError :message="form.errors[field.name]" />
        </div>
    </div>
</template>
