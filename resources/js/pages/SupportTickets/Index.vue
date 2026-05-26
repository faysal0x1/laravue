<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import GlobalListingPage from '@/components/listing/GlobalListingPage.vue';
import { useListingTable } from '@/composables/useListingTable.js';
import { buildListingState, column, createListingProps, formatDate, statusBadgeClass } from '@/utils/tableUtils.js';
import { deleteItemWithConfirmation } from '@/utils/listingActions.js';
import { dashboard } from '@/routes';
import {
    create as supportTicketsCreate,
    destroy as supportTicketsDestroy,
    edit as supportTicketsEdit,
    index as supportTicketsIndex,
    show as supportTicketsShow,
    table as supportTicketsTable,
} from '@/actions/App/Http/Controllers/SupportTicketController';

const page = usePage();

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Support tickets', href: supportTicketsIndex() },
        ],
    }),
});

const statusFilter = ref(props.filters?.status ?? '');
const typeFilter = ref(props.filters?.type ?? '');
const extraPayload = computed(() => ({
    status: statusFilter.value || undefined,
    type: typeFilter.value || undefined,
}));

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const selectedRows = ref([]);

const { loading, searchTerm, tableState, firstRecordIndex, onTableChange, fetchRows } = useListingTable({
    filters: props.filters,
    initialPage: props.tickets.current_page,
    initialPerPage: props.tickets.per_page,
    postUrl: supportTicketsTable().url,
    only: ['tickets', 'filters', 'canManage'],
    extraPayload,
    defaultSortColumn: 'created_at',
});

const columns = [
    column('ticket_number', 'Ticket #'),
    column('subject', 'Subject'),
    column('type', 'Type'),
    column('priority', 'Priority'),
    column('status', 'Status'),
    column('user', 'Requester'),
    column('created_at', 'Created'),
];

const ticketRows = computed(() => props.tickets.data ?? []);

const listingProps = computed(() =>
    createListingProps(
        buildListingState({
            title: 'Support tickets',
            subtitle: searchTerm.value ? `Results for "${searchTerm.value}"` : 'Track conversations and replies in one place.',
            rows: ticketRows.value,
            columns,
            records: props.tickets,
            tableState: tableState.value,
            firstRecordIndex: firstRecordIndex.value,
            searchTerm: searchTerm.value,
            selectedRows: selectedRows.value,
            loading: loading.value,
            createDialogOpen: createDialogOpen.value,
            editDialogOpen: editDialogOpen.value,
            createProcessing: false,
            editProcessing: false,
            createTitle: 'Create ticket',
            editTitle: 'Edit',
            createButtonLabel: 'New ticket',
        }),
    ),
);

const canCreate = computed(() => page.props.permissions?.['support-tickets.create'] === true);

const authUserId = computed(() => page.props.auth?.user?.id);

const onFilterChange = () => {
    fetchRows({ page: 1 });
};

const STATUS_BADGES = {
    open: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
    pending: 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200',
    in_progress: 'bg-violet-100 text-violet-900 dark:bg-violet-900/40 dark:text-violet-200',
    resolved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
    closed: 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
};

const PRIORITY_BADGES = {
    low: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
    high: 'bg-orange-100 text-orange-900 dark:bg-orange-900/40 dark:text-orange-200',
    urgent: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
};

const removeTicket = async (row) => {
    await deleteItemWithConfirmation({
        title: 'Delete ticket?',
        text: `Ticket ${row.ticket_number} will be removed.`,
        confirmButtonText: 'Delete',
        url: supportTicketsDestroy(row.id).url,
    });
};

const canEditRow = (row) => props.canManage || row.user?.id === authUserId.value;
const canDeleteRow = (row) => props.canManage || (row.user?.id === authUserId.value && ['open', 'pending', 'in_progress'].includes(row.status));
</script>

<template>
    <Head title="Support tickets" />

    <GlobalListingPage
        v-bind="listingProps"
        :show-create-form="false"
        :show-edit-form="false"
        :show-create-button="false"
        @update:global-filter="searchTerm = $event"
        @update:selected-rows="selectedRows = $event"
        @update:create-dialog-open="createDialogOpen = $event"
        @update:edit-dialog-open="editDialogOpen = $event"
        @change="onTableChange"
    >
        <template #header-actions>
            <select
                v-model="statusFilter"
                class="rounded-lg border border-sidebar-border/70 bg-background px-3 py-2 text-sm shadow-sm outline-none ring-offset-background focus:ring-2 focus:ring-ring dark:border-sidebar-border"
                @change="onFilterChange"
            >
                <option value="">All statuses</option>
                <option value="open">Open</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
            <select
                v-model="typeFilter"
                class="rounded-lg border border-sidebar-border/70 bg-background px-3 py-2 text-sm shadow-sm outline-none ring-offset-background focus:ring-2 focus:ring-ring dark:border-sidebar-border"
                @change="onFilterChange"
            >
                <option value="">All types</option>
                <option value="delivery_issue">Delivery issue</option>
                <option value="billing">Billing</option>
                <option value="account">Account</option>
                <option value="complaint">Complaint</option>
                <option value="general">General</option>
            </select>
            <Link
                v-if="canCreate"
                :href="supportTicketsCreate().url"
                class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:opacity-95 sm:w-auto"
            >
                New ticket
            </Link>
        </template>

        <template #cell-type="{ row }">
            <span class="capitalize">{{ String(row.type ?? '').replace('_', ' ') }}</span>
        </template>

        <template #cell-priority="{ row }">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                :class="statusBadgeClass(row.priority, PRIORITY_BADGES)"
            >
                {{ row.priority }}
            </span>
        </template>

        <template #cell-status="{ row }">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                :class="statusBadgeClass(row.status, STATUS_BADGES)"
            >
                {{ String(row.status ?? '').replace('_', ' ') }}
            </span>
        </template>

        <template #cell-user="{ row }">
            {{ row.user?.name ?? '—' }}
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <div class="flex flex-wrap gap-1">
                <Link :href="supportTicketsShow(row.id).url">
                    <Button icon="pi pi-eye" size="small" text rounded title="View" />
                </Link>
                <Link v-if="canEditRow(row)" :href="supportTicketsEdit(row.id).url">
                    <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" title="Edit" />
                </Link>
                <Button
                    v-if="canDeleteRow(row)"
                    icon="pi pi-trash"
                    size="small"
                    text
                    rounded
                    severity="danger"
                    title="Delete"
                    @click="removeTicket(row)"
                />
            </div>
        </template>
    </GlobalListingPage>
</template>
