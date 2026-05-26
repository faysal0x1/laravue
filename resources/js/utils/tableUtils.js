export const column = (field, header, options = {}) => ({
    field,
    header,
    sortable: true,
    ...options,
});

export const normalizeTableChange = (payload, current) => {
    const nextState = {};

    if (payload.page !== undefined) nextState.page = Number(payload.page);
    if (payload.per_page !== undefined) nextState.per_page = Number(payload.per_page);
    if (payload.sort_column !== undefined) nextState.sort_column = String(payload.sort_column);
    if (payload.sort_direction !== undefined) nextState.sort_direction = String(payload.sort_direction);

    return {
        page: nextState.page ?? current.page,
        per_page: nextState.per_page ?? current.per_page,
        sort_column: nextState.sort_column ?? current.sort_column,
        sort_direction: nextState.sort_direction ?? current.sort_direction,
    };
};

export const formatCurrency = (value, currencySymbol = 'BDT') => {
    const numericValue = Number(value ?? 0);
    if (Number.isNaN(numericValue)) return `${currencySymbol}0`;
    return `${currencySymbol}${numericValue.toLocaleString()}`;
};

export const formatDate = (value, locale = 'en-US', options = { year: 'numeric', month: 'short', day: 'numeric' }) => {
    if (!value) return '-';
    const date = new Date(String(value));
    if (Number.isNaN(date.getTime())) return '-';
    return new Intl.DateTimeFormat(locale, options).format(date);
};

export const statusBadgeClass = (
    status,
    map = {},
    fallback = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
) => {
    const key = String(status ?? '').toLowerCase();
    return map[key] ?? fallback;
};

export const buildListingState = ({
    title,
    subtitle = '',
    rows,
    columns,
    records,
    tableState,
    firstRecordIndex,
    searchTerm,
    selectedRows,
    loading,
    createDialogOpen,
    editDialogOpen,
    createProcessing,
    editProcessing,
    createTitle = 'Create',
    editTitle = 'Edit',
    createButtonLabel = 'New',
}) => ({
    title,
    subtitle,
    rows,
    columns,
    totalRecords: records?.total ?? 0,
    rowsPerPage: tableState.per_page,
    first: firstRecordIndex,
    sortField: tableState.sort_column,
    sortDirection: tableState.sort_direction,
    globalFilter: searchTerm,
    selectedRows,
    loading,
    createDialogOpen,
    editDialogOpen,
    createProcessing,
    editProcessing,
    createTitle,
    editTitle,
    createButtonLabel,
});

export const createListingProps = (input) => input;
