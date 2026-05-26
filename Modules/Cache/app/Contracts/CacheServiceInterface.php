<?php

namespace Modules\Cache\Contracts;

interface CacheServiceInterface
{
    public function remember(string $namespace, string $key, int $seconds, callable $callback): mixed;

    public function rememberForever(string $namespace, string $key, callable $callback): mixed;

    public function rememberModel(
        string $modelClass,
        int $seconds,
        callable $callback,
        ?string $key = null,
        ?string $namespace = null,
    ): mixed;

    public function forget(string $namespace, string $key): void;

    public function forgetNamespace(string $namespace): void;
}
