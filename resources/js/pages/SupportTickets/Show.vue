<script setup>
import { Head, Link, router, setLayoutProps, useForm, usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import Button from 'primevue/button';
import { Button as UiButton } from '@/components/ui/button';
import { formatDate, statusBadgeClass } from '@/utils/tableUtils.js';
import { dashboard } from '@/routes';
import { destroy as supportTicketsDestroy, edit as supportTicketsEdit, index as supportTicketsIndex } from '@/actions/App/Http/Controllers/SupportTicketController';
import { store as replyStore } from '@/actions/App/Http/Controllers/SupportTicketReplyController';
import { destroyReply as destroyReplyAttachment, destroyTicket as destroyTicketAttachment } from '@/actions/App/Http/Controllers/SupportTicketMediaController';
import { deleteItemWithConfirmation } from '@/utils/listingActions.js';

const page = usePage();

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    canReply: {
        type: Boolean,
        default: false,
    },
    replyFormDefaults: {
        type: Object,
        default: () => ({}),
    },
});

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard().url },
            { title: 'Support tickets', href: supportTicketsIndex().url },
            { title: props.ticket.ticket_number, href: '#' },
        ],
    });
});

const authUserId = computed(() => page.props.auth?.user?.id);

const canEdit = computed(
    () => props.canManage || (props.ticket.user?.id === authUserId.value && props.ticket.status !== 'closed'),
);

const canDeleteTicket = computed(
    () =>
        props.canManage ||
        (props.ticket.user?.id === authUserId.value &&
            ['open', 'pending', 'in_progress'].includes(props.ticket.status)),
);

const replyForm = useForm({
    message: props.replyFormDefaults.message ?? '',
    is_internal: props.replyFormDefaults.is_internal ?? false,
    attachments: [],
});

const onReplyFiles = (e) => {
    const files = e.target.files;
    replyForm.attachments = files ? Array.from(files) : [];
};

const submitReply = () => {
    replyForm
        .transform((data) => ({
            ...data,
            is_internal: props.canManage ? Boolean(data.is_internal) : false,
        }))
        .post(replyStore(props.ticket.id).url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                replyForm.reset('message', 'attachments', 'is_internal');
                replyForm.clearErrors();
            },
        });
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

const formatBytes = (n) => {
    if (n == null || Number.isNaN(Number(n))) return '';
    const v = Number(n);
    if (v < 1024) return `${v} B`;
    if (v < 1024 * 1024) return `${(v / 1024).toFixed(1)} KB`;
    return `${(v / (1024 * 1024)).toFixed(1)} MB`;
};

const removeTicket = async () => {
    await deleteItemWithConfirmation({
        title: 'Delete this ticket?',
        text: 'This action cannot be undone.',
        confirmButtonText: 'Delete',
        url: supportTicketsDestroy(props.ticket.id).url,
    });
};

const deleteTicketFile = (attachment) => {
    router.visit(destroyTicketAttachment({ support_ticket: props.ticket.id, media: attachment.id }).url, {
        method: 'delete',
        preserveScroll: true,
    });
};

const deleteReplyFile = (reply, attachment) => {
    router.visit(
        destroyReplyAttachment({
            support_ticket: props.ticket.id,
            reply: reply.id,
            media: attachment.id,
        }).url,
        { method: 'delete', preserveScroll: true },
    );
};

const isImage = (mime) => mime && String(mime).startsWith('image/');
</script>

