<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import GlobalFormFields from '@/components/forms/GlobalFormFields.vue';
import GlobalListingPage from '@/components/listing/GlobalListingPage.vue';
import { useListingTable } from '@/composables/useListingTable.js';
import { buildListingState, column, createListingProps } from '@/utils/tableUtils.js';
import { deleteItemWithConfirmation, exportTableRows } from '@/utils/listingActions.js';
import { dashboard } from '@/routes';
import { index as usersIndex } from '@/routes/users';

const props = defineProps({
    designations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    departments: {
        type: Array,
        default: () => [],
    },
});

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Users', href: usersIndex() },
            { title: 'Designations', href: '/users/designations' },
        ],
    }),
});

const columns = [
    column('serial', '#', { sortable: false, headerClass: 'w-16' }),
    column('name', 'Designation'),
    column('department_name', 'Department'),
    column('level', 'Level'),
    column('employees_count', 'Employees'),
];

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const selectedRows = ref([]);
const selectedDesignation = ref(null);

const { loading, searchTerm, tableState, firstRecordIndex, onTableChange } = useListingTable({
    filters: props.filters,
    initialPage: props.designations.current_page,
    initialPerPage: props.designations.per_page,
    postUrl: '/users/designations/table',
    only: ['designations', 'filters', 'departments'],
    defaultSortColumn: 'id',
    defaultSortDirection: 'desc',
});

const designationRows = computed(() => props.designations.data ?? []);
const designationRowsWithSerial = computed(() =>
    designationRows.value.map((row, index) => ({
        ...row,
        serial: firstRecordIndex.value + index + 1,
    })),
);

const departmentOptions = computed(() =>
    props.departments.map((department) => ({
        value: department.id,
        label: department.name,
    })),
);
const createForm = useForm({
    name: '',
    department_id: '',
    level: 1,
    employees_total: 0,
});

const editForm = useForm({
    name: '',
    department_id: '',
    level: 1,
    employees_total: 0,
});

const formFields = [
    { name: 'name', label: 'Name', type: 'text', required: true, placeholder: 'Designation name' },
    { name: 'department_id', label: 'Department', type: 'select', required: true, options: [] },
    { name: 'level', label: 'Level (1=Top)', type: 'number', required: true, placeholder: '1' },
    { name: 'employees_total', label: 'Employees', type: 'number', required: true, placeholder: '0' },
];

const listingProps = computed(() =>
    createListingProps(buildListingState({
        title: 'Designations',
        subtitle: searchTerm.value ? `Results for "${searchTerm.value}"` : 'Designation employees table',
        rows: designationRowsWithSerial.value,
        columns,
        records: props.designations,
        tableState: tableState.value,
        firstRecordIndex: firstRecordIndex.value,
        searchTerm: searchTerm.value,
        selectedRows: selectedRows.value,
        loading: loading.value,
        createDialogOpen: createDialogOpen.value,
        editDialogOpen: editDialogOpen.value,
        createProcessing: createForm.processing,
        editProcessing: editForm.processing,
        createTitle: 'Add Designation',
        editTitle: 'Edit Designation',
        createButtonLabel: 'Add Designation',
    })),
);

const submitCreate = () => {
    createForm.post('/users/designations', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            createDialogOpen.value = false;
        },
    });
};

const openEdit = (designation) => {
    selectedDesignation.value = designation;
    editForm.name = designation.name;
    editForm.department_id = designation.department_id;
    editForm.level = designation.level ?? 1;
    editForm.employees_total = designation.employees_total ?? 0;
    editDialogOpen.value = true;
};

