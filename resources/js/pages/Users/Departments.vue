<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard } from '@/routes';
import { index as usersIndex } from '@/routes/users';
import { Input } from '@/components/ui/input';
import UniversalForm from '@/components/forms/UniversalForm.vue';
import { deleteItemWithConfirmation } from '@/utils/listingActions.js';

const props = defineProps({
    departments: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({ departments: 0, employees: 0, avg_team_size: 0 }),
    },
});

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Users', href: usersIndex() },
            { title: 'Departments', href: '/users/departments' },
        ],
    }),
});

const departments = computed(() => props.departments.data ?? []);

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const selectedDepartment = ref(null);

const createForm = useForm({
    name: '',
    head_name: '',
    employees_total: null,
    description: '',
});

const editForm = useForm({
    name: '',
    head_name: '',
    employees_total: null,
    description: '',
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    createDialogOpen.value = true;
};

const openEdit = (department) => {
    selectedDepartment.value = department;
    editForm.name = department.name;
    editForm.head_name = department.head_name ?? '';
    editForm.employees_total = department.employees_total ?? null;
    editForm.description = department.description ?? '';
    editForm.clearErrors();
    editDialogOpen.value = true;
};

const submitCreate = () => {
    createForm.post('/users/departments', {
        preserveScroll: true,
        onSuccess: () => {
            createDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    if (!selectedDepartment.value) return;

    editForm.put(`/users/departments/${selectedDepartment.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editDialogOpen.value = false;
            selectedDepartment.value = null;
        },
    });
};

const removeDepartment = async (department) => {
    await deleteItemWithConfirmation({
        title: 'Delete department?',
        text: `This will permanently delete "${department.name}".`,
        confirmButtonText: 'Delete',
        url: `/users/departments/${department.id}`,
    });
};
</script>

<template>
    <Head title="Departments" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#121c2a]">Departments</h1>
                <p class="text-sm text-[#777587]">Manage departments, heads, and team assignments.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link
                    href="/users/designations"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#c7c4d8] px-4 py-2 text-sm font-semibold text-[#3525cd] hover:bg-[#e6eeff]"
                >
                    Designations
                </Link>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#3525cd] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#2f21b9]"
                    @click="openCreate"
                >
                    Add Department
                </button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-[#f4edff] p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#8b5cf6]">Departments</p>
                <p class="mt-2 text-2xl font-bold text-[#3525cd]">{{ stats.departments ?? 0 }}</p>
            </div>
            <div class="rounded-2xl bg-[#edf4ff] p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#3b82f6]">Total Employees</p>
                <p class="mt-2 text-2xl font-bold text-[#2563eb]">{{ stats.employees ?? 0 }}</p>
            </div>
            <div class="rounded-2xl bg-[#ecfdf3] p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#16a34a]">Avg Team Size</p>
                <p class="mt-2 text-2xl font-bold text-[#15803d]">{{ stats.avg_team_size ?? 0 }}</p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="department in departments"
                :key="department.id"
                class="rounded-2xl border border-[#e2e8f0] bg-white p-5 shadow-sm"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f4edff] text-sm font-bold text-[#8b5cf6]">
                            DEPT
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-[#121c2a]">{{ department.name }}</h3>
                            <p class="text-sm text-[#64748b]">{{ department.description || 'No description' }}</p>
                        </div>
                    </div>
                    <button
                        class="text-sm font-semibold text-red-500 hover:text-red-600"
                        @click="removeDepartment(department)"
                    >
                        Remove
                    </button>
                </div>

                <div class="mt-4 border-t border-[#eef2ff] pt-4">
                    <div class="flex items-center justify-between text-sm text-[#64748b]">
                        <span>Head: <span class="font-semibold text-[#1f2937]">{{ department.head_name || 'Unassigned' }}</span></span>
                        <span class="text-[#8b5cf6]">
                            {{ department.employees_count ?? 0 }} Employees
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <button
                        class="rounded-lg border border-[#c7c4d8] px-3 py-1 text-xs font-semibold text-[#3525cd] hover:bg-[#e6eeff]"
                        @click="openEdit(department)"
                    >
                        Edit
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-[#c7c4d8] bg-white p-6 text-sm font-semibold text-[#94a3b8] hover:border-[#3525cd] hover:text-[#3525cd]"
                @click="openCreate"
            >
                <span class="text-3xl">+</span>
                Add Department
            </button>
        </div>
    </div>

    <UniversalForm
        as-modal
        :visible="createDialogOpen"
        title="Add Department"
        submit-label="Create"
        :processing="createForm.processing"
        @update:visible="createDialogOpen = $event"
        @submit="submitCreate"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <label class="text-sm font-semibold">Name</label>
                <Input v-model="createForm.name" placeholder="Department name" />
                <div v-if="createForm.errors.name" class="text-xs text-red-500">{{ createForm.errors.name }}</div>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-semibold">Head Name</label>
                <Input v-model="createForm.head_name" placeholder="Head's name" />
                <div v-if="createForm.errors.head_name" class="text-xs text-red-500">{{ createForm.errors.head_name }}</div>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-semibold">Total Employees</label>
                <Input v-model.number="createForm.employees_total" type="number" min="0" placeholder="0" />
                <div v-if="createForm.errors.employees_total" class="text-xs text-red-500">{{ createForm.errors.employees_total }}</div>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-semibold">Description</label>
                <textarea
                    v-model="createForm.description"
                    class="min-h-[90px] rounded-lg border border-input bg-background px-3 py-2 text-sm text-slate-700 dark:text-slate-100"
                    placeholder="Short description"
                />
                <div v-if="createForm.errors.description" class="text-xs text-red-500">{{ createForm.errors.description }}</div>
            </div>
        </div>
    </UniversalForm>

    <UniversalForm
        as-modal
        :visible="editDialogOpen"
        title="Edit Department"
        submit-label="Save"
        :processing="editForm.processing"
        @update:visible="editDialogOpen = $event"
        @submit="submitEdit"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <label class="text-sm font-semibold">Name</label>
                <Input v-model="editForm.name" placeholder="Department name" />
                <div v-if="editForm.errors.name" class="text-xs text-red-500">{{ editForm.errors.name }}</div>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-semibold">Head Name</label>
                <Input v-model="editForm.head_name" placeholder="Head's name" />
                <div v-if="editForm.errors.head_name" class="text-xs text-red-500">{{ editForm.errors.head_name }}</div>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-semibold">Total Employees</label>
                <Input v-model.number="editForm.employees_total" type="number" min="0" placeholder="0" />
                <div v-if="editForm.errors.employees_total" class="text-xs text-red-500">{{ editForm.errors.employees_total }}</div>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-semibold">Description</label>
                <textarea
                    v-model="editForm.description"
                    class="min-h-[90px] rounded-lg border border-input bg-background px-3 py-2 text-sm text-slate-700 dark:text-slate-100"
                    placeholder="Short description"
                />
                <div v-if="editForm.errors.description" class="text-xs text-red-500">{{ editForm.errors.description }}</div>
            </div>
        </div>
    </UniversalForm>
</template>