<template>
    <Head :title="ticket.ticket_number" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 pb-16">
        <!-- Header -->
        <div
            class="relative overflow-hidden rounded-2xl border border-sidebar-border/70 bg-gradient-to-br from-indigo-50 via-white to-slate-50 shadow-sm dark:border-sidebar-border dark:from-slate-900 dark:via-slate-950 dark:to-slate-900"
        >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500" />
            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ ticket.ticket_number }}
                    </p>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                        {{ ticket.subject }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                            :class="statusBadgeClass(ticket.status, STATUS_BADGES)"
                        >
                            {{ String(ticket.status).replace('_', ' ') }}
                        </span>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                            :class="statusBadgeClass(ticket.priority, PRIORITY_BADGES)"
                        >
                            {{ ticket.priority }} priority
                        </span>
                        <span>· {{ formatDate(ticket.created_at) }}</span>
                        <span v-if="ticket.parcel?.tracking_number"> · Parcel {{ ticket.parcel.tracking_number }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <UiButton v-if="canEdit" as-child variant="outline" size="sm">
                        <Link :href="supportTicketsEdit(ticket.id).url">Edit</Link>
                    </UiButton>
                    <Button
                        v-if="canDeleteTicket"
                        label="Delete"
                        icon="pi pi-trash"
                        size="small"
                        severity="danger"
                        outlined
                        @click="removeTicket"
                    />
                </div>
            </div>
        </div>

        <!-- Original message -->
        <section class="rounded-2xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
            <div class="flex items-start gap-3 border-b border-sidebar-border/60 pb-4 dark:border-sidebar-border">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary"
                >
                    {{ ticket.user?.name?.charAt(0) ?? '?' }}
                </div>
                <div>
                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ ticket.user?.name }}</p>
                    <p class="text-xs text-slate-500">{{ ticket.user?.email }} · {{ formatDate(ticket.created_at) }}</p>
                </div>
            </div>
            <div class="prose prose-sm mt-4 max-w-none whitespace-pre-wrap text-slate-800 dark:prose-invert dark:text-slate-200">
                {{ ticket.description }}
            </div>
            <div v-if="ticket.attachments?.length" class="mt-4 space-y-2">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Attachments</p>
                <ul class="flex flex-col gap-2">
                    <li
                        v-for="file in ticket.attachments"
                        :key="file.id"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-sidebar-border/60 bg-muted/30 px-3 py-2 dark:border-sidebar-border"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <a
                                :href="file.url"
                                target="_blank"
                                rel="noreferrer"
                                class="truncate text-sm font-medium text-primary hover:underline"
                            >
                                {{ file.file_name }}
                            </a>
                            <span class="text-xs text-slate-500">{{ formatBytes(file.size) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a v-if="isImage(file.mime_type)" :href="file.url" target="_blank" rel="noreferrer">
                                <img :src="file.url" alt="" class="h-12 w-12 rounded-lg object-cover ring-1 ring-border" />
                            </a>
                            <Button
                                v-if="canEdit"
                                icon="pi pi-times"
                                size="small"
                                text
                                rounded
                                severity="danger"
                                title="Remove"
                                @click="deleteTicketFile(file)"
                            />
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Thread -->
        <section class="space-y-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Conversation</h2>

            <article
                v-for="reply in ticket.replies"
                :key="reply.id"
                class="rounded-2xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border"
                :class="reply.is_internal ? 'border-amber-200/80 bg-amber-50/40 dark:border-amber-900/50 dark:bg-amber-950/20' : ''"
            >
                <div class="flex items-start justify-between gap-2 border-b border-sidebar-border/50 pb-3 dark:border-sidebar-border">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold dark:bg-slate-700"
                        >
                            {{ reply.user?.name?.charAt(0) ?? '?' }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ reply.user?.name }}</p>
                            <p class="text-xs text-slate-500">{{ formatDate(reply.created_at) }}</p>
                        </div>
                    </div>
                    <span
                        v-if="reply.is_internal"
                        class="rounded-full bg-amber-200/80 px-2 py-0.5 text-xs font-medium text-amber-900 dark:bg-amber-900/60 dark:text-amber-100"
                    >
                        Internal
                    </span>
                </div>
                <div class="prose prose-sm mt-3 max-w-none whitespace-pre-wrap text-slate-800 dark:prose-invert dark:text-slate-200">
                    {{ reply.message }}
                </div>
                <ul v-if="reply.attachments?.length" class="mt-3 space-y-2">
                    <li
                        v-for="file in reply.attachments"
                        :key="file.id"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-sidebar-border/50 bg-muted/20 px-3 py-2"
                    >
                        <a :href="file.url" target="_blank" rel="noreferrer" class="text-sm text-primary hover:underline">
                            {{ file.file_name }}
                        </a>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">{{ formatBytes(file.size) }}</span>
                            <Button
                                v-if="canEdit"
                                icon="pi pi-times"
                                size="small"
                                text
                                severity="danger"
                                @click="deleteReplyFile(reply, file)"
                            />
                        </div>
                    </li>
                </ul>
            </article>
        </section>

        <!-- Reply box -->
        <section
            v-if="canReply && ticket.status !== 'closed'"
            class="sticky bottom-4 z-10 rounded-2xl border border-sidebar-border/80 bg-background/95 p-4 shadow-lg backdrop-blur dark:border-sidebar-border"
        >
            <h3 class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200">Add a reply</h3>
            <form class="space-y-3" @submit.prevent="submitReply">
                <textarea
                    v-model="replyForm.message"
                    rows="4"
                    required
                    class="w-full rounded-xl border border-sidebar-border/70 bg-background px-3 py-2 text-sm outline-none ring-offset-background focus:ring-2 focus:ring-primary dark:border-sidebar-border"
                    placeholder="Write your message…"
                />
                <p v-if="replyForm.errors.message" class="text-sm text-red-600">{{ replyForm.errors.message }}</p>

                <label v-if="canManage" class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input v-model="replyForm.is_internal" type="checkbox" class="rounded border-input" />
                    Internal note (not visible to customer)
                </label>

                <input
                    type="file"
                    multiple
                    accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,image/*,application/pdf"
                    class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-muted file:px-3 file:py-1.5"
                    @change="onReplyFiles"
                />

                <div class="flex justify-end">
                    <UiButton type="submit" size="sm" :disabled="replyForm.processing">
                        {{ replyForm.processing ? 'Sending…' : 'Send reply' }}
                    </UiButton>
                </div>
            </form>
        </section>
    </div>
</template>
