<?php

namespace Modules\Cache\Traits;

use Modules\Cache\Contracts\CacheServiceInterface;

trait HasModelCache
{
    public static function bootHasModelCache(): void
    {
        static::saved(function ($model): void {
            $model->flushModelCache();
        });

        static::deleted(function ($model): void {
            $model->flushModelCache();
        });
    }

    public static function cacheNamespace(): string
    {
        return (new static)->getTable();
    }

    public function flushModelCache(): void
    {
        app(CacheServiceInterface::class)->forgetNamespace(static::cacheNamespace());
    }
}