const submitEdit = () => {
    if (!selectedDesignation.value) return;

    editForm.put(`/users/designations/${selectedDesignation.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            selectedDesignation.value = null;
            editDialogOpen.value = false;
        },
    });
};

const removeDesignation = async (designation) => {
    await deleteItemWithConfirmation({
        title: 'Delete designation?',
        text: `This will permanently delete "${designation.name}".`,
        confirmButtonText: 'Delete',
        url: `/users/designations/${designation.id}`,
    });
};

const exportRows = (type) => {
    exportTableRows({
        selectedRows: selectedRows.value,
        allRows: designationRows.value,
        type,
        fileName: 'designations',
        sheetName: 'Designations',
    });
};

const bulkDeleteDesignations = async () => {
    if (selectedRows.value.length === 0) return;

    await deleteItemWithConfirmation({
        title: 'Delete selected designations?',
        text: `You are about to delete ${selectedRows.value.length} designations.`,
        confirmButtonText: 'Delete all',
        url: '/users/designations/bulk-destroy',
        data: { ids: selectedRows.value.map((item) => Number(item.id)) },
        onSuccess: () => {
            selectedRows.value = [];
        },
    });
};

const departmentFieldOptions = computed(() =>
    departmentOptions.value.map((option) => ({
        value: option.value,
        label: option.label,
    })),
);
</script>

<template>
    <Head title="Designations" />

    <GlobalListingPage
        v-bind="listingProps"
        @update:global-filter="searchTerm = $event"
        @update:selected-rows="selectedRows = $event"
        @update:create-dialog-open="createDialogOpen = $event"
        @update:edit-dialog-open="editDialogOpen = $event"
        @change="onTableChange"
        @export="exportRows"
        @bulk-delete="bulkDeleteDesignations"
        @create-submit="submitCreate"
        @edit-submit="submitEdit"
    >
        <template #header-actions>
            <div class="flex gap-2">
                <Link
                    href="/users/departments"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#c7c4d8] px-4 py-2 text-xs font-semibold text-[#3525cd] hover:bg-[#e6eeff]"
                >
                    Departments
                </Link>
                <Button
                    size="small"
                    class="rounded-lg bg-[#3525cd] px-4 py-2 text-xs font-semibold text-white hover:bg-[#2f21b9]"
                    @click="createDialogOpen = true"
                >
                    Add Designation
                </Button>
            </div>
        </template>

        <template #cell-serial="{ row }">
            <span class="text-sm font-semibold text-[#121c2a]">{{ row.serial }}</span>
        </template>

        <template #cell-level="{ row }">
            <span class="rounded-full bg-[#eff4ff] px-2 py-1 text-xs font-semibold text-[#3525cd]">L{{ row.level }}</span>
        </template>

        <template #cell-employees_count="{ row }">
            <span class="text-[#3525cd]">
                {{ row.employees_count ?? 0 }} Employees
            </span>
        </template>

        <template #actions="{ row }">
            <div class="flex gap-2">
                <Button icon="pi pi-pencil" size="small" text rounded @click="openEdit(row)" />
                <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="removeDesignation(row)" />
            </div>
        </template>

        <template #create-form>
            <GlobalFormFields :form="createForm" :fields="formFields" :gap-class="'gap-5'">
                <template #field-department_id>
                    <select v-model="createForm.department_id" class="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm text-slate-700 dark:text-slate-100">
                        <option value="" disabled>Select department</option>
                        <option v-for="opt in departmentFieldOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </template>

                <template #field-employees_total>
                    <input
                        v-model.number="createForm.employees_total"
                        type="number"
                        min="0"
                        class="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm text-slate-700 dark:text-slate-100"
                        placeholder="0"
                    />
                </template>
            </GlobalFormFields>
        </template>

        <template #edit-form>
            <GlobalFormFields :form="editForm" :fields="formFields" :gap-class="'gap-5'">
                <template #field-department_id>
                    <select v-model="editForm.department_id" class="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm text-slate-700 dark:text-slate-100">
                        <option value="" disabled>Select department</option>
                        <option v-for="opt in departmentFieldOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </template>

                <template #field-employees_total>
                    <input
                        v-model.number="editForm.employees_total"
                        type="number"
                        min="0"
                        class="h-11 w-full rounded-lg border border-input bg-background px-3 text-sm text-slate-700 dark:text-slate-100"
                        placeholder="0"
                    />
                </template>
            </GlobalFormFields>
        </template>
    </GlobalListingPage>
</template>
