<script setup>
import { Form, Head } from '@inertiajs/vue3';
import DatabaseBackupController from '@/actions/App/Http/Controllers/Settings/DatabaseBackupController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/database-backup';

const props = defineProps({
    backups: {
        type: Array,
        required: true,
    },
    retentionDays: {
        type: Number,
        required: true,
    },
    scheduleBackupAt: {
        type: String,
        required: true,
    },
    schedulePruneAt: {
        type: String,
        required: true,
    },
});

const formatBytes = (bytes) => {
    if (!bytes || bytes < 1) return '0 B';

    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${(bytes / k ** i).toFixed(2)} ${sizes[i]}`;
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Database backup', href: edit().url }],
    },
});
</script>

<template>
    <Head title="Database backup" />

    <h1 class="sr-only">Database backup</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Manual backup"
            description="Create an on-demand copy of your database stored under storage/app/database-backups. MySQL/MariaDB require mysqldump in PATH (or DATABASE_BACKUP_MYSQLDUMP_PATH). PostgreSQL requires pg_dump."
        />

        <Form
            v-bind="DatabaseBackupController.store.form()"
            :options="{
                preserveScroll: true,
            }"
            v-slot="{ processing }"
        >
            <Button type="submit" class="rounded-md" :disabled="processing">
                {{ processing ? 'Creating…' : 'Create backup now' }}
            </Button>
        </Form>

        <Heading
            variant="small"
            title="Automated backups"
            description="The scheduler creates a nightly dump and prunes backups older than the retention window."
        />

        <ul class="text-muted-foreground list-inside list-disc space-y-1 text-sm leading-relaxed">
            <li>New backup scheduled daily at {{ scheduleBackupAt }} (DATABASE_BACKUP_SCHEDULE_AT).</li>
            <li>Removal of files older than {{ retentionDays }} days runs daily at {{ schedulePruneAt }} (DATABASE_BACKUP_PRUNE_SCHEDULE_AT).</li>
            <li>Add a cron entry that runs Laravel’s scheduler every minute (see the scheduling documentation). Use <code class="rounded bg-muted px-1 py-0.5 font-mono text-xs">php artisan schedule:run</code> from your project directory.</li>
        </ul>

        <Heading variant="small" title="Stored backups" description="Downloads use your current login session." />

        <div
            class="divide-y rounded-xl border bg-card text-card-foreground shadow-xs"
            v-if="props.backups.length"
        >
            <div class="flex items-center justify-between gap-4 px-4 py-3 text-sm font-medium text-muted-foreground">
                <span>Filename</span>
                <span class="hidden shrink-0 sm:inline">Created</span>
                <span class="hidden shrink-0 sm:inline">Size</span>
                <span />
            </div>
            <div
                v-for="backup in props.backups"
                :key="backup.filename"
                class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm sm:flex-nowrap"
            >
                <span class="break-all font-mono text-xs sm:min-w-[12rem] sm:flex-1">{{ backup.filename }}</span>
                <span class="shrink-0 text-muted-foreground">
                    {{
                        new Date(backup.modified * 1000).toLocaleString(undefined, {
                            dateStyle: 'medium',
                            timeStyle: 'short',
                        })
                    }}
                </span>
                <span class="hidden shrink-0 sm:inline">{{ formatBytes(backup.size) }}</span>
                <Button variant="outline" size="sm" as-child class="ml-auto shrink-0">
                    <a :href="DatabaseBackupController.download.url(backup.filename)">Download</a>
                </Button>
            </div>
        </div>
        <p v-else class="text-muted-foreground text-sm leading-relaxed">No backups stored yet.</p>
    </div>
</template>
