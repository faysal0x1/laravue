<?php

namespace Modules\Cache\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Cache\Contracts\CacheServiceInterface;

class CacheService implements CacheServiceInterface
{
    private function resolveNamespace(string $modelClass, ?string $namespace = null): string
    {
        if ($namespace) {
            return $namespace;
        }

        if (is_subclass_of($modelClass, Model::class) && method_exists($modelClass, 'cacheNamespace')) {
            /** @var string $resolved */
            $resolved = $modelClass::cacheNamespace();

            return $resolved;
        }

        /** @var Model $model */
        $model = new $modelClass;

        return $model->getTable();
    }

    private function defaultRequestKey(?Request $request): string
    {
        if (! $request) {
            return 'request:cli';
        }

        return 'request:'.md5($request->method().'|'.$request->fullUrl());
    }

    private function fullKey(string $namespace, string $key): string
    {
        return "{$namespace}:{$key}";
    }

    private function indexKey(string $namespace): string
    {
        return "{$namespace}:__keys";
    }

    private function registerKey(string $namespace, string $fullKey): void
    {
        $indexKey = $this->indexKey($namespace);
        $keys = Cache::get($indexKey, []);
        $keys[$fullKey] = true;
        Cache::forever($indexKey, $keys);
    }

    public function remember(string $namespace, string $key, int $seconds, callable $callback): mixed
    {
        $fullKey = $this->fullKey($namespace, $key);
        $this->registerKey($namespace, $fullKey);

        return Cache::remember($fullKey, now()->addSeconds($seconds), $callback);
    }

    public function rememberForever(string $namespace, string $key, callable $callback): mixed
    {
        $fullKey = $this->fullKey($namespace, $key);
        $this->registerKey($namespace, $fullKey);

        return Cache::rememberForever($fullKey, $callback);
    }

    public function rememberModel(
        string $modelClass,
        int $seconds,
        callable $callback,
        ?string $key = null,
        ?string $namespace = null,
    ): mixed {
        $request = request();
        $resolvedNamespace = $this->resolveNamespace($modelClass, $namespace);
        $resolvedKey = $key ?? $this->defaultRequestKey($request);

        return $this->remember($resolvedNamespace, $resolvedKey, $seconds, $callback);
    }

    public function forget(string $namespace, string $key): void
    {
        Cache::forget($this->fullKey($namespace, $key));
    }

    public function forgetNamespace(string $namespace): void
    {
        $indexKey = $this->indexKey($namespace);
        $keys = Cache::get($indexKey, []);

        foreach (array_keys($keys) as $key) {
            Cache::forget($key);
        }

        Cache::forget($indexKey);
    }
}
