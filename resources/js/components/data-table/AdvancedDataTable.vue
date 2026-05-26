<script setup lang="ts">
import Menu from 'primevue/menu';
import Skeleton from 'primevue/skeleton';
import { computed, ref } from 'vue';
import type { PropType } from 'vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { MoreHorizontal } from 'lucide-vue-next';

type TableRow = Record<string, unknown>;

type TableColumn = {
    field: string;
    header: string;
    sortable?: boolean;
    style?: Record<string, string> | string;
    headerClass?: string;
    bodyClass?: string;
};

const props = defineProps({
    value: { type: Array as PropType<TableRow[]>, required: true },
    columns: { type: Array as PropType<TableColumn[]>, required: true },
    totalRecords: { type: Number, required: true },
    rows: { type: Number, required: true },
    first: { type: Number, required: true },
    loading: { type: Boolean, default: false },
    sortField: { type: String, default: 'created_at' },
    sortDirection: { type: String, default: 'desc' },
    globalFilter: { type: String, default: '' },
    title: { type: String, default: 'Listing' },
    subtitle: { type: String, default: '' },
    rowKey: { type: String, default: 'id' },
    selectedRows: { type: Array as PropType<TableRow[]>, default: () => [] },
    loadingRows: { type: Number, default: 6 },
    showSelection: { type: Boolean, default: true },
    showExport: { type: Boolean, default: true },
    showBulkActions: { type: Boolean, default: true },
    showTableTools: { type: Boolean, default: true },
    actionType: { type: String as PropType<'default' | 'dropdown'>, default: 'default' },
});

const emit = defineEmits<{
    'update:globalFilter': [value: string];
    'update:selectedRows': [rows: TableRow[]];
    change: [payload: Record<string, number | string>];
    export: [type: 'csv' | 'xlsx'];
    'bulk-delete': [];
    filters: [];
    sort: [];
}>();

const bulkMenuRef = ref();
const selectedCount = computed(() => props.selectedRows.length);
const bulkMenuItems = computed(() => [
    { label: 'Delete selected', icon: 'pi pi-trash', command: () => emit('bulk-delete') },
]);

const currentPage = computed(() => Math.floor(props.first / props.rows) + 1);
const totalPages = computed(() => Math.ceil(props.totalRecords / props.rows) || 1);

const visiblePages = computed(() => {
    const current = currentPage.value;
    const total = totalPages.value;
    const delta = 1;
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
            range.push(i);
        }
    }

    for (const i of range) {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }
    return rangeWithDots;
});

const changePage = (pageIndex: number) => {
    if (pageIndex >= 0 && pageIndex < totalPages.value) {
        emit('change', { page: pageIndex + 1, per_page: props.rows });
    }
};

const changeRows = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    emit('change', { page: 1, per_page: parseInt(target.value, 10) });
};

const handleSort = (field: string) => {
    const col = props.columns.find(c => c.field === field);
    if (!col || !col.sortable) return;

    let direction = 'asc';
    if (props.sortField === field) {
        direction = props.sortDirection === 'asc' ? 'desc' : 'asc';
    }
    emit('change', {
        sort_column: field,
        sort_direction: direction,
    });
};

const isSelected = (row: TableRow) => props.selectedRows.some(r => r[props.rowKey] === row[props.rowKey]);
const isAllSelected = computed(() => props.value.length > 0 && props.selectedRows.length === props.value.length);

const toggleSelection = (row: TableRow) => {
    const selected = [...props.selectedRows];
    const index = selected.findIndex(r => r[props.rowKey] === row[props.rowKey]);
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
        emit('update:selectedRows', [...props.value]);
    }
};

const getStatusClass = (status: unknown) => {
    if (typeof status !== 'string') return 'px-2.5 py-1 bg-surface-container-high text-on-surface-variant rounded-full text-[12px] leading-[16px] tracking-[0.05em] font-bold';
    const s = status.toLowerCase();
    if (s === 'active' || s === 'confirm' || s === 'confirmed') {
        return 'px-2.5 py-1 bg-green-500/10 text-green-500 border border-green-500/20 rounded-full text-[12px] leading-[16px] tracking-[0.05em] font-bold';
    }
    if (s === 'pending') {
        return 'px-2.5 py-1 bg-amber-500/10 text-amber-500 border border-amber-500/20 rounded-full text-[12px] leading-[16px] tracking-[0.05em] font-bold';
    }
    if (s === 'reject' || s === 'rejected' || s === 'deactive' || s === 'inactive') {
        return 'px-2.5 py-1 bg-red-500/10 text-red-500 border border-red-500/20 rounded-full text-[12px] leading-[16px] tracking-[0.05em] font-bold';
    }
    return 'px-2.5 py-1 bg-surface-container-high text-on-surface-variant rounded-full text-[12px] leading-[16px] tracking-[0.05em] font-bold';
};

