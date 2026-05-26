<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar navigation cache
    |--------------------------------------------------------------------------
    |
    | Navigation is built from `modules` and `sub_modules` tables and cached.
    | TTL in seconds (default 1 hour). Set to 0 to disable caching (not recommended).
    |
    */

    'cache_key' => env('SIDEBAR_NAV_CACHE_KEY', 'sidebar.navigation'),

    'cache_ttl' => (int) env('SIDEBAR_NAV_CACHE_TTL', 3600),

];
