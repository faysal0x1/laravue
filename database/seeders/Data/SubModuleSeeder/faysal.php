<?php

declare(strict_types=1);

/**
 * Faysal’s sub-module rows — edit only this file.
 *
 * `module_id` 6 = Settings. Sub-module id 4.
 */
return [
    [
        'id' => 210,
        'module_id' => 101,
        'title' => 'Users',
        'route_name' => 'users.index',
        'icon' => null,
        'permission' => 'users.view',
        'sort_order' => 10,
    ],
   
    [
        'id' => 201,
        'module_id' => 303,
        'title' => 'Database backup',
        'route_name' => 'database-backup.edit',
        'icon' => null,
        'permission' => null,
        'sort_order' => 40,
    ],
     [
        'id' => 100,
        'module_id' => 303,
        'title' => 'Profile',
        'route_name' => 'profile.edit',
        'icon' => null,
        'permission' => 'settings.profile',
        'sort_order' => 10,
    ],
    [
        'id' => 101,
        'module_id' => 303,
        'title' => 'Security',
        'route_name' => 'security.edit',
        'icon' => null,
        'permission' => 'settings.password',
        'sort_order' => 20,
    ],
    [
        'id' => 102,
        'module_id' => 303,
        'title' => 'General',
        'route_name' => 'general-settings.edit',
        'icon' => null,
        'permission' => 'settings.view',
        'sort_order' => 30,
    ],
];
