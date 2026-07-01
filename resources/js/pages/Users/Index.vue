<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, ShieldCheck, Trash2, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import SimpleDataTable from '@/components/data-table/SimpleDataTable.vue';
import BulkAssignRoleModal from '@/components/BulkAssignRoleModal.vue';
import UserRoleModal from '@/components/UserRoleModal.vue';
import ActionButton from '@/components/ActionButton.vue';
import { Button } from '@/components/ui/button';

import { useListingTable } from '@/composables/useListingTable.js';

import { dashboard } from '@/routes';

import {
    create as usersCreate,
    destroy as usersDestroy,
    edit as usersEdit,
    index as usersIndex,
    permissions as usersPermissions,
    table as usersTable,
} from '@/routes/users';

import { exportTableRows } from '@/utils/listingActions.js';
import { column } from '@/utils/tableUtils.js';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    availableRoles: {
        type: Array,
        default: () => [],
    },
});

const users = computed(() => props.users.data ?? []);

const selectedRows = ref([]);

const roleModalOpen = ref(false);
const roleModalUser = ref(null);

const bulkRoleModalOpen = ref(false);

const openRoleModal = (user) => {
    roleModalUser.value = user;
    roleModalOpen.value = true;
};

const {
    loading,
    searchTerm,
    tableState,
    firstRecordIndex,
    onTableChange,
} = useListingTable({
    filters: props.filters,
    initialPage: props.users.current_page,
    initialPerPage: props.users.per_page,
    postUrl: usersTable().url,
    only: ['users', 'filters'],
    defaultSortColumn: 'id',
    defaultSortDirection: 'desc',
});

const columns = [
    column('name', 'Name'),
    column('email', 'Email'),
    column('joined_at', 'Joined'),
    column('roles', 'Roles', { sortable: false }),
    column('permissions_count', 'Permissions', { sortable: false }),
    column('status', 'Status', { sortable: false }),
];

const removeUser = (user) => {
    if (!window.confirm(`Delete ${user.name}?`)) {
        return;
    }

    router.delete(usersDestroy(user.id).url);
};

const exportRows = (type) => {
    exportTableRows({
        selectedRows: selectedRows.value,
        allRows: users.value,
        type,
        fileName: 'users',
        sheetName: 'Users',
    });
};

const updateSelectedRows = (rows) => {
    selectedRows.value = rows;
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'User & Roles',
                href: usersIndex(),
            },
            {
                title: 'Users',
                href: usersIndex(),
            },
        ],
    }),
});
</script>

<template>

    <Head title="Users" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <SimpleDataTable
            :value="users"
            :columns="columns"
            :total-records="props.users.total"
            :table-state="tableState"
            :global-filter="searchTerm"
            :selected-rows="selectedRows"
            :loading="loading"
            title="Users"
            subtitle="Manage your team members and their account permissions."
            @update:global-filter="searchTerm = $event"
            @update:selected-rows="updateSelectedRows"
            @change="onTableChange"
            @export="exportRows"
        >
            <template #header-actions>
                <Button variant="outline"
                    class="h-10 rounded-lg border-[#3525cd] px-4 font-medium text-[#3525cd] hover:bg-indigo-50 dark:hover:bg-indigo-950"
                    @click="bulkRoleModalOpen = true">
                    <ShieldCheck class="size-4" />
                    Assign Role
                    <span v-if="selectedRows.length > 0"
                        class="ml-1 rounded-full bg-[#3525cd] px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                        {{ selectedRows.length }}
                    </span>
                </Button>
                <Button as-child
                    class="h-10 rounded-lg bg-[#3525cd] px-5 font-bold text-white shadow-sm hover:bg-[#2f21b9]">
                    <Link :href="usersCreate().url">
                        <UserPlus class="size-4" />
                        New User
                    </Link>
                </Button>
            </template>

            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <ActionButton
                        :icon="Pencil"
                        :route="usersEdit(row.id)"
                        name="Edit"
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-lg text-muted-foreground hover:bg-indigo-100 hover:text-[#3525cd]"
                        hideText
                    />
                    <ActionButton
                        :icon="ShieldCheck"
                        name="Role"
                        variant="secondary"
                        size="sm"
                        @click="openRoleModal(row)"
                    />
                    <ActionButton
                        :icon="ShieldCheck"
                        :route="usersPermissions(row.id)"
                        name="Permission"
                        variant="secondary"
                        size="sm"
                    />
                    <Button size="icon-sm" variant="ghost"
                        class="rounded-lg text-muted-foreground hover:bg-red-100 hover:text-red-700" title="Delete"
                        @click="removeUser(row)">
                        <Trash2 class="size-4" />
                        <span class="sr-only">Delete</span>
                    </Button>
                </div>
            </template>
        </SimpleDataTable>

    </div>

    <UserRoleModal v-model:is-open="roleModalOpen" :user="roleModalUser" :available-roles="availableRoles" />

    <BulkAssignRoleModal v-model:is-open="bulkRoleModalOpen" :selected-users="selectedRows" :users="users"
        :available-roles="availableRoles" />
</template>
