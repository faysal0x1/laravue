<script setup>
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import {
    index as supportTicketsIndex,
    show as supportTicketsShow,
    update as supportTicketsUpdate,
} from '@/actions/App/Http/Controllers/SupportTicketController';

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
    parcels: {
        type: Array,
        default: () => [],
    },
    assignees: {
        type: Array,
        default: () => [],
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard().url },
            { title: 'Support tickets', href: supportTicketsIndex().url },
            { title: props.ticket.ticket_number, href: supportTicketsShow(props.ticket.id).url },
            { title: 'Edit', href: '#' },
        ],
    });
});

const form = useForm({
    subject: props.ticket.subject,
    description: props.ticket.description,
    type: props.ticket.type,
    priority: props.ticket.priority,
    parcel_id: props.ticket.parcel_id ?? '',
    status: props.ticket.status,
    assigned_to: props.ticket.assigned_to ?? '',
    attachments: [],
});

const assigneeOptions = computed(() => props.assignees);

const onFiles = (event) => {
    const files = event.target.files;
    form.attachments = files ? Array.from(files) : [];
};

const submit = () => {
    form.transform((data) => {
        const out = {
            ...data,
            parcel_id: data.parcel_id === '' ? null : data.parcel_id,
        };
        if (data.assigned_to === '') {
            out.assigned_to = null;
        }
        if (!props.canManage) {
            delete out.status;
            delete out.assigned_to;
        }
        return out;
    }).put(supportTicketsUpdate(props.ticket.id).url, {
        forceFormData: true,
    });
};

const typeOptions = [
    { value: 'delivery_issue', label: 'Delivery issue' },
    { value: 'billing', label: 'Billing' },
    { value: 'account', label: 'Account' },
    { value: 'complaint', label: 'Complaint' },
    { value: 'general', label: 'General' },
];

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
];

const statusOptions = [
    { value: 'open', label: 'Open' },
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In progress' },
    { value: 'resolved', label: 'Resolved' },
    { value: 'closed', label: 'Closed' },
];
</script>

<template>
    <Head :title="`Edit ${ticket.ticket_number}`" />

    <div class="mx-auto w-full max-w-3xl space-y-6 p-4">
        <div class="overflow-hidden rounded-2xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-slate-950">
            <div class="border-b border-sidebar-border/60 px-6 py-5 dark:border-sidebar-border">
                <h1 class="text-xl font-semibold tracking-tight">Edit ticket {{ ticket.ticket_number }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Update details and optionally add more attachments.</p>
            </div>

            <form class="space-y-5 p-6" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium">Subject</label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        />
                        <p v-if="form.errors.subject" class="text-sm text-red-600">{{ form.errors.subject }}</p>
                    </div>

                    <div v-if="canManage" class="space-y-2">
                        <label class="text-sm font-medium">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.status" class="text-sm text-red-600">{{ form.errors.status }}</p>
                    </div>

                    <div v-if="canManage" class="space-y-2">
                        <label class="text-sm font-medium">Assignee</label>
                        <select
                            v-model="form.assigned_to"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option value="">Unassigned</option>
                            <option v-for="u in assigneeOptions" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <p v-if="form.errors.assigned_to" class="text-sm text-red-600">{{ form.errors.assigned_to }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Type</label>
                        <select
                            v-model="form.type"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Priority</label>
                        <select
                            v-model="form.priority"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option v-for="opt in priorityOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium">Related parcel</label>
                        <select
                            v-model="form.parcel_id"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option value="">None</option>
                            <option v-for="p in parcels" :key="p.id" :value="p.id">
                                {{ p.label }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium">Description</label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="6"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        />
                        <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium">Add attachments</label>
                        <input
                            type="file"
                            multiple
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,image/*,application/pdf"
                            class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground"
                            @change="onFiles"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-2">
                    <Button as-child variant="outline">
                        <Link :href="supportTicketsShow(ticket.id).url">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save changes' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
