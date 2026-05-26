<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import { dashboard, pulse } from '@/routes';

defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    period: {
        type: String,
        required: true,
    },
});

const periods = [
    { value: '1_hour', label: '1 hour' },
    { value: '6_hours', label: '6 hours' },
    { value: '24_hours', label: '24 hours' },
    { value: '7_days', label: '7 days' },
];

defineOptions({
    layout: () => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Pulse',
                href: pulse(),
            },
        ],
    }),
});
</script>

<template>
    <Head title="Pulse" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Application Pulse</h1>
                <p class="text-muted-foreground text-sm">
                    Live metrics (Vue). Ensure workers ingest data:
                    <code class="rounded bg-muted px-1 py-0.5 text-xs">php artisan pulse:work</code>
                    and snapshots:
                    <code class="rounded bg-muted px-1 py-0.5 text-xs">php artisan pulse:check</code>.
                </p>
            </div>
            <nav class="flex flex-wrap gap-2">
                <Link
                    v-for="p in periods"
                    :key="p.value"
                    :href="pulse({ query: { period: p.value } })"
                    preserve-scroll
                    class="rounded-lg border px-3 py-1.5 text-sm transition-colors"
                    :class="
                        period === p.value
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-sidebar-border/70 hover:bg-muted/60 dark:border-sidebar-border'
                    "
                >
                    {{ p.label }}
                </Link>
            </nav>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <Card class="gap-0 py-0">
                <CardHeader class="border-b py-4">
                    <CardTitle>Slow requests</CardTitle>
                    <CardDescription>
                        Threshold {{ metrics.config?.slow_requests?.threshold ?? '—' }} ms · sample rate
                        {{ metrics.config?.slow_requests?.sample_rate ?? '—' }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">Method</th>
                                <th class="px-4 py-2 font-medium">URI</th>
                                <th class="px-4 py-2 font-medium">Slowest</th>
                                <th class="px-4 py-2 font-medium">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.slowRequests"
                                :key="i"
                                class="border-b border-border/60 last:border-0"
                            >
                                <td class="px-4 py-2 font-mono text-xs">{{ row.method }}</td>
                                <td class="max-w-[14rem] truncate px-4 py-2 font-mono text-xs" :title="row.uri">
                                    {{ row.uri }}
                                </td>
                                <td class="px-4 py-2">{{ row.slowest }} ms</td>
                                <td class="px-4 py-2">{{ row.count }}</td>
                            </tr>
                            <tr v-if="!metrics.slowRequests?.length">
                                <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                    No slow requests in this window.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0">
                <CardHeader class="border-b py-4">
                    <CardTitle>Slow queries</CardTitle>
                    <CardDescription>
                        Threshold from Pulse config · {{ metrics.slowQueries?.length ?? 0 }} rows
                    </CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">Slowest</th>
                                <th class="px-4 py-2 font-medium">Count</th>
                                <th class="px-4 py-2 font-medium">SQL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.slowQueries"
                                :key="i"
                                class="border-b border-border/60 align-top last:border-0"
                            >
                                <td class="px-4 py-2 whitespace-nowrap">{{ row.slowest }} ms</td>
                                <td class="px-4 py-2">{{ row.count }}</td>
                                <td class="max-w-xl px-4 py-2 font-mono text-xs break-all">
                                    {{ row.sql }}
                                </td>
                            </tr>
                            <tr v-if="!metrics.slowQueries?.length">
                                <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                    No slow queries in this window.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0">
                <CardHeader class="border-b py-4">
                    <CardTitle>Exceptions</CardTitle>
                    <CardDescription>Latest occurrence uses aggregated timestamps.</CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">Class</th>
                                <th class="px-4 py-2 font-medium">Count</th>
                                <th class="px-4 py-2 font-medium">Latest</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.exceptions"
                                :key="i"
                                class="border-b border-border/60 last:border-0"
                            >
                                <td class="max-w-md truncate px-4 py-2 font-mono text-xs" :title="row.class">
                                    {{ row.class }}
                                </td>
                                <td class="px-4 py-2">{{ row.count }}</td>
                                <td class="px-4 py-2 text-xs">{{ row.latest }}</td>
                            </tr>
                            <tr v-if="!metrics.exceptions?.length">
                                <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                    No exceptions recorded.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0">
                <CardHeader class="border-b py-4">
                    <CardTitle>Top users (requests)</CardTitle>
                    <CardDescription>Pulse user request attribution.</CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">User</th>
                                <th class="px-4 py-2 font-medium">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.usage?.requests"
                                :key="i"
                                class="border-b border-border/60 last:border-0"
                            >
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <img
                                            :src="row.user.avatar"
                                            alt=""
                                            class="size-8 rounded-full"
                                            width="32"
                                            height="32"
                                        />
                                        <div>
                                            <div class="font-medium">{{ row.user.name }}</div>
                                            <div class="text-muted-foreground text-xs">{{ row.user.extra }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">{{ row.count }}</td>
                            </tr>
                            <tr v-if="!metrics.usage?.requests?.length">
                                <td colspan="2" class="px-4 py-8 text-center text-muted-foreground">
                                    No usage data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 xl:col-span-2">
                <CardHeader class="border-b py-4">
                    <CardTitle>Cache</CardTitle>
                    <CardDescription class="max-w-3xl space-y-1">
                        <span>
                            Hits: {{ metrics.cache?.totals?.hits ?? 0 }} · Misses:
                            {{ metrics.cache?.totals?.misses ?? 0 }}
                        </span>
                        <span class="block text-muted-foreground">
                            A miss means the key was not present when read (first load after deploy, TTL
                            expired, cache cleared, or permissions/sidebar cache invalidated). That is
                            normal—not an application error.
                        </span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">Key pattern</th>
                                <th class="px-4 py-2 font-medium">Hits</th>
                                <th class="px-4 py-2 font-medium">Misses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.cache?.keys"
                                :key="i"
                                class="border-b border-border/60 last:border-0"
                            >
                                <td class="max-w-xl truncate px-4 py-2 font-mono text-xs" :title="row.key">
                                    {{ row.key }}
                                </td>
                                <td class="px-4 py-2">{{ row.hits }}</td>
                                <td class="px-4 py-2">{{ row.misses }}</td>
                            </tr>
                            <tr v-if="!metrics.cache?.keys?.length">
                                <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                    No per-key cache metrics.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 xl:col-span-2">
                <CardHeader class="border-b py-4">
                    <CardTitle>Queues (time series)</CardTitle>
                    <CardDescription>Aggregated counts per queue state.</CardDescription>
                </CardHeader>
                <CardContent class="px-6 pb-4">
                    <pre
                        class="max-h-64 overflow-auto rounded-lg border bg-muted/40 p-4 text-xs leading-relaxed"
                    ><code>{{ JSON.stringify(metrics.queues, null, 2) }}</code></pre>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 xl:col-span-2">
                <CardHeader class="border-b py-4">
                    <CardTitle>Servers</CardTitle>
                    <CardDescription>Requires pulse:check / Horizon-style reporting.</CardDescription>
                </CardHeader>
                <CardContent class="px-6 pb-4">
                    <div v-if="metrics.servers?.length" class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="(srv, i) in metrics.servers"
                            :key="i"
                            class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                        >
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="font-semibold">{{ srv.name }}</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="
                                        srv.recently_reported
                                            ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    {{ srv.recently_reported ? 'live' : 'stale' }}
                                </span>
                            </div>
                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                <dt class="text-muted-foreground">CPU</dt>
                                <dd>{{ srv.cpu_current }}%</dd>
                                <dt class="text-muted-foreground">Memory</dt>
                                <dd>{{ srv.memory_current }} / {{ srv.memory_total }} MB</dd>
                                <dt class="text-muted-foreground">Updated</dt>
                                <dd class="text-xs">{{ srv.updated_at }}</dd>
                            </dl>
                        </div>
                    </div>
                    <p v-else class="text-muted-foreground text-sm">No server snapshots yet.</p>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0">
                <CardHeader class="border-b py-4">
                    <CardTitle>Slow jobs</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">Job</th>
                                <th class="px-4 py-2 font-medium">Slowest</th>
                                <th class="px-4 py-2 font-medium">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.slowJobs"
                                :key="i"
                                class="border-b border-border/60 last:border-0"
                            >
                                <td class="max-w-xs truncate px-4 py-2 font-mono text-xs">{{ row.job }}</td>
                                <td class="px-4 py-2">{{ row.slowest }} ms</td>
                                <td class="px-4 py-2">{{ row.count }}</td>
                            </tr>
                            <tr v-if="!metrics.slowJobs?.length">
                                <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                    No slow jobs.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0">
                <CardHeader class="border-b py-4">
                    <CardTitle>Slow outgoing HTTP</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto px-0 pb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="px-4 py-2 font-medium">Method</th>
                                <th class="px-4 py-2 font-medium">URI</th>
                                <th class="px-4 py-2 font-medium">Slowest</th>
                                <th class="px-4 py-2 font-medium">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, i) in metrics.slowOutgoingRequests"
                                :key="i"
                                class="border-b border-border/60 last:border-0"
                            >
                                <td class="px-4 py-2 font-mono text-xs">{{ row.method }}</td>
                                <td class="max-w-sm truncate px-4 py-2 font-mono text-xs">{{ row.uri }}</td>
                                <td class="px-4 py-2">{{ row.slowest }} ms</td>
                                <td class="px-4 py-2">{{ row.count }}</td>
                            </tr>
                            <tr v-if="!metrics.slowOutgoingRequests?.length">
                                <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                    No slow outgoing requests.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
