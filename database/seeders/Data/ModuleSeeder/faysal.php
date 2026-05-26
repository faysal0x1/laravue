<?php

declare(strict_types=1);

/**
 * Faysal’s module rows — edit only this file to add or change these entries.
 *
 * Keep `id` values unique across emon, jayead, and faysal (see ModuleSeeder constants).
 */
return [
    [
        'id' => 201,
        'title' => 'Settings',
        'route_name' => null,
        'icon' => 'Settings',
        'permission' => null,
        'sort_order' => 60,
    ],
    [
        'id' => 202,
        'title' => 'Audit Log',
        'route_name' => 'audit-logs.index',
        'icon' => 'History',
        'permission' => 'audit-logs.view',
        'sort_order' => 70,
    ],
    [
        'id' => 202,
        'title' => 'Pulse',
        'route_name' => 'pulse',
        'icon' => 'Activity',
        'permission' => 'pulse.view',
        'sort_order' => 75,
    ],
];