const formatStatus = (status: unknown) => {
    if (typeof status !== 'string') return String(status || '');
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
};
</script>

<template>
    <div class="advanced-data-table font-['Inter']">
        <!-- Table Header & Actions -->
        <div class="mb-[32px] flex flex-col md:flex-row md:items-end justify-between gap-[16px]">
            <div>
                <h1 class="text-[36px] leading-[44px] tracking-[-0.02em] font-bold text-on-surface mb-1">{{ title }}</h1>
                <p v-if="subtitle" class="text-[16px] leading-[24px] font-normal text-on-surface-variant">{{ subtitle }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-[16px]">
                <div v-if="showExport || showBulkActions"
                    class="flex border border-[#c7c4d8] rounded-lg overflow-hidden bg-[#ffffff] shadow-sm">
                    <button v-if="showExport" @click="emit('export', 'csv')"
                        class="flex items-center gap-1 px-3 py-2 border-r border-[#c7c4d8] hover:bg-[#d9e3f6] text-[12px] leading-[16px] tracking-[0.05em] font-medium text-[#464555] transition-colors">
                        <span class="material-symbols-outlined text-[18px]">download</span> CSV
                    </button>
                    <button v-if="showExport" @click="emit('export', 'xlsx')"
                        class="flex items-center gap-1 px-3 py-2 border-r border-[#c7c4d8] hover:bg-[#d9e3f6] text-[12px] leading-[16px] tracking-[0.05em] font-medium text-[#464555] transition-colors">
                        <span class="material-symbols-outlined text-[18px]">table_view</span> Excel
                    </button>
                    <button v-if="showBulkActions" @click="bulkMenuRef?.toggle($event)"
                        class="flex items-center gap-1 px-3 py-2 hover:bg-[#d9e3f6] text-[12px] leading-[16px] tracking-[0.05em] font-medium text-[#464555] transition-colors">
                        <span class="material-symbols-outlined text-[18px]">checklist</span> Bulk Actions
                    </button>
                </div>
                <slot name="header-actions" />
                <Menu v-if="showBulkActions" ref="bulkMenuRef" :model="bulkMenuItems" popup />
            </div>
        </div>

        <!-- Table Search and Filter Row -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative w-full sm:max-w-md">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#777587]">search</span>
                    <input :value="globalFilter"
                        @input="emit('update:globalFilter', ($event.target as HTMLInputElement).value)"
                        class="w-full pl-10 pr-4 py-2 bg-[#f8f9ff] border border-[#c7c4d8] rounded-lg text-[14px] leading-[20px] font-normal focus:ring-2 focus:ring-[#3525cd] focus:border-[#3525cd] transition-all outline-none"
                        placeholder="Search..." type="text" />
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="showTableTools" @click="emit('filters')"
                        class="flex items-center gap-2 px-3 py-2 border border-[#c7c4d8] rounded-lg text-[12px] leading-[16px] tracking-[0.05em] font-medium text-[#464555] hover:bg-[#e6eeff] hover:text-[#121c2a] transition-all">
                        <span class="material-symbols-outlined text-[18px]">filter_list</span> Filters
                    </button>
                    <button v-if="showTableTools" @click="emit('sort')"
                        class="flex items-center gap-2 px-3 py-2 border border-[#c7c4d8] rounded-lg text-[12px] leading-[16px] tracking-[0.05em] font-medium text-[#464555] hover:bg-[#e6eeff] hover:text-[#121c2a] transition-all">
                        <span class="material-symbols-outlined text-[18px]">sort</span> Sort
                    </button>
                </div>
            </div>

            <!-- Modern Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-[#eff4ff] text-[12px] leading-[16px] tracking-[0.05em] font-medium text-[#464555] uppercase tracking-wider">
                            <th v-if="showSelection" class="px-6 py-4 w-12 border-b border-[#c7c4d8]">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleAll"
                                    class="rounded border-[#c7c4d8] text-[#3525cd] focus:ring-[#3525cd]" />
                            </th>
                            <th v-for="col in columns" :key="col.field"
                                class="px-6 py-4 border-b border-[#c7c4d8] transition-colors"
                                :class="[col.sortable ? 'cursor-pointer hover:text-[#3525cd]' : '', col.headerClass]"
                                :style="col.style" @click="handleSort(col.field)">
                                <div class="flex items-center gap-1">
                                    {{ col.header }}
                                    <span v-if="col.sortable"
                                        class="material-symbols-outlined text-sm">unfold_more</span>
                                </div>
                            </th>
                            <th v-if="$slots.actions" class="px-6 py-4 border-b border-[#c7c4d8] text-right">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#c7c4d8]">
                        <tr v-if="loading" v-for="i in loadingRows" :key="'loading-' + i"
                            class="hover:bg-[#eff4ff] transition-colors">
                            <td :colspan="columns.length + (showSelection ? 1 : 0) + ($slots.actions ? 1 : 0)"
                                class="px-6 py-4">
                                <Skeleton height="2rem" />
                            </td>
                        </tr>
                        <template v-else-if="value && value.length > 0">
                            <tr v-for="row in value" :key="String(row[rowKey])"
                                class="hover:bg-[#eff4ff] transition-colors group">
                                <td v-if="showSelection" class="px-6 py-4">
                                    <input type="checkbox" :checked="isSelected(row)" @change="toggleSelection(row)"
                                        class="rounded border-[#c7c4d8] text-[#3525cd] focus:ring-[#3525cd]" />
                                </td>
                                <td v-for="col in columns" :key="col.field"
                                    class="px-6 py-4 text-[14px] leading-[20px] font-normal text-[#464555]"
                                    :class="col.bodyClass">
                                    <slot :name="`cell-${col.field}`" :row="row">
                                        <span v-if="col.field === 'status'" :class="getStatusClass(row[col.field])">
                                            {{ formatStatus(row[col.field]) }}
                                        </span>
                                        <template v-else>
                                            {{ row[col.field] }}
                                        </template>
                                    </slot>
                                </td>
                                <td v-if="$slots.actions" class="px-6 py-4 text-right">
                                    <div v-if="actionType === 'default'"
                                        class="flex items-center justify-end gap-2 transition-opacity">
                                        <slot name="actions" :row="row" />
                                    </div>
                                    <div v-else-if="actionType === 'dropdown'" class="flex justify-end">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost"
                                                    class="h-8 w-8 p-0 rounded-lg text-muted-foreground hover:bg-indigo-100 hover:text-[#3525cd]">
                                                    <span class="sr-only">Open menu</span>
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-[160px]">
                                                <slot name="actions" :row="row" />
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td :colspan="columns.length + (showSelection ? 1 : 0) + ($slots.actions ? 1 : 0)"
                                class="px-6 py-12 text-center text-[#777587]">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-4xl">inbox</span>
                                    <p>No records found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                class="px-6 py-4 bg-[#eff4ff] border-t border-[#c7c4d8] flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 text-[14px] leading-[20px] font-normal text-[#464555]">
                    <div class="flex items-center gap-2">
                        <span>Show</span>
                        <select :value="rows" @change="changeRows"
                            class="bg-[#ffffff] border border-[#c7c4d8] rounded-lg px-2 py-1 text-[14px] leading-[20px] font-medium focus:ring-1 focus:ring-[#3525cd] focus:border-[#3525cd] outline-none cursor-pointer">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                        <span>rows per page</span>
                    </div>
                    <span class="hidden md:inline">Showing {{ totalRecords === 0 ? 0 : first + 1 }} to {{ Math.min(first
                        + rows,
                        totalRecords) }} of {{ totalRecords }} entries</span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="changePage(0)" :disabled="currentPage === 1"
                        class="p-1.5 border border-[#c7c4d8] rounded-lg hover:bg-[#d9e3f6] disabled:opacity-30 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">keyboard_double_arrow_left</span>
                    </button>
                    <button @click="changePage(currentPage - 2)" :disabled="currentPage === 1"
                        class="p-1.5 border border-[#c7c4d8] rounded-lg hover:bg-[#d9e3f6] disabled:opacity-30 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                    </button>
                    <div class="flex items-center gap-1">
                        <template v-for="(page, idx) in visiblePages" :key="idx">
                            <span v-if="page === '...'" class="px-1 text-[#777587]">...</span>
                            <button v-else @click="changePage(Number(page) - 1)"
                                :class="['w-9 h-9 flex items-center justify-center rounded-lg text-[14px] leading-[20px] transition-colors', page === currentPage ? 'bg-[#3525cd] text-[#ffffff] font-bold' : 'text-[#464555] hover:bg-[#d9e3f6] font-medium']">{{
                                page }}</button>
                        </template>
                    </div>
                    <button @click="changePage(currentPage)" :disabled="currentPage === totalPages"
                        class="p-1.5 border border-[#c7c4d8] rounded-lg hover:bg-[#d9e3f6] disabled:opacity-30 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                    </button>
                    <button @click="changePage(totalPages - 1)" :disabled="currentPage === totalPages"
                        class="p-1.5 border border-[#c7c4d8] rounded-lg hover:bg-[#d9e3f6] disabled:opacity-30 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">keyboard_double_arrow_right</span>
                    </button>
                </div>
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
