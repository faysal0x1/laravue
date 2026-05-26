<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import {
    create as supportTicketsCreate,
    index as supportTicketsIndex,
    store as supportTicketsStore,
} from '@/actions/App/Http/Controllers/SupportTicketController';

const props = defineProps({
    parcels: {
        type: Array,
        default: () => [],
    },
});

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Support tickets', href: supportTicketsIndex() },
            { title: 'New ticket', href: supportTicketsCreate() },
        ],
    }),
});

const form = useForm({
    subject: '',
    description: '',
    type: 'general',
    priority: 'medium',
    parcel_id: '',
    attachments: [],
});

const onFiles = (event) => {
    const files = event.target.files;
    form.attachments = files ? Array.from(files) : [];
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        parcel_id: data.parcel_id === '' ? null : data.parcel_id,
    })).post(supportTicketsStore().url, {
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
</script>

<template>
    <Head title="New support ticket" />

    <div class="mx-auto w-full max-w-3xl space-y-6 p-4">
        <div class="overflow-hidden rounded-2xl border border-sidebar-border/70 bg-gradient-to-br from-slate-50 to-white shadow-sm dark:border-sidebar-border dark:from-slate-900 dark:to-slate-950">
            <div class="border-b border-sidebar-border/60 bg-white/80 px-6 py-5 dark:border-sidebar-border dark:bg-slate-900/50">
                <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">Open a support ticket</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Describe your issue and attach screenshots or documents. Our team will respond on this thread.
                </p>
            </div>

            <form class="space-y-5 p-6" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Subject</label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm shadow-inner outline-none ring-offset-background focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                            placeholder="Short summary of the problem"
                        />
                        <p v-if="form.errors.subject" class="text-sm text-red-600">{{ form.errors.subject }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Type</label>
                        <select
                            v-model="form.type"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.type" class="text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Priority</label>
                        <select
                            v-model="form.priority"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option v-for="opt in priorityOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.priority" class="text-sm text-red-600">{{ form.errors.priority }}</p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Related parcel (optional)</label>
                        <select
                            v-model="form.parcel_id"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                        >
                            <option value="">None</option>
                            <option v-for="p in parcels" :key="p.id" :value="p.id">
                                {{ p.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.parcel_id" class="text-sm text-red-600">{{ form.errors.parcel_id }}</p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Description</label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="6"
                            class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm shadow-inner outline-none ring-offset-background focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                            placeholder="Include steps to reproduce, order numbers, or anything that helps us assist faster."
                        />
                        <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Attachments</label>
                        <input
                            type="file"
                            multiple
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,image/*,application/pdf"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:opacity-90 dark:text-slate-400"
                            @change="onFiles"
                        />
                        <p class="text-xs text-slate-500">Up to 10 files, 10MB each. Images, PDF, Word, or plain text.</p>
                        <p v-if="form.errors.attachments" class="text-sm text-red-600">{{ form.errors.attachments }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <Button as-child variant="outline">
                        <Link :href="supportTicketsIndex().url">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Submitting…' : 'Submit ticket' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
