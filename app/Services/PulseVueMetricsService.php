<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Recorders\SlowJobs as SlowJobsRecorder;
use Laravel\Pulse\Recorders\SlowOutgoingRequests as SlowOutgoingRequestsRecorder;
use Laravel\Pulse\Recorders\SlowQueries as SlowQueriesRecorder;
use Laravel\Pulse\Recorders\SlowRequests as SlowRequestsRecorder;

class PulseVueMetricsService
{
    /**
     * Build serialized metrics for the Vue Pulse dashboard (same underlying data as Laravel Pulse Livewire cards).
     *
     * @param  '1_hour'|'6_hours'|'24_hours'|'7_days'  $period
     * @return array<string, mixed>
     */
    public function gather(string $period): array
    {
        $interval = $this->interval($period);

        return [
            'slowRequests' => $this->slowRequests($interval),
            'slowQueries' => $this->slowQueries($interval),
            'slowJobs' => $this->slowJobs($interval),
            'slowOutgoingRequests' => $this->slowOutgoingRequests($interval),
            'exceptions' => $this->exceptions($interval),
            'usage' => $this->usage($interval),
            'cache' => $this->cache($interval),
            'queues' => $this->queuesGraph($interval),
            'servers' => $this->servers($interval),
            'config' => [
                'slow_requests' => Config::get('pulse.recorders.'.SlowRequestsRecorder::class),
                'slow_queries' => Config::get('pulse.recorders.'.SlowQueriesRecorder::class),
            ],
        ];
    }

    /**
     * @param  '1_hour'|'6_hours'|'24_hours'|'7_days'  $period
     */
    public function interval(string $period): CarbonInterval
    {
        return CarbonInterval::hours(match ($period) {
            '6_hours' => 6,
            '24_hours' => 24,
            '7_days' => 168,
            default => 1,
        });
    }

