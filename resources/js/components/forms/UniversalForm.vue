<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    asModal: {
        type: Boolean,
        default: false,
    },
    visible: {
        type: Boolean,
        default: false,
    },
    width: {
        type: String,
        default: '34rem',
    },
    submitLabel: {
        type: String,
        default: 'Submit',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['submit', 'cancel', 'update:visible']);

const close = () => {
    emit('update:visible', false);
    emit('cancel');
};
</script>

<template>
    <Dialog
        v-if="asModal"
        :visible="visible"
        modal
        :header="title"
        :style="{ width }"
        @update:visible="(value) => emit('update:visible', value)"
        @hide="close"
        :pt="{
            root: { class: '!bg-surface-container !border !border-outline-variant !rounded-xl !shadow-2xl overflow-hidden' },
            header: { class: '!bg-surface-container-high !border-b !border-outline-variant !px-8 !py-6' },
            title: { class: 'font-headline-lg text-xl md:text-2xl !text-on-surface font-semibold tracking-tight' },
            content: { class: '!bg-transparent !px-8 !py-6' },
            closeButton: { class: 'hover:!bg-surface-variant !text-on-surface-variant transition-colors rounded-full' }
        }"
    >
        <form class="space-y-3" @submit.prevent="emit('submit')">
            <slot />
            <div class="flex justify-end gap-2 pt-3">
                <Button :label="cancelLabel" text :disabled="processing" @click="close" />
                <Button :label="submitLabel" type="submit" :loading="processing" />
            </div>
        </form>
    </Dialog>

    <form v-else class="space-y-3" @submit.prevent="emit('submit')">
        <slot />
        <div class="flex justify-end gap-2 pt-3">
            <Button :label="cancelLabel" text :disabled="processing" @click="emit('cancel')" />
            <Button :label="submitLabel" type="submit" :loading="processing" />
        </div>
    </form>
</template>
