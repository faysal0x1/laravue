<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdvancedDataTable from '@/components/data-table/AdvancedDataTable.vue';
import { useListingTable } from '@/composables/useListingTable.js';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as usersIndex } from '@/routes/users';
import { create as rolesCreate, destroy as rolesDestroy, edit as rolesEdit, index as rolesIndex, table as rolesTable } from '@/routes/roles';
import { exportTableRows } from '@/utils/listingActions.js';
import { column, statusBadgeClass } from '@/utils/tableUtils.js';

const props = defineProps({
    roles: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const roles = computed(() => props.roles.data ?? []);
const selectedRows = ref([]);
const { loading, searchTerm, tableState, firstRecordIndex, onTableChange } = useListingTable({
    filters: props.filters,
    initialPage: props.roles.current_page,
    initialPerPage: props.roles.per_page,
    postUrl: rolesTable().url,
    only: ['roles', 'filters'],
    defaultSortColumn: 'id',
    defaultSortDirection: 'desc',
});

const columns = [
    column('name', 'Name'),
    column('slug', 'Slug', { sortable: false }),
    column('permissions_count', 'Permission', { sortable: false }),
    column('status', 'Status'),
];

const removeRole = (role) => {
    if (!window.confirm(`Delete ${role.name}?`)) return;

    router.delete(rolesDestroy(role.id).url);
};

const exportRows = (type) => {
    exportTableRows({
        selectedRows: selectedRows.value,
        allRows: roles.value,
        type,
        fileName: 'roles',
        sheetName: 'Roles',
    });
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'User & Roles', href: usersIndex() },
            { title: 'Roles', href: rolesIndex() },
        ],
    }),
});
</script>

<template>
    <Head title="Roles" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <AdvancedDataTable
            :value="roles"
            :columns="columns"
            :total-records="props.roles.total"
            :rows="tableState.per_page"
            :first="firstRecordIndex"
            :sort-field="tableState.sort_column"
            :sort-direction="tableState.sort_direction"
            :global-filter="searchTerm"
            :selected-rows="selectedRows"
            :loading="loading"
            title="Roles"
            subtitle="Manage roles and permissions"
            @update:global-filter="searchTerm = $event"
            @update:selected-rows="selectedRows = $event"
            @change="onTableChange"
            @export="exportRows"
        >
            <template #header-actions>
                <Button as-child>
                    <Link :href="rolesCreate().url">New Role</Link>
                </Button>
            </template>

            <template #cell-permissions_count="{ row }">
                <span class="rounded-full bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-200">
                    {{ row.permissions_count }}
                </span>
            </template>

            <template #cell-status="{ row }">
                <span
                    class="rounded-full px-2 py-1 text-xs font-semibold"
                    :class="statusBadgeClass(row.status, {
                        active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200',
                        inactive: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                    })"
                >
                    {{ row.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <Button as-child size="sm" variant="outline">
                        <Link :href="rolesEdit(row.id).url">Edit</Link>
                    </Button>
                    <Button size="sm" variant="destructive" @click="removeRole(row)">
                        Delete
                    </Button>
                </div>
            </template>
        </AdvancedDataTable>
    </div>
</template>