    /**
     * @return list<array{uri: string, method: string, action: ?string, count: int, slowest: int, threshold: int}>
     */
    protected function slowRequests(CarbonInterval $interval): array
    {
        return Pulse::aggregate('slow_request', ['max', 'count'], $interval, 'max', 'desc', 101)
            ->map(function ($row) {
                [$method, $uri, $action] = json_decode((string) $row->key, flags: JSON_THROW_ON_ERROR);

                return [
                    'uri' => $uri,
                    'method' => $method,
                    'action' => $action,
                    'count' => (int) $row->count,
                    'slowest' => (int) $row->max,
                    'threshold' => $this->threshold($uri, SlowRequestsRecorder::class),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{sql: string, location: ?string, count: int, slowest: int, threshold: int}>
     */
    protected function slowQueries(CarbonInterval $interval): array
    {
        return Pulse::aggregate('slow_query', ['max', 'count'], $interval, 'max', 'desc', 101)
            ->map(function ($row) {
                [$sql, $location] = json_decode((string) $row->key, flags: JSON_THROW_ON_ERROR);

                return [
                    'sql' => $sql,
                    'location' => $location,
                    'count' => (int) $row->count,
                    'slowest' => (int) $row->max,
                    'threshold' => $this->threshold($sql, SlowQueriesRecorder::class),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{job: string, count: int, slowest: int, threshold: int}>
     */
    protected function slowJobs(CarbonInterval $interval): array
    {
        return Pulse::aggregate('slow_job', ['max', 'count'], $interval, 'max', 'desc', 101)
            ->map(fn ($row) => [
                'job' => $row->key,
                'count' => (int) $row->count,
                'slowest' => (int) $row->max,
                'threshold' => $this->threshold($row->key, SlowJobsRecorder::class),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{method: string, uri: string, count: int, slowest: int, threshold: int}>
     */
    protected function slowOutgoingRequests(CarbonInterval $interval): array
    {
        return Pulse::aggregate('slow_outgoing_request', ['max', 'count'], $interval, 'max', 'desc', 101)
            ->map(function ($row) {
                [$method, $uri] = json_decode((string) $row->key, flags: JSON_THROW_ON_ERROR);

                return [
                    'method' => $method,
                    'uri' => $uri,
                    'count' => (int) $row->count,
                    'slowest' => (int) $row->max,
                    'threshold' => $this->threshold($uri, SlowOutgoingRequestsRecorder::class),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{class: string, location: ?string, count: int, latest: string}>
     */
    protected function exceptions(CarbonInterval $interval): array
    {
        return Pulse::aggregate('exception', ['max', 'count'], $interval, 'count', 'desc', 101)
            ->map(function ($row) {
                [$class, $location] = json_decode((string) $row->key, flags: JSON_THROW_ON_ERROR);

                return [
                    'class' => $class,
                    'location' => $location,
                    'count' => (int) $row->count,
                    'latest' => CarbonImmutable::createFromTimestamp($row->max)->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     requests: list<array{user_key: string|int|null, user: array{name: string, extra: string, avatar: string}, count: int}>,
     *     slow_requests: list<array{user_key: string|int|null, user: array{name: string, extra: string, avatar: string}, count: int}>,
     *     jobs: list<array{user_key: string|int|null, user: array{name: string, extra: string, avatar: string}, count: int}>
     * }
     */
    protected function usage(CarbonInterval $interval): array
    {
        return [
            'requests' => $this->usageForType('user_request', $interval),
            'slow_requests' => $this->usageForType('slow_user_request', $interval),
            'jobs' => $this->usageForType('user_job', $interval),
        ];
    }

    /**
     * @return list<array{user_key: string|int|null, user: array{name: string, extra: string, avatar: string}, count: int}>
     */
    protected function usageForType(string $type, CarbonInterval $interval): array
    {
        $counts = Pulse::aggregate($type, 'count', $interval, null, 'desc', 10);
        $users = Pulse::resolveUsers($counts->pluck('key'));

        return $counts
            ->map(function ($row) use ($users) {
                $u = $users->find($row->key);

                return [
                    'user_key' => $row->key,
                    'user' => [
                        'name' => $u->name ?? '',
                        'extra' => $u->extra ?? '',
                        'avatar' => $u->avatar ?? '',
                    ],
                    'count' => (int) $row->count,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{totals: array{hits: int, misses: int}, keys: list<array{key: string, hits: int, misses: int}>}
     */
    protected function cache(CarbonInterval $interval): array
    {
        $totals = Pulse::aggregateTotal(['cache_hit', 'cache_miss'], 'count', $interval);
        $hits = 0;
        $misses = 0;
        if ($totals instanceof Collection) {
            $hits = (int) $totals->get('cache_hit', 0);
            $misses = (int) $totals->get('cache_miss', 0);
        }

        $keys = Pulse::aggregateTypes(['cache_hit', 'cache_miss'], 'count', $interval)
            ->map(fn ($row) => [
                'key' => $row->key,
                'hits' => (int) ($row->cache_hit ?? 0),
                'misses' => (int) ($row->cache_miss ?? 0),
            ])
            ->values()
            ->all();

        return [
            'totals' => [
                'hits' => $hits,
                'misses' => $misses,
            ],
            'keys' => $keys,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function queuesGraph(CarbonInterval $interval): array
    {
        $queues = Pulse::graph(
            ['queued', 'processing', 'processed', 'released', 'failed'],
            'count',
            $interval,
        );

        return json_decode(json_encode($queues), true) ?? [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function servers(CarbonInterval $interval): array
    {
        $graphs = Pulse::graph(['cpu', 'memory'], 'avg', $interval);

        return Pulse::values('system')
            ->map(function ($system, $slug) use ($graphs) {
                $values = json_decode((string) $system->value, flags: JSON_THROW_ON_ERROR);
                $cpuSeries = $graphs->get($slug)?->get('cpu') ?? collect();
                $memorySeries = $graphs->get($slug)?->get('memory') ?? collect();

                return [
                    'name' => (string) $values->name,
                    'cpu_current' => (int) $values->cpu,
                    'cpu' => json_decode(json_encode($cpuSeries), true) ?? [],
                    'memory_current' => (int) $values->memory_used,
                    'memory_total' => (int) $values->memory_total,
                    'memory' => json_decode(json_encode($memorySeries), true) ?? [],
                    'storage' => json_decode(json_encode($values->storage), true) ?? [],
                    'updated_at' => CarbonImmutable::createFromTimestamp($system->timestamp)->toIso8601String(),
                    'recently_reported' => CarbonImmutable::createFromTimestamp($system->timestamp)
                        ->isAfter(now()->subSeconds(30)),
                ];
            })
            ->values()
            ->sortBy('name')
            ->values()
            ->all();
    }

    protected function threshold(string $key, string $recorderClass): int
    {
        $config = Config::get("pulse.recorders.{$recorderClass}.threshold", 1000);

        if (! is_array($config)) {
            return (int) $config;
        }

        $custom = collect($config)
            ->except(['default'])
            ->first(fn ($threshold, $pattern) => preg_match($pattern, $key) === 1);

        return (int) ($custom ?? $config['default'] ?? 1000);
    }
}
