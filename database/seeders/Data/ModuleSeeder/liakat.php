<?php

declare(strict_types=1);

/**
 * Emon’s module rows — edit only this file to add or change these entries.
 *
 * Keep `id` values unique across emon, jayead, and faysal (see ModuleSeeder constants).
 */
return [
    [
        'id' => 100,
        'title' => 'Dashboard',
        'route_name' => 'dashboard',
        'icon' => 'LayoutGrid',
        'permission' => null,
        'sort_order' => 10,
    ],
    [
        'id' => 101,
        'title' => 'Users',
        'route_name' => null,
        'icon' => 'Users',
        'permission' => 'users.view',
        'sort_order' => 20,
    ],
    [
        'id' => 103,
        'title' => 'Roles',
        'route_name' => 'roles.index',
        'icon' => 'Users',
        'permission' => 'roles.view',
        'sort_order' => 40,
    ],
    [
        'id' => 104,
        'title' => 'To Do List',
        'route_name' => 'todo.index',
        'icon' => 'Check',
        'permission' => 'todo.view',
        'sort_order' => 50,
    ],
    [
        'id' => 301,
        'title' => 'Reports',
        'route_name' => 'reports.index',
        'icon' => 'BarChart3',
        'permission' => 'reports.view',
        'sort_order' => 40,
    ],
    [
        'id' => 302,
        'title' => 'Support',
        'route_name' => 'support-tickets.index',
        'icon' => 'LifeBuoy',
        'permission' => 'support-tickets.view',
        'sort_order' => 50,
    ],
    [
        'id' => 303,
        'title' => 'Settings',
        'route_name' => null,
        'icon' => 'Settings',
        'permission' => null,
        'sort_order' => 55,
    ],
];
