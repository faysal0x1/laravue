import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

const confirmDelete = async ({ title, text, confirmButtonText = 'Delete', cancelButtonText = 'Cancel' }) => {
    const result = await Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    });

    return result.isConfirmed;
};

export const deleteItemWithConfirmation = async (options) => {
    const confirmed = await confirmDelete(options);
    if (!confirmed) return;

    router.visit(options.url, {
        method: 'delete',
        data: options.data,
        preserveScroll: true,
        onSuccess: options.onSuccess,
    });
};

export const exportTableRows = ({ selectedRows, allRows, type, fileName, sheetName = 'Sheet1' }) => {
    const rows = selectedRows.length > 0 ? selectedRows : allRows;
    const sheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, sheet, sheetName);

    XLSX.writeFile(workbook, `${fileName}.${type}`, { bookType: type });
};
