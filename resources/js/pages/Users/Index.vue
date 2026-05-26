<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, ShieldCheck, Trash2, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { PropType } from 'vue';
import AdvancedDataTable from '@/components/data-table/AdvancedDataTable.vue';
import BulkAssignRoleModal from '@/components/BulkAssignRoleModal.vue';
import UserRoleModal from '@/components/UserRoleModal.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { useListingTable } from '@/composables/useListingTable.js';
import { dashboard } from '@/routes';
import { create as usersCreate, destroy as usersDestroy, edit as usersEdit, index as usersIndex, permissions as usersPermissions, table as usersTable } from '@/routes/users';
import { exportTableRows } from '@/utils/listingActions.js';
import { column } from '@/utils/tableUtils.js';

type User = {
    id: number;
    name: string;
    email: string;
    joined_at?: string;
    roles?: string[];
    permissions_count?: number;
};

type PaginatedUsers = {
    data: User[];
    current_page: number;
    per_page: number;
    total: number;
};

const props = defineProps({
    users: {
        type: Object as PropType<PaginatedUsers>,
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
const selectedRows = ref<User[]>([]);
const roleModalOpen = ref(false);
const roleModalUser = ref<User | null>(null);
const bulkRoleModalOpen = ref(false);

const openRoleModal = (user: User) => {
    roleModalUser.value = user;
    roleModalOpen.value = true;
};
const { loading, searchTerm, tableState, firstRecordIndex, onTableChange } = useListingTable({
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

const removeUser = (user: User) => {
    if (!window.confirm(`Delete ${user.name}?`)) {
        return;
    }

    router.delete(usersDestroy(user.id).url);
};

const exportRows = (type: string) => {
    exportTableRows({
        selectedRows: selectedRows.value,
        allRows: users.value,
        type,
        fileName: 'users',
        sheetName: 'Users',
    });
};

const updateSelectedRows = (rows: unknown[]) => {
    selectedRows.value = rows as User[];
};

const initials = (name: string) =>
    name
        .split(' ')
        .filter(Boolean)
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();

const joinedDate = (value?: string) => value || '-';

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
        <AdvancedDataTable :value="users" :columns="columns" :total-records="props.users.total"
            :rows="tableState.per_page" :first="firstRecordIndex" :sort-field="tableState.sort_column"
            :sort-direction="tableState.sort_direction" :global-filter="searchTerm" :selected-rows="selectedRows"
            :loading="loading" title="Users" subtitle="Manage your team members and their account permissions."
            @update:global-filter="searchTerm = $event" @update:selected-rows="updateSelectedRows"
            @change="onTableChange" @export="exportRows">
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

            <template #cell-name="{ row }">
                <div class="flex items-center gap-3">
                    <Avatar class="h-10 w-10">
                        <AvatarFallback
                            class="bg-slate-100 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            {{ initials(row.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="min-w-0">
                        <div class="font-semibold text-foreground">{{ row.name }}</div>
                        <div class="truncate text-sm text-muted-foreground">User account</div>
                    </div>
                </div>
            </template>

            <template #cell-email="{ row }">
                <div class="font-medium text-muted-foreground">{{ row.email }}</div>
            </template>

            <template #cell-joined_at="{ row }">
                <div class="font-medium text-muted-foreground">{{ joinedDate(row.joined_at) }}</div>
            </template>

            <template #cell-roles="{ row }">
                <div class="flex flex-wrap gap-1">
                    <template v-if="row.roles && row.roles.length > 0">
                        <span
                            v-for="role in row.roles"
                            :key="role"
                            class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300"
                        >
                            {{ role }}
                        </span>
                    </template>
                    <span v-else class="text-sm text-muted-foreground">—</span>
                </div>
            </template>

            <template #cell-permissions_count="{ row }">
                <span
                    v-if="(row.permissions_count ?? 0) > 0"
                    class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
                >
                    {{ row.permissions_count }}
                </span>
                <span v-else class="text-sm text-muted-foreground">0</span>
            </template>

            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <Button as-child size="icon-sm" variant="ghost"
                        class="rounded-lg text-muted-foreground hover:bg-indigo-100 hover:text-[#3525cd]" title="Edit">
                        <Link :href="usersEdit(row.id).url">
                            <Pencil class="size-4" />
                            <span class="sr-only">Edit</span>
                        </Link>
                    </Button>
                    <Button size="sm" variant="secondary" @click="openRoleModal(row)">
                        Role
                    </Button>
                    <Button as-child size="sm" variant="secondary">
                        <Link :href="usersPermissions(row.id).url">Permission</Link>
                    </Button>
                    <Button size="icon-sm" variant="ghost"
                        class="rounded-lg text-muted-foreground hover:bg-red-100 hover:text-red-700" title="Delete"
                        @click="removeUser(row)">
                        <Trash2 class="size-4" />
                        <span class="sr-only">Delete</span>
                    </Button>
                </div>
            </template>
        </AdvancedDataTable>


    </div>

    <UserRoleModal v-model:is-open="roleModalOpen" :user="roleModalUser" :available-roles="availableRoles" />

    <BulkAssignRoleModal v-model:is-open="bulkRoleModalOpen" :selected-users="selectedRows" :users="users"
        :available-roles="availableRoles" />
</template>
