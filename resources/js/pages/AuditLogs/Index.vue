<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdvancedDataTable from '@/components/data-table/AdvancedDataTable.vue';
import { useListingTable } from '@/composables/useListingTable.js';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as auditLogsIndex, table as auditLogsTable } from '@/routes/audit-logs';
import { exportTableRows } from '@/utils/listingActions.js';
import { column } from '@/utils/tableUtils.js';

import InputText from 'primevue/inputtext';

const props = defineProps({
    auditLogs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const auditLogs = computed(() => props.auditLogs.data ?? []);
const selectedRows = ref([]);

// We use local refs for specific filters so they sync with the table state
const actionFilter = ref(props.filters.action || '');
const modelTypeFilter = ref(props.filters.model_type || '');

const { loading, searchTerm, tableState, firstRecordIndex, onTableChange } = useListingTable({
    filters: props.filters,
    initialPage: props.auditLogs.current_page,
    initialPerPage: props.auditLogs.per_page,
    postUrl: auditLogsTable().url,
    only: ['auditLogs', 'filters'],
    defaultSortColumn: 'id',
    defaultSortDirection: 'desc',
});

// Update table params when specific filters change
const onSpecificFilterChange = () => {
    onTableChange({
        action: actionFilter.value,
        model_type: modelTypeFilter.value,
        page: 1, // Reset to page 1 on filter
    });
};

const columns = [
    column('id', 'ID'),
    column('action', 'Action'),
    column('model_type', 'Model'),
    column('model_id', 'Model ID'),
    column('ip_address', 'IP Address'),
    column('created_at', 'Date'),
];

const exportRows = (type) => {
    exportTableRows({
        selectedRows: selectedRows.value,
        allRows: auditLogs.value,
        type,
        fileName: 'audit_logs',
        sheetName: 'Audit Logs',
    });
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Audit Logs',
                href: auditLogsIndex(),
            },
        ],
    }),
});
</script>

<template>
    <Head title="Audit Logs" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <AdvancedDataTable
            :value="auditLogs"
            :columns="columns"
            :total-records="props.auditLogs.total"
            :rows="tableState.per_page"
            :first="firstRecordIndex"
            :sort-field="tableState.sort_column"
            :sort-direction="tableState.sort_direction"
            :global-filter="searchTerm"
            :selected-rows="selectedRows"
            :loading="loading"
            title="Audit Logs"
            subtitle="View system audit logs"
            @update:global-filter="searchTerm = $event"
            @update:selected-rows="selectedRows = $event"
            @change="onTableChange"
            @export="exportRows"
        >
            <template #header-actions>
                <div class="flex gap-2">
                    <InputText
                        v-model="actionFilter"
                        placeholder="Filter by Action"
                        class="w-40"
                        @update:model-value="onSpecificFilterChange"
                    />
                    <InputText
                        v-model="modelTypeFilter"
                        placeholder="Filter by Model Type"
                        class="w-48"
                        @update:model-value="onSpecificFilterChange"
                    />
                </div>
            </template>
        </AdvancedDataTable>
    </div>
</template>
