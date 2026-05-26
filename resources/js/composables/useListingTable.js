import { router } from '@inertiajs/vue3';
import { computed, ref, unref, watch } from 'vue';
import { normalizeTableChange } from '@/utils/tableUtils.js';

export const useListingTable = (options) => {
    const loading = ref(false);
    const searchTerm = ref(options.filters?.search ?? '');
    const tableState = ref({
        page: Number(options.filters?.page ?? options.initialPage ?? 1),
        per_page: Number(options.filters?.per_page ?? options.initialPerPage ?? 10),
        sort_column: options.filters?.sort_column ?? options.defaultSortColumn ?? 'created_at',
        sort_direction: options.filters?.sort_direction ?? options.defaultSortDirection ?? 'desc',
    });

    let searchTimer = null;
    const firstRecordIndex = computed(() => (tableState.value.page - 1) * tableState.value.per_page);

    const fetchRows = (incoming = {}) => {
        tableState.value = { ...tableState.value, ...incoming };

        const extra = options.extraPayload ? unref(options.extraPayload) : {};

        router.visit(options.postUrl, {
            method: 'post',
            data: {
                search: searchTerm.value || undefined,
                page: tableState.value.page,
                per_page: tableState.value.per_page,
                sort_column: tableState.value.sort_column,
                sort_direction: tableState.value.sort_direction,
                ...extra,
            },
            only: options.only ?? ['employees', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => {
                loading.value = true;
            },
            onFinish: () => {
                loading.value = false;
            },
        });
    };

    const onTableChange = (params) => {
        fetchRows(normalizeTableChange(params, tableState.value));
    };

    watch(searchTerm, () => {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(() => fetchRows({ page: 1 }), options.searchDebounceMs ?? 400);
    });

    return {
        loading,
        searchTerm,
        tableState,
        firstRecordIndex,
        fetchRows,
        onTableChange,
    };
};
