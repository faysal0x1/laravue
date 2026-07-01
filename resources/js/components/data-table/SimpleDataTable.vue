<script setup lang="ts">
import { computed, ref } from 'vue';
import type { PropType } from 'vue';

type TableRow = Record<string, unknown>;

type TableColumn = {
    field: string;
    header: string;
    sortable?: boolean;
    style?: Record<string, string> | string;
    headerClass?: string;
    bodyClass?: string;
};

type TableState = {
    page?: number;
    per_page?: number;
    sort_column?: string;
    sort_direction?: string;
};

type TableData = {
    value: TableRow[];
    columns: TableColumn[];
    totalRecords: number;
    tableState?: TableState;
    rows?: number;
    first?: number;
    sortField?: string;
    sortDirection?: string;
    globalFilter?: string;
    title?: string;
    subtitle?: string;
    rowKey?: string;
    selectedRows?: TableRow[];
    showSelection?: boolean;
    showExport?: boolean;
    showPagination?: boolean;
    loading?: boolean;
};

const props = defineProps({
    table: { type: Object as PropType<TableData>, default: null },
    value: { type: Array as PropType<TableRow[]>, required: true },
    columns: { type: Array as PropType<TableColumn[]>, required: true },
    totalRecords: { type: Number, required: true },
    rows: { type: Number, default: 10 },
    first: { type: Number, default: 0 },
    loading: { type: Boolean, default: false },
    sortField: { type: String, default: '' },
    sortDirection: { type: String, default: 'asc' },
    globalFilter: { type: String, default: '' },
    title: { type: String, default: 'Listing' },
    subtitle: { type: String, default: '' },
    rowKey: { type: String, default: 'id' },
    selectedRows: { type: Array as PropType<TableRow[]>, default: () => [] },
    showSelection: { type: Boolean, default: true },
    showExport: { type: Boolean, default: true },
    showPagination: { type: Boolean, default: true },
});

const emit = defineEmits(['update:selectedRows', 'update:globalFilter', 'change', 'export']);

const effectiveProps = computed(() => {
    const table = props.table ?? {};
    return {
        value: table.value ?? props.value,
        columns: table.columns ?? props.columns,
        totalRecords: table.totalRecords ?? props.totalRecords,
        tableState: table.tableState,
        rows: table.rows ?? props.rows,
        first: table.first ?? props.first,
        sortField: table.sortField ?? props.sortField,
        sortDirection: table.sortDirection ?? props.sortDirection,
        globalFilter: table.globalFilter ?? props.globalFilter,
        title: table.title ?? props.title,
        subtitle: table.subtitle ?? props.subtitle,
        rowKey: table.rowKey ?? props.rowKey,
        selectedRows: table.selectedRows ?? props.selectedRows,
        showSelection: table.showSelection ?? props.showSelection,
        showExport: table.showExport ?? props.showExport,
        showPagination: table.showPagination ?? props.showPagination,
        loading: table.loading ?? props.loading,
    };
});

const rows = computed(() => effectiveProps.value.tableState?.per_page ?? effectiveProps.value.rows);
const first = computed(() => {
    if (
        effectiveProps.value.tableState?.page !== undefined &&
        effectiveProps.value.tableState?.per_page !== undefined
    ) {
        return (effectiveProps.value.tableState.page - 1) * effectiveProps.value.tableState.per_page;
    }
    return effectiveProps.value.first;
});
const sortField = computed(() => effectiveProps.value.tableState?.sort_column ?? effectiveProps.value.sortField);
const sortDirection = computed(() => effectiveProps.value.tableState?.sort_direction ?? effectiveProps.value.sortDirection);
const currentPage = computed(() => Math.floor(first.value / rows.value) + 1);
const totalPages = computed(() => Math.max(1, Math.ceil(effectiveProps.value.totalRecords / rows.value)));
const isAllSelected = computed(
    () => effectiveProps.value.showSelection && effectiveProps.value.value.length > 0 && effectiveProps.value.selectedRows.length === effectiveProps.value.value.length,
);
const selectedCount = computed(() => effectiveProps.value.selectedRows.length);

const toggleSelection = (row: TableRow) => {
    const selected = [...effectiveProps.value.selectedRows];
    const index = selected.findIndex((r) => r[effectiveProps.value.rowKey] === row[effectiveProps.value.rowKey]);
    if (index >= 0) {
        selected.splice(index, 1);
    } else {
        selected.push(row);
    }
    emit('update:selectedRows', selected);
};

const toggleAll = () => {
    if (isAllSelected.value) {
        emit('update:selectedRows', []);
    } else {
        emit('update:selectedRows', [...effectiveProps.value.value]);
    }
};

