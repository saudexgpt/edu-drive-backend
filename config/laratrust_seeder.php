<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'super' => [
            'users' => 'c,r,u,d',
            'customers' => 'c,r,u,d',
            'sales_target' => 'c,r,u,d',
            'sales' => 'd',
            'customer_types' => 'c,r,u,d',
            'payments' => 'c,r,u,d',
            'products' => 'r',
            'regions' => 'c,r,u,d',
            'teams' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'admin' => [
            'users' => 'c,r,u,d',
            'customers' => 'c,r,u,d',
            'customer_types' => 'c,r,u,d',
            'payments' => 'c,r,u,d',
            'products' => 'r',
            'regions' => 'c,r,u,d',
            'teams' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'sales_rep' => [
            'customers' => 'c,r,u',
            'sales' => 'c,r,u,d',
            'products' => 'r',
            'profile' => 'r,u',
        ],
        'customer' => [
            'profile' => 'r,u',
        ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]
];
