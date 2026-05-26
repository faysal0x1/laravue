<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import GlobalFormFields from '@/components/forms/GlobalFormFields.vue';
import GlobalListingPage from '@/components/listing/GlobalListingPage.vue';
import { Input } from '@/components/ui/input';
import { useListingTable } from '@/composables/useListingTable.js';
import { buildListingState, column, createListingProps, statusBadgeClass } from '@/utils/tableUtils.js';
import { deleteItemWithConfirmation, exportTableRows } from '@/utils/listingActions.js';
import {
    bulkDestroy as socialLinksBulkDestroy,
    destroy as socialLinkDestroy,
    index as socialLinkIndex,
    store as socialLinkStore,
    table as socialLinkTable,
    toggleStatus as socialLinkToggleStatus,
    update as socialLinkUpdate,
} from '@/actions/App/Http/Controllers/SocialLinkController';
import { dashboard } from '@/routes';

const props = defineProps({
    socialLinks: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, active: 0, inactive: 0 }),
    },
});

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Social Links', href: socialLinkIndex() },
        ],
    }),
});

const columns = [
    column('serial', '#', { sortable: false, headerClass: 'w-16' }),
    column('name', 'Name'),
    column('icon', 'Icon Class'),
    column('link', 'Link'),
    column('position', 'Position'),
    column('status', 'Status'),
];

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const selectedRows = ref([]);
const selectedLink = ref(null);

const { loading, searchTerm, tableState, firstRecordIndex, onTableChange } = useListingTable({
    filters: props.filters,
    initialPage: props.socialLinks.current_page,
    initialPerPage: props.socialLinks.per_page,
    postUrl: socialLinkTable().url,
    only: ['socialLinks', 'filters', 'stats'],
});

const socialRows = computed(() => props.socialLinks.data ?? []);
const socialRowsWithSerial = computed(() =>
    socialRows.value.map((row, index) => ({
        ...row,
        serial: firstRecordIndex.value + index + 1,
    })),
);

const createForm = useForm({
    name: '',
    icon: '',
    link: '',
    position: 0,
    status: true,
});

const editForm = useForm({
    name: '',
    icon: '',
    link: '',
    position: 0,
    status: true,
});

const formFields = [
    { name: 'name', label: 'Name', type: 'text', required: true, placeholder: 'e.g. Facebook' },
    { name: 'icon', label: 'Icon Class', type: 'text', required: true, placeholder: 'e.g. fab fa-facebook' },
    { name: 'link', label: 'URL', type: 'url', required: true, placeholder: 'https://facebook.com/yourpage' },
    { name: 'position', label: 'Position', type: 'number', required: true, placeholder: '0' },
    { name: 'status', label: 'Status', type: 'checkbox', placeholder: 'Active' },
];

const listingProps = computed(() =>
    createListingProps(buildListingState({
        title: 'Social Links',
        subtitle: searchTerm.value ? `Results for "${searchTerm.value}"` : '',
        rows: socialRowsWithSerial.value,
        columns,
        records: props.socialLinks,
        tableState: tableState.value,
        firstRecordIndex: firstRecordIndex.value,
        searchTerm: searchTerm.value,
        selectedRows: selectedRows.value,
        loading: loading.value,
        createDialogOpen: createDialogOpen.value,
        editDialogOpen: editDialogOpen.value,
        createProcessing: createForm.processing,
        editProcessing: editForm.processing,
        createTitle: 'Add Social Link',
        editTitle: 'Edit Social Link',
        createButtonLabel: 'Add Social Link',
    })),
);

const submitCreate = () => {
    createForm.post(socialLinkStore().url, {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            createDialogOpen.value = false;
        },
    });
};

const openEdit = (link) => {
    selectedLink.value = link;
    editForm.name = link.name;
    editForm.icon = link.icon;
    editForm.link = link.link;
    editForm.position = link.position;
    editForm.status = link.status;
    editDialogOpen.value = true;
};

const submitEdit = () => {
    if (!selectedLink.value) return;

    editForm.put(socialLinkUpdate(selectedLink.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            selectedLink.value = null;
            editDialogOpen.value = false;
        },
    });
};

const toggleStatus = (link) => {
    router.put(socialLinkToggleStatus(link.id).url, {}, { preserveScroll: true });
};

const removeLink = async (link) => {
    await deleteItemWithConfirmation({
        title: 'Delete social link?',
        text: `This will permanently delete "${link.name}".`,
        confirmButtonText: 'Delete',
        url: socialLinkDestroy(link.id).url,
    });
};

const exportRows = (type) => {
    exportTableRows({
        selectedRows: selectedRows.value,
        allRows: socialRows.value,
        type,
        fileName: 'social-links',
        sheetName: 'Social Links',
    });
};