const handleSort = (field: string) => {
    const column = effectiveProps.value.columns.find((col) => col.field === field);
    if (!column?.sortable) return;

    const direction = sortField.value === field && sortDirection.value === 'asc' ? 'desc' : 'asc';
    emit('change', {
        sort_column: field,
        sort_direction: direction,
        page: 1,
    });
};

const changePage = (pageIndex: number) => {
    if (pageIndex < 1 || pageIndex > totalPages.value) return;
    emit('change', { page: pageIndex, per_page: rows.value });
};

const changeRows = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('change', { page: 1, per_page: Number(target.value) });
};
</script>

<template>
    <div class="simple-data-table font-['Inter']">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">{{ effectiveProps.title }}</h1>
                <p v-if="effectiveProps.subtitle" class="mt-1 text-sm text-slate-600">{{ effectiveProps.subtitle }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center rounded-lg border border-slate-300 bg-white shadow-sm">
                    <button
                        v-if="effectiveProps.showExport"
                        type="button"
                        class="px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                        @click="emit('export', 'csv')"
                    >
                        CSV
                    </button>
                    <button
                        v-if="effectiveProps.showExport"
                        type="button"
                        class="px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                        @click="emit('export', 'xlsx')"
                    >
                        Excel
                    </button>
                </div>
                <slot name="header-actions" />
            </div>
        </div>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input
                    :value="effectiveProps.globalFilter"
                    @input="emit('update:globalFilter', ($event.target as HTMLInputElement).value)"
                    placeholder="Search..."
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-10 py-2 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                />
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-600">
                <span v-if="selectedCount">Selected: {{ selectedCount }}</span>
                <slot name="tools" />
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-700 uppercase tracking-[0.02em]">
                    <tr>
                        <th v-if="effectiveProps.showSelection" class="px-4 py-3">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleAll"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600"
                            />
                        </th>
                        <th
                            v-for="col in effectiveProps.columns"
                            :key="col.field"
                            class="px-4 py-3"
                            :class="col.headerClass"
                            :style="col.style"
                            @click="handleSort(col.field)"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ col.header }}</span>
                                <span v-if="col.sortable" class="text-xs text-slate-400">⇅</span>
                            </div>
                        </th>
                        <th v-if="$slots.actions" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr v-if="effectiveProps.loading" v-for="n in 5" :key="`loading-${n}`" class="animate-pulse">
                        <td :colspan="effectiveProps.columns.length + (effectiveProps.showSelection ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-4 py-6">
                            <div class="h-4 rounded bg-slate-200" />
                        </td>
                    </tr>
                    <template v-else-if="effectiveProps.value && effectiveProps.value.length > 0">
                        <tr v-for="row in effectiveProps.value" :key="String(row[effectiveProps.rowKey])" class="hover:bg-slate-50">
                            <td v-if="effectiveProps.showSelection" class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    :checked="effectiveProps.selectedRows.some((r) => r[effectiveProps.rowKey] === row[effectiveProps.rowKey])"
                                    @change="toggleSelection(row)"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600"
                                />
                            </td>
                            <td
                                v-for="col in effectiveProps.columns"
                                :key="col.field"
                                class="px-4 py-3 text-slate-700"
                                :class="col.bodyClass"
                            >
                                <slot :name="`cell-${col.field}`" :row="row">
                                    {{ row[col.field] ?? '—' }}
                                </slot>
                            </td>
                            <td v-if="$slots.actions" class="px-4 py-3 text-right">
                                <slot name="actions" :row="row" />
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td :colspan="effectiveProps.columns.length + (effectiveProps.showSelection ? 1 : 0) + ($slots.actions ? 1 : 0)" class="px-4 py-12 text-center text-slate-500">
                            No records found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="effectiveProps.showPagination" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm text-slate-600">
            <div class="flex items-center gap-2">
                <span>Show</span>
                <select
                    :value="rows"
                    @change="changeRows"
                    class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 outline-none"
                >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
                <span>rows per page</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    :disabled="currentPage === 1"
                    @click="changePage(1)"
                    class="rounded-lg border border-slate-300 px-3 py-1 text-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    First
                </button>
                <button
                    type="button"
                    :disabled="currentPage === 1"
                    @click="changePage(currentPage - 1)"
                    class="rounded-lg border border-slate-300 px-3 py-1 text-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Prev
                </button>
                <span>Page {{ currentPage }} of {{ totalPages }}</span>
                <button
                    type="button"
                    :disabled="currentPage === totalPages"
                    @click="changePage(currentPage + 1)"
                    class="rounded-lg border border-slate-300 px-3 py-1 text-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Next
                </button>
                <button
                    type="button"
                    :disabled="currentPage === totalPages"
                    @click="changePage(totalPages)"
                    class="rounded-lg border border-slate-300 px-3 py-1 text-sm transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Last
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
