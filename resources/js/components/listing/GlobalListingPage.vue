<script setup>
import { Head } from '@inertiajs/vue3';
import Button from 'primevue/button';
import { computed, useSlots } from 'vue';
import AdvancedDataTable from '@/components/data-table/AdvancedDataTable.vue';
import UniversalForm from '@/components/forms/UniversalForm.vue';

const props = defineProps({
        pageTitle: {
            type: String,
            default: '',
        },
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            default: '',
        },
        rows: {
            type: Array,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
        totalRecords: {
            type: Number,
            required: true,
        },
        rowsPerPage: {
            type: Number,
            required: true,
        },
        first: {
            type: Number,
            required: true,
        },
        sortField: {
            type: String,
            required: true,
        },
        sortDirection: {
            type: String,
            required: true,
        },
        globalFilter: {
            type: String,
            required: true,
        },
        selectedRows: {
            type: Array,
            required: true,
        },
        loading: {
            type: Boolean,
            required: true,
        },
        createDialogOpen: {
            type: Boolean,
            default: false,
        },
        editDialogOpen: {
            type: Boolean,
            default: false,
        },
        createProcessing: {
            type: Boolean,
            default: false,
        },
        editProcessing: {
            type: Boolean,
            default: false,
        },
        createTitle: {
            type: String,
            default: 'Create',
        },
        editTitle: {
            type: String,
            default: 'Edit',
        },
        createSubmitLabel: {
            type: String,
            default: 'Create',
        },
        editSubmitLabel: {
            type: String,
            default: 'Save Changes',
        },
        showCreateForm: {
            type: Boolean,
            default: true,
        },
        showEditForm: {
            type: Boolean,
            default: true,
        },
        showCreateButton: {
            type: Boolean,
            default: true,
        },
        createButtonLabel: {
            type: String,
            default: 'New',
        },
        createFormWidth: {
            type: String,
            default: '34rem',
        },
        editFormWidth: {
            type: String,
            default: '34rem',
        },
    });

const emit = defineEmits([
    'update:globalFilter',
    'update:selectedRows',
    'update:createDialogOpen',
    'update:editDialogOpen',
    'change',
    'export',
    'bulkDelete',
    'createSubmit',
    'editSubmit',
]);

const slots = useSlots();
const forwardedSlotNames = computed(() =>
    Object.keys(slots).filter(
        (slotName) =>
            slotName !== 'header-actions' &&
            slotName !== 'actions' &&
            slotName !== 'create-form' &&
            slotName !== 'edit-form',
    ),
);
</script>

<template>
    <Head :title="props.pageTitle || props.title" />

    <div class="flex h-full flex-1 flex-col gap-4 bg-background p-2 text-foreground sm:p-4">
        <AdvancedDataTable
            :value="props.rows"
            :columns="props.columns"
            :total-records="props.totalRecords"
            :rows="props.rowsPerPage"
            :first="props.first"
            :sort-field="props.sortField"
            :sort-direction="props.sortDirection"
            :global-filter="props.globalFilter"
            :selected-rows="props.selectedRows"
            :loading="props.loading"
            :title="props.title"
            :subtitle="props.subtitle"
            @update:global-filter="emit('update:globalFilter', $event)"
            @update:selected-rows="emit('update:selectedRows', $event)"
            @change="emit('change', $event)"
            @export="emit('export', $event)"
            @bulk-delete="emit('bulkDelete')"
        >
            <template #header-actions>
                <slot name="header-actions">
                    <Button
                        v-if="props.showCreateButton"
                        :label="props.createButtonLabel"
                        size="small"
                        icon="pi pi-plus"
                        class="w-full sm:w-auto"
                        @click="emit('update:createDialogOpen', true)"
                    />
                </slot>
            </template>

            <template #actions="{ row }">
                <slot name="actions" :row="row" />
            </template>

            <template v-for="slotName in forwardedSlotNames" :key="slotName" #[slotName]="slotProps">
                <slot :name="slotName" v-bind="slotProps" />
            </template>
        </AdvancedDataTable>
    </div>

    <UniversalForm
        v-if="props.showCreateForm"
        :title="props.createTitle"
        as-modal
        :visible="props.createDialogOpen"
        :width="props.createFormWidth"
        :submit-label="props.createSubmitLabel"
        :processing="props.createProcessing"
        @update:visible="emit('update:createDialogOpen', $event)"
        @submit="emit('createSubmit')"
    >
        <slot name="create-form" />
    </UniversalForm>

    <UniversalForm
        v-if="props.showEditForm"
        :title="props.editTitle"
        as-modal
        :visible="props.editDialogOpen"
        :width="props.editFormWidth"
        :submit-label="props.editSubmitLabel"
        :processing="props.editProcessing"
        @update:visible="emit('update:editDialogOpen', $event)"
        @submit="emit('editSubmit')"
    >
        <slot name="edit-form" />
    </UniversalForm>
</template>