const bulkDeleteLinks = async () => {
    if (selectedRows.value.length === 0) return;

    await deleteItemWithConfirmation({
        title: 'Delete selected social links?',
        text: `You are about to delete ${selectedRows.value.length} social links.`,
        confirmButtonText: 'Delete all',
        url: socialLinksBulkDestroy().url,
        data: { ids: selectedRows.value.map((item) => Number(item.id)) },
        onSuccess: () => {
            selectedRows.value = [];
        },
    });
};
</script>

<template>
    <GlobalListingPage
        v-bind="listingProps"
        @update:global-filter="searchTerm = $event"
        @update:selected-rows="selectedRows = $event"
        @update:create-dialog-open="createDialogOpen = $event"
        @update:edit-dialog-open="editDialogOpen = $event"
        @change="onTableChange"
        @export="exportRows"
        @bulk-delete="bulkDeleteLinks"
        @create-submit="submitCreate"
        @edit-submit="submitEdit"
    >
        <template #cell-serial="{ row }">
            <span class="text-sm font-semibold text-[#121c2a]">{{ row.serial }}</span>
        </template>

        <template #cell-name="{ row }">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <i :class="row.icon" class="text-base" />
                </div>
                <span class="font-semibold text-[#121c2a]">{{ row.name }}</span>
            </div>
        </template>

        <template #cell-icon="{ row }">
            <code class="rounded-md bg-[#f8f9ff] px-2 py-1 text-xs font-medium text-[#464555]">
                {{ row.icon }}
            </code>
        </template>

        <template #cell-link="{ row }">
            <a
                :href="row.link"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex max-w-[240px] items-center gap-1 truncate text-[#3525cd] hover:underline"
            >
                <span class="truncate">{{ row.link }}</span>
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            </a>
        </template>

        <template #cell-position="{ row }">
            <span class="rounded-full bg-[#eff4ff] px-2 py-1 text-xs font-semibold text-[#3525cd]">
                {{ row.position }}
            </span>
        </template>

        <template #cell-status="{ row }">
            <button
                type="button"
                class="rounded-full px-2.5 py-1 text-xs font-bold transition-colors"
                :class="statusBadgeClass(row.status ? 'active' : 'inactive', {
                    active: 'bg-emerald-100 text-emerald-700',
                    inactive: 'bg-red-100 text-red-700',
                })"
                @click="toggleStatus(row)"
            >
                {{ row.status ? 'Active' : 'Inactive' }}
            </button>
        </template>

        <template #actions="{ row }">
            <div class="flex gap-2">
                <Button icon="pi pi-pencil" size="small" text rounded @click="openEdit(row)" />
                <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="removeLink(row)" />
            </div>
        </template>

        <template #create-form>
            <GlobalFormFields :form="createForm" :fields="formFields" :gap-class="'gap-5'">
                <template #field-icon="{ form, field }">
                    <div class="relative">
                        <Input
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="field.type"
                            :placeholder="field.placeholder"
                            :required="field.required"
                            class="pr-10"
                        />
                        <span
                            v-if="form.icon"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#3525cd]"
                        >
                            <i :class="form.icon" />
                        </span>
                    </div>
                </template>

                <template #field-status="{ form }">
                    <div class="flex items-center gap-3 pt-1">
                        <button
                            type="button"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out"
                            :class="form.status ? 'bg-emerald-500' : 'bg-slate-300'"
                            @click="form.status = !form.status"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                                :class="form.status ? 'translate-x-5' : 'translate-x-0'"
                            />
                        </button>
                        <span class="text-sm font-medium" :class="form.status ? 'text-emerald-600' : 'text-slate-500'">
                            {{ form.status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </template>
            </GlobalFormFields>
        </template>

        <template #edit-form>
            <GlobalFormFields :form="editForm" :fields="formFields" :gap-class="'gap-5'">
                <template #field-icon="{ form, field }">
                    <div class="relative">
                        <Input
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="field.type"
                            :placeholder="field.placeholder"
                            :required="field.required"
                            class="pr-10"
                        />
                        <span
                            v-if="form.icon"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#3525cd]"
                        >
                            <i :class="form.icon" />
                        </span>
                    </div>
                </template>

                <template #field-status="{ form }">
                    <div class="flex items-center gap-3 pt-1">
                        <button
                            type="button"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out"
                            :class="form.status ? 'bg-emerald-500' : 'bg-slate-300'"
                            @click="form.status = !form.status"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                                :class="form.status ? 'translate-x-5' : 'translate-x-0'"
                            />
                        </button>
                        <span class="text-sm font-medium" :class="form.status ? 'text-emerald-600' : 'text-slate-500'">
                            {{ form.status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </template>
            </GlobalFormFields>
        </template>
    </GlobalListingPage>
</template>
